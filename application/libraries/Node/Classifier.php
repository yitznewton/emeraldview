<?php

class Node_Classifier extends Node
{
  protected function recurse()
  {
    if (
      substr($this->id, 0, 1) == 'C'
      && isset($this->data['contains'])
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
    if (substr( $node_id, 0, 2 ) == 'CL') {
      return new Node_Classifier( $this->collection, $node_id );
    }
    else {
      return Node_Document::factory( $this->collection, $node_id, true );
    }
  }
  
  public function format()
  {
    //TODO: placeholder - write NodeFormatter or the like
    return $this->id;
  }

  public static function factory(
    Collection $collection, $node_id, $root_only = false
  )
  {
    return new Node_Classifier( $collection, $node_id, $root_only );
  }

  protected function staticFactory(
    Collection $collection, $node_id, $root_only = false
  )
  {
    return Node_Classifier::factory( $collection, $node_id, $root_only );
  }
}
