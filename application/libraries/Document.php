<?php

class Document
{
  protected $id;
  protected $collection;
  protected $metadata;
  protected $tree;
  
  protected function __construct(
    Collection $collection, $id, array $metadata
  )
  {
    $this->id = $id;
    $this->collection = $collection;
    $this->metadata = $metadata;
  }
  
  public function getCoverUrl()
  {
  }
  
  public function getMetadata( $subnode_id = null )
  {
    $node_id = $subnode_id ? "$this->id.$subnode_id" : $this->id;

    return $this->metadata[ $node_id ];
  }
  
  public function getMetadataElement( $element_names, $subnode_id = null )
  {
    if (!is_array($element_names)) {
      $element_names = array( $element_names );
    }

    $metadata = $this->getMetadata( $subnode_id );

    if (!$metadata) {
      $error = 'Section metadata missing ('
        . $this->getCollection()->getGreenstoneName() . '/'
        . $this->getId();

      if ($subnode_id) {
        $error .= '.' . $subnode_id;
      }

      $error .= ')';

      throw new Exception( $error );
    }

    // cycle through element_names until we find one
    foreach ($element_names as $element) {
      if (isset( $metadata[ $element ] )) {
        return $metadata[ $element ];
      }
    }

    // no metadata found with any of these names
    return false;
  }
  
  public function getHTML()
  {
  }
  
  public function getId()
  {
  }
  
  public function getUrl()
  {
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
  
  public static function factory( Collection $collection, $id )
  {
    // check existence of doc by id
    if ($metadata = $collection->getInfodb()->getDocumentMetadata( $id )) {
      return new Document( $collection, $id, $metadata );
    }
    
    return false;
  }
}