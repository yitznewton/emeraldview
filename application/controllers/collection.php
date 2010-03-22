<?php

class Collection_Controller extends Emeraldview_Template_Controller
{
	public function index()
  {
    $this->view = new View( $this->theme . '/index' );
    $this->view->collections = Collection::getAllAvailable();

		$this->template->set_global( 'page_title',      EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'method',          'index' );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'language',        $this->language );
  }
  
  public function about( $collection_name )
  {
		$collection = $this->loadCollection( $collection_name );

    if ( ! $collection ) {
      return $this->show404();
    }

    $this->view = new View( $this->theme . '/about' );
    
    $this->template->set_global( 'method',          'about' );
    $this->template->set_global( 'page_title',      $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'collection_description',     $collection->getDescription( $this->language ) );
  }
  
  public function browse( $collection_name, $classifier_slug )
  {
    $collection = $this->loadCollection( $collection_name );

    if ( ! $collection ) {
      return $this->show404();
    }

    $classifier = NodePage_Classifier::retrieveBySlug( $collection, $classifier_slug );

    if ( ! $classifier ) {
      return $this->show404();
    }

    $root_node           = $classifier->getNode();
    $node_tree_formatter = $classifier->getNodeTreeFormatter();
    $tree                = $node_tree_formatter->render();

    if ( $node_tree_formatter->isUsingTabs() ) {
      $this->template->addCss( 'libraries/tabs/jquery-ui-1.7.2.css' );
      $this->template->addJs(  'libraries/tabs/jquery-ui-1.7.2.js'  );
    }

    if ( $node_tree_formatter->isUsingTree() ) {
      $this->template->addCss( 'libraries/treeview/jquery.treeview.css' );
      $this->template->addJs(  'libraries/treeview/jquery.treeview.js'  );
    }

    $this->view = new View( $this->theme . '/browse' );
    
    $this->template->set_global( 'method',          'browse' );
    $this->template->set_global( 'page_title',      $classifier->getTitle()
                                                    . ' | ' . $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'page',            $classifier );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'tree',            $tree );
  }

  public function search( $collection_name )
  {
    if ( ! $this->input->get() ) {
      url::redirect( $collection_name );
    }

    $collection = $this->loadCollection( $collection_name );

    if ( ! $collection ) {
      return $this->show404();
    }

    if ( (int) $this->input->get( 'p' ) ) {
      $page_number = (int) $this->input->get( 'p' );
    }
    else {
      $page_number = 1;
    }

    $search_handler = new SearchHandler(
      $this->input->get(), $collection
    );

    try {
      $per_page = $collection->getConfig('search_hits_per_page');

      if ( $per_page && is_int( $per_page ) ) {
        $hits_page = new HitsPage( $search_handler, $page_number, $per_page );
      }
      else {
        $hits_page = new HitsPage( $search_handler, $page_number );
      }
    }
    catch (InvalidArgumentException $e) {
      // TODO: document what is throwing this
      url::redirect( $collection->getUrl() );
    }

    $session = Session::instance();
    $history = $session->getSearchHistory( $collection );
    $session->recordSearch( $search_handler );

    $this->view                 = new View( $this->theme . '/search' );
    $this->template->set_global( 'page_title',      'Search | '
                                 . $this->collection->getDisplayName( $this->language )
                                 . " | $this->emeraldviewName"
                               );
    $this->template->set_global( 'method',          'search' );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'search_handler',  $search_handler );
    $this->template->set_global( 'search_history',  $history );
    $this->template->set_global( 'hits_page',       $hits_page );
  }
  
  public function view( $collection_name, $slug )
  {
    $subnode_id = '';
    $args = func_get_args();

    $collection = $this->loadCollection( $collection_name );

    if ( ! $collection ) {
      return $this->show404();
    }

    $document_id = $collection->getSlugLookup()->retrieveId( $slug );

    if ( ! $document_id ) {
      return $this->show404();
    }

    $root_node = Node_Document::factory( $collection, $document_id );
    $subnode_id = null;
    $subnode_title = null;

    if ( ! $root_node ) {
      return $this->show404();
    }

    if ( $root_node->isPaged() ) {
      if ( isset( $args[2] ) ) {
        $subnode_title = (int) $args[2];
      }
      elseif ( $this->input->get('page') ) {
        $subnode_title = (int) $this->input->get('page');
      }
      else {
        // page not indicated; default to first page
        $subnode_id = '1';
      }
    }
    elseif (count( $args ) > 2) {
      // not paged, and subnode is indicated
      $subnodes = array_slice( $args, 2 );
      $subnode_id = implode( '.', $subnodes );
    } // ... otherwise no subnode specified; will default to root Node

    if ( $subnode_id ) {
      $node = $root_node->getCousin( $subnode_id );
    }
    elseif ( $subnode_title ) {
      $node = $root_node->getCousinByTitle( $subnode_title );
    }
    else {
      $node = $root_node;
    }

    if ( ! $node ) {
      // whatever subnode was requested could not be found
      return $this->show404();
    }

    $page = $node->getNodePage();
    $search_terms = $this->input->get('search');

    if ( $search_terms ) {
      $highlighter = new Highlighter_Text();
      $highlighter->setTerms( $search_terms );
      $highlighter->setDocument( $page->getHTML() );
      $text = $highlighter->execute();
    }
    else {
      $text = $page->getHTML();
    }

    $paged_urls = $page->getPagedUrls();

    if ( ! $paged_urls ) {
      $node_tree_formatter = $page->getNodeTreeFormatter();
      $tree = $node_tree_formatter->render();

      if ( $node_tree_formatter->isUsingTree() ) {
        $this->template->addCss( 'libraries/treeview/jquery.treeview.css' );
        $this->template->addJs(  'libraries/treeview/jquery.treeview.js'  );
      }
    }
    else {
      $tree = false;
    }

    $this->view = new View( $this->theme . '/view' );

    $this->template->set_global( 'method',          'view' );
    $this->template->set_global( 'page_title',      $node->getField('Title')
                                                    . ' | ' . $collection->getDisplayName( $this->language )
                                                    . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->template->set_global( 'node',            $node );
    $this->template->set_global( 'page',            $page );
    $this->template->set_global( 'root_node',       $node->getRootNode() );
    $this->template->set_global( 'root_page',       $node->getRootNode()->getNodePage() );
    $this->template->set_global( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->template->set_global( 'tree_pager',      NodeTreePager::html( $node ) );
    $this->template->set_global( 'paged_urls',      $paged_urls );
    $this->template->set_global( 'tree',            $tree );
    $this->template->set_global( 'search_terms',    $this->input->get('search') );
    $this->template->set_global( 'text',            $text );
  }

  public function show404()
  {
    $this->view = new View( 'default/show404' );
    $this->template->set_global( 'method',          'show404' );
    $this->template->set_global( 'page_title', 'Page not found' );
  }
}
