<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../public/index.php';

class NodePage_ClassifierTest extends PHPUnit_Framework_TestCase
{
  protected $object;

  protected function setUp()
  {
    $collection = Collection::factory( 'demo' );

    $node = Node_Classifier::factory( $collection, 'CL1' );

    if ( ! $node instanceof Node_Classifier ) {
      throw new Exception('Node IDs need to be updated');
    }

    $this->object = $node->getPage();
  }

  protected function tearDown()
  {
  }

  public function testGetUrl()
  {
    $this->assertType( 'string', $this->object->getUrl() );
  }

  public function testGetTree()
  {
    $this->assertType( 'string', $this->object->getTree() );
  }

  public function testGetSubnodeId()
  {
    $subnode_id = $this->object->getSubnodeId();
    $this->assertTrue( $subnode_id === false || strpos( $this->object->getId(), $subnode_id ));
  }

  public function testGetNodeFormatter()
  {
    $this->assertTrue( $this->object->getNodeFormatter() instanceof NodeFormatter );
  }
}
