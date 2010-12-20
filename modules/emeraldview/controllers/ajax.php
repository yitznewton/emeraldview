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
