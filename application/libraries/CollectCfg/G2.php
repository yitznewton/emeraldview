<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.net/emeraldview/index.php/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * CollectCfg_G2 is a reader interface for Greenstone's collection configuration
 * file(s) as implemented in Greenstone2 as collect.cfg
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class CollectCfg_G2 extends CollectCfg
{
  private $isPublic;
  private $buildtype;
  private $infodbtype;
  private $indexes;
  private $levels;
  private $defaultLevel;
  private $metadata;
  
  /**
   * @param Collection $collection
   */
  protected function __construct( Collection $collection )
  {
    $filename = $collection->getGreenstoneDirectory() . '/etc/collect.cfg';
    $fh = fopen( $filename, 'rb' );
    
    if (!$fh) {
      throw new Exception( "Couldn't open collect.cfg for "
                           . $collection->getGreenstoneName() );
    }
    
    while (!feof($fh)) {
      $line = trim(fgets( $fh ));
      $this->parse( $line );
    }
  }
  
  /**
   * @param string $line
   * @return boolean
   */
  private function parse( $line )
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
        if ($line_matches[2] == 'sqlite') {
          $this->infodbtype = Infodb::TYPE_SQLITE;
          return true;
        }
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

  /**
   * @return boolean
   */
  public function isPublic()
  {
    return $this->isPublic;
  }
  
  /**
   * @return string
   */
  public function getBuildtype()
  {
    return $this->buildtype;
  }
  
  /**
   * @return string
   */
  public function getInfodbtype()
  {
    return $this->infodbtype;
  }
  
  /**
   * @return array
   */
  public function getIndexes()
  {
    return $this->indexes;
  }
  
  /**
   * @return array
   */
  public function getLevels()
  {
    return $this->levels;
  }
  
  /**
   * @return string
   */
  public function getDefaultLevel()
  {
    return $this->defaultLevel;
  }
  
  /**
   * @return string
   */
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
