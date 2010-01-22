<?php

class HitsPage
{
  protected $hits;
  protected $perPage;

  public function __construct( SearchHandler $search_handler, $page_number, $per_page = 20 )
  {
    if ( ! is_int( $per_page ) ) {
      throw new InvalidArgumentException( 'Second argument must be an integer' );
    }
    
    $this->hits = $search_handler->execute();
    $this->perPage = $per_page;
    var_dump($this);exit;
  }

  public function hits()
  {
  }

  public function links()
  {
    
  }
}