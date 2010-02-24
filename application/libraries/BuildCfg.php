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
 * @version 0.2.0-b1
 * @package libraries
 */
/**
 * BuildCfg is a reader interface for Greenstone's build configuration
 * file(s)
 * 
 * @package EmeraldView
 * @subpackage libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
abstract class BuildCfg
{
  private function __construct() {}

  /**
   * Return an associative array of index keys and titles
   * 
   * @return array
   */
  abstract public function getIndexes();
  /**
   * Return the UNIX date the collection was last built
   *
   * @return string
   */
  abstract public function getBuildDate();
  
  /**
   * @param Collection $collection
   * @return BuildCfg
   */
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