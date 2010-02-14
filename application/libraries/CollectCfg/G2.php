<?php

class CollectCfg_G2 extends CollectCfg
{
  protected $isPublic;
  protected $buildtype;
  protected $infodbtype;
  protected $indexes;
  protected $levels;
  protected $defaultLevel;
  protected $metadata;
  
  protected function __construct( Collection $collection )
  {
    $filename = $collection->getGreenstoneDirectory() . '/etc/collect.cfg';
    $fh = fopen( $filename, 'rb' );
    
    if (!$fh) {
      throw new Exception( "Couldn't open collect.cfg for "
                           . $collection->getName() );
    }
    
    while (!feof($fh)) {
      $line = trim(fgets( $fh ));
      $this->parse( $line );
    }
  }
  
  protected function parse( $line )
  {
    preg_match('/^ (\S+) \s+ (\S+) /x', $line, $line_matches);
    
    if (
      !isset( $line_matches )
      || !is_array( $line_matches )
      || count( $line_matches ) < 3
    ) {
      return false;
    }
    
    // first, check for the easy ones:
    
    switch ($line_matches[1]) {
      case 'public':
        $this->isPublic = $line_matches[2] == 'true' ? true : false;
        return true;
        
      case 'buildtype':
        $this->buildtype = $line_matches[2];
        return true;
        
      case 'infodbtype':
        $this->infodbtype = $line_matches[2];
        return true;
    }
    
    if (substr($line, 0, 6) == 'levels') {
      preg_match_all( '/\S+/', $line, $matches, null, 6 );

      foreach ( $matches[0] as $match ) {
        if ( $match != 'paragraph' ) {
          // Lucene builds do not support paragraph level
          $this->levels[] = $match;
        }
      }

      return true;
    }
    
    // now the hard ones:
    
    if (substr($line, 0, 14) == 'collectionmeta') {
      // this assumes that there are no multi-line collectionmeta entries...
      $pattern  = "/^ collectionmeta \s+ (\S+) \s+";
      $pattern .= "\\[l= ([^]]+) \\]" . '\s+ "? ([^"]+) /ix';
      preg_match($pattern, $line, $matches);

      if (isset($matches) && is_array($matches) && count($matches) == 4) {
        $key      = $matches[1];
        $language = $matches[2];
        
        $search  = array("\'", '\\n');
        $replace = array("'", '<br />');

        $field_value = str_replace($search, $replace, $matches[3]);
        $this->metadata[ $key ][ $language ] = $field_value;
        
        return true;
      }
    }
  }
  
  public function isPublic()
  {
    return $this->isPublic;
  }
  
  public function getBuildtype()
  {
    return $this->buildtype;
  }
  
  public function getInfodbtype()
  {
    return $this->infodbtype;
  }
  
  public function getIndexes()
  {
    return $this->indexes;
  }
  
  public function getLevels()
  {
    return $this->levels;
  }
  
  public function getDefaultLevel()
  {
    return $this->defaultLevel;
  }
  
  public function getMetadata( $element_name, $language )
  {
    if (isset( $this->metadata[ $element_name ][ $language ] )) {
      return $this->metadata[ $element_name ][ $language ];
    }

    foreach ( EmeraldviewConfig::get( 'languages' ) as $code => $name ) {
      if (isset( $this->metadata[ $element_name ][ $code ] )) {
        return $this->metadata[ $element_name ][ $code ];
      }
    }

    return false;
  }
}