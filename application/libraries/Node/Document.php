<?php

class Node_Document extends Node
{
  protected function recurse()
  {
  }
  
  protected function getChild( $node_id )
  {
  }
  
  public function format()
  {
    //TODO: placeholder - write NodeFormatter or the like
    if ($this->getChildren()) {
      return $this->id;
    }
    else {
      $url = Document::factory($this)->getUrl();
      return html::anchor( $url, $this->id );
    }
  }

  public static function factory(
    Collection $collection, $node_id, $root_only = false
  )
  {
    return new Node_Document( $collection, $node_id, $root_only );
  }
}