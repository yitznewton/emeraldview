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
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * QueryBuilder_Fielded creates Zend_Search_Lucene objects for search, based on
 * the Collection and fielded query parameters
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class QueryBuilder_Fielded extends QueryBuilder
{
  /**
   * @return Zend_Search_Lucene_Search_Query
   */
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
  
  /**
   * @return string
   */
  public function getDisplayQuery()
  {
    return $this->params['i'] . ':' . $this->params['q'];
  }
}
