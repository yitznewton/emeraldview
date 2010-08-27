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
 * QueryBuilder_Boolean creates Zend_Search_Lucene objects for search, based on
 * the Collection and boolean query parameters
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class QueryBuilder_Boolean extends QueryBuilder
{
  protected $querystring;

  /**
   * @return Zend_Search_Lucene_Search_Query
   */
  public function getQuery()
  {
    if ($this->query) {
      return $this->query;
    }

    $querystring = $this->getDisplayQuery();

    $query = Zend_Search_Lucene_Search_QueryParser::parse( $querystring );

    return $this->query = $query;
  }

  /**
   * @return string
   */
  public function getDisplayQuery()
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
