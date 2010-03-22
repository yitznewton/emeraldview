<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.org/emeraldview/index.php?title=License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b3
 * @package libraries
 */
/**
 * NodeTreeFormatter creates an HTML <<ul>> tree representing the hierarchy of
 * a given Node
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
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
   * @var NodePage|SearchHandler
   */
  protected $context;
  /**
   * Whether the current page uses a tree; for loading Javascript
   *
   * @var boolean
   */
  protected $isUsingTree = false;
  /**
   * Whether the current page uses tabs; for loading Javascript
   *
   * @var boolean
   */
  protected $isUsingTabs = false;
  
  /**
   * @param Node $node The root Node of the classifier/document that we're building a tree for
   * @param NodePage|SearchHandler $context An object representing the situation where the string is needed; used for determining which format specification to use
   */
  public function __construct( Node $node, $context )
  {
    $this->rootNode = $node;
    $this->context = $context;
  }

  /**
   * Returns the HTML <<ul>> tree
   *
   * @return string
   */
  public function render()
  {
    $children = $this->rootNode->getChildren();

    if ( ! $children ) {
      return false;
    }

    if ( $this->rootNode != $this->rootNode->getRootNode() ) {
      $msg = 'Attempting to create node tree for a non-root node';
      throw new Exception( $msg );
    }

    return $this->renderChildren( $this->rootNode );
  }

  /**
   * Renders HTML for the children of a single Node
   *
   * @param array $nodes
   */
  protected function renderChildren( Node $node )
  {
    $children = $node->getChildren();

    if ( ! $children ) {
      return '';
    }
    
    if ( $node instanceof Node_Classifier ) {
      $mdoffsets = $node->getMdOffsets();
    }
    else {
      $mdoffsets = null;
    }

    if ( $mdoffsets && count( $mdoffsets ) != count( $node->getChildren() ) ) {
      throw new Exception( 'mdoffset count does not match children count');
    }

    $output = '';

    if ( $node->getField('childtype') == 'HList' ) {
      // tabs
      $this->isUsingTabs = true;

      $top_html    = '';
      $bottom_html = '';

      for ( $i = 0; $i < count( $children ); $i++ ) {
        $child = $children[ $i ];
        $mdoffset = isset( $mdoffsets[ $i ] ) ? $mdoffsets[ $i ] : null;
        $child_output = $this->renderNode( $child, $mdoffset, false );

        $dashed_id = str_replace( '.', '-', $child->getId() );
        $top_html .= '<li><a href="#browse-' . $dashed_id . '">'
                     . $child_output . "</a></li>\n";

        $dashed_id = str_replace( '.', '-', $child->getId() );
        $bottom_html .= '<div id="browse-' . $dashed_id . '">' . "\n"
                        . '<h2 class="browse-section">' . $child_output . '</h2>'
                        . $this->renderChildren( $child ) . "</div>\n";
      }

      $output .= '<div class="browse-tabs"><ul>' . "\n"
                 . $top_html . "</ul>\n" . $bottom_html . '</div>' . "\n";
    }
    elseif ( ! $node->getSubnodeId() ) {
      // $node is root - start new tree
      $this->isUsingTree = true;

      $output .= '<ul class="browse-tree">' . "\n";

      for ( $i = 0; $i < count( $children ); $i++ ) {
        $child = $children[ $i ];
        $mdoffset = isset( $mdoffsets[ $i ] ) ? $mdoffsets[ $i ] : null;

        $recurse = ( get_class( $node ) == get_class( $child ) );
        $output .= '<li>' . $this->renderNode( $child, $mdoffset, $recurse ) . "</li>\n";
      }

      $output .= "</ul>\n";
    }
    elseif ( $node->getParent()->getField('childtype') != 'VList' ) {
      // first level in a VList - start tree
      $this->isUsingTree = true;

      $output .= '<ul class="browse-tree">' . "\n";

      for ( $i = 0; $i < count( $children ); $i++ ) {
        $child = $children[ $i ];
        $mdoffset = isset( $mdoffsets[ $i ] ) ? $mdoffsets[ $i ] : null;

        $recurse = ( get_class( $node ) == get_class( $child ) );
        $output .= '<li>' . $this->renderNode( $child, $mdoffset, $recurse ) . "</li>\n";
      }

      $output .= "</ul>\n";
    }
    else {
      // continue existing tree
      $output .= '<ul>' . "\n";

      for ( $i = 0; $i < count( $children ); $i++ ) {
        $child = $children[ $i ];
        $mdoffset = isset( $mdoffsets[ $i ] ) ? $mdoffsets[ $i ] : null;
        
        $recurse = ( get_class( $node ) == get_class( $child ) );

        $output .= '<li>' . $this->renderNode( $child, $mdoffset, $recurse ) . "</li>\n";
      }

      $output .= "</ul>\n";
    }

    return $output;
  }
  
  /**
   * Renders HTML for a single child Node in the hierarchy
   *
   * @param Node $node
   * @param integer $mdoffset The index of the value of a classifier's metadata field to use
   * @param boolean $recurse Whether to recurse through child Nodes
   * @return string
   */
  protected function renderNode( Node $node, $mdoffset, $recurse = true )
  {
    $output = '';

    $formatter = NodeFormatter::factory( $node, $this->context );
    $node_output = $formatter->format( $mdoffset );

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

    if ( $recurse ) {
      $output .= $this->renderChildren( $node );
    }

    return $output;
  }

  /**
   * Returns whether the current page uses a tree; for loading Javascript
   *
   * @return boolean
   */
  public function isUsingTree()
  {
    return $this->isUsingTree;
  }

  /**
   * Returns whether the current page uses tabs; for loading Javascript
   *
   * @return boolean
   */
  public function isUsingTabs()
  {
    return $this->isUsingTabs;
  }
}
