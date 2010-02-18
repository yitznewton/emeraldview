<?php

abstract class Infodb
{
  const TYPE_SQLITE = 0;

  protected $collection;
  
  abstract public function getDocumentMetadata( $id );
  abstract public function getClassifierMetadata();
  abstract public function getCollectionMetadata();
  abstract public function getClassifierIds();
  
  protected function __construct( Collection $collection )
  {
    $this->collection = $collection;
  }
  
  abstract public function getNode( $key );
  abstract public function getAllNodes();
  abstract public function getCousinIdByDocnum( Node_Document $node, $docnum );
  abstract public function getCousinIdByTitle(  Node_Document $node, $title );

  public static function factory( Collection $collection )
  {
    $type = $collection->getCollectCfg()->getInfodbtype();
    
    if ($type == Infodb::TYPE_SQLITE) {
      return new Infodb_Sqlite( $collection );
    }
    
    throw new Exception( 'Unsupported infodbtype for collection '
                         . $collection->getGreenstoneName() );
  }
}