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
    $id = $this->node->getId();

    if ( $this->node->getRootId() != $this->node->getId() ) {
      $root_id = $this->node->getRootId();
      $section_id = substr( $id, strpos( $id, '.' ) + 1);
      $section_url = str_replace( '.', '/', $section_id );
    }
    else {
      $root_id = $id;
      $section_url = '';
    }

    $slug = $this->node->getCollection()->getSlugLookup()
            ->retrieveSlug( $root_id );

    return $this->node->getCollection()->getUrl() . "/view/$slug/$section_url";
  }

  public function getNode()
  {
    return $this->node;
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
    $element = $this->node->getField( $element_name );

    if (!$element) {
      return false;
    }

    $element = preg_replace('/"[^"]+$/', '', $element);

    $element = substr(
      $element,
      strpos( $element, 'index/assoc/' ) + 12
    );

    if ( $this->node->getId() != $this->node->getRootId() ) {
      $assoc_path = $this->node->getField('assocfilepath');
      $element
        = str_replace( '[parent(Top):assocfilepath]', $assoc_path, $element );
    }

    // interpolate bracketed metadata values
    // TODO: does this ever bring up unset indexes of $doc_metadata?
    $element = preg_replace(
      '/ \[ (\w+) \] /ex', '$doc_metadata["\\1"]', $element);

    $url  = $this->getCollection()->getGreenstoneUrl()
            . '/index/assoc/' . $element;

    return $url;
  }

  public function getDisplayMetadata()
  {
    
  }
  
  public function getThumbnailUrl()
  {
  }
  
  public function getTree( NodeFormatter $node_formatter )
  {
    return NodeTreeFormatter::format( $this->node->getRootNode(), $node_formatter );
  }
}