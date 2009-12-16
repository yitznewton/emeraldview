<?php

class Node_Document extends Node
{
  protected $document;
  
  protected function recurse()
  {
  }
  
  protected function getChild( $node_id )
  {
  }
  
  public static function factory(
    Collection $collection, $node_id, $root_only = false
  )
  {
    return new Node_Document( $collection, $node_id, $root_only );
  }
}