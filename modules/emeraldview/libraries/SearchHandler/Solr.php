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
 * SearchHandler for Solr instances
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class SearchHandler_Solr extends SearchHandler
{
  public function execute()
  {
    $querystring = $this->query->getQuerystring();

    $solr_params = array(
      'hl'      => 'on',
      'hl.fl'   => '*',
      'qf'      => 'text EX^2.5',
      'wt'      => 'xslt',
      'tr'      => 'emeraldview.xsl',
      'start'   => $this->startAt-1,
      'rows'    => $this->hitsPerPage,
      'q'       => $querystring,
    );

    if ( $this->query instanceof Query_Simple ) {
      //$solr_params['defType'] = 'dismax';  // breaks apostrophes in Hebrew
    }

    $host = $this->query->getCollection()->getConfig( 'solr_host' );

    if ( ! $host ) {
      $msg = 'No Solr host specified in config for collection '
             . $this->query->getCollection()->getGreenstoneName();

      throw new Exception( $msg );
    }

    $query_url = 'http://' . $host . '/select/?'
                 . http_build_query( $solr_params );

    $query_url = substr( $query_url, 0, 2048 );

    $last_quote_pos = strrpos( $query_url, '+OR+%22' );

    if ( $last_quote_pos !== false && substr( $query_url, -3 ) != '%22' ) {
      // truncated in the middle of a quoted portion
      // FIXME: this hack doesn't always seem to work
      $query_url = substr( $query_url, 0, -3 ) . '%22';
    }
    
    $ctx = stream_context_create( array( 'http' => array(
      'timeout' => 6,
    )));
    
    $xml = @file_get_contents( $query_url, 0, $ctx );

    if ( ! $xml ) {
      throw new Exception( 'Unexpected or no response from Solr' );
    }
    
    $data = new SimpleXMLElement( $xml );
    
    $attributes          = $data->attributes();
    $this->totalHitCount = (int) $attributes['numFound'];

    $hits = array();

    foreach ( $data->children() as $child ) {
      $hits[] = new Hit_Solr( $this, $child );
    }

    return $hits;
  }

  /**
   * Returns an array of query parameters with irrelevant ones filtered out -
   * prepares them for search history processing
   *
   * @param array $params An array of raw parameters
   * @return array
   */
  protected function filterParams( array $params )
  {
    $valid_params = array(
      'l', 'i', 'i1', 'i2', 'i3', 'q', 'q1', 'q2', 'q3', 'b1', 'b2', 'b3',
    );

    foreach( $params as $key => $value ) {
      if ( ! in_array( $key, $valid_params ) ) {
        unset( $params[ $key ] );
      }
    }

    return $params;
  }
}
