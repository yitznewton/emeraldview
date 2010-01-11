<?php

class DocumentSection
{
  protected $node;
  protected $tree;
  
  protected function __construct( Node $node )
  {
    $this->node = $node;

    $id = $node->getId();
    $root_id_length = strpos( $id, '.' );

    if ($root_id_length) {
      $root_id = substr( $id, 0, $root_id_length );
      $this->tree = Node_Document::factory( $node->getCollection(), $root_id );
    }
    else {
      $this->tree = $node;
    }
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

  public function getNode()
  {
    return $this->node;
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
    return $this->tree;
  }
  
  public static function factory( Node $node )
  {
    return new DocumentSection( $node );
  }
}