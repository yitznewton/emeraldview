<?php

class NodeTreeFormatter
{
  public static function format( Node $node )
  {
    if (! $children = $node->getChildren()) {
      return false;
    }
    
    $output = '<ul class="browse-tree">' . "\n";
    
    foreach ($children as $child) {
      $output .= self::renderNode( $child );
    }
    
    $output .= "</ul>\n";

    return $output;
  }
  
  protected static function renderNode( Node $node )
  {
    $output = "<li>\n";
    $output .= $node->format();

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