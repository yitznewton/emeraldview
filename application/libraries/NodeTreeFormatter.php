<?php

class NodeTreeFormatter
{
  protected $rootNode;
  
  public function __construct( Node $node )
  {
    $this->rootNode = $node;
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
    
    $node_output = $node->getFormatter( NodeFormatter::METHOD_TREE )->format();

    if (
      ( $this->rootNode instanceof Node_Classifier && $node instanceof Node_Document )
      || ( $this->rootNode instanceof Node_Document && ! $node->getChildren() )
    ) {
      // leaf node
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