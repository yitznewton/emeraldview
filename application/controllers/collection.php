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

    $classifier = $root_node->getPage();
    $tree = $classifier->getTree();

    $this->view = new View( $this->theme . '/browse' );
    
    $this->template->set_global( 'collection',      $collection );
    $this->template->set_global( 'collection_display_name',    $collection->getDisplayName( $this->language ) );
    $this->template->set_global( 'page_title',      $classifier->getTitle()
                                                    . ' | ' . $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'page',            $classifier );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'description',     $collection->getDescription( $this->language ) );
    $this->template->set_global( 'tree',            $tree );
  }
  
  public function view( $collection_name, $slug )
  {
    $subnode_id = '';
    $args = func_get_args();

    if (count( $args ) > 2) {
      $subnodes = array_slice( $args, 2 );
      $subnode_id = '.' . implode( '.', $subnodes );
    }

    $collection = $this->loadCollection( $collection_name );

    $document_id = $collection->getSlugLookup()->retrieveId( $slug );

    if (!$document_id) {
      url::redirect( $collection->getUrl() );
    }

    $node = Node_Document::factory( $collection, $document_id . $subnode_id );
    
    if (!$node) {
      url::redirect( $collection->getUrl() );
    }

    if ($node->isPaged()) {
      if ( $this->input->get('page') ) {
        // user submitted the 'go to page' form
        $page_number = (int) $this->input->get('page');
        $paged_node = $node->getRelatedNodeByTitle( $page_number );

        if ($paged_node) {
          // found a subnode with the requested page number
          $node = $paged_node;
        }
        else {
          // no subnode has that page number; redirect to first page
          url::redirect( $node->getPage()->getUrl() );
        }
      }
      elseif (!$subnode_id) {
        // there's no appropriate NodePage for root node of a Paged document;
        // set to display first subnode
        $node = Node_Document::factory( $collection, "$document_id.1" );

        if (!$node) {
          url::redirect( $collection->getUrl() );
        }
      }
    }
    //var_dump($node->getId());

    $page = $node->getPage();

    $this->view = new View( $this->theme . '/view' );

    $this->template->set_global( 'collection',      $collection );
    $this->template->set_global( 'collection_display_name',    $collection->getDisplayName( $this->language ) );
    $this->template->set_global( 'page_title',      $node->getField('Title')
                                                    . ' | ' . $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'node',            $node );
    $this->template->set_global( 'page',            $page );
    $this->template->set_global( 'root_node',       $node->getRootNode() );
    $this->template->set_global( 'root_page',       $node->getRootNode()->getPage() );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'tree_pager',      NodeTreePager::html( $node ) );
    $this->template->set_global( 'paged_urls',      $page->getPagedUrls() );
  }
}