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

  public function getTree()
  {
    return NodeTreeFormatter::format( $this->getNode()->getRootNode(), $this->getNodeFormatter() );
  }

  public static function factory( Node $node )
  {
    switch ( get_class( $node ) ) {
      case 'Node_Classifier':
        return new NodePage_Classifier( $node );
      case 'Node_Document':
        return new NodePage_DocumentSection( $node );
      default:
        throw new Exception('Unrecognized Node subclass');
    }
  }
}