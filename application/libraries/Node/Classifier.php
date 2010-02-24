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
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * Node_Classifier represents a classifier or section thereof, and provides
 * basic functionality centered around the node's metadata
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class Node_Classifier extends Node
{
  /**
   * Not yet implemented
   *
   * @ignore
   * @link http://bitbucket.org/yitznewton/emeraldview/issue/8/mdoffset-implementation
   * @var array
   */
  protected $mdoffsets;

  /**
   * @param Collection $collection
   * @param string $node_id
   * @param boolean $recurse
   */
  protected function __construct(
    Collection $collection, $node_id = null, $recurse = true
  )
  {
    parent::__construct( $collection, $node_id, $recurse );
    
    if ( isset( $this->data['mdoffset'] ) && $this->data['mdoffset'] !== '') {
      $this->mdoffsets = explode( ';', $this->data['mdoffset'] );
    }

    unset( $this->data['mdoffset'] );
  }

  /**
   * Not yet implemented
   *
   * @ignore
   * @link http://bitbucket.org/yitznewton/emeraldview/issue/8/mdoffset-implementation
   * @return array
   */
  private function getMdOffsets()
  {
    return $this->mdoffsets;
  }

  protected function recurse()
  {
    if (
      substr($this->id, 0, 1) == 'C'
      && isset($this->data['contains'])
      && $this->data['contains']
    ) {
      // ... node has 'contains' and is not empty
      $children_names = split(';', $this->data['contains']);

      $children = array();
      foreach ($children_names as $child) {
        $child_id = str_replace('"', $this->id, $child);
        $this->children[] = $this->getChild( $child_id );
      }
    }
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
      return Node_Document::factory( $this->collection, $node_id, false );
    }
  }

  /**
   * @param Collection $collection
   * @param string $node_id
   * @param boolean $recurse
   * @return Node_Classifier
   */
  public static function factory(
    Collection $collection, $node_id, $recurse = true
  )
  {
    try {
      return new Node_Classifier( $collection, $node_id, $recurse );
    }
    catch (InvalidArgumentException $e) {
      return false;
    }
  }

  /**
   * @param Collection $collection
   * @param string $node_id
   * @param boolean $recurse
   * @return Node_Classifier
   */
  protected function staticFactory(
    Collection $collection, $node_id, $recurse = true
  )
  {
    return Node_Classifier::factory( $collection, $node_id, $recurse );
  }
}
