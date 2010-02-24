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
 * @version 0.2.0-b1
 * @package libraries
 */
/**
 * QueryBuilder creates Zend_Search_Lucene objects for search, based on the
 * Collection and query parameters
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
abstract class QueryBuilder
{
  /**
   * The Collection being searched
   *
   * @var Collection
   */
  protected $collection;
  /**
   * The search parameters
   *
   * @var array
   */
  protected $params;
  /**
   * The query represented as a Zend_Search_Lucene_Query object
   *
   * @var Zend_Search_Lucene_Search_Query
   */
  protected $query;
  /**
   * An array of the search terms represented in string form
   *
   * @var array
   */
  protected $rawTerms;
  
  protected function __construct( array $params, Collection $collection )
  {
    $this->params     = $params;
    $this->collection = $collection;
  }
  
  /**
   * Returns the query represented as a Zend_Search_Lucene_Query object
   *
   * @return Zend_Search_Lucene_Search_Query
   */
  abstract public function getQuery();
  /**
   * Returns a string representing the query in human-readable form
   *
   * @return string
   */
  abstract public function getDisplayQuery();

  /**
   * Returns an array of the search terms represented in string form, building
   * it first if needed
   *
   * @todo are there any URL, HTML, or regex encoding issues that haven't been dealt with?
   * @return array
   */
  public function getRawTerms()
  {
    if ( $this->rawTerms ) {
      return $this->rawTerms;
    }

    $terms = array();

    if ( method_exists( $this->getQuery(), 'getTerms' ) ) {
      // query is instance of e.g. Zend_Search_Lucene_Search_Query_MultiTerm -
      // leverage Zend_Search_Lucene API for this

      $all_terms = $this->getQuery()->getTerms();
      $signs = $this->getQuery()->getSigns();

      for ( $i = 0; $i < count($all_terms); $i++ ) {
        if ( $signs === null || $signs[ $i ] !== false ) {
          // not a NOT term
          $terms[] = $all_terms[ $i ]->text;
        }
      }
    }
    elseif ( ! empty( $this->params['q'] ) ) {
      $pattern = '/ " \b (.+?) \b " | \S+ /ux';
      preg_match_all( $pattern, $this->params['q'], $term_matches );

      for ( $i = 0; $i < count( $term_matches[0] ); $i++ ) {
        if ( $term_matches[1][$i] ) {
          // matched a quoted segment
          $terms[] = $term_matches[1][$i];
        }
        else {
          $terms[] = $term_matches[0][$i];
        }
      }
    }
    else {
      throw new Exception( 'Could not get raw query terms' );
    }

    return $this->rawTerms = $terms;
  }

  /**
   * @param array $params The query parameters to build the query around
   * @param Collection $collection The Collection to search
   * @return QueryBuilder
   */
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
