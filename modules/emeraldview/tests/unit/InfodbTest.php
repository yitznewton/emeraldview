<?php
class InfodbTest extends PHPUnit_Framework_TestCase
{
  protected $continuous_paged_collection;

  protected function setUp()
  {
    $this->continuous_paged_collection = Collection::factory( 'tidhar' );

    if ( ! $this->continuous_paged_collection instanceof Collection ) {
      throw new Exception( 'Could not load Collection' );
    }
  }

  public function testGetNodeIdByTitle()
  {
    $this->assertInternalType( 'string',
      $this->continuous_paged_collection->getInfodb()
      ->getNodeIdByTitle('100'));
  }
}
