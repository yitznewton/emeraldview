<?php
class CollectionTest extends PHPUnit_Framework_TestCase
{
  protected $paged_collection;
  protected $nonpaged_collection;
  protected $continuous_paged_collection;
  protected $continuous_paged_node;
  protected $paged_node;
  protected $nonpaged_node;

  protected function setUp()
  {
    $this->paged_collection = Collection::factory( 'paged' );
    $this->continuous_paged_collection = Collection::factory( 'tidhar' );
    $this->nonpaged_collection = Collection::factory( 'demo' );

    if ( ! $this->paged_collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }

    if ( ! $this->continuous_paged_collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }

    if ( ! $this->nonpaged_collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }

    $this->paged_node = Node_Document::factory( $this->paged_collection,
      'HASH010d952d4f6624863c78611d' );
    $this->continuous_paged_node = Node_Document::factory(
      $this->continuous_paged_collection, 'HASH01fb5e6d0499d20a049915b2' );
    $this->nonpaged_node = Node_Document::factory(
      $this->nonpaged_collection, 'D1' );

    if ( ! $this->paged_node instanceof Node_Document ) {
      throw new Exception( 'Error loading Node' );
    }

    if ( ! $this->continuous_paged_node instanceof Node_Document ) {
      throw new Exception( 'Error loading Node' );
    }

    if ( ! $this->nonpaged_node instanceof Node_Document ) {
      throw new Exception( 'Error loading Node' );
    }
  }

  public function testGetNodeByTitle()
  {
    $this->assertInstanceOf( 'Node_Document',
      $this->continuous_paged_collection->getNodeByTitle( '4875' ) );
  }
}
