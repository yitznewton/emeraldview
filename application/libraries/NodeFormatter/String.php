<?php

class NodeFormatter_String extends NodeFormatter
{
  protected $branchFormat;
  protected $leafFormat;
  
  public function __construct( $format_config )
  {
    if (is_array( $format_config )) {
      // separate formats for branches and leaves specified

      if ( ! isset($format_config['branch']) || ! isset($format_config['leaf'] )) {
        throw new Exception( 'Invalid format config setting' );
      }

      $this->branchFormat = $format_config['branch'];
      $this->leafFormat   = $format_config['leaf'];
    }
    else {
      $this->branchFormat = $format_config;
      $this->leafFormat   = $format_config;
    }
  }

  public function format( Node $node, $index = null )
  {
    if ( $node->getChildren() ) {
      $text = $this->branchFormat;
    }
    else {
      $text = $this->leafFormat;
    }

    $node_page = NodePage::factory( $node );

    if (
      stripos($text, '[thumbicon]') !== false
      && $node_page->getThumbnailUrl()
    )
    {
      // parse thumbicon URL and compose <img> tag
      $thumb_url = $node_page->getThumbnailUrl();
      $thumb_img = "<img src=\"$thumb_url\">";
      $text = str_ireplace('[thumbicon]', $thumb_img, $text);
    }

    $url = $node_page->getUrl();

    if ($url) {
      $text = str_replace(
        // compose explicitly-specified <a> tags
        array('[a]', '[/a]'),
        array("<a href=\"$url\">", '</a>'),
        $text
      );
    }
    else {
      $text = str_replace( array('[a]', '[/a]'), '', $text );
    }

    if ($node->getChildren()) {
      $text = str_ireplace('[numleafdocs]', count( $node->getChildren() ), $text);
    }

    /*
    if ( $node instanceof Node_Document ) {
      $parent_all_pattern = "/ \[ parent \( All '([^']+)' \) : ([^\]]+) \] /x";
      if ( preg_match_all( $parent_all_pattern, $text, $parent_all_matches ) ) {
        for ( $i = 0; $i < count( $parent_all_matches[0] ); $i++ ) {
          $field_glob = $this->getAncestorFieldGlob( $node, $parent_all_matches[1][$i], $parent_all_matches[2][$i] );
          str_replace( $parent_all_matches[0][$i], $field_glob, $text );
        }
      }
    }
     */

    // parse for remaining, generic metadata tokens
    $text = $this->expandTokens( $text, $index );

    // clean up
    $empties = array('()', '[]', '<>', '{}');
    $text = str_replace($empties, '', $text);
    $text = preg_replace('/ {2,}/', ' ', $text);
    $text = trim($text);

    if ($text) {
      return $text;
    }
    else {
      // fall back to last-resort metadata fields
      return parent::format( $node );
    }
  }

  protected function getAncestorFieldGlob( Node_Document $node, $fieldname, $separator )
  {
    $fields = array();

    foreach ( $node->getAncestors() as $ancestor ) {
      if ( $ancestor->getField( $fieldname ) ) {
        $fields[] = $ancestor->getField( $fieldname );
      }
    }

    return implode( $separator, $fields );
  }

  protected function expandTokens( $text, $index )
  {
    // FIXME: implement
  }
}
