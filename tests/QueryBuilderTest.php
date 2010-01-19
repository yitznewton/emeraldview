<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../public/index.php';

class QueryBuilderTest extends PHPUnit_Framework_TestCase
{
  protected $objects = array();

  protected function setUp()
  {
    $param_sets[ 'simple' ] = array(
      'q' => 'boy',
    );
    $param_sets[ 'blank' ] = array(
      'q' => '',
    );
    /*
    $param_sets[ 'fielded' ] = array(
      'i' => 'TI',
      'q' => 'education',
    );
    $param_sets[ 'boolean' ] = array(
      'i1' => 'ti',
      'q1' => 'education',
      'i2' => '',
      'q2' => 'boy',
    );
    */

    $collection = Collection::factory( 'demo' );

    foreach ($param_sets as $key => $set) {
      $object = QueryBuilder::factory( $set, $collection );
      if ($object) { $this->objects[ $key ] = $object; }
    }
  }

  protected function tearDown()
  {
  }

  public function testGetLevel()
  {
    foreach ($this->objects as $object) {
      $this->assertContains( $object->getLevel(), array( 'document', 'section' ) );
    }
  }

  public function testGetQuery()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getQuery() instanceof Zend_Search_Lucene_Search_Query );
    }
  }

  public function testGetDisplayQuery()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'string', $object->getDisplayQuery() );
    }
  }

  public function testGetRawTerms()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'array', $object->getRawTerms() );
    }
  }
}
