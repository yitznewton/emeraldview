<?php

class NodePage_DocumentSection extends NodePage
{
  protected $node;
  
  protected function __construct( Node $node )
  {
    $this->node = $node;
}
  
  public function getCoverUrl()
  {
    if ( ! $this->getNode()->getField('hascover') ) {
      return false;
    }

    return $this->getNode()->getCollection()->getGreenstoneUrl() . '/archives'
           . $this->getNode()->getField('assocfilepath') . '/cover.jpg';
  }

  public function getHTML()
  {
    $xml_file = $this->getCollection()->getGreenstoneDirectory()
                . '/index/text/' . $this->getNode()->getRootNode()->getField( 'archivedir' )
                . '/doc.xml';

    $dom = DOMDocument::load( $xml_file );

    $xpath = new DOMXPath( $dom );
    $query = '/Doc/Sec[@gs2:docOID=\'' . $this->getId() . '\']';
    $dom_nodes = $xpath->query( $query );

    if ($dom_nodes->length == 0) {
      return false;
    }

    $html = trim( $dom_nodes->item(0)->nodeValue );

    // fix Greenstone macro'ed internal URLs
    $path = '/files/' . $this->getCollection()->getName() . '/index/assoc/'
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

    return $html;
  }
  
  public function getUrl()
  {
    if ( $this->getSubnodeId() ) {
      $section_url = '/' . str_replace( '.', '/', $this->getSubnodeId() );
    }
    else {
      $section_url = '';
    }

    $slug = $this->getCollection()->getSlugLookup()
            ->retrieveSlug( $this->getNode()->getRootNode()->getId() );

    return $this->getCollection()->getUrl() . "/view/$slug$section_url";
  }

  public function getSourceDocumentUrl()
  {
    return $this->getMetadataUrl( 'srclink' );
  }

  public function getScreenIconUrl()
  {
    return $this->getMetadataUrl( 'screenicon' );
  }

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

  public function getDisplayMetadata()
  {
    $fields_to_display
      = $this->getCollection()->getConfig( 'display_metadata' );

    if (!$fields_to_display) {
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
  
  public function getThumbnailUrl()
  {
    if ($this->getNode()->getField('thumbicon')) {
      $node = $this->getNode();
      $url = $this->getNode()->getField('thumbicon');
    }
    elseif ($this->getNode()->getRootNode()->getField('thumbicon')) {
      $node = $this->getNode()->getRootNode();
      $url = $this->getNode()->getRootNode()->getField('thumbicon');
    }
    else {
      return false;
    }

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

    $url  = $this->getCollection()->getUrl()
            . '/index/assoc/' . $url;

    return $url;
  }

  public function getPagedUrls()
  {
    $prev_url = '';
    $next_url = '';
    if (!$this->getNode()->isPaged() ) {
      return false;
    }

    $page_count = $this->getNode()->getRootNode()->getField( 'NumPages' );

    if ( $this->getSubnodeId() && $this->getSubnodeId() !== '1' ) {
      // current node is not the first page
      // in paged documents, there SHOULD only be one level of section nodes,
      // hence casting subnode id as integer SHOULD give us good results
      $prev_section_id = ((string) ((int) $this->getSubnodeId()) - 1);
      $prev_url = $this->getNode()->getRelatedNode( $prev_section_id )
                  ->getPage()->getUrl();
    }
    else {
      // current node is the first page or root node
      $prev_url = '';
    }

    if ( (int) $this->getSubnodeId() >= (int) $page_count ) {
      $next_url = '';
    }
    else {
      $next_section_id = ((string) ((int) $this->getSubnodeId()) + 1);
      $next_url = $this->getNode()->getRelatedNode( $next_section_id )
                  ->getPage()->getUrl();
    }

    return array(
      'previous' => $prev_url,
      'next'     => $next_url,
    );
  }
}
