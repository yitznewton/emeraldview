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
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * Node_Document represents a document or section thereof, and provides
 * basic functionality centered around the node's metadata
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class Node_Document extends Node
{
  protected function recurse()
  {
    if (
      isset($this->data['contains'])
      && $this->data['contains']
    ) {
      // ... node has 'contains' and is not empty
      $children_names = explode(';', $this->data['contains']);

      $children = array();
      foreach ($children_names as $child) {
        $child_id = str_replace('"', $this->id, $child);
        $this->children[] = $this->getChild( $child_id );
      }
    }
  }

  /**
   * Returns the previous Node in the document's sequence
   *
   * @return Node_Document
   */
  public function getPreviousNode()
  {
    return $this->getNodeFromInterval( -1 );
  }

  /**
   * Returns the next Node in the document's sequence
   *
   * @return Node_Document
   */
  public function getNextNode()
  {
    return $this->getNodeFromInterval( 1 );
  }

  /**
   * Returns a Node in the document which is $interval Nodes after the current
   * Node in the document's sequence
   *
   * @param integer $interval
   * @return Node_Document
   */
  protected function getNodeFromInterval( $interval )
  {
    if (!is_int( $interval )) {
      throw new Exception('Argument must be an integer');
    }

    $starting_docnum = (int) $this->getField( 'docnum' );
    $new_docnum = $starting_docnum + $interval;

    // use ad hoc function rather than write a whole ORM mapping
    $new_id = $this->collection->getInfodb()
              ->getCousinIdByDocnum( $this, $new_docnum );

    return $this->getCousin( $new_id );
  }

  /**
   * Returns a Node in the document which has the specified Title.  Used in
   * collections built with Greenstone's PagedImagePlugin
   *
   * @param string $title
   * @return Node_Document
   */
  public function getCousinByTitle( $title )
  {
    $id = $this->getCollection()->getInfodb()
          ->getCousinIdByTitle( $this, $title );

    return $this->getCousin( $id );
  }

  /**
   * Returns whether the document was built with the PagedImagePlugin or
   * another paged plugin
   *
   * @return boolean
   */
  public function isPaged()
  {
    if (
      $this->getRootNode()->hasChildren()
      && $this->getRootNode()->getField( 'childtype' ) == 'Paged'
    ) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * @param string $node_id
   * @return Node_Document
   */
  protected function getChild( $node_id )
  {
    return Node_Document::factory( $this->collection, $node_id );
  }

  /**
   * Whether the current Node has child Nodes
   *
   * @return boolean
   */
  protected function hasChildren()
  {
    if ($this->children) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Returns an array of all root Node_Document instances for the given
   * Collection
   *
   * @param Collection $collection
   * @return array
   */
  public static function getAllRootNodes( Collection $collection )
  {
    $nodes = array();

    $infodb_nodes = array_keys( $collection->getInfodb()->getAllNodes() );

    foreach ( $infodb_nodes as $id )
    {
      if ( substr( $id, 0, 2 ) == 'CL' || strpos( $id, '.' ) ) {
        // only do root nodes of documents
        continue;
      }

      $nodes[] = Node_Document::factory( $collection, $id );
    }

    return $nodes;
  }

  /**
   * @param Collection $collection
   * @param string $node_id
   * @param boolean $recurse
   * @return Node_Document
   */
  public static function factory(
    Collection $collection, $node_id, $recurse = true
  )
  {
    try {
      return new Node_Document( $collection, $node_id, $recurse );
    }
    catch (InvalidArgumentException $e) {
      return false;
    }
  }

  /**
   *
   * @param Collection $collection
   * @param string $node_id
   * @param boolean $recurse
   * @return Node_Document
   */
  protected function staticFactory(
    Collection $collection, $node_id, $recurse = true
  )
  {
    return Node_Document::factory( $collection, $node_id, $recurse );
  }
}
