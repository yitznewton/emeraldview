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

  public function mockGetNode()
  {
    $args = func_get_args();
    return array( 'id' => $args[0] );
  }

  public function testFactory()
  {
    $this->assertInstanceOf( 'Node_Classifier',
      Node::factory( $this->infodb, 'CL1' ) );

    $this->assertInstanceOf( 'Node_Document',
      Node::factory( $this->infodb, 'D0' ) );

    $this->assertInstanceOf( 'Node_Document',
      Node::factory( $this->infodb, 'HASHASDFHKJ' ) );

    $this->assertFalse( Node::factory( $this->infodb, 'somejunk' ) );
  }
}
