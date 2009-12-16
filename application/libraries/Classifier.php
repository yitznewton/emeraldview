<?php

class Classifier
{
  protected $collection;
  protected $id;
  protected $tree;
  
  public function __construct( Collection $collection, $id )
  {
    $this->collection = $collection;
    $this->id = $id;
  }
  
  public function getCollection()
  {
    return $this->collection;
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getName()
  {
    return $this->getId();
  }
  
  public function getUrl()
  {
    return $this->getCollection()->getUrl() . '/browse/' . $this->getId();
  }
  
  public function getTitle()
  {
    return Node_Classifier::factory( $this->getCollection(), $this->id, true )->getField('Title');
  }
  
  public function getTree()
  {
    if ($this->tree) {
      return $this->tree;
    }
    
    return $this->tree = Node_Classifier::factory( $this->getCollection(), $this->id );
  }
}