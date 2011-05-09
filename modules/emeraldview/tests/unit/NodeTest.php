<?php
class NodeTest extends PHPUnit_Framework_TestCase
{
  protected $collection;
  protected $infodb;

  public function setUp()
  {
    $this->collection = $this->getMockBuilder('Collection')
                        ->disableOriginalConstructor()
                        ->getMock();

    $this->infodb = $this->getMockBuilder('Infodb')
                    ->disableOriginalConstructor()
                    ->getMock();

    $this->infodb->expects( $this->any() )
                 ->method('getNode')
                 ->will( $this->returnCallback(
                   array( $this, 'mockGetNode' ) )
                 );
  }

  public function mockGetNode( $id )
  {
    if ( $id == 'D0' ) {
      $children = '".1';
    }
    else {
      $children = false;
    }

    return array( 'id' => $id, 'contains' => $children );
  }

  public function testFactory()
  {
    $this->assertInstanceOf( 'Node_Classifier',
      Node::factory( $this->infodb, 'CL1' ) );

    $this->assertInstanceOf( 'Node_Document',
      Node::factory( $this->infodb, 'D0' ) );

    $this->assertInstanceOf( 'Node_Document',
      Node::factory( $this->infodb, 'HASHASDFHKJ' ) );
  }

  public function testGetChildren()
  {
    $node_with_children = Node::factory( $this->infodb, 'D0' );
    $node_without_children = Node::factory( $this->infodb, 'D1' );

    $this->assertInternalType( 'array', $node_with_children->getChildren() );
    $this->assertInternalType( 'array', $node_without_children->getChildren() );
  }
}
