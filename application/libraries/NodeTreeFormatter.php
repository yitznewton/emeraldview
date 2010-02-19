<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.net/emeraldview/index.php/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0b1
 * @package libraries
 */
/**
 * NodeTreeFormatter creates an HTML <ul> tree representing the hierarchy of
 * a given Node
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class NodeTreeFormatter
{
  /**
   * The Node that we're building a tree for
   *
   * @var Node
   */
  protected $rootNode;
  /**
   * An object representing the situation where the string is needed; used
   * for determining which format specification to use
   *
   * @var mixed
   */
  protected $context;
  
  /**
   * @param Node $node The root Node of the classifier/document that we're building a tree for
   * @param mixed $context An object representing the situation where the string is needed; used for determining which format specification to use
   */
  public function __construct( Node $node, $context )
  {
    $this->rootNode = $node;
    $this->context = $context;
  }

  /**
   * Returns the HTML <ul> tree
   *
   * @return string
   */
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
  
  /**
   * Renders HTML for a single child Node in the hierarchy
   *
   * @param Node $node
   * @return string
   */
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
