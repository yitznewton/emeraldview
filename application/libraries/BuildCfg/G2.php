<?php

class BuildCfg_G2 extends BuildCfg
{
  protected $indexes;
  
  protected function __construct( Collection $collection )
  {
    $filename = $collection->getGreenstoneDirectory() . '/index/build.cfg';
    $fh = fopen( $filename, 'rb' );
    
    if (!$fh) {
      throw new Exception( "Couldn't open build.cfg for "
                           . $collection->getName() );
    }
    
    while (!feof($fh)) {
      $line = trim(fgets( $fh ));
      $this->parse( $line );
    }
  }
  
  public function getIndexes()
  {
    return $this->indexes;
  }
  
  protected function parse( $line )
  {
    if (substr( $line, 0, 13 ) == 'indexfieldmap') {
      preg_match_all( '/ (\S+) -> ([A-Z]{2}) /x', $line, $matches );
      $this->indexes = array_combine( $matches[2], $matches[1] );
    }
  }
}