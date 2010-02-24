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
 * @version 0.2.0-b2
 * @package helpers
 */
/**
 * url extends Kohana's URL helpers to include support for a blank URL for
 * the default controller method
 *
 * @package helpers
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class url extends url_Core
{
  /**
   * @param boolean $qs
   * @return string
   */
  public static function current( $qs = FALSE )
  {
    $url     = parent::current( $qs );
    $default = Router::routed_uri( '_default' );

    if ( $url == $default ) {
      return $qs ? Router::$query_string : '';
    }
    else {
      return $url;
    }
  }
}
