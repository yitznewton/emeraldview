<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.org/emeraldview/index.php?title=License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b4
 * @package libraries
 */
/**
 * Hit is a container for search-hit-related classes.  It currently also
 * includes snippet fragmentation code, although this is to be factored out
 * in an upcoming release
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
abstract class Hit
{
  const HIT_PATTERN = '/(?<=[^_\pL\pN]|^)(%s)(?=[^_\pL\pN]|$)/iu';  // Unicode
  // const HIT_PATTERN = '/\\b(%s)\\b/i';  // ASCII-only

  /**
   * The headline link of the Hit
   *
   * @var string
   */
  public $link;
  /**
   * The Greenstone document ID
   *
   * @var string
   */
  public $docOID;
  /**
   * The document snippet accompanying the Hit
   *
   * @var string
   */
  public $snippet;
  /**
   * Title of the document
   *
   * @var string
   */
  protected $title;
  /**
   * Raw terms of the search
   *
   * @var array string[]
   */
  protected $terms;
  /**
   * The SearchHandler responsible for generating the Hit
   *
   * @var SearchHandler
   */
  protected $searchHandler;

  abstract public function __construct( SearchHandler $search_handler );

  /**
   * Builds the link and snippet for the Hit.  This is expensive, so this
   * functionality is not called in __construct()
   */
  public function build()
  {
    $collection = $this->searchHandler->getCollection();
    $this->terms = $this->searchHandler->getQueryBuilder()->getRawTerms();
    $term_string = implode( '&search[]=', $this->terms );

    $node = Node_Document::factory( $collection, $this->docOID );
    $formatter = NodeFormatter::factory( $node, $this->searchHandler );
    $this->title = $formatter->format();

    // add link

    if ( strpos( $this->title, '[a]' ) === false ) {
      $this->title = '[a]' . $this->title . '[/a]';
    }

    $url = NodePage_DocumentSection::factory( $node )->getUrl() . '?search[]=' . $term_string;
    $search = array( '[a]', '[/a]' );
    $replace = array( '<a href="' . $url . '">', '</a>' );
    $this->link = str_replace( $search, $replace, $this->title );
  }
}
