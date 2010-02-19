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
 * @version 0.2.0b1
 * @package libraries
 */
/**
 * QueryBuilder_Boolean creates Zend_Search_Lucene objects for search, based on
 * the Collection and boolean query parameters
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class QueryBuilder_Boolean extends QueryBuilder
{
  /**
   * @return Zend_Search_Lucene_Search_Query
   */
  public function getQuery()
  {
    if ($this->query) {
      return $this->query;
    }

    $this->query = new Zend_Search_Lucene_Search_Query_MultiTerm();

    $this->addTerm( 1 );

    if ( ! empty( $this->params['q2'] ) && ! empty( $this->params['i2'] ) ) {
      $this->addTerm( 2 );
    }

    if ( ! empty( $this->params['q3'] ) && ! empty( $this->params['i3'] ) ) {
      $this->addTerm( 3 );
    }

    $this->getDisplayQuery();
    return $this->query;
  }

  /**
   * @return string
   */
  public function getDisplayQuery()
  {
    $query = '';

    $terms = $this->getQuery()->getTerms();
    $signs = $this->getQuery()->getSigns();

    $query .= $terms[0]->field . ':' . $terms[0]->text;

    for ( $i = 1; $i < count( $terms ); $i++ ) {
      if ( $signs === null ) {
        // when Zend_Search_Lucene_Search_Query_MultiTerm::getSigns() returns
        // null and not an array, it means all terms are ANDed
        $query .= ' AND ';
      }
      elseif ( $signs[ $i ] === true ) {
        $query .= ' AND ';
      }
      elseif ( $signs[ $i ] === false ) {
        $query .= ' NOT ';
      }
      else {
        $query .= ' OR ';
      }

      $query .= $terms[ $i ]->field . ':' . $terms[ $i ]->text;
    }

    return $query;
  }

  /**
   * Adds a term of specifed index (e.g. 1, 2 or 3) to $this->query
   *
   * @param integer $index
   */
  protected function addTerm( $index )
  {
    if ( ! is_int( $index ) ) {
      throw new InvalidArgumentException( 'Argument must be an integer' );
    }

    if (
      empty( $this->params["q$index"] )
      || empty( $this->params["i$index"] )
    ) {
      throw new InvalidArgumentException( 'No values present for specified index' );
    }

    if ( ! empty( $this->params["q$index"] ) && ! empty( $this->params["i$index"] ) ) {
      if ( $index == 1 ) {
        $sign = true;
      }
      elseif ( empty( $this->params["b$index"] ) ) {
        $sign = null;
      }
      elseif ( $this->params["b$index"] == 'AND' ) {
        $sign = true;
      }
      elseif ( $this->params["b$index"] == 'NOT' ) {
        $sign = false;
      }
      else {
        $sign = null;
      }

      $term = new Zend_Search_Lucene_Index_Term( $this->params["q$index"],
                                                 $this->params["i$index"] );
      $this->query->addTerm( $term, $sign );
    }
  }
}
