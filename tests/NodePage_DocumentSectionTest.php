<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../public/index.php';

class NodePage_DocumentSectionTest extends PHPUnit_Framework_TestCase
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

    $nodes[] = Node_Document::factory( $hier_collection, $root_hier_id );
    $nodes[] = Node_Document::factory( $hier_collection, $branch_hier_id );
    $nodes[] = Node_Document::factory( $hier_collection, $leaf_hier_id );

    $nodes[] = Node_Document::factory( $paged_collection, $root_paged_id );
    $nodes[] = Node_Document::factory( $paged_collection, $first_page_id );
    $nodes[] = Node_Document::factory( $paged_collection, $middle_page_id );
    $nodes[] = Node_Document::factory( $paged_collection, $last_page_id );

    foreach ($nodes as $node) {
      if ( ! $node instanceof Node_Document ) {
        throw new Exception('Node IDs need to be updated');
      }

      $this->objects[] = $node->getPage();
    }
  }

  protected function tearDown()
  {
  }

  public function testGetUrl()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'string', $object->getUrl() );
    }
  }

  public function testGetTree()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'string', $object->getTree() );
    }
  }

  public function testGetSubnodeId()
  {
    foreach ($this->objects as $object) {
      $subnode_id = $object->getSubnodeId();
      $this->assertTrue( $subnode_id === false || strpos( $object->getId(), $subnode_id ));
    }
  }

  public function testGetNodeFormatter()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getNodeFormatter() instanceof NodeFormatter );
    }
  }

  public function testGetCoverUrl()
  {
    // TODO: use curl to test the URLs themselves?
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getCoverUrl() === false || is_string( $object->getCoverUrl() ) );
    }
  }

  public function testGetDisplayMetadata()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'array', $object->getDisplayMetadata() );
    }
  }

  public function testGetHTML()
  {
    foreach ($this->objects as $object) {
      $this->assertType( 'string', $object->getHTML() );
    }
  }

  public function testGetPagedUrls()
  {
    foreach ($this->objects as $object) {
      $urls = $object->getPagedUrls();
      $this->assertTrue( $urls === false || is_array( $urls ) );

      if ($urls) {
        foreach ($urls as $url) {
          $this->assertType( 'string', $url );
        }
      }
    }
  }

  public function testGetScreenIconUrl()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getScreenIconUrl() === false || is_string( $object->getScreenIconUrl() ) );
    }
  }

  public function testGetSourceDocumentUrl()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getSourceDocumentUrl() === false || is_string( $object->getSourceDocumentUrl() ) );
    }
  }

  public function testGetThumbnailUrl()
  {
    foreach ($this->objects as $object) {
      $this->assertTrue( $object->getThumbnailUrl() === false || is_string( $object->getThumbnailUrl() ) );
    }
  }
}
