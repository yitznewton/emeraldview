<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../public/index.php';

class SlugLookupTest extends PHPUnit_Framework_TestCase
{
  protected $object;

  protected function setUp()
  {
    $collection = Collection::factory( 'demo' );
    $this->object = new SlugLookup( $collection );
  }

  protected function tearDown()
  {
  }

  public function testRetrieveSlug()
  {
    $ids = array(
      'HASH01d3389e1766ebc61610e2b8',
      'HASH0173330302846a9dafdcd462',
      'bob',
    );

    foreach ($ids as $id) {
      $slug = $this->object->retrieveSlug( $id );
      $this->assertTrue( $slug === false || is_string( $slug ) );
    }
  }

  public function testRetrieveId()
  {
    $slugs = array(
      'te-whetu-o-te-tau',
      'te-waka-o-te-iwi',
      'bob',
    );

    foreach ($slugs as $slug) {
      $id = $this->object->retrieveId( $slug );
      $this->assertTrue( $id === false || preg_match( '/^(HASH|D)\w+$/', $id ) );
    }
  }
}
