<?php

class SearchHandler
{
  protected $params;
  protected $collection;
  protected $luceneObject;

  public function __construct( array $params, Collection $collection )
  {
    $this->params = $params;
    $this->collection = $collection;

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
  
  protected function getIndexLevel()
  {
    if (
      isset($this->params['l'])
      && in_array($this->params['l'], $this->collection->getIndexLevels())
    ) {
      return $this->params['l'];
    }
    elseif ($this->collection->getDefaultIndexLevel()) {
      return $this->collection->getDefaultIndexLevel();
    }
    else {
      $search_levels = $this->collection->getIndexLevels();
      return $search_levels[0];
    }
  }
}