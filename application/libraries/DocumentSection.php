<?php

class DocumentSection
{
  protected $node;
  protected $tree;
  
  protected function __construct( Node $node )
  {
    $this->node = $node;
  }
  
  public function getCoverUrl()
  {
  }

  public function getHTML()
  {
  }
  
  public function getId()
  {
    throw new Exception('do we need this function?');
  }
  
  public function getUrl()
  {
    $slug = $this->node->getCollection()->getSlugLookup()->retrieveSlug( $this->node->getId() );

    return $this->node->getCollection()->getUrl() . '/view/' . $slug;
  }
  
  public function getSourceDocumentUrl( $section_id = null )
  {
  }
  
  public function getThumbnailUrl()
  {
  }
  
  public function isPaged()
  {
  }

  public function getTree()
  {
    if ($this->tree) {
      return $this->tree;
    }

    // TODO: should Node_Foo::factory() get refactored to just take the base Document/Classifier object?
    return $this->tree = Node_Document::factory( $this->collection, $this->id );
  }
  
  public static function factory( Node $node )
  {
    // TODO what's the difference betw Infodb::getDocumentMetadata() and Infodb::getNode() ?
    $id = $node->getId();
    $root_id_length = strpos( $id, '.' );
    if ($root_id_length) {
      $id = substr( $id, 0, $root_id_length );
    }
    else {
      $id = substr( $id, 0 );
    }

    return new DocumentSection( $node );
  }
}