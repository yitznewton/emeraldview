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
    $host = $this->query->getCollection()->getConfig( 'solr_host' );
    $port = $this->query->getCollection()->getConfig( 'solr_port', 8983 );
    $path = $this->query->getCollection()->getConfig( 'solr_path', '/solr' );

    if ( ! $host ) {
      $msg = 'No Solr host specified in config for collection '
             . $this->query->getCollection()->getGreenstoneName();

      throw new Exception( $msg );
    }

    $querystring = $this->query->getQuerystring();

    $solr_params = array(
      'hl'      => 'on',
      'hl.fl'   => '*',
      'wt'      => 'xslt',
      'tr'      => 'emeraldview.xsl',
      'start'   => $this->startAt-1,
      'rows'    => $this->hitsPerPage,
      'q'       => $querystring,
    );

    $qt = $this->query->getCollection()->getConfig( 'solr_simple_qt' );

    if ( $this->query instanceof Query_Simple && $qt ) {
      $solr_params['qt'] = $qt;
    }

    $path .= '/select';

    $xml = $this->post( $host, $path, $port, http_build_query( $solr_params ) );

    if ( ! $xml || substr( $xml, 0, 5 ) !== '<?xml' ) {
      throw new Exception( 'Unexpected or no response from Solr' );
    }
    
    return $this->parseHits( $xml );
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

  protected function parseHits( $xml )
  {
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
   *
   * @param string $host The host on which Solr is running
   * @param string $path The path to Solr
   * @param int $port The port Solr is listening on
   * @param array|string $data The query data
   * @return string
   */
  protected function post( $host, $path, $port, $data )
  {
    $curl = curl_init();

    curl_setopt( $curl, CURLOPT_TIMEOUT, 5 );
    curl_setopt( $curl, CURLOPT_PORT, $port );
    curl_setopt( $curl, CURLOPT_URL, 'http://' . $host . $path );
    curl_setopt( $curl, CURLOPT_POST, true );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );

    $response = curl_exec( $curl );

    curl_close( $curl );

    return $response;
  }
}
