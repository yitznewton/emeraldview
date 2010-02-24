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
 * CollectCfg is a reader interface for Greenstone's collection configuration
 * file(s)
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
abstract class CollectCfg
{
  private function __construct() {}
  
  /**
   * Whether Collection is set to display publicly
   *
   * @return boolean
   */
  abstract public function isPublic();
  /**
   * Returns Collection's index build type
   *
   * @return string
   */
  abstract public function getBuildtype();
  /**
   * Returns Collection's metadata datastore type
   *
   * @return string
   */
  abstract public function getInfodbtype();
  /**
   * Returns array of available indexes
   *
   * @return array
   */
  abstract public function getIndexes();
  /**
   * Returns array of available index levels
   *
   * @return array
   */
  abstract public function getLevels();
  /**
   * Returns default index level
   *
   * @return string
   */
  abstract public function getDefaultLevel();
  /**
   * Returns a Collection-level metadata element
   *
   * @return string
   */
  abstract public function getMetadata( $element_name, $language );
  
  /**
   * @param Collection $collection
   * @return CollectCfg
   */
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
