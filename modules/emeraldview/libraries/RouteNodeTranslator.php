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
 * RouteNodeTranslator finds a cousin for a given Node_Document based on
 * route arguments
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class RouteNodeTranslator
{
  /**
   * The root Node of the document
   * 
   * @var Node_Document
   */
  protected $root_node;

  /**
   *
   * @param Node_Document $root_node The root Node of the document
   */
  protected function __construct( Node_Document $root_node )
  {
    $this->root_node = $root_node;
  }

  /**
   * Returns cousin of root Node based on provided arguments from route
   *
   * @param array $subnode_args Subnode arguments from route
   * @return Node_Document|false
   */
  public function getNode( array $subnode_args )
  {
    if ( empty( $subnode_args ) ) {
      return $this->root_node;
    }

    $subnode_id = implode( '.', $subnode_args );

    return $this->root_node->getCousin( $subnode_id );
  }

  /**
   * @param Node_Document $root_node
   * @return RouteNodeTranslator_Paged
   */
  public static function factory( Node_Document $root_node )
  {
    if (
      $root_node->isPaged()
      && $root_node->getCollection()->getConfig('paged_continuous')
    ) {
      return new RouteNodeTranslator_PagedContinuous( $root_node );
    }
    elseif ( $root_node->isPaged() ) {
      return new RouteNodeTranslator_Paged( $root_node );
    }
    else {
      return new RouteNodeTranslator( $root_node );
    }
  }
}
