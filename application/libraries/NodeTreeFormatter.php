<?php

class NodeTreeFormatter
{
  public static function format( Node $node )
  {
    if (! $children = $node->getChildren()) {
      return false;
    }

    if ($node != $node->getRootNode()) {
      $msg = 'Attempting to create node tree for a non-root node';
      throw new Exception( $msg );
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
    
    $node_output = $node->getFormatter( NodeFormatter::METHOD_TREE )->format( $node );

    if (
      $node instanceof Node_Document
      && strpos( $node_output, '<a' ) === false
    ) {
      $url = NodePage::factory( $node )->getUrl();
      $node_output = "<a href=\"$url\">$node_output</a>";
    }

    $output .= $node_output;

    $children = $node->getChildren();
    
    if ( $children ) {
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