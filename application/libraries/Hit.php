<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.net/emeraldview/index.php/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * Hit is a container for search-hit-related classes.  It currently also
 * includes snippet fragmentation code, although this is to be factored out
 * in an upcoming release
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class Hit
{
  // FIXME: this being here is part of the temporary centralization this type of
  // functionality before we actually factor the snippet generation and
  // highlighting in a sensible way
  const HIT_PATTERN = '/(?<=[^_\pL\pN]|^)(%s)(?=[^_\pL\pN]|$)/iu';
  // const HIT_PATTERN = '/\\b(%s)\\b/i';

  /**
   * The headline link of the Hit
   *
   * @var string
   */
  public $link;
  /**
   * The document snippet accompanying the Hit
   *
   * @var string
   */
  public $snippet;

  /**
   * The SearchHandler responsible for generating the Hit
   *
   * @var SearchHandler
   */
  protected $search_handler;
  /**
   * The parent Zend hit object
   *
   * @var Zend_Search_Lucene_Search_QueryHit
   */
  protected $lucene_hit;

  /**
   * @param Zend_Search_Lucene_Search_QueryHit $lucene_hit
   * @param SearchHandler $search_handler 
   */
  public function __construct( Zend_Search_Lucene_Search_QueryHit $lucene_hit, SearchHandler $search_handler )
  {
    $this->search_handler = $search_handler;
    $this->lucene_hit = $lucene_hit;
  }

  /**
   * Builds the link and snippet for the Hit.  This is expensive, so this
   * functionality is not called in __construct()
   */
  public function build()
  {
    $collection = $this->search_handler->getCollection();
    $terms = $this->search_handler->getQueryBuilder()->getRawTerms();
    $term_string = implode( '&search[]=', $terms );

    $node = Node_Document::factory( $collection, $this->lucene_hit->docOID );
    $formatter = NodeFormatter::factory( $node, $this->search_handler );
    $title = $formatter->format();

    $highlighter = new Highlighter_Text();
    $highlighter->setDocument( $title );
    $highlighter->setTerms( $terms );
    $title = $highlighter->execute();

    // add link

    if ( strpos( $title, '[a]' ) === false ) {
      $title = '[a]' . $title . '[/a]';
    }

    $url = NodePage_DocumentSection::factory( $node )->getUrl() . '?search[]=' . $term_string;
    $search = array( '[a]', '[/a]' );
    $replace = array( '<a href="' . $url . '">', '</a>' );
    $this->link = str_replace( $search, $replace, $title );

    $lucene_document = $this->lucene_hit->getDocument();

    try {
      $text_field = $lucene_document->getField('TX');
      $this->snippet = $this->snippetize( $text_field->value );
    }
    catch ( Zend_Search_Lucene_Exception $e ) {
      $this->snippet = null;
    }
  }

  /**
   * Temporary location for snippet fragmentation code pending refactor
   *
   * @param string $text
   * @return string
   */
  protected function snippetize( $text )
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

    // find the first instance of any one of the terms

    $text = preg_replace('/\s{2,}/u', ' ', $text);
    $terms = $this->search_handler->getQueryBuilder()->getRawTerms();
    array_walk( $terms, 'preg_quote' );

    $term_pattern  = implode( '|', $terms );
    $pattern = sprintf( Hit::HIT_PATTERN, $term_pattern );
    preg_match( $pattern, $text, $matches );
    
    $first_hit_position = strpos( $text, $matches[1] );

    // take snippet, padding around the term match

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

    if ( $first_hit_position > 150 ) {
      // only start a bit before first hit
      $snippet_start = strpos( $text, ' ', $first_hit_position - 50 );
    }
    else {
      // we have room; start from beginning of sentence
      $snippet_start = 0;
    }

    $snippet = substr( $text, $snippet_start );

    $pattern = "/^ .{0,$max_length} .*? [^_\pL\pN] | $ /ux";
    preg_match( $pattern, $snippet, $matches );

    if ( ! $matches ) {
      throw new Exception( 'Regex fail in Hit::snippetize' );
    }

    if ($matches[0] != $snippet) {
      // we needed to truncate at the end
      $snippet = $matches[0] . ' ...';
    }

    if ($snippet_start > 0) {
      // we needed to truncate from the beginning
      $snippet = '... ' . $snippet;
    }

    $highlighter = new Highlighter_Text();
    $highlighter->setDocument( $snippet );
    $highlighter->setTerms( $terms );
    
    return $highlighter->execute();
  }
}
