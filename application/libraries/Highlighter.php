<?php

abstract class Highlighter
{
  protected $terms;
  
  public function setTerms( array $raw_terms )
  {
    $this->terms = array();

    foreach ( $raw_terms as $term ) {
      // replace wildcards with regex equivalents
      $term = preg_quote( $term );
      //$term = str_replace( array('\\*', '\\?'), array('.*\\b', '.'), $term );
      $term = str_replace( array('\\*', '\\?'), array('.*', '.'), $term );

      $this->terms[] = $term;
    }
  }
  
  abstract public function execute();
  abstract public function getDocument();
  abstract public function setDocument( $document );
}
