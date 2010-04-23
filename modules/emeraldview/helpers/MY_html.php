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
 * @package helpers
 */
/**
 * html fixes html_Core::link() for HTML 4.01 Transitional validity
 *
 * @package helpers
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class html extends html_Core
{
  /**
	 * @param   string|array  filename
	 * @param   string|array  relationship
	 * @param   string|array  mimetype
	 * @param   string        specifies suffix of the file
	 * @param   string|array  specifies on what device the document will be displayed
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function link($href, $rel, $type, $suffix = FALSE, $media = FALSE, $index = FALSE)
  {
    $parent_link = parent::link($href, $rel, $type, $suffix, $media, $index);

    return substr( $parent_link, 0, -4 ) . ">\n";
  }
}
