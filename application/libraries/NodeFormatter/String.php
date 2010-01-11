<?php

class NodeFormatter_String extends NodeFormatter
{
  protected $branchFormat;
  protected $leafFormat;
  
  public function __construct( $format_config )
  {
    if (is_array( $format_config )) {
      // separate formats for branches and leaves specified

      if ( !isset($format_config['branch']) || !isset($format_config['leaf'] )) {
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
    $text = $node->getChildren()
          ? $this->branchFormat
          : $this->leafFormat
          ;

    // FIXME this section
    /*
    if (
      stripos($text, '[thumbicon]') !== false
      && $this->getField('thumbicon')
    )
    {
      // parse thumbicon URL and compose <img> tag
      // TODO: what if this is a section node, and thumb is doc-level?
      $thumb_url = Document::extractThumbnailUrl(
        $this->getClassifier()->getCollection(), $this->getAllFields()
      );
      $thumb_img = "<img src=\"$thumb_url\">";
      $text = str_ireplace('[thumbicon]', $thumb_img, $text);
    }
     */

    $url = $node->getUrl();

    if ($url) {
      $text = str_replace(
        // compose explicit <a> tags
        array('[a]', '[/a]'),
        array("<a href=\"$url\">", '</a>'),
        $text
      );
    }
    else {
      $text = str_replace( array('[a]', '[/a]'), '', $text );
    }

    if ($node->getChildren()) {
      $text = str_ireplace('[numleafdocs]', count( $this->getChildren() ), $text);
    }

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

  protected function expandTokens( $text, $index )
  {

  }
}