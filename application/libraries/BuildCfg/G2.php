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
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0b1
 * @package libraries
 */
/**
 * BuildCfg_G2 is a reader interface for Greenstone's build configuration
 * file(s) as implemented in Greenstone2 as build.cfg
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
final class BuildCfg_G2 extends BuildCfg
{
  /**
   * @var array
   */
  private $indexes;
  /**
   * @var string
   */
  private $buildDate;
  
  /**
   * @param Collection $collection 
   */
  protected function __construct( Collection $collection )
  {
    $filename = $collection->getGreenstoneDirectory() . '/index/build.cfg';
    $fh = fopen( $filename, 'rb' );
    
    if (!$fh) {
      throw new Exception( "Couldn't open build.cfg for "
                           . $collection->getGreenstoneName() );
    }
    
    while (!feof($fh)) {
      $line = trim(fgets( $fh ));
      $this->parse( $line );
    }
  }
  
  /**
   * @return array
   */
  public function getIndexes()
  {
    return $this->indexes;
  }

  /**
   * @return string
   */
  public function getBuildDate()
  {
    return $this->buildDate;
  }
  
  /**
   * @param string $line
   * @return void
   */
  private function parse( $line )
  {
    if (substr( $line, 0, 13 ) == 'indexfieldmap') {
      preg_match_all( '/ (\S+) -> ([A-Z]{2}) /x', $line, $matches );
      $this->indexes = array_combine( $matches[2], $matches[1] );
    }

    if (substr( $line, 0, 9 ) == 'builddate') {
      preg_match( '/\d+$/', $line, $matches );
      if (isset($matches[0]) && $matches[0]) {
        $this->buildDate = $matches[0];
      }
      else {
        $msg = 'Invalid build date for ' . $collection->getGreenstoneName();
        throw new Exception( $msg );
      }
    }
  }
}