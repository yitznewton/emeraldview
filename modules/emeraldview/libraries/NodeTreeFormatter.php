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
 * @version 0.2.0
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
   * Whether to load branches using AJAX
   *
   * @var boolean
   */
  protected $loadAjax = false;

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
   * @param boolean $a Whether to load branches using AJAX
   */
  public function setLoadAjax( $a )
  {
    if ( ! is_bool( $a ) ) {
      throw new InvalidArgumentException( 'Argument must be a boolean' );
    }

    $this->loadAjax = ( $a );
  }

  /**
   * Returns the HTML <<ul>> tree
   *
   * @return string
   */
  public function render()
  {
    if ( ! $this->rootNode->getChildCount() ) {
      return false;
    }

    return $this->renderNode( $this->rootNode );
  }

  /**
   * Renders HTML for a Node and its children
   *
   * @param array $nodes
   */
  protected function renderNode( Node $node )
  {
    if ( ! $node->getChildCount() ) {
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

    if ( $node->getField('childtype') == 'HList' ) {
      return $this->renderNodeTabRoot( $node, $mdoffsets );
    }
    elseif ( ! $node->getSubnodeId() ) {
      return $this->renderNodeTreeRoot( $node, $mdoffsets );
    }
    elseif ( $node->getParent()->getField('childtype') != 'VList' ) {
      return $this->renderNodeTreeRoot( $node, $mdoffsets );
    }
    else {
      return $this->renderNodeTreeBranch( $node, $mdoffsets );
    }
  }

  /**
   * Renders HTML for a Node at the root of a tabset and its children
   *
   * @param array $nodes
   */
  protected function renderNodeTabRoot( Node $node, $mdoffsets )
  {
    $this->isUsingTabs = true;

    $children    = $node->getChildren();
    $top_html    = '';
    $bottom_html = '';

    for ( $i = 0; $i < count( $children ); $i++ ) {
      $child = $children[ $i ];
      $mdoffset = isset( $mdoffsets[ $i ] ) ? $mdoffsets[ $i ] : null;
      $child_output = $this->formatNode( $child, $mdoffset, false );

      $dashed_id = str_replace( '.', '-', $child->getId() );

      if ( $this->loadAjax ) {
        $url = url::base() . 'ajax/' . $node->getCollection()->getName()
               . '/browse/' . $child->getId();
        
        $top_html .= "<li><a href=\"$url\"><span class=\"spinner\"></span>"
                     . $child_output . '</a></li>';
      }
      else {
        $top_html .= '<li><a href="#browse-' . $dashed_id . '">'
                     . $child_output . "</a></li>\n";

        $bottom_html .= '<div id="browse-' . $dashed_id . '">' . "\n"
                        . '<h2 class="browse-section">' . $child_output . '</h2>'
                        . $this->renderNode( $child ) . "</div>\n";
      }
    }

    $dir = strtolower( $node->getNodePage()->getConfig('dir') );

    $attr = array( 'class' => 'browse-tabs ' . $dir );

    if ( $dir ) {
      $attr[ 'dir' ] = $dir;
    }

    $inner_html = "<ul>\n$top_html\n</ul>\n$bottom_html\n</div>\n";

    return myhtml::element( 'div', $inner_html, $attr );
  }

  /**
   * Renders HTML for a Node at the root of a tree and its children
   *
   * @param array $nodes
   */
  protected function renderNodeTreeRoot( Node $node, $mdoffsets )
  {
    $this->isUsingTree = true;

    $children = $node->getChildren();

    $inner_html = '';

    if ( $this->rootNode instanceof Node_Document ) {
      $node_page = $this->rootNode->getNodePage();

      if ( $node_page->getHTML() ) {
        $url = $node_page->getUrl();
        $inner_html .= "<li><a href=\"$url\">" . L10n::_('Title page') . "</a></li>\n";
      }
    }

    for ( $i = 0; $i < count( $children ); $i++ ) {
      $child = $children[ $i ];
      $mdoffset = isset( $mdoffsets[ $i ] ) ? $mdoffsets[ $i ] : null;

      $recurse = ( get_class( $node ) == get_class( $child ) );
      $inner_html .= '<li>' . $this->formatNode( $child, $mdoffset, $recurse ) . "</li>\n";
    }

    if ( $node instanceof Node_Classifier ) {
      $dir = strtolower( $node->getNodePage()->getConfig('dir') );
    }
    else {
      $dir = null;
    }

    $attr = array( 'class' => 'browse-tree ' . $dir );

    if ( $dir ) {
      $attr[ 'dir' ] = $dir;
    }

    return myhtml::element( 'ul', $inner_html, $attr );
  }

  /**
   * Renders HTML for a sub-Node of a tree, and its children
   *
   * @param array $nodes
   */
  protected function renderNodeTreeBranch( Node $node, $mdoffsets )
  {
    $children = $node->getChildren();
    $output   = '<ul>' . "\n";

    for ( $i = 0; $i < count( $children ); $i++ ) {
      $child = $children[ $i ];
      $mdoffset = isset( $mdoffsets[ $i ] ) ? $mdoffsets[ $i ] : null;

      $recurse = ( get_class( $node ) == get_class( $child ) );

      $output .= '<li>' . $this->formatNode( $child, $mdoffset, $recurse ) . "</li>\n";
    }

    $output .= "</ul>\n";

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
  protected function formatNode( Node $node, $mdoffset, $recurse = true )
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
      $output .= $this->renderNode( $node );
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
