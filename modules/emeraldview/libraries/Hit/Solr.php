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
 * @version 0.2.0-b4
 * @package libraries
 */
/**
 * Hit for Solr instances
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class Hit_Solr extends Hit
{
  public function __construct(
    SearchHandler $search_handler,
    SimpleXMLElement $solr_hit
  ) {
    $this->searchHandler = $search_handler;

    $attributes   = $solr_hit->attributes();
    $this->docOID  = (string) $attributes['docOID'];
    $this->snippet = trim( (string) $solr_hit );
  }
}
