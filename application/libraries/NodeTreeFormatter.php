<?php

class NodeTreeFormatter
{
  protected $tree;
  
  protected function __construct( Node $tree )
  {
    $this->tree = $tree;
  }
  
  public function html()
  {
    if (! $children = $this->tree->getChildren()) {
      return false;
    }
    
    $output = '<ul class="browse-tree">' . "\n";
    
    foreach ($children as $child) {
      $output .= $this->renderNode( $child );
    }
    
    $output .= "</ul>\n";

    return $output;
  }
  
  protected function renderNode( Node $node )
  {
    $output = "<li>\n";
    $output .= $node->format();

    if ($children = $node->getChildren()) {
      $output .= "<ul>\n";
      
      foreach ($children as $child) {
        $output .= $this->renderNode( $child );
      }
      
      $output .= "</ul>\n";
    }

    $output .= "</li>\n";
    
    return $output;
  }
  
  public static function factory( Node $tree )
  {
    return new NodeTreeFormatter( $tree );
  }
}