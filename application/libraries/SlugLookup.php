<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.net/emeraldview/index.php/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * SlugLookup connects to, builds, and retrieves NodePage_DocumentSection
 * slug data from a SQLite file
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class SlugLookup
{
  /**
   * The path to the directory where the slug data files are located
   *
   * @var string
   */
  protected $filepath;
  /**
   * The name of the lock file
   *
   * @var string
   */
  protected $lockFilename;
  /**
   * @var PDO
   */
  protected $pdo;
  /**
   * @var Collection
   */
  protected $collection;
  /**
   * An associative array of root Node_Document ids and their slugs
   *
   * @var array
   */
  protected $slugs;

  /**
   * @param Collection $collection
   */
  function __construct( Collection $collection )
  {
    $this->collection = $collection;

    $this->filepath = APPPATH . 'data/';
    
    if ( ! file_exists( $this->filepath ) ) {
      mkdir( $this->filepath );
    }    

    $this->lockFilename = $this->filepath . $this->collection->getGreenstoneName()
                        . '_slugs.lck';

    $db_filename = $this->filepath . $collection->getGreenstoneName()
                . '_slugs.db';

    $is_new_db = file_exists( $db_filename ) ? false : true;
    
    if ( ! is_writable( $this->filepath ) ) {
      throw new Exception("Cannot write to data path $this->filepath");
    }

    if ( file_exists( $this->filepath && ! is_dir( $this->filepath ) ) ) {
      throw new Exception('Data path exists and is not a directory');
    }

    if ( ! file_exists( $this->filepath ) ) {
      mkdir( $this->filepath );
    }

    $this->pdo = new PDO( 'sqlite:' . $db_filename );
    $this->pdo->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );

    if ($is_new_db) {
      $this->buildFull();
      return;
    }
    
    $elements = $collection
                ->getConfig( 'slug_metadata_elements', array( 'Title' ) );

    $elements_string = serialize( $elements );

    $query = 'SELECT value FROM metadata WHERE key="elements_string"';
    
    try {
      $stmt = $this->pdo->query( $query );
    }
    catch (PDOException $e) {
      // database corrupt or absent; do full build
      copy( $db_filename, $db_filename . '.bak' );
      $this->buildFull();
      return;
    }

    if ( ! $stmt || $stmt->fetchColumn() != $elements_string ) {
      // changed metadata settings since last build; backup and rebuild
      copy( $db_filename, $db_filename . '.bak' );
      $this->buildFull();
      return;
    }

    $query  = 'SELECT value FROM metadata WHERE key="build_date"';

    try {
      $stmt = $this->pdo->query( $query );
    }
    catch (PDOException $e) {
      // database corrupt or absent; full build
      $this->buildFull();
      return;
    }

    if ( ! $stmt ) {
      copy( $db_filename, $db_filename . '.bak' );
      $this->buildFull();
      return;
    }
    
    $build_date = $stmt->fetchColumn();

    if ($collection->getBuildCfg()->getBuildDate() > $build_date) {
      // collection was built since last slug build; do incremental build
      $this->buildIncremental();
      return;
    }

    //$this->load();
  }

  /**
   * Returns the slug corresponding to the specified node id
   *
   * @param string $id
   * @return string
   */
  public function retrieveSlug( $id )
  {
    $query = 'SELECT slug FROM slugs WHERE key=?';
    $stmt = $this->pdo->prepare( $query );
    $stmt->execute( array( $id ) );

    return $stmt->fetchColumn();
  }

  /**
   * Returns the node id corresponding to the specified slug
   *
   * @param string $slug_string
   * @return string
   */
  public function retrieveId( $slug_string )
  {
    $query = 'SELECT key FROM slugs WHERE slug=?';
    $stmt = $this->pdo->prepare( $query );
    $stmt->execute( array( $slug_string ) );

    return $stmt->fetchColumn();
  }

  /**
   * Loads $this->slugs with data from slug database
   */
  protected function load()
  {
    Benchmark::start('a');
    $query = 'SELECT key, slug FROM slugs';
    $stmt = $this->pdo->query( $query );

    while ( $record = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
      $this->data[ $record['key'] ] = $record[ 'slug' ];
    }
    Benchmark::stop('a');
    var_dump(Benchmark::get('a'));
  }

  /**
   * Builds the slug database from scratch
   */
  protected function buildFull()
  {
    if ( $this->otherBuildSucceeded() ) {
      return;
    }

    $this->lock();

    $elements = $this->collection
                ->getConfig( 'slug_metadata_elements', array( 'Title' ) );

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

    $this->buildIncremental();
  }

  /**
   * Builds slugs for nodes which aren't yet slugged
   */
  protected function buildIncremental()
  {
    // go through all [nodes?] and formulate & store slugs for them
    
    $this->lock();

    $query = 'SELECT key, slug FROM slugs';
    $stmt = $this->pdo->prepare( $query );

    $stmt->execute();
    $existing_keys  = $stmt->fetchAll( PDO::FETCH_COLUMN );
    $stmt->execute();
    $existing_slugs = $stmt->fetchAll( PDO::FETCH_COLUMN, 1 );

    $all_nodes = Node_Document::getAllRootNodes( $this->collection );

    foreach ( $all_nodes as $node ) {
      if ( ! in_array( $node->getId(), $existing_keys ) ) {
        // no slug for this document yet

        $config_elements = $this->collection->getConfig( 'slug_metadata_elements' );
        $element = $node->getFirstFieldFound( $config_elements );

        if ( ! $element ) {
          $element = $node->getField( 'Title' );
        }

        if ( ! $element ) {
          $element = $node->getId();
        }

        $slug_generator = new SlugGenerator( $this->collection );
        $slug_base = $slug_generator->toSlug( $element );
        $slug = $slug_base;

        // check for existing identical slugs and suffix them
        $count = 2;
        while ( in_array( $slug, $existing_slugs ) ) {
          $slug = "$slug_base-$count";
          $count++;
        }

        try {
          $query = 'INSERT INTO slugs VALUES (?, ?)';
          $stmt = $this->pdo->prepare( $query );
          $stmt->execute( array( $node->getId(), $slug ) );
        }
        catch (Exception $e) {
          Log::add( 'error', 'insert into slug database failed' );
          throw $e;
        }

        $existing_keys[]  = $node->getId();
        $existing_slugs[] = $slug;
      }
    }

    $this->unlock();
  }

  /**
   * Returns whether an earlier build finished successfully
   *
   * @return boolean
   */
  protected function otherBuildSucceeded()
  {
    $lock_time = $this->getLockTime();
    
    if ( ! $lock_time ) {
      return false;
    }

    if ( $lock_time > (time() - 8) ) {
      // recent build in progress; wait and see if it succeeds
      sleep( 7 );

      if ( ! $this->getLockTime() ) {
        // the other build succeeded
        return true;
      }

      // the other build apparently stopped; build again
    }

    return false;
  }

  /**
   * Returns the time of the last call to lock()
   *
   * @return string
   */
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

  /**
   * Records the present time in a lock file
   */
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
  }

  /**
   * Removes the lock file
   */
  protected function unlock()
  {
    if ( file_exists( $this->lockFilename ) ) {
      unlink( $this->lockFilename );
    }
  }
}
