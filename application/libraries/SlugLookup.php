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

    $query = 'SELECT value FROM metadata WHERE key="slug_metadata_elements"';
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
      $this->buildFull;
    }
    
    $build_date = $stmt->fetchColumn();

    if ($collection->getBuildCfg()->getBuildDate() > $build_date) {
      // collection was built since last slug build; do incremental build
      $this->buildIncremental();

      return;
    }
  }

  protected function save( stdClass $slug )
  {
  }

  public function retrieveId( $slug_string )
  {
    // if already set, return

    // not set - compose based on slug metadata config
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
      CREATE TABLE slugs (key VARCHAR(25), base_slug VARCHAR, slug VARCHAR);
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
}
