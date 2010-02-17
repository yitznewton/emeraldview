<?php
/**
 * Node_Document class definition
 * @package libraries
 */
/**
 * Node_Document class definition
 * @package libraries
 */
class Node_Document extends Node
{
  protected $subnode_id;

  protected function __construct(
    Collection $collection, $node_id = null, $root_only = false
  )
  {
    $pos = strpos( $node_id, '.');

    if ( $pos ) {
      $this->subnode_id = substr( $node_id, $pos + 1 );
    }

    parent::__construct( $collection, $node_id, $root_only );
  }

  protected function recurse()
  {
    if (
      isset($this->data['contains'])
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
  
  public function getPreviousNode()
  {
    return $this->getNodeFromInterval( -1 );
  }

  public function getNextNode()
  {
    return $this->getNodeFromInterval( 1 );
  }

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

  public function getCousinByTitle( $title )
  {
    $id = $this->getCollection()->getInfodb()
          ->getCousinIdByTitle( $this, $title );

    return $this->getCousin( $id );
  }

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
   * Returns a specified subnode of $this
   * @param string $node_id The complete node ID of the desired subnode
   * @return Node_Document
   */
  protected function getChild( $node_id )
  {
    return Node_Document::factory( $this->collection, $node_id );
  }

  protected function hasChildren()
  {
    if ($this->children) {
      return true;
    }
    else {
      return false;
    }
  }

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

  public static function factory(
    Collection $collection, $node_id, $root_only = false
  )
  {
    try {
      return new Node_Document( $collection, $node_id, $root_only );
    }
    catch (InvalidArgumentException $e) {
      return false;
    }
  }

  protected function staticFactory(
    Collection $collection, $node_id, $root_only = false
  )
  {
    return Node_Document::factory( $collection, $node_id, $root_only );
  }
}
