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
 * Query parses the query parameters in the context of a given collection
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
abstract class Query
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
  
  /**
   * @param Collection $collection The Collection to search
   * @param array $params The search parameters
   */
  protected function __construct( Collection $collection, array $params )
  {
    $this->collection = $collection;
    $this->params     = $params;
  }

  /**
   * Returns one string representing all terms in the query; for 
   * for use by SearchHandler
   *
   * @return string
   */
  abstract public function getQuerystring();

  /**
   * Returns one string representing all terms in the query; for display
   *
   * @return string
   * @todo make sure client code is using this new method for human-readable output
   */
  public function getDisplayQuery()
  {
    return $this->getQuerystring();
  }

  /**
   * Returns the Collection of this Query
   *
   * @return Collection
   */
  public function getCollection()
  {
    return $this->collection;
  }

  /**
   * Returns the params of this Query
   *
   * @return array
   */
  public function getParams()
  {
    return $this->params;
  }

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
   * @return array
   */
  public function getValidParams()
  {
    return $this->validParams;
  }

  /**
   * @param array $params
   */
  public function setValidParams( array $params )
  {
    $this->validParams = $params;
  }

  /**
   * @param Collection $collection The Collection to search
   * @param array $params The query parameters to build the query around
   * @return Query
   */
  public static function factory( Collection $collection, array $params )
  {
    $indexes = array_keys( $collection->getIndexes() );

    if ( array_key_exists( 'i', $params ) && in_array( $params['i'], $indexes ) ) {
      return new Query_Fielded( $collection, $params );
    }
    elseif (array_key_exists( 'q1', $params )) {
      return new Query_Boolean( $collection, $params );
    }
    elseif (array_key_exists( 'q', $params )) {
      return new Query_Simple( $collection, $params );
    }
    else {
      return false;
    }
  }
}
