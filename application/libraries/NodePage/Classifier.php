<?php

class NodePage_Classifier extends NodePage
{
  protected static $slugs;

  public function getConfig( $subnode = null )
  {
    $node = 'classifiers.' . $this->getId();
    
    if ($subnode) {
      $node .= '.' . $subnode;
    }
    
    return $this->getCollection()->getConfig( $node );
  }

  public function getUrl()
  {
    return $this->getCollection()->getUrl() . '/browse/' . $this->getSlug();
  }
  
  public function getTitle()
  {
    return $this->getNode()->getField('Title');
  }

  public function getSlug()
  {
    if ( NodePage_Classifier::$slugs === null ) {
      NodePage_Classifier::generateSlugs( $this->getCollection() );
    }

    return NodePage_Classifier::$slugs[ $this->getId() ];
  }

  protected static function generateSlugs( Collection $collection )
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

  public static function retrieveBySlug( Collection $collection, $slug )
  {
    if ( ! is_string( $slug ) ) {
      throw new InvalidArgumentException( 'Second argument must be a string' );
    }

    if (NodePage_Classifier::$slugs === null) {
      NodePage_Classifier::generateSlugs( $collection );
    }

    foreach ( NodePage_Classifier::$slugs as $id => $test_slug ) {
      if ( $slug == $test_slug ) {
        $node = Node_Classifier::factory( $collection, $id );
        return $node->getPage();
      }
    }
  }
}
