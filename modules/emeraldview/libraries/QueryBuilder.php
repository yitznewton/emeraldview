<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.org/emeraldview/index.php?title=License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b4
 * @package libraries
 */
/**
 * QueryBuilder creates Zend_Search_Lucene objects for search, based on the
 * Collection and query parameters
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
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
   * Returns one string representing all terms in the query; for display and
   * for use by SearchHandler
   *
   * @return string
   */
  abstract public function getQuerystring();

  /**
   * Returns an array of the search term string fragments for highlighting,
   * building it first if needed
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

    $to_check = array( 'q', 'q1', 'q2', 'q3' );
    $terms    = array();
    $pattern  = '/ " (?<=[^_\pL\pN]) (.+?) (?=[^_\pL\pN]) " | [^"\s]+ /ux';

    foreach ( $to_check as $key ) {
      if ( isset( $this->params[ $key ] ) ) {
        preg_match_all( $pattern, $this->params[ $key ], $term_matches );

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
