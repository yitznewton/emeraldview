<?php

class QueryBuilder_Simple extends QueryBuilder
{
  public function getQuery()
  {
    if ($this->query) {
      return $this->query;
    }
    
    // run it against TX at regular boost, plus title with extra boost
    $query = Zend_Search_Lucene_Search_QueryParser::parse(
      $this->params['q']
    );
    
    $title_index = null;
    
    foreach ($this->collection->getIndexes() as $key => $index) {
      if (strpos( $index, 'Title' ) !== false) {
        $title_index = $key;
      }
    }
    
    if ($title_index) {
      $subquery_title = new Zend_Search_Lucene_Search_Query_Preprocessing_Term(
        $querystring, '', $title_index
      );
      
      $subquery_title->setBoost( 10 );
      $query->addSubquery( $subquery_title, null );
    }
    
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
