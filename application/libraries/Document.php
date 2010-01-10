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
        . $this->id;

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
    throw new Exception('do we need this function?');
  }
  
  public function getUrl()
  {
    $slug = $this->collection->getSlugLookup()->retrieveSlug( $this->id );

    return $this->collection->getUrl() . '/view/' . $slug;
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
  
  public static function factory( $object, $id = null )
  {
    // compensate for lack of __callStatic() in <5.3
    switch (get_class( $object )) {
      case 'Node_Document':
        return Document::factoryNodeDocument( $object );
      case 'Collection':
        return Document::factoryCollection( $object, $id );
      default:
        $msg = 'First argument must be an instance of Node_Document '
             . 'or Collection';
        throw new Exception( $msg );
    }
  }

  public static function factoryCollection( Collection $collection, $id )
  {
    // check existence of doc by id
    if ( $metadata = $collection->getInfodb()->getDocumentMetadata( $id ) ) {
      return new Document( $collection, $id, $metadata );
    }

    return false;
  }

  public static function factoryNodeDocument( Node_Document $node )
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

    return Document::factoryCollection( $node->getCollection(), $id );
  }
}