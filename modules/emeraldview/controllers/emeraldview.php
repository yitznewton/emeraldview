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
 * @version 0.2.0
 * @package controllers
 */
/**
 * BuildCfg_G2 is a reader interface for Greenstone's build configuration
 * file(s) as implemented in Greenstone2 as build.cfg
 *
 * @package controllers
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class Emeraldview_Controller extends Emeraldview_Template_Controller
{
  public function index()
  {
    $this->loadView( 'index' );
    
    $this->passDown( 'collections', Collection::getAllAvailable() );
		$this->passDown( 'page_title',      EmeraldviewConfig::get('emeraldview_name') );
    $this->passDown( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
  }
  
  public function about( $collection_name )
  {
    $collection = $this->loadCollection( $collection_name );

    if ( ! $collection ) {
      return $this->show404();
    }

    $this->loadView( 'about' );

    $history = $this->session->getSearchHistory( $collection );

    $this->passDown( 'page_title',             $collection->getDisplayName( $this->language )
                                               . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->passDown( 'language_select',        myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->passDown( 'collection_description', $collection->getDescription( $this->language ) );
    $this->passDown( 'search_history',         $history );
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

    $this->loadView( 'browse' );

    $root_node           = $classifier->getNode();
    $node_tree_formatter = $classifier->getNodeTreeFormatter();
    $tree                = $node_tree_formatter->render();

    if ( $node_tree_formatter->isUsingTabs()
         || $node_tree_formatter->isUsingAjax()
         || $node_tree_formatter->isUsingCache() ) {
      $this->template->addCss( 'libraries/tabs/jquery-ui-1.7.2.css' );
      $this->template->addJs(  'libraries/tabs/jquery-ui-1.7.2.js'  );
      $this->template->addJs(  'libraries/tabs/jquery.cookie.js'  );
    }

    if ( $node_tree_formatter->isUsingTree()
         || $node_tree_formatter->isUsingAjax()
         || $node_tree_formatter->isUsingCache() ) {
      $this->template->addCss( 'libraries/treeview/jquery.treeview.css' );
      $this->template->addJs(  'libraries/treeview/jquery.treeview.js'  );
    }

    $this->passDown( 'page_title',      $classifier->getTitle()
                                        . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->passDown( 'page',            $classifier );
    $this->passDown( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->passDown( 'tree',            $tree );
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

    $per_page = $collection->getConfig( 'search_hits_per_page', 20 );

    if ( (int) $this->input->get( 'p' ) ) {
      $start_at = 1 + ((int) $this->input->get( 'p' ) - 1) * $per_page;
    }
    else {
      $start_at = 1;
    }

    $query = Query::factory( $collection, $this->input->get() )
      or url::redirect( $collection->getUrl() );

    $search_handler = SearchHandler::factory( $query );
    $search_handler->setHitsPerPage( $per_page );
    $search_handler->setStartAt( $start_at );

    $hits_page = new HitsPage( $search_handler )
      or url::redirect( $collection->getUrl() );

    $history = $this->session->getSearchHistory( $collection );
    $this->session->recordSearch( $query );

    $this->loadView( 'search' );

    $this->passDown( 'page_title', 'Search'
                                   . ' | ' . $this->collection->getDisplayName( $this->language )
                                   . ' | ' . $this->emeraldviewName );
    $this->passDown( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->passDown( 'search_handler',  $search_handler );
    $this->passDown( 'search_history',  $history );
    $this->passDown( 'hits_page',       $hits_page );
  }
  
  public function view( $collection_name, $slug )
  {
    $collection = $this->loadCollection( $collection_name );

    if ( ! $collection ) {
      return $this->show404();
    }

    $document_id = $collection->getSlugLookup()->retrieveId( $slug );

    if ( ! $document_id ) {
      return $this->show404();
    }

    $root_node = $collection->getNode( $document_id );
    $root_page = NodePage::factory( $collection, $root_node );

    if ( ! $root_node ) {
      return $this->show404();
    }

    if ( $root_node->isPaged() && $this->input->get('page') ) {
      $subnode_args = array( $this->input->get('page') );
    }
    else {
      $args         = func_get_args();
      $subnode_args = array_slice( $args, 2 );
    }

    $node = RouteNodeTranslator::factory( $collection, $root_node )
            ->getNode( $subnode_args );

    if ( ! $node ) {
      // whatever subnode was requested could not be found
      return $this->show404();
    }

    if ( $node->getRootId() != $root_node->getId() ) {
      // crossing into another document via RouteNodeTranslator_PagedContinuous
      url::redirect( NodePage::factory( $collection, $node )->getUrl() );
    }

    try {
      // templates split as of 0.2.1
      if ( $node->isPaged() ) {
        $this->loadView( 'viewPaged' );
      }
      else {
        $this->loadView( 'viewNonpaged' );
      }
    }
    catch ( Kohana_Exception $e ) {
      // try with single template from 0.2.0
      $this->loadView( 'view' );
    }

    $page = NodePage::factory( $collection, $node );
    
    $search_terms = $this->input->get('search');

    if ( ! $search_terms ) {
      $search_terms = array();
    }

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

    $this->passDown( 'page_title',      $node->getField('Title')
                                        . ' | ' . EmeraldviewConfig::get('emeraldview_name') );
    $this->passDown( 'node',            $node );
    $this->passDown( 'page',            $page );
    $this->passDown( 'root_node',       $root_node );
    $this->passDown( 'root_page',       $root_page );
    $this->passDown( 'language_select', myhtml::language_select( $this->availableLanguages, $this->language ) );
    $this->passDown( 'tree_pager',      NodeTreePager::html( $collection, $node ) );
    $this->passDown( 'paged_urls',      $paged_urls );
    $this->passDown( 'tree',            $tree );
    $this->passDown( 'search_terms',    $search_terms );
    $this->passDown( 'text',            $text );
  }

  public function show404()
  {
    $this->loadView( 'show404' );
    $this->passDown( 'page_title',      'Page not found' );
  }
}
