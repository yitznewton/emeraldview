<?php

class Node_Document extends Node
{
  protected $subnode_id;

  protected function __construct(
    Collection $collection, $node_id = null, $root_only = false
  )
  {
    if ($pos = strpos( $node_id, '.') ) {
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
    $new_node = $this->collection->getInfodb()
                ->getRelatedNodeByDocnum( $this, $new_docnum );

    return $new_node;
  }

  public function getRelatedNodeByTitle( $title )
  {
    $id = $this->getCollection()->getInfodb()
          ->getRelatedNodeIdByTitle( $this, $title );

    return $this->getRelatedNode( $id );
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

  protected function getChild( $node_id )
  {
    // TODO refactor this to take subnode/section id rather than full node id
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