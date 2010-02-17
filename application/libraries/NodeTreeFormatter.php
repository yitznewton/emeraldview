<?php

class NodeTreeFormatter
{
  protected $rootNode;
  protected $context;
  
  public function __construct( Node $node, $context )
  {
    $this->rootNode = $node;
    $this->context = $context;
  }

  public function format()
  {
    $children = $this->rootNode->getChildren();

    if ( ! $children ) {
      return false;
    }

    if ( $this->rootNode != $this->rootNode->getRootNode() ) {
      $msg = 'Attempting to create node tree for a non-root node';
      throw new Exception( $msg );
    }
    
    $output = '<ul class="browse-tree">' . "\n";
    
    foreach ( $children as $child ) {
      $output .= $this->renderNode( $child );
    }
    
    $output .= "</ul>\n";

    return $output;
  }
  
  protected function renderNode( Node $node )
  {
    $output = "<li>\n";
    
    $formatter = NodeFormatter::factory( $node, $this->context );
    $node_output = $formatter->format();

    if (
      ( $this->rootNode instanceof Node_Classifier && $node instanceof Node_Document )
      || ( $this->rootNode instanceof Node_Document )
    ) {
      $url = NodePage::factory( $node )->getUrl();
      $replace = array( '<a href="' . $url . '">', '</a>' );
    }
    else {
      $replace = array( '', '' );
    }

    $search = array( '[a]', '[/a]' );
    $node_output = str_replace( $search, $replace, $node_output );

    $output .= $node_output;

    $children = $node->getChildren();
    
    if ( $children ) {
      $output .= "<ul>\n";
      
      foreach ($children as $child) {
        $output .= $this->renderNode( $child );
      }
      
      $output .= "</ul>\n";
    }

    $output .= "</li>\n";
    
    return $output;
  }
}