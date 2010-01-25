<?php

abstract class QueryBuilder
{
  protected $collection;
  protected $params;
  protected $query;
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
    $indexes = array_keys( $collection->getIndexes() );

    if ( array_key_exists( 'i', $params ) && in_array( $params['i'], $indexes ) ) {
      return new QueryBuilder_Fielded( $params, $collection );
    }
    elseif (array_key_exists( 'q1', $params )) {
      return new QueryBuilder_Boolean( $params, $collection );
    }
    elseif (array_key_exists( 'q', $params )) {
      return new QueryBuilder_Simple( $params, $collection );
    }
    else {
      return false;
    }
  }
}