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
 * Node represents a classifier, document or section thereof, and provides
 * basic functionality centered around the node's metadata
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
abstract class Node
{
  /**
   * The parent Collection
   *
   * @var Collection
   */
  protected $collection;
  /**
   * @var string
   */
  protected $id;
  /**
   * The Node's metadata
   *
   * @var array
   */
  protected $data;
  /**
   * The Node's child Nodes
   *
   * @var array
   */
  protected $children = array();
  /**
   * The root Node of this classifier or document
   *
   * @var Node
   */
  protected $rootNode;
  
  /**
   * Returns the current Node's child of specified id.  This is abstract
   * because, for example, Node_Classifier::getChild() may return a
   * Node_Classifier or a Node_Document depending on the context
   *
   * @todo just incorporate subclass detection based on id into Node::factory (#20)
   * @param string $node_id
   * @return Node
   */
  abstract protected function getChild( $node_id );
  
  /**
   * @param Collection $collection
   * @param string $node_id
   */
  protected function __construct(
    Collection $collection, $node_id = null
  )
  {
    $this->id = $node_id;
    $this->data = $collection->getInfodb()->getNode( $this->id );

    if ( ! $this->data ) {
      throw new InvalidArgumentException('No such node');
    }

    $this->collection = $collection;
  }
  
  /**
   * Returns a string representing the Node in a greater context (e.g. classifier
   * tree or search results list)
   *
   * @return string
   */
  public function format()
  {
    $node_formatter = NodeFormatter::factory( $this );
    $text = $node_formatter->format();

    if ( strpos( $text, '<a' ) === false ) {
      $text = html::anchor( $this->getNodePage()->getUrl(), $text );
    }

    return $text;
  }

  /**
   * Returns the parent Node one level up
   *
   * @return Node
   */
  public function getParent()
  {
    if ( $this->id == $this->getRootId() ) {
      return false;
    }

    $parent_id = substr( $this->id, 0, strrpos( $this->id, '.' ) );
    
    return $this->getCousin( $parent_id );
  }

  /**
   * Returns an array of the lineage of all ancestor Node objects
   *
   * @return array
   */
  public function getAncestors()
  {
    $id = $this->getId();
    $ancestors = array();

    while ( strpos( $id, '.' ) !== false ) {
      $id = substr( $id, 0, strrpos( $id, '.' ) );
      $ancestors[] = $this->getCousin( $id );
    }

    return array_reverse( $ancestors );
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Returns the id of the root Node of this entity (classifier/document)
   *
   * @return string
   */
  public function getRootId()
  {
    if (strpos( $this->id, '.' )) {
      return substr( $this->id, 0, strpos( $this->id, '.' ) );
    }
    else {
      return $this->id;
    }
  }

  /**
   * Returns the root Node of this entity (classifier/document)
   *
   * @return Node
   */
  public function getRootNode()
  {
    if ($this->rootNode) {
      return $this->rootNode;
    }

    if ( $this->getRootId() == $this->getId() ) {
      $this->rootNode = $this;

      return $this->rootNode;
    }

    $class = get_class( $this );

    // ugly workaround for lack of LSB in < 5.3
    $this->rootNode = Node::factory(
      $this->collection, $this->getRootId()
    );

    return $this->rootNode;
  }

  /**
   * Returns the section portion of the id
   *
   * @return string
   */
  public function getSubnodeId()
  {
    if (strpos( $this->id, '.' )) {
      return substr( $this->id, strpos( $this->id, '.' ) + 1 );
    }
    else {
      return false;
    }
  }

  /**
   * Returns a NodePage built around the Node
   *
   * @return NodePage
   */
  public function getNodePage()
  {
    return NodePage::factory( $this );
  }

  /**
   * Returns a descendent of the root Node which has the supplied node id
   * or subnode id
   *
   * @param string $id
   * @return Node
   */
  public function getCousin( $id )
  {
    if ( strpos( $id, $this->getRootId() ) === false ) {
      // client did not specify the root ID (really this is the more sensible
      // way to call, but we are accomodating certain methods that favor
      // the full subnode id)
      $id = $this->getRootId() . '.' . $id;
    }

    return Node::factory( $this->collection, $id );
  }

  /**
   * Returns the parent Collection
   *
   * @return Collection
   */
  public function getCollection()
  {
    return $this->collection;
  }

  /**
   * Returns an array of all child Nodes
   *
   * @return array
   */
  public function getChildren()
  {
    if ( $this->children ) {
      return $this->children;
    }

    if (
      isset($this->data['contains'])
      && $this->data['contains']
    ) {
      // ... node has 'contains' and is not empty
      $children_names = explode( ';', $this->getField( 'contains' ) );

      $children = array();
      foreach ($children_names as $child) {
        $child_id = str_replace('"', $this->id, $child);
        $this->children[] = $this->getChild( $child_id );
      }
    }

    unset( $this->data['contains'] );

    return $this->children;
  }

  /**
   * The number of child Nodes that this Node posesses
   *
   * @return integer
   */
  public function getChildCount()
  {
    if ( $this->children ) {
      return count( $this->children );
    }
    elseif ( $this->getField( 'contains' ) ) {
      return count( explode( ';', $this->getField( 'contains') ) );
    }
    else {
      return 0;
    }
  }

  /**
   * Returns a string (if single value) or array of strings (if multiple
   * values) corresponding to the specified metadata field for the current Node
   *
   * @param string $field_name
   * @return mixed
   */
  public function getField( $field_name )
  {
    if (array_key_exists( $field_name, $this->data )) {
      return $this->data[ $field_name ];
    }
    else {
      return false;
    }
  }

  /**
   * Finds the first metadata field for the current Node among the specified
   * fields, and returns the value(s)
   *
   * @param array $field_names
   * @return mixed
   */
  public function getFirstFieldFound( $field_names )
  {
    if ( ! is_array( $field_names ) ) {
      $field_names = array( $field_names );
    }

    foreach ( $field_names as $field ) {
      if ( $this->getField( $field ) ) {
        return $this->getField( $field );
      }
    }

    return false;
  }

  /**
   * Returns an array of all of the current Node's metadata
   *
   * @return array
   */
  public function getAllFields()
  {
    return $this->data;
  }

  /**
   * @param Collection $collection
   * @param string $node_id
   * @return Node
   */
  public static function factory( Collection $collection, $node_id )
  {
    try {
      if ( substr( $node_id, 0, 2 ) == 'CL' ) {
        return new Node_Classifier( $collection, $node_id );
      }
      else {
        return new Node_Document( $collection, $node_id );
      }
    }
    catch (InvalidArgumentException $e) {
      return false;
    }
  }
}
