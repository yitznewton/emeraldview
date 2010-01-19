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
        $this->getConfig( "classifiers.$id.format_function" )
      );
    }
    else {
      return new NodeFormatter();
    }
  }
}