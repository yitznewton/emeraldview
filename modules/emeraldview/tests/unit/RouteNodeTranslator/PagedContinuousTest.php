<?php
class RouteNodeTranslator_PagedContinuousTest extends PHPUnit_Framework_TestCase
{
  protected $collection;
  protected $root_node;

  protected function setUp()
  {
    $this->collection = Collection::factory( 'tidhar' );

    if ( ! $this->collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }
    
    $this->root_node = Node_Document::factory( $this->collection,
      'HASH01fb5e6d0499d20a049915b2' );
    
    if ( ! $this->root_node instanceof Node_Document ) {
      throw new Exception( 'Error loading Node' );
    }
  }

  public function testFactory()
  {
    $this->assertInstanceOf( 'RouteNodeTranslator_PagedContinuous',
      RouteNodeTranslator::factory( $this->root_node ) );
  }

  /**
   * @expectedException UnexpectedValueException
   */
  public function testGetNode()
  {
    $this->assertTrue( $this->collection->getConfig( 'paged_continuous' ) );

    $rnt = RouteNodeTranslator::factory( $this->root_node );

    $existing_subnode_args = array(
      array(),
      array( '4873' ),
      array( 4873 ),
      array( '5081' ),
      array( 5081 ),
    );

    $nonexisting_subnode_args = array(
      array( false ),
      array( null ),
      array( '9999' ),
      array( 9999 ),
    );

    foreach ( $existing_subnode_args as $args ) {
      $this->assertInstanceOf( 'Node_Document', $rnt->getNode( $args ) );
    }

    foreach ( $nonexisting_subnode_args as $args ) {
      $this->assertFalse( $rnt->getNode( $args ) );
    }
  }
}
