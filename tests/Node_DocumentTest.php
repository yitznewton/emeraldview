<?php

require_once 'PHPUnit/Framework.php';
require_once '../public/index.php';

class Node_DocumentTest extends PHPUnit_Framework_TestCase
{
  protected $objects = array();

  protected function setUp()
  {
    $hier_collection = Collection::factory( 'demo' );
    $paged_collection = Collection::factory( 'paged' );

    $root_hier_id   = 'HASH011ef1906c82786b67228f00';
    $branch_hier_id = 'HASH01d3389e1766ebc61610e2b8.7';
    $leaf_hier_id   = 'HASH2035e567bb418c236a5938.2.10';

    $root_paged_id  = 'HASH01afbed20b729ac7cbdb3b66';
    $first_page_id  = 'HASH0143d85cfcd2963e2d266c61.1';
    $middle_page_id = 'HASH0173330302846a9dafdcd462.3';
    $last_page_id   = 'HASH010d952d4f6624863c78611d.5';

    $this->objects[] = Node_Document::factory( $hier_collection, $root_hier_id );
    $this->objects[] = Node_Document::factory( $hier_collection, $branch_hier_id );
    $this->objects[] = Node_Document::factory( $hier_collection, $leaf_hier_id );

    $this->objects[] = Node_Document::factory( $paged_collection, $root_paged_id );
    $this->objects[] = Node_Document::factory( $paged_collection, $first_page_id );
    $this->objects[] = Node_Document::factory( $paged_collection, $middle_page_id );
    $this->objects[] = Node_Document::factory( $paged_collection, $last_page_id );
  }

  protected function tearDown()
  {
  }

  public function testGetId()
  {
    foreach ($this->objects as $object) {
      $this->assertRegExp( '/^(HASH|D)[\d\.]+/', $object->getId() );
    }
  }

  public function testGetRootId()
  {
    foreach ($this->objects as $object) {
      $this->assertRegExp( '/^\w+$/', $object->getRootId() );
    }
  }

  public function testGetSubnodeId()
  {
    foreach ($this->objects as $object) {
      $subnode_id = $object->getSubnodeId();

      $this->assertTrue( $subnode_id === false
                         || strpos( $object->getId(), $subnode_id ) !== false );
    }
  }

  public function testGetRootNode()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getRootNode() instanceof Node_Document );
    }
  }

  public function testGetPage()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getPage() instanceof NodePage_DocumentSection );
    }
  }

  public function testGetRelatedNode()
  {
    foreach ($this->objects as $object) {
      $related = $object->getRelatedNode( '1' );
      $this->assertTrue( $related === false || $related instanceof Node_Document );
      $this->assertTrue( $related === false || $object->getRootId() === $related->getRootId() );

      $related = $object->getRelatedNode( '3.4' );
      $this->assertTrue( $related === false || $related instanceof Node_Document );
      $this->assertTrue( $related === false || $object->getRootId() === $related->getRootId() );
    }
  }

  public function testGetCollection()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getCollection() instanceof Collection );
    }
  }

  public function testGetChildren()
  {
    foreach ($this->objects as $object) {
      $children = $object->getChildren();

      $this->assertTrue( $children === false || is_array( $children ) );

      if (is_array( $children )) {
        foreach ($children as $child) {
          $this->assertTrue( $child instanceof Node_Document );
        }
      }
    }
  }

  public function testGetField()
  {
    $fieldnames = array(
      'Title', 'Author', 'dc.Title', 'jimbob', 'Subject', 'dc.Subject',
    );

    foreach ($this->objects as $object) {
      foreach ($fieldnames as $fieldname) {
        $field = $object->getField( $fieldname );

        $this->assertTrue(
          is_string( $field )
          || is_array( $field )
          || $field === false
        );
      }
      
      // 'children' member of array should have been unset
      $this->assertFalse( $object->getField('children') );
    }
  }

  public function testGetAllFields()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'array', $object->getAllFields() );
    }
  }
}

