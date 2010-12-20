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
 * HitsPageLinks is a simple extension of stdObject to hold URLs for HitsPage
 * navigation
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class HitsPageLinks
{
  /**
   * URL for the first page of hits
   *
   * @var string
   */
  public $first;
  /**
   * URL for the previous page of hits
   *
   * @var string
   */
  public $previous;
  /**
   * URL for the next page of hits
   *
   * @var string
   */
  public $next;
  /**
   * URL for the last page of hits
   *
   * @var string
   */
  public $last;
  /**
   * An associative array of URLs for all except the current page of hits,
   * with the current page represented by e.g. array( 123 => null )
   *
   * @var array
   */
  public $pages = array();
}
