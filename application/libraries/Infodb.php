<?php

abstract class Infodb
{
  protected $collection;
  
  abstract public function getDocumentMetadata( $id );
  abstract public function getClassifierMetadata();
  abstract public function getCollectionMetadata();
  abstract public function getClassifierIds();
  
  protected function __construct( Collection $collection )
  {
    $this->collection = $collection;
    // FIXME: get rid of this once we see it doesn't break stuff
    //$this->allNodes = $this->getAllNodes();
  }
  
  abstract public function getNode( $key );
  abstract public function getAllNodes();
  abstract public function getRelatedNodeIdByDocnum( Node_Document $node, $docnum );
  abstract public function getRelatedNodeIdByTitle(  Node_Document $node, $title );

  public static function factory( Collection $collection )
  {
    if ($collection->getCollectCfg()->getInfodbtype() == 'sqlite') {
      return new Infodb_Sqlite( $collection );
    }
    
    throw new Exception('Unsupported infodbtype for collection '
                        . $collection->getName());
  }
}