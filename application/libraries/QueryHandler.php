<?php

class QueryHandler
{
  protected $queryBuilder;
  protected $zendSearchObject;
  protected $results;
  
  public function __construct( QueryBuilder $query_builder )
  {
    $this->queryBuilder = $query_builder;
    
    $level_prefix = substr( $this->queryBuilder->getLevel(), 0, 1 );
    $index_dir = $this->queryBuilder->getCollection()
                 ->getGreenstoneDirectory() . "/index/$level_prefix" . 'idx';
          
    if (!is_dir( $index_dir ) || !is_readable( $index_dir )) {
      throw new Exception("Could not read index directory $index_dir");
    }

    $b_and = Zend_Search_Lucene_Search_QueryParser::B_AND;
    Zend_Search_Lucene_Search_QueryParser::setDefaultOperator( $b_and );
    Zend_Search_Lucene_Analysis_Analyzer::setDefault(
      new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());

    $this->zendSearchObject = Zend_Search_Lucene::open( $index_dir );
  }
  
  public function query()
  {
    if (isset($this->results)) {
      return $this->results;
    }
    
    try {
      // suppress errors, as this can generate undefined-offset notices
      $results = @$this->zendSearchObject->find( $this->queryBuilder->getQuery() );
    }
    catch (Zend_Search_Lucene_Exception $e) {
      // malformed search
      
      // FIXME add more-specific handling
      return $this->results = array();
    }
    
    return $this->results = $results;
  }
}