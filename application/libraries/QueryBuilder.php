<?php

abstract class QueryBuilder
{
  protected $collection;
  protected $params;
  protected $query;
  protected $rawTerms;
  
  protected function __construct( array $params, Collection $collection )
  {
    $this->params     = $params;
    $this->collection = $collection;
  }
  
  abstract public function getQuery();
  abstract public function getDisplayQuery();

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
    elseif ( isset( $this->params['q'] ) ) {
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