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
 * Query_Boolean parses the query parameters for boolean searches in the
 * context of a given collection
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class Query_Boolean extends Query
{
  protected $querystring;

  /**
   * @return string
   */
  public function getQuerystring()
  {
    if ( $this->querystring ) {
      return $this->querystring;
    }

    $this->querystring = $this->params['i1'] . ':(' . $this->params['q1'] . ')';

    if ( ! empty( $this->params['q2'] ) && ! empty( $this->params['i2'] ) ) {
      $this->querystring .= ' ' . $this->params['b2'] . ' '
                            . $this->params['i2']
                            . ':(' . $this->params['q2'] . ')';
    }

    if ( ! empty( $this->params['q3'] ) && ! empty( $this->params['i3'] ) ) {
      $this->querystring .= ' ' . $this->params['b3'] . ' '
                            . $this->params['i3']
                            . ':(' . $this->params['q3'] . ')';
    }

    return $this->querystring;
  }
}
