<?php

class SlugLookup
{
  protected $pdo;

  function __construct( Collection $collection )
  {
    $filepath = APPPATH . 'data/';
    $filename = $filepath . $collection->getName()
                . '_slugs.db';

    if (! file_exists( $filename ) && ! is_writable( $filepath )) {
      throw new Exception("Could not write to data path $filepath");
    }

    $this->pdo = new PDO( 'sqlite:' . $filename );
    
    $query  = 'SELECT value FROM metadata WHERE key="build_date"';
    $stmt = $this->pdo->query( $query );

    if (! $stmt) {
      // database corrupt or absent; full build
      $this->build( true );
    }
    
    $build_date = $stmt->fetchColumn();

    if ($collection->getBuildCfg()->getBuildDate() > $build_date) {
      // collection was built since last slug build; do incremental build
      $this->build();
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

  protected function build( $full_build = false )
  {
    if ($full_build === true) {
      $query1 = 'DROP TABLE IF EXISTS metadata';
      $query2 = 'DROP TABLE IF EXISTS slugs';
      $query3 = 'CREATE TABLE metadata (key VARCHAR(25), value VARCHAR)';
      $query4 = 'CREATE TABLE slugs (key VARCHAR(25), value VARCHAR)';

      $this->pdo->exec( $query1 );
      $this->pdo->exec( $query2 );
      $this->pdo->exec( $query3 );
      $this->pdo->exec( $query4 );
    }
  }
}
