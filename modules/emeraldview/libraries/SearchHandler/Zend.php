<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.org/emeraldview/index.php?title=License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b4
 * @package libraries
 */
/**
 * SearchHandler for Zend_Search_Lucene indexes
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class SearchHandler_Zend extends SearchHandler
{
  /**
   * The Zend_Search_Lucene interface to the appropriate index
   *
   * @var Zend_Search_Lucene
   */
  protected $luceneObject;

  /**
   * @param Query $query The Query to perform
   */
  protected function __construct( Query $query )
  {
    parent::__construct( $query );

    $collection = $query->getCollection();

    $level_prefix = substr( $this->getIndexLevel(), 0, 1 );
    $index_dir = $collection->getGreenstoneDirectory()
               . "/index/$level_prefix" . 'idx';

    if ( ! is_dir( $index_dir ) || ! is_readable( $index_dir ) ) {
      throw new Exception("Could not read index directory $index_dir");
    }

    $b_and = Zend_Search_Lucene_Search_QueryParser::B_AND;
    Zend_Search_Lucene_Search_QueryParser::setDefaultOperator( $b_and );
    Zend_Search_Lucene_Analysis_Analyzer::setDefault(
      new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());

    $this->luceneObject = Zend_Search_Lucene::open( $index_dir );
  }

  public function execute()
  {
    $query = $this->query->getQuerystring();
    
    try {
      $lucene_hits = @$this->luceneObject->find( $query );
    }
    catch (Zend_Search_Lucene_Exception $e) {
      // malformed search
      Kohana::log( 'error', $e->getMessage() );
      return array();
    }

    $this->totalHitCount = count( $lucene_hits );

    $lucene_hits = array_slice( $lucene_hits, $this->startAt-1, $this->hitsPerPage );

    $hits = array();

    foreach ( $lucene_hits as $lucene_hit ) {
      $hits[] = new Hit_Zend( $this, $lucene_hit );
    }

    //return array_slice( $hits, $start_at-1, $per_page );
    return $hits;
  }
}
