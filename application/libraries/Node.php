<?php

abstract class Node
{
  protected $collection;
  protected $id;
  protected $data;
  protected $children = array();
  
  abstract protected function recurse();
  abstract protected function getChild( $node_id );
  abstract public function format();

  protected function __construct(
    Collection $collection, $node_id = null, $root_only = false
  )
  {
    $this->id = $node_id;
    
    $this->collection = $collection;
    $this->data = $collection->getInfodb()->getNode( $this->id );
    
    if (!$root_only) {
      $this->recurse();
    }
  }
  
  public function getId()
  {
    return $this->id;
  }

  public function getRootId()
  {
    if (strpos( $this->id, '.' )) {
      return substr( $this->id, 0, strpos( $this->id, '.' ) );
    }
    else {
      return $this->id;
    }
  }

  public function getCollection()
  {
    return $this->collection;
  }

  public function getChildren()
  {
    return $this->children;
  }
  
  public function getField( $field_name )
  {
    if (array_key_exists( $field_name, $this->data )) {
      return $this->data[ $field_name ];
    }
    else {
      return false;
    }
  }

  public function getFormatter()
  {
    return NodeTreeFormatter::factory( $this );
  }
}