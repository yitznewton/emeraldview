<?php

class NodeFormatter
{
  public function __construct() {}

  public function format( Node $node )
  {
    $title = '';

    $title = $node->getField( 'dc.Title' )
      or $title = $node->getField( 'Title' );
    
    if (is_array( $title )) {
      $text = $title[0];
    }
    elseif ($title) {
      $text = $title;
    }
    else {
      $text = $node->getId();
    }

    return $text;
  }
}