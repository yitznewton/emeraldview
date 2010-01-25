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

    // FIXME
    $per_page = 20;

    try {
      $search_handler = new SearchHandler(
        $this->input->get(), $collection
      );
    }
    catch (Exception $e) {
      url::redirect( $collection->getUrl() );
    }

    try {
      $hits_page  = new HitsPage( $search_handler, $page_number, $per_page );
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
  
  public function getQueryBuilder()
  {
    return $this->queryBuilder;
  }
  
  public function getCurrentPage()
  {
    // short name => easier to read
    $pars = $this->input->get();
    
    if ( isset($pars['p']) && is_numeric($pars['p']) && $pars['p'] > 0 ) {
      // a valid page number was passed in $_GET['p']
      return $pars['p'];
    }
    else {
      return 1;
    }
  }
  
  public function getSearchLevel()
  {
    // short name => easier to read
    $pars = $this->input->get();

    // FIXME
    return 'document';
    
    if (
      isset($pars['l'])
      && in_array($pars['l'], $this->collection->getIndexLevels())
    ) {
      return $pars['l'];
    }
    elseif ($this->collection->getDefaultLevel()) {
      return $this->collection->getDefaultLevel();
    }
    else {
      $search_levels = $this->collection->getIndexLevels();
      return $search_levels[0];
    }
  }
  
  public function getHitsPerPage()
  {
    // FIXME
    return 20;
  }
  
  protected function getSearchType()
  {
  
  }
  
  protected function getSearchHistory()
  {
  
  }
}