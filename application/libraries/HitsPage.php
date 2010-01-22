<?php

class HitsPage
{
  public $totalHitCount;
  public $links;
  public $hits;
  public $firstHit;
  public $lastHit;

  protected $searchHandler;
  protected $perPage;
  protected $pageNumber;
  protected $totalPages;

  public function __construct( SearchHandler $search_handler, $page_number, $per_page = 20 )
  {
    $this->searchHandler = $search_handler;

    if ( ! is_int( $page_number ) ) {
      throw new InvalidArgumentException( 'Second argument must be an integer' );
    }

    if ( ! is_int( $per_page ) ) {
      throw new InvalidArgumentException( 'Third argument must be an integer' );
    }
    
    $this->perPage = $per_page;

    $all_hits = $search_handler->execute();
    $this->totalHitCount = count( $all_hits );
    $this->totalPages = ceil( $this->totalHitCount / $per_page );

    // FIXME: what if no hits

    if ( $this->totalPages >= $page_number ) {
      $this->pageNumber = $page_number;
    }
    else {
      throw new InvalidArgumentException( 'Page number exceeds total pages' );
    }

    $this->firstHit = ( $this->pageNumber - 1 ) * $this->perPage + 1;
    
    if ( $this->firstHit + $this->perPage <= $this->totalHitCount ) {
      $this->lastHit = $this->firstHit + $this->perPage;
    }
    else {
      $this->lastHit = $this->totalHitCount;
    }

    $this->hits = array_slice( $all_hits, $this->firstHit - 1, $this->perPage );
    $this->links = $this->buildLinks();
  }

  protected function buildLinks()
  {
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
    
    return $links;
  }
}