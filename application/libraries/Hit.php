<?php

class Hit
{
  public $title;
  public $link;
  public $snippet;

  public function __construct( Zend_Search_Lucene_Search_QueryHit $lucene_hit, SearchHandler $search_handler )
  {
    $collection = $search_handler->getCollection();
    $terms = $search_handler->getQueryBuilder()->getRawTerms();
    $term_string = implode( '&search[]=', $terms );

    $node = Node_Document::factory( $collection, $lucene_hit->docOID );
    // FIXME: designate field(s) used in config
    $this->title = $node->getField( 'Title' );
    $this->link = NodePage_DocumentSection::factory( $node )->getUrl() . '?search[]=' . $term_string;

    $lucene_document = $lucene_hit->getDocument();

    $text_field = $lucene_document->getField('TX');
    if ( $text_field ) {
      $this->snippet = $text_field->value;
    }
  }
}