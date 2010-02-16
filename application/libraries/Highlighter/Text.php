<?php

class Highlighter_Text extends Highlighter
{
  protected $document;

  public function execute()
  {
    $term_pattern  = implode( '|', $this->terms );
    $search = sprintf( Hit::HIT_PATTERN, $term_pattern );
    $replace = "<span class=\"highlight\">\\1</span>";

    return preg_replace( $search, $replace, $this->document );
  }

  public function getDocument()
  {
    return $this->document;
  }

  public function setDocument( $document )
  {
    if ( ! is_string( $document ) || $document === '' ) {
      throw new InvalidArgumentException( 'Argument must be a non-empty string' );
    }

    $this->document = $document;
  }
}