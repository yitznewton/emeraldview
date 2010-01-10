<?php

class Collection_Controller extends Emeraldview_Template_Controller
{
	public function index()
  {
		$this->template->page_title  = EmeraldviewConfig::get('emeraldview_name');
    
    $this->view = new View( $this->theme . '/index' );
    $this->view->collections = Collection::getAllAvailable();
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'language', $this->language );
  }
  
  public function about( $collection_name )
  {
		$collection = $this->loadCollection( $collection_name );

    $this->view = new View( $this->theme . '/about' );
    
    $this->template->set_global( 'collection',      $collection );
    $this->template->set_global( 'collection_display_name',    $collection->getDisplayName( $this->language ) );
    $this->template->set_global( 'page_title',      $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'description',     $collection->getDescription( $this->language ) );
  }
  
  public function browse( $collection_name, $classifier_name )
  {
    $collection = $this->loadCollection( $collection_name );
    $classifier = $collection->getClassifier( $classifier_name );

    if (!$classifier) {
      url::redirect( $collection->getUrl() );
    }
    
    $this->view = new View( $this->theme . '/browse' );
    
    $this->template->set_global( 'collection',      $collection );
    $this->template->set_global( 'collection_display_name',    $collection->getDisplayName( $this->language ) );
    $this->template->set_global( 'page_title',      $classifier->getName()
                                                    . ' | ' . $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'classifier',      $classifier );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'description',     $collection->getDescription( $this->language ) );
    $this->template->set_global( 'tree',            $classifier->getTree()->getFormatter()->html() );
  }
  
  public function view( $collection_name, $slug )
  {
    $collection = $this->loadCollection( $collection_name );

    $document_id = $collection->getSlugLookup()->retrieveId( $slug );

    if (!$document_id) {
      url::redirect( $collection->getUrl() );
    }

    $document = Document::factory( $collection, $document_id );

    $this->view = new View( $this->theme . '/view' );

    $this->template->set_global( 'collection',      $collection );
    $this->template->set_global( 'collection_display_name',    $collection->getDisplayName( $this->language ) );
    $this->template->set_global( 'page_title',      $document->getMetadataElement('Title')
                                                    . ' | ' . $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'document',        $document );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'tree',            $document->getTree()->getFormatter()->html() );
  }
}