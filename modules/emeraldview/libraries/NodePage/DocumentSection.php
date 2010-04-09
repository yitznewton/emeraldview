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
 * @version 0.2.0-b3
 * @package libraries
 */
/**
 * NodePage_DocumentSection is a wrapper for Node_Document which extends
 * webpage functionalities such as URLs and node tree generation
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class NodePage_DocumentSection extends NodePage
{
  /**
   * The NodePage as rendered in HTML
   *
   * @var string
   */
  protected $html;

  /**
   * Returns the URL for the document's cover image
   *
   * @return string
   */
  public function getCoverUrl()
  {
    $root_node = $this->getNode()->getRootNode();
    
    if ( ! $root_node->getField('hascover') ) {
      return false;
    }

    return $this->getNode()->getCollection()->getGreenstoneUrl() . '/archives/'
           . $root_node->getField('assocfilepath') . '/cover.jpg';
  }

  /**
   * Returns the HTML text of the Node_Document as extracted by Greenstone
   *
   * @return string
   */
  public function getHTML()
  {
    if ( isset( $this->html ) ) {
      return $this->html;
    }

    $xml_file = $this->getCollection()->getGreenstoneDirectory()
                . '/index/text/' . $this->getNode()->getRootNode()->getField( 'archivedir' )
                . '/doc.xml';

    $dom = DOMDocument::load( $xml_file );

    $xpath = new DOMXPath( $dom );
    $query = '/Doc/Sec[@gs2:docOID=\'' . $this->getId() . '\']';
    $dom_nodes = $xpath->query( $query );

    if ($dom_nodes->length == 0) {
      return $this->html = false;
    }

    $html = trim( $dom_nodes->item(0)->nodeValue );

    // fix Greenstone macro'ed internal URLs
    $path = url::base() . 'files/' . $this->getCollection()->getGreenstoneName() . '/index/assoc/'
            . $this->getNode()->getRootNode()->getField( 'archivedir' );
    $html = str_replace( '_httpdocimg_', $path, $html );

    // fix Greenstone macro'ed external URLs
    $ptn = '/_httpextlink_.*?href= ([^"]+) (?=") /x';
    $html = preg_replace_callback(
      $ptn,
      create_function('$matches', 'return urldecode( $matches[1] );'),
      $html
    );

    // strip hanging <b> tags
    $last_open_b_pos = strripos($html, '<b>');
    if ($last_open_b_pos && (!stripos($html, '</b>', $last_open_b_pos))) {
      $html = substr($html, 0, $last_open_b_pos);
    }

    // strip hanging <i> tags
    $last_open_i_pos = strripos($html, '<i>');
    if ($last_open_i_pos && (!stripos($html, '</i>', $last_open_i_pos))) {
      $html = substr($html, 0, $last_open_i_pos);
    }

    // rip out Javascript (hackish)
    $html = preg_replace('_ \<script .*? \</script\> _x', '', $html);

    return $this->html = $html;
  }
  
  /**
   * @return string
   */
  public function getUrl()
  {
    $root_id = $this->getNode()->getRootId();

    if ( $this->getNode()->isPaged() ) {
      if ( $this->getNode()->getId() == $root_id ) {
        $subnode_id = false;
      }
      else {
        $subnode_id = $this->getNode()->getField( 'Title' );
        //$subnode_id = 'jim';
      }
    }
    else {
      $subnode_id = $this->getNode()->getSubnodeId();
    }

    if ( $subnode_id ) {
      $section_url = '/' . str_replace( '.', '/', $subnode_id );
    }
    else {
      $section_url = '';
    }

    $slug = $this->getCollection()->getSlugLookup()
            ->retrieveSlug( $root_id );

    if ( ! $slug ) {
      throw new Exception( 'Slug lookup failed' );
    }

    return $this->getCollection()->getUrl() . "/view/$slug$section_url";
  }

  /**
   * Returns the URL for the source document
   *
   * @return string
   */
  public function getSourceDocumentUrl()
  {
    return $this->getMetadataUrl( 'srclink' );
  }

  /**
   * Returns the URL for an icon representaton of the source document;
   * e.g. smaller version of the source image
   *
   * @return string
   */
  public function getScreenIconUrl()
  {
    return $this->getMetadataUrl( 'screenicon' );
  }

  /**
   * A low-level method for generating URLs pointing to the Greenstone
   * collection's filesystem
   *
   * @param string $element_name
   * @return string
   */
  protected function getMetadataUrl( $element_name )
  {
    $element = $this->getNode()->getField( $element_name );

    if ( ! $element ) {
      return false;
    }

    $element = preg_replace('/"[^"]+$/', '', $element);

    $element = substr(
      $element,
      strpos( $element, 'index/assoc/' ) + 12
    );

    if ( $this->getId() != $this->getNode()->getRootId() ) {
      $assoc_path = $this->getNode()->getRootNode()->getField('assocfilepath');
      $element
        = str_replace( '[parent(Top):assocfilepath]', $assoc_path, $element );
    }

    // interpolate bracketed metadata values
    $metadata = $this->getNode()->getAllFields();
    $element = @preg_replace(
      '/ \[ (\w+) \] /ex', '$metadata["\\1"]', $element);
    
    if ( preg_last_error() ) {
      // probably tried to get a metadata element that was not set
      $msg = 'regex error in NodePage_DocumentSection::getMetadataUrl() '
             . 'in collection ' . $this->getNode()->getCollection() . ' '
             . 'for node ' . $this->getNode()->getId() . 'trying to retrieve '
             . 'element ' . $element_name;

      Log::add( 'error', $msg );

      return false;
    }

    $url  = $this->getCollection()->getGreenstoneUrl()
            . '/index/assoc/' . $element;

    return $url;
  }

  /**
   * Returns an array of metadata elements to display for the NodePage,
   * based on config settings
   *
   * @return array
   */
  public function getDisplayMetadata()
  {
    $fields_to_display = $this->getCollection()->getConfig( 'display_metadata' );

    if ( ! $fields_to_display ) {
      return array();
    }

    $display_metadata = array();

    foreach ($fields_to_display as $field_name => $display_name) {
      $element = $this->getNode()->getField( $field_name );

      if ( $element ) {
        if ( ! is_array( $element ) ) {
          $element = array( $element );
        }

        $display_metadata[ $display_name ] = $element;
      }
    }

    return $display_metadata;
  }
  
  /**
   * Returns a URL pointing to a thumbnail of the source document
   *
   * @return string
   */
  public function getThumbnailUrl()
  {
    if ($this->getNode()->getField('thumbicon')) {
      $node = $this->getNode();
    }
    elseif ($this->getNode()->getRootNode()->getField('thumbicon')) {
      $node = $this->getNode()->getRootNode();
    }
    else {
      return false;
    }

    $url = $node->getField('thumbicon');
    // strip end quote and attributes
    $url = preg_replace('/"[^"]+$/', '', $url);

    if ($node->getField('assocfilepath')) {
      $thumbicon = str_replace(
        '[assocfilepath]', $node->getField('assocfilepath'), $url
      );
    }

    $url = substr( $url, strpos($url, 'index/assoc/') + 12 );

    // interpolate bracketed metadata values
    $metadata = $this->getNode()->getAllFields();
    $url = preg_replace('/ \[ (\w+) \] /ex', '$metadata["\\1"]', $url);

    $url  = $this->getCollection()->getGreenstoneUrl()
            . '/index/assoc/' . $url;

    return $url;
  }

  /**
   * Returns an array of URLs of the previous and next nodes in the document;
   * for use with collections build with PagedImagePlugin
   *
   * @return array
   */
  public function getPagedUrls()
  {
    // this method assumes that there is only one level of child Nodes for
    // the root Node, which is the case with PagedImagePlugin documents

    if ( ! $this->getNode()->isPaged() ) {
      return false;
    }

    $prev_node = $this->getNode()->getPreviousNode();
    $next_node = $this->getNode()->getNextNode();

    if ( $prev_node->getId() == $this->getNode()->getRootId() ) {
      $prev_url = '';
    }
    else {
      // previous node exists and is not the root, but rather another child
      // Node
      $prev_url = NodePage::factory( $prev_node )->getUrl();
    }

    if ( $next_node ) {
      $next_url = NodePage::factory( $next_node )->getUrl();
    }
    else {
      $next_url = '';
    }

    return array(
      'previous' => $prev_url,
      'next'     => $next_url,
    );
  }
}
