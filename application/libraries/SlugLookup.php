<?php

class SlugLookup
{
  protected static $buildLock = null;
  protected $collection;
  protected $pdo;

  function __construct( Collection $collection )
  {
    $this->collection = $collection;

    $db_filepath = APPPATH . 'data/';
    $db_filename = $db_filepath . $collection->getName()
                . '_slugs.db';

    if (! file_exists( $db_filename ) && ! is_writable( $db_filepath )) {
      throw new Exception("Could not write to data path $db_filepath");
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

      //return;
    }
    var_dump($elements_string);exit;

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
      // return;
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
    if ( isset( self::$buildLock ) && (self::$buildLock > (time() - 8)) ) {
      // recent build in progress; wait and see if it succeeds
      sleep( 7 );

      if (empty( self::$buildLock )) {
        // the other build succeeded
        return false;
      }
    }

    self::$buildLock = time();

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

    self::$buildLock = null;
  }
}
