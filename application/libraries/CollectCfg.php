<?php

abstract class CollectCfg
{
  private function __construct() {}
  
  abstract public function isPublic();
  abstract public function getBuildtype();
  abstract public function getInfodbtype();
  abstract public function getIndexes();
  abstract public function getLevels();
  abstract public function getDefaultLevel();
  abstract public function getMetadata( $element_name, $language );
  
  public static function factory( Collection $collection )
  {
    $dir = $collection->getGreenstoneDirectory() . '/etc';
    
    if (is_readable( $dir . '/collect.cfg' )) {
      return new CollectCfg_G2( $collection );
    }
    
    throw new Exception( 'Unsupported CollectCfg for collection '
                         . $collection->getGreenstoneName() );
  }
}
