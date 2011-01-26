<?php
class RouteNodeTranslator_PagedTest extends PHPUnit_Framework_TestCase
{
  protected $collection;
  protected $root_node;

  protected function setUp()
  {
    $this->collection = Collection::factory( 'paged' );

    if ( ! $this->collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }

    $this->root_node = Node::factory( $this->collection,
      'HASH010d952d4f6624863c78611d' );

    if ( ! $this->root_node instanceof Node_Document ) {
      throw new Exception( 'Error loading Node' );
    }
  }

  public function testFactory()
  {
    $this->assertInstanceOf( 'RouteNodeTranslator_Paged',
      RouteNodeTranslator::factory( $this->root_node ) );
  }

  /**
   * @expectedException UnexpectedValueException
   */
  public function testGetNode()
  {
    $rnt = RouteNodeTranslator::factory( $this->root_node );

    $existing_subnode_args = array(
      array(),
      array( '9' ),
      array( 9 ),
    );

    $nonexisting_subnode_args = array(
      array( false ),
      array( null ),
      array( '999' ),
      array( 999 ),
    );

    foreach ( $existing_subnode_args as $args ) {
      $this->assertInstanceOf( 'Node_Document', $rnt->getNode( $args ) );
      $this->assertEquals( $this->root_node->getId(),
        $rnt->getNode( $args )->getRootId() );
    }

    foreach ( $nonexisting_subnode_args as $args ) {
      $this->assertFalse( $rnt->getNode( $args ) );
    }
  }
}
