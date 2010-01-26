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
  
  public function getRawTerms()
  {
    if ($this->rawTerms) {
      return $this->rawTerms;
    }

    $query_string = $this->getDisplayQuery();

    $pattern = '/ " \b (.+?) \b " | \S+ /iux';
    preg_match_all( $pattern, $query_string, $query_matches );

    $terms = array();

    for ($i = 0; $i < count( $query_matches[0] ); $i++) {
      if ($query_matches[1][$i]) {
        // matched a quoted segment
        $terms[] = $query_matches[1][$i];
      }
      else {
        $terms[] = $query_matches[0][$i];
      }
    }

    return $terms;
  }

  public function getDisplayQuery()
  {
    return $this->params['q'];
  }
}
