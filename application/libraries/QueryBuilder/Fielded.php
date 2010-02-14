<?php

class QueryBuilder_Fielded extends QueryBuilder
{
  public function getQuery()
  {
    if ($this->query) {
      return $this->query;
    }

    $index = $this->params['i'];

    if ( ! in_array( $index, array_keys( $this->collection->getIndexes() ) ) ) {
      throw new Exception( 'Invalid index' );
    }

    Zend_Search_Lucene::setDefaultSearchField( $index );
    $query = Zend_Search_Lucene_Search_QueryParser::parse( $this->params['q'] );

    return $this->query = $query;
  }
  
  public function getDisplayQuery()
  {
    return $this->params['i'] . ':' . $this->params['q'];
  }
}
