<?php

class HitsPage
{
  protected $hits;
  protected $perPage;

  public function __construct( SearchHandler $search_handler, $per_page )
  {
    if ( ! is_int( $per_page ) ) {
      throw new InvalidArgumentException( 'Second argument must be an integer' );
    }
    
    $this->hits = $search_handler->getHits();
    $this->perPage = $per_page;
  }

  public function hits()
  {
  }

  public function links()
  {
    
  }
}