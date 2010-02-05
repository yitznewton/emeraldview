<?php

abstract class Highlighter
{
  protected $terms;
  
  public function setTerms( $raw_terms )
  {
    if ( ! is_array( $raw_terms ) ) {
      $raw_terms = array( $raw_terms );
    }

    $this->terms = array();

    foreach ( $raw_terms as $term ) {
      if ( ! is_string( $term ) ) {
        $msg = 'Argument must be a string or array of strings';
        throw new InvalidArgumentException( $msg );
      }
      // replace wildcards with regex equivalents
      $term = preg_quote( $term );
      //$term = str_replace( array('\\*', '\\?'), array('.*?', '.'), $term );
      $term = str_replace( array('\\*', '\\?'), array('.*?', '.'), $term );

      $this->terms[] = $term;
    }
  }
  
  abstract public function execute();
  abstract public function getDocument();
  abstract public function setDocument( $document );
}
