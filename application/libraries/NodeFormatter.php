<?php

class NodeFormatter
{
  const METHOD_SEARCH_RESULTS = 0;
  const METHOD_TREE = 1;

  protected $node;

  public function __construct( Node $node )
  {
    $this->node = $node;
  }

  public function format()
  {
    $field_names = array( 'dc.Title', 'Title' );
    $title = $this->node->getFirstFieldFound( $field_names );
    
    if (is_array( $title )) {
      $text = $title[0];
    }
    elseif ($title) {
      $text = $title;
    }
    else {
      $text = $this->node->getId();
    }

    return $text;
  }
}