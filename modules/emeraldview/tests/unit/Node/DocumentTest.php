<?php
class Node_DocumentTest extends PHPUnit_Framework_TestCase
{
  public function testIsPaged()
  {
    $this->assertFalse( Node::factory( Collection::factory( 'demo' ),
      'D0' )->isPaged() );
    $this->assertFalse( Node::factory( Collection::factory( 'demo' ),
      'D0.1' )->isPaged() );
    $this->assertTrue( Node::factory( Collection::factory( 'paged' ),
      'HASH010d952d4f6624863c78611d' )->isPaged() );
    $this->assertTrue( Node::factory( Collection::factory( 'paged' ),
      'HASH010d952d4f6624863c78611d.1' )->isPaged() );
    $this->assertTrue( Node::factory( Collection::factory( 'memory' ),
      'D0' )->isPaged() );
    $this->assertTrue( Node::factory( Collection::factory( 'memory' ),
      'D0.1' )->isPaged() );
  }

  public function testIsPagedPDF()
  {
    $this->assertFalse( Node::factory( Collection::factory( 'demo' ),
      'D0' )->isPagedPDF() );
    $this->assertFalse( Node::factory( Collection::factory( 'paged' ),
      'HASH010d952d4f6624863c78611d' )->isPagedPDF() );
    $this->assertFalse( Node::factory( Collection::factory( 'paged' ),
      'HASH010d952d4f6624863c78611d.1' )->isPagedPDF() );
    $this->assertFalse( Node::factory( Collection::factory( 'memory' ),
      'D0' )->isPagedPDF() );
    $this->assertTrue( Node::factory( Collection::factory( 'memory' ),
      'D0.1' )->isPagedPDF() );
  }
}
