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
  }
  
  public static function factory( Collection $collection )
  {
    if ($collection->getCollectCfg()->getInfodbtype() == 'sqlite') {
      return new Infodb_Sqlite( $collection );
    }
    
    throw new Exception('Unsupported infodbtype for collection '
                        . $collection->getName());
  }
}