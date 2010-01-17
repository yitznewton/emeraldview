<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../public/index.php';

class NodeTreePagerTest extends PHPUnit_Framework_TestCase
{
  protected $objects = array();

  protected function setUp()
  {
    $hier_collection = Collection::factory( 'demo' );
    $paged_collection = Collection::factory( 'paged' );

    $root_hier_id   = 'HASH011ef1906c82786b67228f00';
    $branch_hier_id = 'HASH01d3389e1766ebc61610e2b8.7';
    $leaf_hier_id   = 'HASH2035e567bb418c236a5938.2.10';

    $root_paged_id  = 'HASH01afbed20b729ac7cbdb3b66';
    $first_page_id  = 'HASH0143d85cfcd2963e2d266c61.1';
    $middle_page_id = 'HASH0173330302846a9dafdcd462.3';
    $last_page_id   = 'HASH010d952d4f6624863c78611d.5';

    $this->objects[] = Node_Document::factory( $hier_collection, $root_hier_id );
    $this->objects[] = Node_Document::factory( $hier_collection, $branch_hier_id );
    $this->objects[] = Node_Document::factory( $hier_collection, $leaf_hier_id );

    $this->objects[] = Node_Document::factory( $paged_collection, $root_paged_id );
    $this->objects[] = Node_Document::factory( $paged_collection, $first_page_id );
    $this->objects[] = Node_Document::factory( $paged_collection, $middle_page_id );
    $this->objects[] = Node_Document::factory( $paged_collection, $last_page_id );

    foreach ($this->objects as $object) {
      if ( ! $object instanceof Node_Document ) {
        throw new Exception('Node IDs need to be updated');
      }
    }

    $mofile = realpath(PUBLICPATH . 'views/default/locale/'
            . 'en' . '.mo');

    if (!$mofile) {
      throw new Exception("Could not find .mo file for language $language");
    }

    L10n::load( $mofile );
    L10n::setLanguage( 'en' );
  }

  protected function tearDown()
  {
  }

  public function testHtml()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'string', NodeTreePager::html( $object ));
    }
  }
}
