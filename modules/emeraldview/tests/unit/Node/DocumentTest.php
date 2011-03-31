<?php
class Node_DocumentTest extends PHPUnit_Framework_TestCase
{
  protected $collection;
  protected $infodb_unpaged;
  protected $infodb_paged;
  protected $infodb_paged_pdf;

  public function setUp()
  {
    $this->collection = $this->getMockBuilder('Collection')
                        ->disableOriginalConstructor()
                        ->getMock();

    $infodb = $this->getMockBuilder('Infodb')
              ->disableOriginalConstructor()
              ->getMock();

    $this->infodb_unpaged = clone $infodb;

    $this->infodb_unpaged->expects( $this->any() )
                         ->method('getNode')
                         ->will( $this->returnCallback(
                           array( $this, 'mockGetNodeUnpaged' ) ) );

    $this->infodb_paged = clone $infodb;

    $this->infodb_paged->expects( $this->any() )
                         ->method('getNode')
                         ->will( $this->returnCallback(
                           array( $this, 'mockGetNodePaged' ) ) );

    $this->infodb_paged_pdf = clone $infodb;

    $this->infodb_paged_pdf->expects( $this->any() )
      ->method('getNode')
      ->will( $this->returnCallback(
      array( $this, 'mockGetNodePagedPdf' ) ) );
  }

  public function mockGetNodeUnpaged()
  {
    $args = func_get_args();

    $id = $args[0];

    switch ( $id ) {
      case 'D0':
        return array(
          'id'        => $id,
          'contains'  => '.1',
          'childtype' => 'foonotpaged',
        );
      case 'D0.1':
        return array( 'id' => $id );
      default:
        throw new UnexpectedValueException();
    }
  }

  public function mockGetNodePaged()
  {
    $args = func_get_args();

    $id = $args[0];

    switch ( $id ) {
      case 'D0':
        return array(
          'id'        => $id,
          'contains'  => '.1',
          'childtype' => 'Paged',
        );
      case 'D0.1':
        return array( 'id' => $id );
      default:
        throw new UnexpectedValueException();
    }
  }

  public function mockGetNodePagedPdf()
  {
    $args = func_get_args();

    $id = $args[0];

    switch ( $id ) {
      case 'D0':
        return array(
          'id'        => $id,
          'contains'  => '.1',
          'childtype' => 'Paged',
        );
      case 'D0.1':
        return array(
          'id'         => $id,
          'FileFormat' => 'PagedPDF',
        );
      default:
        throw new UnexpectedValueException();
    }
  }

  public function testIsPaged()
  {
    $this->assertFalse(
      Node::factory( $this->infodb_unpaged, 'D0' )->isPaged() );
    
    $this->assertFalse(
      Node::factory( $this->infodb_unpaged, 'D0.1' )->isPaged() );

    $this->assertTrue(
      Node::factory( $this->infodb_paged, 'D0' )->isPaged() );
    
    $this->assertTrue(
      Node::factory( $this->infodb_paged, 'D0.1' )->isPaged() );

    $this->assertTrue(
      Node::factory( $this->infodb_paged_pdf, 'D0' )->isPaged() );

    $this->assertTrue(
      Node::factory( $this->infodb_paged_pdf, 'D0.1' )->isPaged() );
  }

  public function testIsPagedPDF()
  {
    $this->assertFalse(
      Node::factory( $this->infodb_unpaged, 'D0' )->isPagedPDF() );

    $this->assertFalse(
      Node::factory( $this->infodb_unpaged, 'D0.1' )->isPagedPDF() );

    $this->assertFalse(
      Node::factory( $this->infodb_paged, 'D0' )->isPagedPDF() );

    $this->assertFalse(
      Node::factory( $this->infodb_paged, 'D0.1' )->isPagedPDF() );

    $this->assertFalse(
      Node::factory( $this->infodb_paged_pdf, 'D0' )->isPagedPDF() );

    $this->assertTrue(
      Node::factory( $this->infodb_paged_pdf, 'D0.1' )->isPagedPDF() );
  }
}
