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
 * @package libraries
 */
/**
 * NodePage_Classifier is a wrapper for Node_Classifier which extends webpage
 * functionalities such as URLs and node tree generation
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class NodePage_Classifier extends NodePage
{
  /**
   * An array of slugs for all classifiers in the collection
   *
   * @var array
   */
  protected static $slugs;

  /**
   * Returns one or all nodes from the classifier's config settings as
   * specified in config/emeraldview.yml
   *
   * @param string $subnode
   * @param mixed $default
   * @return mixed
   */
  public function getConfig( $subnode = null, $default = null )
  {
    $node = 'classifiers.' . $this->getId();
    
    if ($subnode) {
      $node .= '.' . $subnode;
    }
    
    return $this->getCollection()->getConfig( $node, $default );
  }

  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->getCollection()->getUrl() . '/browse/' . $this->getSlug();
  }

  /**
   * Returns the title of the classifier as set in Greenstone's metadata
   *
   * @return string
   */
  public function getTitle()
  {
    return $this->getNode()->getField('Title');
  }

  /**
   * Returns the URL slug for the current NodePage
   *
   * @return string
   */
  public function getSlug()
  {
    if ( NodePage_Classifier::$slugs === null ) {
      NodePage_Classifier::buildSlugs( $this->getCollection() );
    }

    return NodePage_Classifier::$slugs[ $this->getNode()->getRootNode()->getId() ];
  }

  /**
   * Returns NodePage_DocumentSections for randomly-selected leaf Nodes
   *
   * @param integer $count
   * @return array
   */
  public function getRandomLeafNodePages( $count = 1 )
  {
    $nodes = $this->node->getRandomLeafNodes( $count );
    $pages = array();
    
    foreach ( $nodes as $node ) {
      $pages[] = $node->getNodePage();
    }

    return $pages;
  }

  /**
   * Builds all classifier slugs
   *
   * @todo add support for custom slugs via emeraldview.yml (#21)
   * @param Collection $collection 
   */
  protected static function buildSlugs( Collection $collection )
  {
    $all_slugs      = array();
    $slug_generator = new SlugGenerator( $collection );

    foreach ( $collection->getClassifiers() as $classifier ) {
      if ( $classifier->getConfig( 'slug' ) ) {
        $slug = $classifier->getConfig( 'slug' );
      }
      else {
        $slug = $slug_generator->toSlug( $classifier->getTitle() );
      }

      $slug_base = $slug;

      // check for existing identical slugs and suffix them
      $count = 2;
      while ( in_array( $slug, $all_slugs ) ) {
        $slug = "$slug_base-$count";
        $count++;
      }

      $all_slugs[ $classifier->getId() ] = $slug;
    }

    NodePage_Classifier::$slugs = $all_slugs;
  }

  /**
   * Returns a NodePage_Classifier based on slug
   *
   * @param Collection $collection
   * @param string $slug
   * @return NodePage_Classifier
   */
  public static function retrieveBySlug( Collection $collection, $slug )
  {
    if ( ! is_string( $slug ) ) {
      throw new InvalidArgumentException( 'Second argument must be a string' );
    }

    if (NodePage_Classifier::$slugs === null) {
      NodePage_Classifier::buildSlugs( $collection );
    }

    foreach ( NodePage_Classifier::$slugs as $id => $test_slug ) {
      if ( $slug == $test_slug ) {
        $node = Node_Classifier::factory( $collection, $id );
        return $node->getNodePage();
      }
    }

    return false;
  }
}
