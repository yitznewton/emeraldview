<?php

abstract class NodePage
{
  protected $node;

  protected function __construct( Node $node )
  {
    $this->node = $node;
  }

  abstract public function getUrl();
  abstract public function getTree( NodeFormatter $node_formatter );

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