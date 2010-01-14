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
  }

  public function getHTML()
  {
    $xml_file = $this->node->getCollection()->getGreenstoneDirectory()
                . '/index/text/' . $this->node->getRootNode()->getField( 'archivedir' )
                . '/doc.xml';

    $dom = DOMDocument::load( $xml_file );

    $xpath = new DOMXPath( $dom );
    $query = '/Doc/Sec[@gs2:docOID=\'' . $this->node->getId() . '\']';
    $dom_nodes = $xpath->query( $query );

    if ($dom_nodes->length == 0) {
      return false;
    }

    $html = trim( $dom_nodes->item(0)->nodeValue );

    // fix Greenstone macro'ed internal URLs
    $path  = '/files/' . $this->node->getCollection()->getName();
    $path .= '/index/assoc/' . $this->node->getField( 'archivedir' );
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
  
  public function getId()
  {
    throw new Exception('do we need this function?');
  }
  
  public function getUrl()
  {
    if ( $this->getSubnodeId() ) {
      $section_url = str_replace( '.', '/', $this->getSubnodeId() );
    }
    else {
      $section_url = '';
    }

    $slug = $this->getNode()->getCollection()->getSlugLookup()
            ->retrieveSlug( $this->getNode()->getRootNode()->getId() );

    return $this->node->getCollection()->getUrl() . "/view/$slug/$section_url";
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
    // FIXME: test this function
    $element = $this->getNode()->getField( $element_name );

    if (!$element) {
      return false;
    }

    $element = preg_replace('/"[^"]+$/', '', $element);

    $element = substr(
      $element,
      strpos( $element, 'index/assoc/' ) + 12
    );

    if ( $this->node->getId() != $this->node->getRootId() ) {
      $assoc_path = $this->getNode()->getRootNode()->getField('assocfilepath');
      $element
        = str_replace( '[parent(Top):assocfilepath]', $assoc_path, $element );
    }

    // interpolate bracketed metadata values
    // TODO: does this ever bring up unset indexes of $doc_metadata?
    $metadata = $this->getNode()->getAllFields();
    // FIXME: this preg_replace errors out if $metadata[x] doesn't exist
    $element = preg_replace(
      '/ \[ (\w+) \] /ex', '$metadata["\\1"]', $element);

    $url  = $this->getNode()->getCollection()->getGreenstoneUrl()
            . '/index/assoc/' . $element;

    return $url;
  }

  public function getDisplayMetadata()
  {
    $fields_to_display
      = $this->getNode()->getCollection()->getConfig( 'display_metadata' );

    if (!$fields_to_display) {
      return false;
    }

    $display_metadata = array();

    foreach ($fields_to_display as $field_name => $display_name) {
      if ($element = $this->getNode()->getField( $field_name )) {
        // FIXME is this reimplemented properly in 0.2?
        if (!is_array( $element )) {
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

    $url  = $this->getNode()->getCollection()->getUrl()
            . '/index/assoc/' . $url;

    return $url;
  }

  public function getNodeFormatter()
  {
    if ($this->getNode()->getCollection()->getConfig( 'document_tree_format' )) {
      return new NodeFormatter_String(
        $this->getNode()->getCollection()->getConfig( 'document_tree_format' )
      );
    }
    elseif ($this->getNode()->getCollection()->getConfig( 'document_tree_format_function' )) {
      return new NodeFormatter_Function(
        $this->getNode()->getCollection()->getConfig( 'document_tree_format_function' )
      );
    }
    else {
      return new NodeFormatter_String( '[Title]' );
    }
  }

  public function getPagedUrls()
  {
    $prev_url = '';
    $next_url = '';
    if (!$this->getNode()->isPaged() ) {
      return false;
    }

    $page_count = $this->getNode()->getRootNode()->getField( 'NumPages' );
    // TODO: refactor to sthg like $collection->getSlugLookup()->retrieveNode()
    $slug       = $this->getNode()->getCollection()->getSlugLookup()
                  ->retrieveSlug( $this->getNode()->getRootNode()->getId() );

    if ( $this->getSubnodeId() == '1' ) {
      $prev_url = '';
    }
    else {
      // in paged documents, there SHOULD only be one level of section nodes,
      // hence casting subnode id as integer SHOULD give us good results
      $prev_section_id = ((string) ((int) $this->getSubnodeId()) - 1);
      $prev_node = $this->getNode()->getRelatedNode( $prev_section_id );
      $prev_url = NodePage_DocumentSection::factory( $prev_node )->getUrl();
    }

    if ( (int) $this->getSubnodeId() >= (int) $page_count ) {
      $next_url = null;
    }
    else {
      $next_section_id = ((string) ((int) $this->getSubnodeId()) + 1);
      $next_node = $this->getNode()->getRelatedNode( $next_section_id );
      $next_url = NodePage_DocumentSection::factory( $next_node )->getUrl();
    }

    return array(
      'previous' => $prev_url,
      'next'     => $next_url,
    );
  }
}