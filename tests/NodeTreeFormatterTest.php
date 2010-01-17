<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../public/index.php';

class SlugLookupTest extends PHPUnit_Framework_TestCase
{
  protected $objects = array();
  protected $formatter;

  protected function setUp()
  {
    $collection = Collection::factory( 'demo' );

    $this->objects[] = Node_Document::factory( $collection, 'HASH011ef1906c82786b67228f00' );
    $this->objects[] = Node_Classifier::factory( $collection, 'CL2' );
  }

  protected function tearDown()
  {
  }

  public function testFormat()
  {
    foreach ($this->objects as $object) {
      $formatter = NodePage::factory( $object )->getNodeFormatter();
      $this->assertType( 'string', NodeTreeFormatter::format( $object, $formatter ));
    }
  }
}
