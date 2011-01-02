<?php
require_once '/www/websites/emeraldview/modules/emeraldview/tests/unit/bootstrap.php';

class RouteNodeTranslatorTest extends PHPUnit_Framework_TestCase
{
  protected $paged_collection;
  protected $nonpaged_collection;
  protected $paged_node;
  protected $nonpaged_node;

  protected function setUp()
  {
    $this->nonpaged_collection = Collection::factory( 'demo' );
    $this->paged_collection = Collection::factory( 'paged' );

    if ( ! $this->nonpaged_collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }

    if ( ! $this->paged_collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }

    $this->nonpaged_node = Node_Document::factory( $this->nonpaged_collection, 'D1' );
    $this->paged_node = Node_Document::factory( $this->paged_collection, 'HASH010d952d4f6624863c78611d' );

    if ( ! $this->nonpaged_node instanceof Node_Document ) {
      throw new Exception( 'Error loading Node' );
    }

    if ( ! $this->paged_node instanceof Node_Document ) {
      throw new Exception( 'Error loading Node' );
    }
  }

  public function testFactory()
  {
    $this->assertInstanceOf( 'RouteNodeTranslator_Paged',
      RouteNodeTranslator::factory( $this->paged_node ) );
    $this->assertInstanceOf( 'RouteNodeTranslator',
      RouteNodeTranslator::factory( $this->nonpaged_node ) );
  }

  /**
   * @expectedException UnexpectedValueException
   */
  public function testGetNodePaged()
  {
    $rnt = RouteNodeTranslator::factory( $this->paged_node );

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
    }

    foreach ( $nonexisting_subnode_args as $args ) {
      $this->assertFalse( $rnt->getNode( $args ) );
    }
  }

  public function testGetNodeNonpaged()
  {
    $rnt = RouteNodeTranslator::factory( $this->nonpaged_node );

    $existing_subnode_args = array(
      array(),
      array( '4' ),
      array( 4 ),
      array( '4', '1' ),
      array( '4', 1 ),
    );

    $nonexisting_subnode_args = array(
      array( false ),
      array( null ),
      array( '999' ),
      array( 999 ),
    );

    foreach ( $existing_subnode_args as $args ) {
      $this->assertInstanceOf( 'Node_Document', $rnt->getNode( $args ) );
    }

    foreach ( $nonexisting_subnode_args as $args ) {
      $this->assertFalse( $rnt->getNode( $args ) );
    }
  }
}
