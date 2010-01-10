<?php

class Node_Document extends Node
{
  protected $subnode_id;

  protected function __construct(
    Collection $collection, $node_id = null, $root_only = false
  )
  {
    if ($pos = strpos( $node_id, '.') ) {
      $this->subnode_id = substr( $node_id, $pos + 1 );
    }

    parent::__construct( $collection, $node_id, $root_only );
  }

  protected function recurse()
  {
    if (
      isset($this->data['contains'])
      && $this->data['contains']
    ) {
      // ... node has 'contains' and is not empty
      $children_names = split(';', $this->data['contains']);

      $children = array();
      foreach ($children_names as $child) {
        $child_id = str_replace('"', $this->id, $child);
        $this->children[] = $this->getChild( $child_id );
      }
    }
  }
  
  protected function getChild( $node_id )
  {
    return Node_Document::factory( $this->collection, $node_id, true );
  }
  
  public function format()
  {
    //TODO: placeholder - write NodeFormatter or the like
    $url = Document::factory($this)->getUrl();

    if ($this->subnode_id) {
      $url .= '/' . str_replace( '.', '/', $this->subnode_id );
    }

    return html::anchor( $url, $this->id );
  }

  public static function factory(
    Collection $collection, $node_id, $root_only = false
  )
  {
    return new Node_Document( $collection, $node_id, $root_only );
  }
}