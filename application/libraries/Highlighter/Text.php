<?php

class Highlighter_Text extends Highlighter
{
  protected $document;

  public function execute()
  {
    $bb = L10n::getWbBefore();
    $ba = L10n::getWbAfter();
    // FIXME is the case-insensitive flag going to work here?
    $search  = '/' . $bb . '(' . implode( '|', $this->terms ) . ')' . $ba . '/iu';
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