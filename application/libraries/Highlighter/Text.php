<?php

class Highlighter_Text extends Highlighter
{
  protected $document;

  public function execute()
  {
    $search  = '/\\b' . implode( '\\b|\\b', $this->terms ) . '\\b/iu';
    $replace = "<span class=\"highlight\">\\0</span>";

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