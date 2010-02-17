<?php

class NodePage_Classifier extends NodePage
{
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
    // FIXME: what if two classifiers have the same title?
    $slug_generator = new SlugGenerator( $this->getCollection() );

    return $slug_generator->toSlug( $this->getTitle() );
  }

  public static function retrieveBySlug( Collection $collection, $slug )
  {
    if ( ! is_string( $slug ) ) {
      throw new InvalidArgumentException( 'Second argument must be a string' );
    }

    $all_classifiers = $collection->getClassifiers();
    $classifier = false;

    foreach ( $all_classifiers as $test_classifier ) {
      if ( $slug == $test_classifier->getSlug() ) {
        $classifier = $test_classifier;
        break;
      }
    }

    return $classifier;
  }
}
