<?php

abstract class QueryBuilder
{
  protected $collection;
  protected $query;
  protected $params;
  protected $rawTerms;
  
  protected function __construct( array $params, Collection $collection )
  {
    $this->params     = $params;
    $this->collection = $collection;
  }
  
  abstract public function getQuery();
  abstract public function getDisplayQuery();
  abstract public function getRawTerms();
  
  public static function factory( array $params, Collection $collection )
  {
    if ( empty( $params['q'] ) && empty( $params['q1'] ) ) {
      return false;
    }
    
    if (array_key_exists( 'i', $params )) {
      return new QueryBuilder_Fielded( $params, $collection );
    }
    elseif (array_key_exists( 'q1', $params )) {
      return new QueryBuilder_Boolean( $params, $collection );
    }
    else {
      return new QueryBuilder_Simple( $params, $collection );
    }
  }
  
  public function getCollection()
  {
    return $this->collection;
  }

  public function getLevel()
  {
    if (
      isset($params['l'])
      && in_array($params['l'], $this->collection->getIndexLevels())
    ) {
      return $params['l'];
    }
    elseif ($this->collection->getDefaultIndexLevel()) {
      return $this->collection->getDefaultIndexLevel();
    }
    else {
      $search_levels = $this->collection->getIndexLevels();
      return $search_levels[0];
    }
  }
  
  public function getParams()
  {
    return $this->params;
  }
}