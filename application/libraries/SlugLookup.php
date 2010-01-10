<?php

class SlugLookup
{
  protected $filepath;
  protected $lockFilename;
  protected $pdo;
  protected $collection;

  function __construct( Collection $collection )
  {
    $this->collection = $collection;

    $this->filepath = APPPATH . 'data/';
    $this->lockFilename = $this->filepath . $this->collection->getName()
                        . '_slugs.lck';

    $db_filename = $this->filepath . $collection->getName()
                . '_slugs.db';

    if (! file_exists( $db_filename ) && ! is_writable( $this->filepath )) {
      throw new Exception("Could not write to data path $this->filepath");
    }

    $this->pdo = new PDO( 'sqlite:' . $db_filename );
    
    ($elements = $collection->getConfig('slug_metadata_elements'))
      or $elements = array( 'Title' );

    $elements_string = serialize( $elements );

    $query = 'SELECT value FROM metadata WHERE key="elements_string"';
    $stmt = $this->pdo->query( $query );

    if (! $stmt || $stmt->fetchColumn() != $elements_string) {
      // changed metadata settings since last build; backup and rebuild
      copy( $db_filename, $db_filename . '.bak' );
      $this->buildFull();

      return;
    }


    $query  = 'SELECT value FROM metadata WHERE key="build_date"';
    $stmt = $this->pdo->query( $query );

    if (! $stmt) {
      // database corrupt or absent; full build
      $this->buildFull();
    }
    
    $build_date = $stmt->fetchColumn();

    if ($collection->getBuildCfg()->getBuildDate() > $build_date) {
      // collection was built since last slug build; do incremental build
      $this->buildIncremental();

      return;
    }
  }

  public function retrieveSlug( $id )
  {
    $query = 'SELECT slug FROM slugs WHERE key=?';
    $stmt = $this->pdo->prepare( $query );
    $stmt->execute( array( $id ) );

    return $stmt->fetchColumn();
  }

  public function retrieveId( $slug_string )
  {
    $query = 'SELECT key FROM slugs WHERE slug=?';
    $stmt = $this->pdo->prepare( $query );
    $stmt->execute( array( $slug_string ) );

    return $stmt->fetchColumn();
  }

  protected function buildFull()
  {
    if ( $this->otherBuildSucceeded() ) {
      return false;
    }

    $this->lock();

    ($elements = $this->collection->getConfig('slug_metadata_elements'))
      or $elements = array( 'Title' );

    $elements_string = serialize( $elements );
    $build_date = $this->collection->getBuildCfg()->getBuildDate();

    if (! preg_match( '/^\d+$/', $build_date )) {
      throw new Exception('Invalid build date');
    }

    $query = <<<EOF
      DROP TABLE IF EXISTS metadata; DROP TABLE IF EXISTS slugs;
      CREATE TABLE metadata (key VARCHAR, value VARCHAR);
      CREATE TABLE slugs (key VARCHAR(50), slug VARCHAR);
      INSERT INTO metadata VALUES ('build_date', '$build_date');
EOF;

    $this->pdo->exec( $query );

    $query = 'INSERT INTO metadata VALUES ("elements_string", ?)';
    $stmt = $this->pdo->prepare( $query );
    $stmt->execute( array( $elements_string ) );

    // go through all [nodes?] and formulate & store slugs for them

    $this->buildIncremental();
  }

  public function buildIncremental()
  {
    $this->lock();

    $all_nodes = $this->collection->getInfodb()->getAllNodes();

    // TODO: check the slug formulation code; written without thorough testing, e.g. duplicate slug detection
    foreach ( $all_nodes as $key => $node ) {
      if ( substr( $key, 0, 2 ) == 'CL' || strpos( $key, '.' ) ) {
        // only do documents
        continue;
      }

      $query = 'SELECT slug FROM slugs';
      $stmt = $this->pdo->prepare( $query );
      $stmt->execute();
      $existing_slugs = $stmt->fetchAll( PDO::FETCH_COLUMN );

      $query = 'SELECT key FROM slugs WHERE key=?';
      $stmt = $this->pdo->prepare( $query );
      
      if (! $stmt->execute( array($key) ) ) {
        // no slug for this document yet

        // FIXME: use specified metadata fields
        $slug_base = self::toSlug( $node['Title'] );
        $slug = $slug_base;

        // check for existing identical slugs and suffix them
        $count = 2;
        while (isset( $existing_slugs[ $slug ] )) {
          $slug = "$slug_base-$count";
          $count++;
        }

        $query = 'INSERT INTO slugs VALUES (?, ?)';
        $stmt = $this->pdo->prepare( $query );
        $stmt->execute( array( $key, $slug ) );

        // FIXME: error handling

        $existing_slugs[] = $slug;
      }
    }

    $this->unlock();
  }

  protected function otherBuildSucceeded()
  {
    $lock_time = $this->getLockTime();
    
    if (! $lock_time) {
      return false;
    }

    if ( $lock_time > (time() - 8) ) {
      // recent build in progress; wait and see if it succeeds
      sleep( 7 );

      if (! $this->getLockTime() ) {
        // the other build succeeded
        return true;
      }

      // the other build apparently stopped; build again
    }

    return false;
  }

  protected function getLockTime()
  {
    if (! file_exists( $this->lockFilename ) ) {
      return false;
    }

    if (! $fh = fopen( $this->lockFilename, 'rb' )) {
      throw new Exception("Could not read lock file $this->lockFilename");
    }

    return fgets( $fh );
  }

  protected function lock()
  {
    if (
      ! file_exists( $this->lockFilename )
      && ! is_writable( $this->filepath )
    ) {
      throw new Exception("Could not write to data path $this->filepath");
    }

    $fh = fopen( $this->lockFilename, 'wb' );
    fwrite( $fh, time() );

    return true;
  }

  protected function unlock()
  {
    if ( file_exists( $this->lockFilename ) ) {
      unlink( $this->lockFilename );
    }

    return true;
  }

  protected static function toSlug( $string, $limit = 0, $spacer = '-' )
  {
    if (function_exists('iconv')) {
      $string = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    }

    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9-]/', $spacer, $slug);
    $slug = trim( $slug, $spacer );
    $slug = preg_replace("/$spacer+/", $spacer, $slug);
    $slug = self::stripStopwords($slug);

    if ($limit && is_int($limit)) {
      if (
        strlen($slug) > $limit
        && substr($slug, $limit, 1) != '-'
      ) {
        // chopped in middle of word
        preg_match("/^.{0,$limit}(?=-)/", $slug, $matches);
        $slug = $matches[0];
      }
      else {
        $slug = substr($slug, 0, $limit);
      }
    }

    return $slug;
  }

  protected static function stripStopwords( $string, $stopwords = array() )
  {
    // TODO: perhaps make Collection-specific, overridden in collections.yml
    // to allow for l10n
    if (!$stopwords) {
      $stopwords = array(
        'an',
        'a',
        'the',
        'of',
        'and',
      );
    }

    $pattern = '/\b(' . join('|', $stopwords) . ')-?\b/';

    return preg_replace( $pattern, '', $string );
  }
}
