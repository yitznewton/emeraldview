<?php

class Hit
{
  public $title;
  public $link;
  public $snippet;

  public function __construct( Zend_Search_Lucene_Search_QueryHit $lucene_hit, Collection $collection )
  {
    $node = Node_Document::factory( $collection, $lucene_hit->docOID );
    // FIXME: designate field(s) used in config
    $this->title = $node->getField( 'Title' );
    $this->link = NodePage_DocumentSection::factory( $node )->getUrl();

    if (isset( $lucene_hit->TX )) {
      $this->snipped = $lucene_hit->TX;
    }
  }
}