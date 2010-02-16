<?php

class NodeFormatter_String extends NodeFormatter
{
  protected $branchFormat;
  protected $leafFormat;
  
  public function __construct( Node $node, $format_config )
  {
    parent::__construct( $node );
    
    if ( is_array( $format_config ) ) {
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

  public function format( $index = null )
  {
    if ( $this->node->getChildren() ) {
      $text = $this->branchFormat;
    }
    else {
      $text = $this->leafFormat;
    }

    $node_page = NodePage::factory( $this->node );

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

    if ($this->node->getChildren()) {
      $text = str_ireplace('[numleafdocs]', count( $this->node->getChildren() ), $text);
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
      return parent::format();
    }
  }

  protected function getAncestorFieldGlob( $fieldname, $separator )
  {
    $fields = array();

    foreach ( $this->node->getAncestors() as $ancestor ) {
      if ( $ancestor->getField( $fieldname ) ) {
        $fields[] = $ancestor->getField( $fieldname );
      }
    }

    return implode( $separator, $fields );
  }

  protected function expandTokens( $text, $index= null )
  {
    preg_match_all('/ \[ ([^\]]+) \] /x', $text, $token_matches);

    foreach ($token_matches[1] as $key => $field) {
      $metadata_value = $this->getMetadataForToken( $field );

      if (is_array($metadata_value)) {
        // TODO: old Trac #25

        // $metadata_index = $this->getMdOffset( $index );
        // $metadata_value = $metadata_value[ $metadata_index ];

        // a short-circuit in the meantime:
        $metadata_value = $metadata_value[0];
      }

      if ($metadata_value) {
        $text =
          str_replace( $token_matches[0][$key], $metadata_value, $text );
      }
      else {
        $text = str_replace($token_matches[0][$key], '', $text);
      }
    }

    return $text;
  }

  protected function getMetadataForToken( $token )
  {
    $replacement_text = null;

    if (strpos( $token, '|' ) !== false) {
      $fields_to_try = split('\|', $token);
    }
    else {
      $fields_to_try = array( $token );
    }



    foreach ($fields_to_try as $current_field) {
      // this loop handles each individual field within the token
      if ( $this->node->getField( $current_field ) ) {
        return $this->node->getField( $current_field );
      }

      if (
        preg_match('/^ format: ( \S+ ) $/x', $current_field, $format_matches)
      ) {
        // date formatting

        $field_name = $format_matches[1];

        if ($date = $this->node->getField( $field_name )) {
          $length = strlen( $date );

          $date_formatted = '';

          if ($length == 8) {
            $date_formatted .= ltrim( substr($date, 6, 2), '0' );
          }

          if ($length >= 6) {
            $month_name = date(
              "F", mktime(0, 0, 0, (int) substr($date, 4, 2), 10));
            $date_formatted .= ' ' . $month_name;
          }

          if ($length >= 4) {
            $date_formatted .= ' ' . substr($date, 0, 4);
          }

          $date_formatted = trim( $date_formatted );

          $replacement_text = $date_formatted;
          break;
        }
      }
    }

    return $replacement_text;
  }
}
