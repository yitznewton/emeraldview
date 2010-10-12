<?php

class Ajax_Controller extends Emeraldview_Template_Controller
{
  protected $theme;
  protected $availableLanguages;
  protected $language;
  protected $collection;
  protected $session;

  public function __construct()
  {
    $this->auto_render = false;

    if ( ! EmeraldviewConfig::get('greenstone_collection_dir') ) {
      $msg = 'Greenstone collection directory not specified '
           . 'in config/emeraldview.yml';
      throw new Exception( $msg );
    }

    Controller::__construct();

    $this->session = Session::instance();
    $this->theme = EmeraldviewConfig::get('default_theme', 'default');

    if ($this->session->get('language')) {
      $this->language = $this->session->get('language');
    }
    else {
      $this->language = EmeraldviewConfig::get('default_language', 'en');
    }

    $this->loadGettextDomain( $this->language );
  }

  protected function loadCollection( $collection_name )
  {
    $this->collection = Collection::factory( $collection_name );

    if (!$this->collection) {
      return false;
    }

    // override global defaults if collection config values set
    if (
      ! $this->session->get('language')
      && $this->collection->getConfig('default_language')
    ) {
      $this->language = $this->collection->getConfig('default_language');
    }

    if ( $this->collection->getConfig('theme') ) {
      $this->theme = $this->collection->getConfig('theme');
    }

    return $this->collection;
  }

  public function browse( $collection_name, $node_id )
  {
    $collection = $this->loadCollection( $collection_name );

    if ( ! $collection ) {
      header('HTTP/1.1 404 Not Found');
      exit;
    }

    $node = Node_Classifier::factory( $collection, $node_id );

    if ( ! $node ) {
      header('HTTP/1.1 404 Not Found');
      exit;
    }

    $page = $node->getRootNode()->getNodePage();

    $node_tree_formatter = new NodeTreeFormatter( $node, $page );

    $output = $node_tree_formatter->render();

    echo $output;
  }
}
