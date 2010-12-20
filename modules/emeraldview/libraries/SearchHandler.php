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
 * SearchHandler is a wrapper class which creates objects and delegates
 * responsibility for search functionality
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
abstract class SearchHandler
{
  /**
   * The Query being performed
   *
   * @var Query
   */
  protected $query;
  /**
   * The total number of hits generated by this search
   *
   * @var integer
   */
  protected $totalHitCount;
  /**
   * The number of hits to return at a time
   *
   * @var integer
   */
  protected $hitsPerPage = 20;
  /**
   * The index of the first hit to return
   *
   * @var integer
   */
  protected $startAt = 1;

  /**
   * @param Query $query The Query to perform
   */
  protected function __construct( Query $query )
  {
    $this->query = $query;
  }

  /**
   * Performs query and returns resultant Hits
   *
   * @return array Hit[]
   */
  abstract public function execute();

  /**
   * Returns associated Query
   *
   * @return Query
   */
  public function getQuery()
  {
    return $this->query;
  }

  /**
   * Returns Collection for the search
   *
   * @return Collection
   */
  public function getCollection()
  {
    return $this->query->getCollection();
  }

  /**
   * Returns total number of hits generated by this search
   *
   * @return integer
   */
  public function getTotalHitCount()
  {
    if ( $this->totalHitCount === null ) {
      throw new UnexpectedValueException( 'Total hit count not set' );
    }

    return $this->totalHitCount;
  }

  /**
   * Returns the number of hits to return at a time
   *
   * @return integer
   */
  public function getHitsPerPage()
  {
    if ( $this->hitsPerPage === null ) {
      throw new UnexpectedValueException( 'Hits per page not set' );
    }

    return $this->hitsPerPage;
  }

  /**
   * Returns the index of the first hit to return
   *
   * @return integer
   */
  public function getStartAt()
  {
    if ( $this->startAt === null ) {
      throw new UnexpectedValueException( 'Start at not set' );
    }

    return $this->startAt;
  }

  /**
   * @param integer $v The number of hits to return at a time
   */
  public function setHitsPerPage( $v )
  {
    if ( ! is_int( $v ) ) {
      throw new InvalidArgumentException( 'Argument must be an integer' );
    }

    $this->hitsPerPage = $v;
  }

  /**
   * @param integer $v The index of the first hit to return
   */
  public function setStartAt( $v )
  {
    if ( ! is_int( $v ) ) {
      throw new InvalidArgumentException( 'Argument must be an integer' );
    }

    $this->startAt = $v;
  }

  /**
   * @param Collection $collection The Collection to search
   */
  public static function factory( Query $query )
  {
    if ( $query->getCollection()->getConfig( 'solr_host' ) ) {
      return new SearchHandler_Solr( $query );
    }
    
    return new SearchHandler_Zend( $query );
  }
}
