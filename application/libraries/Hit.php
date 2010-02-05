<?php

class Hit
{
  protected $search_handler;
  public $title;
  public $link;
  public $snippet;

  public function __construct( Zend_Search_Lucene_Search_QueryHit $lucene_hit, SearchHandler $search_handler )
  {
    $this->search_handler = $search_handler;

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
      $this->snippet = $this->snippet( $text_field->value );
    }
  }

  protected function snippet( $text )
  {
    if ( ! $text ) {
      return false;
    }

    $doc = Zend_Search_Lucene_Document_Html::loadHTML( $text );

    $max_length = $this->search_handler->getCollection()
                  ->getConfig( 'snippet_max_length' );

    if ( ! $max_length || ! is_int( $max_length ) ) {
      $max_length = 200;
    }

    $text = preg_replace('/\s{2,}/u', ' ', $text);
    $terms = $this->search_handler->getQueryBuilder()->getRawTerms();

    $first_hit_position = strlen( $text ) - 1;

    foreach ( $terms as $term ) {
      // account for special search characters
      $term_pattern = preg_quote( $term );
      $term_pattern = str_replace( array('\\*', '\\?'), array('.*\\b', '.'), $term_pattern );
      $term_pattern = '/' . $term_pattern . '/iu';

      if ( preg_match( $term_pattern, $text, $matches ) ) {
        $hit_position = strpos( $text, $matches[0] );

        if ( $hit_position < $first_hit_position ) {
          $first_hit_position = $hit_position;
        }
      }
    }

    // TODO: I18n-ify this?
    $first_hit_reverse_position = 0 - strlen( $text ) + $first_hit_position;
    $prev_sentence_end = strripos( $text, '. ', $first_hit_reverse_position );

    // ignore earlier sentences
    $sentence_start = $prev_sentence_end ? $prev_sentence_end + 2 : 0;
    $first_hit_position -= $sentence_start;
    $text = substr( $text, $sentence_start );

    $first_hit_cutoff = $max_length - 50;
    if ( $first_hit_cutoff < 0 ) {
      $first_hit_cutoff;
    }

    // TODO: double-check this logic
    if ($first_hit_position > 150) {
      // only start a bit before first hit
      $snippet_start = strpos( $text, ' ', $first_hit_position - 50 );
    }
    else {
      // we have room; start from beginning of sentence
      $snippet_start = 0;
    }

    $snippet = substr( $text, $snippet_start );

    preg_match("/^ .{0,$max_length} .*? \b /iux", $snippet, $matches);

    if ($matches[0] != $snippet) {
      // we needed to truncate at the end
      $snippet = $matches[0] . ' ...';
    }

    if ($snippet_start > 0) {
      // we truncated from the beginning
      $snippet = '... ' . $snippet;
    }

    // TODO: fine-tune highlight for refined regex-based $terms
    $highlighter = new Highlighter_Text();
    $highlighter->setDocument( $snippet );
    $highlighter->setTerms( $terms );
    
    return $highlighter->execute();
  }
}