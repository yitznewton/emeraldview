<?php

abstract class Node
{
  protected $collection;
  protected $id;
  protected $data;
  protected $children = array();
  protected $rootNode;
  
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

  public function getSubnodeId()
  {
    if (strpos( $this->id, '.' )) {
      return substr( $this->id, strpos( $this->id, '.' ) + 1 );
    }
    else {
      return false;
    }
  }

  public function getRootNode()
  {
    if ($this->rootNode) {
      return $this->rootNode;
    }
    
    if ( $this->getRootId() == $this->getId() ) {
      $this->rootNode = $this;
      
      return $this->rootNode;
    }

    $class = get_class( $this );

    // ugly workaround for lack of LSB in < 5.3
    $this->rootNode = $this->staticFactory(
      $this->collection, $this->getRootId()
    );
    
    return $this->rootNode;
  }

  public function getRelatedNode( $subnode_id )
  {
    return self::factory( $this->collection, $this->getRootId() . $subnode_id );
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

  abstract public static function factory(
    Collection $collection, $node_id, $root_only = false
  );

  abstract protected function staticFactory(
    Collection $collection, $node_id, $root_only = false
  );
}