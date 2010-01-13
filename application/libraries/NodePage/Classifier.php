<?php

class NodePage_Classifier extends NodePage
{
  public function getCollection()
  {
    return $this->getNode()->getCollection();
  }
  
  public function getConfig( $subnode = null )
  {
    $node = 'classifiers.' . $this->getNode()->getId();
    
    if ($subnode) {
      $node .= '.' . $subnode;
    }
    
    return $this->getNode()->getCollection()->getConfig( $node );
  }

  public function getId()
  {
    return $this->getNode()->getId();
  }
  
  public function getUrl()
  {
    return $this->getNode()->getCollection()->getUrl() . '/browse/' . $this->getId();
  }
  
  public function getTitle()
  {
    return $this->getNode()->getField('Title');
  }
  
  public function getNodeFormatter()
  {
    $id = $this->getId();

    if ($this->getCollection()->getConfig( "classifiers.$id.format" )) {
      return new NodeFormatter_String(
        $this->getCollection()->getConfig( "classifiers.$id.format" )
      );
    }
    elseif ($this->getCollection()->getConfig( "classifiers.$id.format_function" )) {
      return new NodeFormatter_Function(
        $this->getCollection()->getConfig( "classifiers.$id.format_function" )
      );
    }
    else {
      return new NodeFormatter();
    }
  }
}