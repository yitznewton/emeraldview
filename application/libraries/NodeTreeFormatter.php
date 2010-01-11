<?php

class NodeTreeFormatter
{
  public static function format( Node $node, NodeFormatter $node_formatter )
  {
    if (! $children = $node->getChildren()) {
      return false;
    }
    
    $output = '<ul class="browse-tree">' . "\n";
    
    foreach ($children as $child) {
      $output .= self::renderNode( $child, $node_formatter );
    }
    
    $output .= "</ul>\n";

    return $output;
  }
  
  protected static function renderNode( Node $node, NodeFormatter $node_formatter )
  {
    $output = "<li>\n";
    $output .= $node_formatter->format( $node );

    if ($children = $node->getChildren()) {
      $output .= "<ul>\n";
      
      foreach ($children as $child) {
        $output .= self::renderNode( $child );
      }
      
      $output .= "</ul>\n";
    }

    $output .= "</li>\n";
    
    return $output;
  }
}