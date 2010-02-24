<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.net/emeraldview/index.php/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * QueryBuilder_Simple creates Zend_Search_Lucene objects for search, based on
 * the Collection and simple query parameters
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class QueryBuilder_Simple extends QueryBuilder
{
  /**
   * @return Zend_Search_Lucene_Search_Query
   */
  public function getQuery()
  {
    if ($this->query) {
      return $this->query;
    }

    $querystring = $this->params['q'];
    
    // run it against TX at regular boost, plus title with extra boost
    $query = Zend_Search_Lucene_Search_QueryParser::parse( $querystring );
    
    $title_indexes = array();
    
    foreach ( $this->collection->getIndexes() as $key => $index ) {
      if ( strpos( $index, 'Title' ) !== false ) {
        $title_indexes[] = $key;
      }
    }
    
    foreach ( $title_indexes as $title_index ) {
      $subquery_title = new Zend_Search_Lucene_Search_Query_Preprocessing_Term(
        $querystring, '', $title_index
      );
      
      // set to boost title fields collectively to 10
      $subquery_title->setBoost( 10 / count( $title_indexes ) );
      $query->addSubquery( $subquery_title, null );
    }
    
    return $this->query = $query;
  }
  
  /**
   * @return string
   */
  public function getDisplayQuery()
  {
    return $this->params['q'];
  }
}
