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
 * @version 0.2.0-b4
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

  /**
   * @param Collection $collection
   * @param string $node_id
   */
  protected function __construct( Collection $collection, $node_id = null )
  {
    parent::__construct( $collection, $node_id );
    
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
   * @param string $node_id
   * @return Node
   */
  protected function getChild( $node_id )
  {
    if (substr( $node_id, 0, 2 ) == 'CL') {
      return new Node_Classifier( $this->collection, $node_id );
    }
    else {
      return Node_Document::factory( $this->collection, $node_id );
    }
  }

  /**
   * @param Collection $collection
   * @param string $node_id
   * @return Node_Classifier
   */
  public static function factory( Collection $collection, $node_id )
  {
    try {
      return new Node_Classifier( $collection, $node_id );
    }
    catch (InvalidArgumentException $e) {
      return false;
    }
  }

  /**
   * @param Collection $collection
   * @param string $node_id
   * @return Node_Classifier
   */
  protected function staticFactory( Collection $collection, $node_id )
  {
    return Node_Classifier::factory( $collection, $node_id );
  }
}
