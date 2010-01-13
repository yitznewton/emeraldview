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

    $root_node = Node_Classifier::factory( $collection, $classifier_name );
    
    if (!$root_node) {
      url::redirect( $collection->getUrl() );
    }

    $classifier = NodePage_Classifier::factory( $root_node );

    $tree = $classifier->getTree();

    $this->view = new View( $this->theme . '/browse' );
    
    $this->template->set_global( 'collection',      $collection );
    $this->template->set_global( 'collection_display_name',    $collection->getDisplayName( $this->language ) );
    $this->template->set_global( 'page_title',      $classifier->getTitle()
                                                    . ' | ' . $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'classifier',      $classifier );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'description',     $collection->getDescription( $this->language ) );
    $this->template->set_global( 'tree',            $tree );
  }
  
  public function view( $collection_name, $slug )
  {
    $subnode_id = '';

    if (count( func_get_args() ) > 2) {
      $subnodes = array_slice( func_get_args(), 2 );
      $subnode_id = '.' . implode( '.', $subnodes );
    }

    $collection = $this->loadCollection( $collection_name );

    $document_id = $collection->getSlugLookup()->retrieveId( $slug );

    if (!$document_id) {
      url::redirect( $collection->getUrl() );
    }

    $document_id .= $subnode_id;

    $node = Node_Document::factory( $collection, $document_id );
    
    if (!$node) {
      url::redirect( $collection->getUrl() );
    }

    $document_section = NodePage_DocumentSection::factory( $node );
    $tree = $document_section->getTree();

    $this->view = new View( $this->theme . '/view' );

    $this->template->set_global( 'collection',      $collection );
    $this->template->set_global( 'collection_display_name',    $collection->getDisplayName( $this->language ) );
    $this->template->set_global( 'page_title',      $node->getField('Title')
                                                    . ' | ' . $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'document',        $document_section );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'tree',            $tree );
    $this->template->set_global( 'tree_pager',      NodeTreePager::html( $node ) );
    $this->template->set_global( 'paged_urls',      $node->getPagedUrls() );
  }
}