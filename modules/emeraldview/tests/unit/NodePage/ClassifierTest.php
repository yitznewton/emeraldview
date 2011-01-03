<?php
class NodePage_ClassifierTest extends PHPUnit_Framework_TestCase
{
  protected $collection;

  protected function setUp()
  {
    $this->collection = Collection::factory( 'demo' );
  }

  public function testRetrieveBySlug()
  {
    $this->assertInstanceOf( 'NodePage_Classifier',
      NodePage_Classifier::retrieveBySlug( $this->collection, 'title' ) );

    $this->assertInstanceOf( 'NodePage_Classifier',
      NodePage_Classifier::retrieveBySlug( $this->collection, 'how-to' ) );

    $this->assertFalse(
      NodePage_Classifier::retrieveBySlug( $this->collection, 'foo-bar' ) );
  }
}
