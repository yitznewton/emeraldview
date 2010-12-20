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
 * @package helpers
 */
/**
 * myview_Core provides HTML composition functions for the view controller method
 *
 * @package helpers
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class myview_Core
{
  /**
   * Returns an HTML <dl> element representing the specified metadata
   *
   * @param array $metadata An associative array of metadata names and values
   * @return string
   */
  public static function metadata_list( array $metadata )
  {
    $items_html = '';

    foreach ( $metadata as $display_name => $data) {
      $items_html .= myhtml::element( 'dt', L10n::_($display_name) );

      $values = '';

      foreach ($data as $value) {
        $values .= myhtml::element( 'dd', $value );
      }

      $items_html .= $values;
    }

    return myhtml::element( 'dl', $items_html );
  }
}
