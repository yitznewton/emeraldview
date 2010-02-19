<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.net/emeraldview/index.php/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0b1
 * @package libraries
 */
/**
 * NodePage_Classifier is a wrapper for Node_Classifier which extends webpage
 * functionalities such as URLs and node tree generation
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
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
   * @return array
   */
  public function getConfig( $subnode = null )
  {
    $node = 'classifiers.' . $this->getId();
    
    if ($subnode) {
      $node .= '.' . $subnode;
    }
    
    return $this->getCollection()->getConfig( $node );
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
   * Builds all classifier slugs
   *
   * @todo add support for custom slugs via emeraldview.yml
   * @param Collection $collection 
   */
  protected static function buildSlugs( Collection $collection )
  {
    $all_slugs      = array();
    $slug_generator = new SlugGenerator( $collection );

    foreach ( $collection->getClassifiers() as $classifier ) {
      $slug = $slug_generator->toSlug( $classifier->getTitle() );

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
        return $node->getPage();
      }
    }
  }
}
