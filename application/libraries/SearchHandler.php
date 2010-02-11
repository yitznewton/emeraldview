<?php

class SearchHandler
{
  protected $params;
  protected $collection;
  protected $queryBuilder;
  protected $indexLevel;
  protected $luceneObject;

  public function __construct( array $params, Collection $collection )
  {
    $this->params = $params;
    $this->collection = $collection;
    $this->queryBuilder = QueryBuilder::factory( $params, $collection );

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

  public function getParams()
  {
    return $this->params;
  }

  public function getCollection()
  {
    return $this->collection;
  }

  public function getQueryBuilder()
  {
    return $this->queryBuilder;
  }
  
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