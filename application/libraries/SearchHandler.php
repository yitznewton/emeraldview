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
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * SearchHandler is a wrapper class which creates objects and delegates
 * responsibility for search functionality
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class SearchHandler
{
  /**
   * ASCII matching pattern for search term boundary detection -
   * must match the search term in the first backreference
   */
  const BOUNDARIES_PATTERN_ASCII   = '\\b(%s)\\b';
  /**
   * Unicode matching pattern for search term boundary detection -
   * must match the search term in the first backreference
   */
  const BOUNDARIES_PATTERN_UNICODE = '(?:[^_\pL\pN]|^)(%s)(?:[^_\pL\pN]|$)';

  /**
   * An array of the query parameters for this search
   *
   * @var array
   */
  protected $params;
  /**
   * The Collection being searched
   *
   * @var Collection
   */
  protected $collection;
  /**
   * A QueryBuilder representing this search
   *
   * @var QueryBuilder
   */
  protected $queryBuilder;
  /**
   * The level of node being searched (document or section)
   *
   * @var string
   */
  protected $indexLevel;
  /**
   * The Zend_Search_Lucene interface to the appropriate index
   *
   * @var Zend_Search_Lucene
   */
  protected $luceneObject;
  /**
   * The word boundary matching pattern to use with this search
   *
   * @var string
   */
  protected $boundariesPattern;

  /**
   * @param array $params An array of the query parameters
   * @param Collection $collection The Collection to search
   */
  public function __construct( array $params, Collection $collection )
  {
    $this->params = $params;
    $this->collection = $collection;
    $this->queryBuilder = QueryBuilder::factory( $params, $collection );
    $this->boundariesPattern = $collection->getConfig( 'boundaries_pattern', SearchHandler::BOUNDARIES_PATTERN_ASCII );

    $level_prefix = substr( $this->getIndexLevel(), 0, 1 );
    $index_dir = $collection->getGreenstoneDirectory()
               . "/index/$level_prefix" . 'idx';

    if (!is_dir( $index_dir ) || !is_readable( $index_dir )) {
      throw new Exception("Could not read index directory $index_dir");
    }

    $b_and = Zend_Search_Lucene_Search_QueryParser::B_AND;
    Zend_Search_Lucene_Search_QueryParser::setDefaultOperator( $b_and );
    Zend_Search_Lucene_Analysis_Analyzer::setDefault(
      new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());

    $this->luceneObject = Zend_Search_Lucene::open( $index_dir );
  }

  /**
   * Performs query and returns an array of Zend_Search_Lucene_Search_QueryHit
   * objects
   *
   * @return array
   */
  public function execute()
  {
    $query = $this->queryBuilder->getQuery();
    
    try {
      $lucene_hits = @$this->luceneObject->find( $query );
    }
    catch (Zend_Search_Lucene_Exception $e) {
      // malformed search
      return array();
    }

    $hits = array();

    foreach ($lucene_hits as $lucene_hit) {
      $hits[] = new Hit( $lucene_hit, $this );
    }

    return $hits;
  }

  /**
   * Returns an array of the query parameters
   *
   * @return array
   */
  public function getParams()
  {
    return $this->params;
  }

  /**
   * Returns the Collection to search
   *
   * @return Collection
   */
  public function getCollection()
  {
    return $this->collection;
  }

  /**
   * Returns the QueryBuilder for this search
   * 
   * @return QueryBuilder
   */
  public function getQueryBuilder()
  {
    return $this->queryBuilder;
  }
  
  /**
   * Returns the level of node being searched (document or section), building
   * it first if necessary
   *
   * @return string
   */
  protected function getIndexLevel()
  {
    if ( isset( $this->indexLevel ) ) {
      return $this->indexLevel;
    }

    $params = $this->getParams();
    $collection = $this->getCollection();

    if (
      isset($params['l'])
      && in_array($params['l'], $collection->getIndexLevels())
    ) {
      return $this->indexLevel = $params['l'];
    }
    elseif ($collection->getDefaultIndexLevel()) {
      return $this->indexLevel = $collection->getDefaultIndexLevel();
    }
    else {
      $search_levels = $collection->getIndexLevels();
      return $this->indexLevel = $search_levels[0];
    }
  }
}
