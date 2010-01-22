<?php

class SearchHandler
{
  protected $collection;
  protected $queryBuilder;
  protected $indexLevel;
  protected $luceneObject;

  public function __construct( array $params, Collection $collection )
  {
    $this->collection = $collection;
    $this->queryBuilder = QueryBuilder::factory( $params, $collection );
    $this->indexLevel = SearchHandler::getIndexLevel( $params, $collection );

    $level_prefix = substr( $this->indexLevel, 0, 1 );
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
      $hits[] = new Hit( $lucene_hit, $this->collection );
    }

    return $hits;
  }
  
  protected static function getIndexLevel( array $params, Collection $collection )
  {
    if (
      isset($params['l'])
      && in_array($params['l'], $collection->getIndexLevels())
    ) {
      return $params['l'];
    }
    elseif ($collection->getDefaultIndexLevel()) {
      return $collection->getDefaultIndexLevel();
    }
    else {
      $search_levels = $collection->getIndexLevels();
      return $search_levels[0];
    }
  }
}