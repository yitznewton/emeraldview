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
 * Node_Classifier represents a classifier or section thereof, and provides
 * basic functionality centered around the node's metadata
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class Node_Classifier extends Node
{
  /**
   * An array of values used to determine which metadata value to use in
   * formatting each of the Node's children where the child has multiple values
   * for the classifier's metadata field
   *
   * @var array
   */
  protected $mdoffsets;

  protected function __construct( Infodb $infodb, $node_id = null )
  {
    parent::__construct( $infodb, $node_id );
    
    if ( isset( $this->data['mdoffset'] ) && $this->data['mdoffset'] !== '') {
      $this->mdoffsets = explode( ';', $this->data['mdoffset'] );
    }

    unset( $this->data['mdoffset'] );
  }

  /**
   * Returns an array of values used to determine which metadata value to use in
   * formatting each of the Node's children where the child has multiple values
   * for the classifier's metadata field
   *
   * @return array
   */
  public function getMdOffsets()
  {
    return $this->mdoffsets;
  }

  /**
   * Returns randomly-selected leaf Node_Documents
   *
   * @param integer $count
   * @return array
   */
  public function getRandomLeafNodes( $count = 1 )
  {
    $node_ids = $this->infodb->getRandomLeafNodeIds( $this, $count );
    $nodes    = array();

    foreach ( $node_ids as $id ) {
      $nodes[] = $this->getCousin( $id );
    }

    return $nodes;
  }
}
