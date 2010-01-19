<?php

class NodePage_Classifier extends NodePage
{
  public function getConfig( $subnode = null )
  {
    $node = 'classifiers.' . $this->getId();
    
    if ($subnode) {
      $node .= '.' . $subnode;
    }
    
    return $this->getCollection()->getConfig( $node );
  }

  public function getUrl()
  {
    return $this->getCollection()->getUrl() . '/browse/' . $this->getId();
  }
  
  public function getTitle()
  {
    return $this->getNode()->getField('Title');
  }
}