<?php

class Search_Controller extends Emeraldview_Template_Controller
{
  protected $queryBuilder;
  protected $queryParams;
  protected $hits = array();
  
  public function index( $collection_name )
  {
    if ( ! $this->input->get() ) {
      url::redirect( $collection_name );
    }
    
    $collection = $this->loadCollection( $collection_name );
    
    if ( (int) $this->input->get( 'p' ) ) {
      $page_number = (int) $this->input->get( 'p' );
    }
    else {
      $page_number = 1;
    }

    try {
      $search_handler = new SearchHandler(
        $this->input->get(), $collection
      );
    }
    catch (Exception $e) {
      url::redirect( $collection->getUrl() );
    }

    try {
      // FIXME: config hits per page
      $hits_page  = new HitsPage( $search_handler, $page_number );
    }
    catch (InvalidArgumentException $e) {
      url::redirect( $collection->getUrl() );
    }
    
    $this->view                 = new View( $this->theme . '/search' );
    $this->template->set_global( 'page_title',      'Search | '
                                 . $this->collection->getDisplayName( $this->language )
                                 . " | $this->emeraldviewName"
                               );
    $this->template->set_global( 'collection',      $collection );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'collection_display_name',    $collection->getDisplayName( $this->language ) );
    $this->template->set_global( 'description',     $collection->getDescription( $this->language ) );
    $this->template->set_global( 'search_handler',  $search_handler );
    $this->template->set_global( 'hits_page',       $hits_page );
  }
  
  protected function getSearchHistory()
  {
    // TODO implement
  }
}