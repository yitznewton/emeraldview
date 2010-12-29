<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.org/emeraldview/index.php?title=License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0
 * @package libraries
 */
/**
 * CollectCfg is a reader interface for Greenstone's collection configuration
 * file(s)
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
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
    $use_cache = false;

    try {
      if (
        $collection->getConfig( 'cache_internals' )
        && $cache = Cache::instance()
      ) {
        $use_cache = true;
        $cache_address = $collection->getName() . '_' . 'collectcfg';
      }
    }
    catch ( Kohana_Exception $e ) {
      // problem instantiating Cache; log and ignore
      Kohana::log( 'error', $e->getMessage() );
    }

    if ( $use_cache && $collect_cfg = $cache->get( $cache_address ) ) {
      return $collect_cfg;
    }

    $dir = $collection->getGreenstoneDirectory() . '/etc';

    if ( ! is_readable( $dir . '/collect.cfg' ) ) {
      throw new Exception( 'No supported CollectCfg for collection '
                           . $collection->getGreenstoneName() );
    }

    $collect_cfg = new CollectCfg_G2( $collection );

    if ( $use_cache ) {
      $cache->set( $cache_address, $collect_cfg );
    }

    return $collect_cfg;
  }
}
