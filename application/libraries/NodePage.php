<?php

abstract class NodePage
{
  protected $node;

  protected function __construct( Node $node )
  {
    $this->node = $node;
  }

  abstract public function getUrl();
  
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
    $formatter = new NodeTreeFormatter( $this->getNode()->getRootNode(), $this );
    return $formatter->format();
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