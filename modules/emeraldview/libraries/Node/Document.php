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
 * Node_Document represents a document or section thereof, and provides
 * basic functionality centered around the node's metadata
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class Node_Document extends Node
{
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
    $new_id = $this->infodb->getCousinIdByDocnum( $this, $new_docnum );

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
    $id = $this->infodb->getCousinIdByTitle( $this, $title );

    return $this->getCousin( $id );
  }

  /**
   * Returns a Node in the document which has the specified Title.  Used in
   * collections built with Greenstone's PagedImagePlugin and paged_continuous
   *
   * @param string $title
   * @return Node_Document
   */
  public function getContinuousCousinByTitle( $title )
  {
    $id = $this->infodb->getNodeIdByTitle( $title );

    return Node::factory( $this->infodb, $id );
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
      $this->getRootNode()->getChildCount()
      && $this->getRootNode()->getField( 'childtype' ) == 'Paged'
    ) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Returns whether the current Node is a PDF section Node of a paged
   * document
   *
   * @return boolean
   */
  public function isPagedPDF()
  {
    return ( $this->getField('FileFormat') == 'PagedPDF' );
  }

  /**
   * Returns an array of all root Node_Document instances for the given
   * Collection
   *
   * @todo refactor this into SlugLookup
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

      $nodes[] = Node::factory( $collection->getInfodb(), $id );
    }

    return $nodes;
  }
}
