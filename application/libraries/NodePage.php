<?php

abstract class NodePage
{
  protected $node;

  protected function __construct( Node $node )
  {
    $this->node = $node;
  }

  abstract public function getUrl();
  abstract public function getNodeFormatter();
  
  public function getNode()
  {
    return $this->node;
  }

  public function getId()
  {
    return $this->getNode()->getId();
  }

  public function getTree()
  {
    return NodeTreeFormatter::format( $this->getNode()->getRootNode(), $this->getNodeFormatter() );
  }

  public function getSubnodeId()
  {
    // TODO: refactor existing code to use this new method
    $id = $this->getNode()->getId();

    if (strpos( $id, '.' )) {
      return substr( $id, strpos( $id, '.' ) + 1);
    }
    else {
      return false;
    }
  }

  public function getCollection()
  {
    return $this->getNode()->getCollection();
  }

  public static function factory( Node $node )
  {
    switch ( get_class( $node ) ) {
      case 'Node_Classifier':
        return new NodePage_Classifier( $node );
      case 'Node_Document':
        return new NodePage_DocumentSection( $node );
      default:
        throw new Exception( 'Unrecognized subclass of Node' );
    }
  }
}