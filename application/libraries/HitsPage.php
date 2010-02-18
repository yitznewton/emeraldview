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
 * @version 0.2.0b1
 * @package libraries
 */
/**
 * HitsPager is a container class to organize and display the Hit objects
 * appropriate to a given search request
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class HitsPage
{
  /**
   * Total number of hits that the Lucene library returned for the request
   *
   * @var integer
   */
  public $totalHitCount;
  /**
   * An object of URLs corresponding to various pages of hits
   *
   * @var HitsPageLinks
   */
  public $links;
  /**
   * An array of Hit objects based on the requested page
   *
   * @var array
   */
  public $hits;
  /**
   * Index of the first Hit to display
   *
   * @var integer
   */
  public $firstHit;
  /**
   * Index of the last Hit to display
   *
   * @var integer
   */
  public $lastHit;

  /**
   * SearchHandler object responsible for this request
   *
   * @var SearchHandler
   */
  protected $searchHandler;
  /**
   * Number of Hits per page as specified in config
   *
   * @var integer
   */
  protected $perPage;
  /**
   * Number of the requested page
   *
   * @var integer
   */
  protected $pageNumber;
  /**
   * Total number of pages available for this query
   *
   * @var integer
   */
  protected $totalPages;

  /**
   * @param SearchHandler $search_handler
   * @param integer $page_number
   * @param integer $per_page 
   */
  public function __construct( SearchHandler $search_handler, $page_number, $per_page = 20 )
  {
    $this->searchHandler = $search_handler;

    if ( ! is_int( $page_number ) ) {
      throw new InvalidArgumentException( 'Second argument must be an integer' );
    }

    if ( ! is_int( $per_page ) ) {
      throw new InvalidArgumentException( 'Third argument must be an integer' );
    }
    
    // calculate page breakdown

    $this->perPage = $per_page;

    $all_hits = $search_handler->execute();
    
    $this->totalHitCount = count( $all_hits );

    if ( $this->totalHitCount === 0 ) {
      $this->totalPages = 0;
      $this->hits = array();
      $this->links = false;
    }
    else {
      $this->totalPages = ceil( $this->totalHitCount / $per_page );

      if ( $this->totalPages >= $page_number ) {
        $this->pageNumber = $page_number;
      }
      else {
        throw new InvalidArgumentException( 'Page number exceeds total pages' );
      }

      $this->firstHit = ( $this->pageNumber - 1 ) * $this->perPage + 1;

      if ( $this->firstHit + $this->perPage <= $this->totalHitCount ) {
        $this->lastHit = $this->firstHit + $this->perPage - 1;
      }
      else {
        $this->lastHit = $this->totalHitCount;
      }

      $this->hits = array_slice( $all_hits, $this->firstHit - 1, $this->perPage );

      foreach( $this->hits as $hit ) {
        $hit->build();
      }

      $this->links = $this->buildLinks();
    }
  }

  /**
   * Builds the HitsPageLinks URLs corresponding to various pages of hits
   * @todo refactor this into HitsPageLinks
   */
  protected function buildLinks()
  {
    if ($this->totalPages == 1) {
      return false;
    }

    $links = new HitsPageLinks();

    $params = $this->searchHandler->getParams();

    // first, the first-previous-next-last links...

    if ( $this->pageNumber > 1 ) {
      $params['p'] = 1;
      $links->first = $this->searchHandler->getCollection()->getUrl()
                      . '/search?' . http_build_query( $params );

      $params['p'] = $this->pageNumber - 1;
      $links->previous = $this->searchHandler->getCollection()->getUrl()
                         . '/search?' . http_build_query( $params );
    }

    if ( $this->pageNumber < $this->totalPages ) {
      $params['p'] = $this->pageNumber + 1;
      $links->next = $this->searchHandler->getCollection()->getUrl()
                      . '/search?' . http_build_query( $params );

      $params['p'] = $this->totalPages;
      $links->last = $this->searchHandler->getCollection()->getUrl()
                      . '/search?' . http_build_query( $params );
    }

    // now, the individual page links...

    for ( $i = 1; $i < $this->pageNumber; $i++ )
    {
      // add pages before this one
      $params['p'] = $i;
      $links->pages[ $i ] = $this->searchHandler->getCollection()->getUrl()
                          . '/search?' . http_build_query( $params );
    }

    $links->pages[ $this->pageNumber ] = null;

    // add pages after this one
    for ( $i = $this->pageNumber + 1; $i < $this->totalPages + 1; $i++ )
    {
      $params['p'] = $i;
      $links->pages[ $i ] = $this->searchHandler->getCollection()->getUrl()
                          . '/search?' . http_build_query( $params );
    }
    
    $this->links = $links;
  }
}
