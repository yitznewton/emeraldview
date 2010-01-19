<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../public/index.php';

class QueryHandlerTest extends PHPUnit_Framework_TestCase
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
      $builder = QueryBuilder::factory( $set, $collection );
      if (!$builder) { continue; }

      $this->objects[] = new QueryHandler( $builder );
    }
  }

  protected function tearDown()
  {
  }

  public function testQuery()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'array', $object->query() );
    }
  }
}
