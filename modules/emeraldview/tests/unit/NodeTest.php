<?php
class NodeTest extends PHPUnit_Framework_TestCase
{
  protected $collection;

  public function setUp()
  {
    $this->collection = Collection::factory( 'demo' );

    if ( ! $this->collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }
  }

  public function testFactory()
  {
    $this->assertInstanceOf( 'Node_Classifier', Node::factory( $this->collection, 'CL1' ) );
    $this->assertInstanceOf( 'Node_Document', Node::factory( $this->collection, 'D0' ) );
  }
}
