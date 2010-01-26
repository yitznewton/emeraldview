<?php

class QueryBuilder_Simple extends QueryBuilder
{
  public function getQuery()
  {
    if ($this->query) {
      return $this->query;
    }

    $querystring = $this->params['q'];
    
    // run it against TX at regular boost, plus title with extra boost
    $query = Zend_Search_Lucene_Search_QueryParser::parse( $querystring );
    
    // FIXME what if there are multiple title indexes (Title, dc.Title, etc)?
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
  
  public function getDisplayQuery()
  {
    return $this->params['q'];
  }
}
