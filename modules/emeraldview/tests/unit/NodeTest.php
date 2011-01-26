<?php
class MockCollection extends Collection
{
  public function __construct() {}
}

class MockInfodb extends Infodb
{
  public function __construct() {}
  public function getDocumentMetadata( $id ) {}
  public function getClassifierIds() {}
  public function getAllNodes() {}
  public function getCousinIdByDocnum( Node_Document $node, $docnum ) {}
  public function getNodeIdByTitle( $title ) {}
  public function getCousinIdByTitle( Node_Document $node, $title ) {}
  public function getRandomLeafNodeIdsHavingMetadata( $element, $count = 1 ) {}
  public function getRandomLeafNodeIds( Node_Classifier $node, $count = 1 ) {}

  public function getNode( $id ) {
    return array( 'id' => $id );
  }
}

class NodeTest extends PHPUnit_Framework_TestCase
{
  protected $collection;
  protected $infodb;

  public function setUp()
  {
    $this->collection = new MockCollection();
    $this->infodb     = new MockInfoDb();
  }

  public function testFactory()
  {
    $this->assertInstanceOf( 'Node_Classifier',
      Node::factory( $this->infodb, 'CL1' ) );
    $this->assertInstanceOf( 'Node_Document',
      Node::factory( $this->infodb, 'D0' ) );
  }
}
