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
 * NodePage is a wrapper for Node which extends webpage functionalities
 * such as URLs and node tree generation
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
abstract class NodePage
{
  /**
   * The Node being wrapped
   *
   * @var Node
   */
  protected $node;

  /**
   * @param Node $node 
   */
  protected function __construct( Node $node )
  {
    $this->node = $node;
  }

  /**
   * Returns the URL by which the page can be accessed
   * 
   * @return string
   */
  abstract public function getUrl();
  
  /**
   * Returns the wrapped Node
   *
   * @return Node
   */
  public function getNode()
  {
    return $this->node;
  }

  /**
   * Returns the ID of the wrapped Node
   *
   * @return string
   */
  public function getId()
  {
    return $this->getNode()->getId();
  }

  /**
   * Returns the NodeTreeFormatter for this entire classifier or document
   *
   * @return string
   */
  public function getNodeTreeFormatter()
  {
    return new NodeTreeFormatter( $this->getNode()->getRootNode(), $this );
  }

  /**
   * Returns the Collection of the wrapped Node
   *
   * @return Collection
   */
  public function getCollection()
  {
    return $this->getNode()->getCollection();
  }

  /**
   * @param Node $node
   * @return NodePage
   */
  public static function factory( Node $node )
  {
    switch ( get_class( $node ) ) {
      case 'Node_Classifier':
        return new NodePage_Classifier( $node );
      case 'Node_Document':
        return new NodePage_DocumentSection( $node );
      default:
        throw new Exception( 'Unrecognized subclass of Node' );
    }
  }
}
