<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../public/index.php';

class Node_ClassifierTest extends PHPUnit_Framework_TestCase
{
  protected $objects = array();

  protected function setUp()
  {
    $collection = Collection::factory( 'demo' );

    $root_id   = 'CL1';
    $middle_id = 'CL2.6';  // in between root and another Node_Classifier
    $last_id   = 'CL3.4';  // last node before document

    $this->objects[] = Node_Classifier::factory( $collection, $root_id );
    $this->objects[] = Node_Classifier::factory( $collection, $middle_id );
    $this->objects[] = Node_Classifier::factory( $collection, $last_id );

    foreach ($this->objects as $object) {
      if ( ! $object instanceof Node_Classifier ) {
        throw new Exception('Node IDs need to be updated');
      }
    }
  }

  protected function tearDown()
  {
  }

  public function testGetId()
  {
    foreach ($this->objects as $object) {
      $this->assertRegExp( '/^CL[\d\.]+/', $object->getId() );
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
      $this->assertTrue( $object->getRootNode() instanceof Node_Classifier );
    }
  }

  public function testGetPage()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getPage() instanceof NodePage_Classifier );
    }
  }

  public function testGetRelatedNode()
  {
    foreach ($this->objects as $object) {
      $related = $object->getCousin( '1' );
      $this->assertTrue( $related === false || $related instanceof Node_Classifier );
      $this->assertTrue( $related === false || $object->getRootId() === $related->getRootId() );

      $related = $object->getCousin( '3.4' );
      $this->assertTrue( $related === false || $related instanceof Node_Classifier );
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
          $this->assertTrue( $child instanceof Node_Classifier || $child instanceof Node_Document );
        }
      }
    }
  }

  public function testGetField()
  {
    $fieldnames = array(
      'Title', 'hastxt',
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

  public function testFormat()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'string', $object->format() );
    }
  }
}

