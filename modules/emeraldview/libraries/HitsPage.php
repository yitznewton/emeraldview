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
 * HitsPager is a container class to organize and display the Hit objects
 * appropriate to a given search request
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class HitsPage
{
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
   */
  public function __construct( SearchHandler $search_handler )
  {
    $this->searchHandler = $search_handler;

    // calculate page breakdown

    $this->perPage  = $search_handler->getHitsPerPage();
    $this->firstHit = $search_handler->getStartAt();

    $this->hits = $search_handler->execute();

    $total_hit_count = $search_handler->getTotalHitCount();

    if ( $total_hit_count === 0 || $this->firstHit > $total_hit_count ) {
      $this->totalPages = 0;
      $this->pageNumber = 1;
      $this->hits = array();
      $this->links = false;
    }
    else {
      $this->totalPages = ceil( $total_hit_count / $this->perPage );

      $this->pageNumber = floor( ($this->firstHit / $this->perPage) ) + 1;

      if ( $this->firstHit + $this->perPage <= $total_hit_count ) {
        $this->lastHit = $this->firstHit + $this->perPage - 1;
      }
      else {
        $this->lastHit = $total_hit_count;
      }

      foreach ( $this->hits as $hit ) {
        try {
          $hit->build();
        }
        catch ( UnexpectedValueException $e ) {
          // unable to retrieve Node for this result; log error
          $msg = 'Unable to retrieve Node for docOID ' . $hit->docOID . ' in '
                 . 'query [' . $search_handler->getQuery()->getQuerystring()
                 . '] for collection ' . $search_handler->getCollection()->getName();
          Kohana::log( 'error', $msg );
        }
      }

      $this->buildLinks();
    }
  }

  /**
   * Builds the HitsPageLinks URLs corresponding to various pages of hits
   * 
   * @todo refactor this into HitsPageLinks
   */
  protected function buildLinks()
  {
    if ($this->totalPages == 1) {
      return false;
    }

    $links = new HitsPageLinks();

    $params = $this->searchHandler->getQuery()->getParams();

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

    if ( $this->pageNumber > 10 ) {
      $start_with = $this->pageNumber - 9;
    }
    else {
      $start_with = 1;
    }

    if ( $this->pageNumber + 9 < $this->totalPages ) {
      $end_with = $this->pageNumber + 9;
    }
    else {
      $end_with = $this->totalPages;
    }

    for ( $i = $start_with; $i < $this->pageNumber; $i++ )
    {
      // add pages before this one
      $params['p'] = $i;
      $links->pages[ $i ] = $this->searchHandler->getCollection()->getUrl()
                          . '/search?' . http_build_query( $params );
    }

    $links->pages[ $this->pageNumber ] = null;

    // add pages after this one
    for ( $i = $this->pageNumber + 1; $i < $end_with + 1; $i++ )
    {
      $params['p'] = $i;
      $links->pages[ $i ] = $this->searchHandler->getCollection()->getUrl()
                          . '/search?' . http_build_query( $params );
    }
    
    $this->links = $links;
  }
}
