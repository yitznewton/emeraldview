<?php

abstract class BuildCfg
{
  private function __construct() {}

  abstract public function getIndexes();
  abstract public function getBuildDate();
  
  public static function factory( Collection $collection )
  {
    $dir = $collection->getGreenstoneDirectory();
    
    if (is_readable( $dir . '/index/build.cfg' )) {
      return new BuildCfg_G2( $collection );
    }
    
    throw new Exception( 'Unsupported BuildCfg for collection '
                         . $collection->getGreenstoneName() );
  }
}