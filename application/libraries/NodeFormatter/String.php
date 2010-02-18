<?php

class NodeFormatter_String extends NodeFormatter
{
  protected $branchFormat;
  protected $leafFormat;
  
  public function __construct( Node $node, $context, $format_config )
  {
    parent::__construct( $node, $context );
    
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

    if ($this->node->getChildren()) {
      $text = str_ireplace('[numleafdocs]', count( $this->node->getChildren() ), $text);
    }

    if ( $this->node instanceof Node_Document ) {
      // implement Greenstone parent(All) format
      $parent_all_pattern = "/ \[ parent \( All ('([^']+)')? \) : ([^\]]+) \] /x";
      if ( preg_match_all( $parent_all_pattern, $text, $parent_all_matches ) ) {
        for ( $i = 0; $i < count( $parent_all_matches[0] ); $i++ ) {
          $field_glob = $this->getAncestorFieldGlob( $parent_all_matches[3][$i], $parent_all_matches[2][$i] );
          $text = str_replace( $parent_all_matches[0][$i], $field_glob, $text );
        }
      }

      // implement Greenstone parent(Top) format
      $root_pattern = '/ \[ parent \(Top\) : ([^\]]+) \] /x';
      if ( preg_match_all( $root_pattern, $text, $root_matches ) ) {
        $root_node = $this->node->getRootNode();

        for ( $i = 0; $i < count( $root_matches[0] ); $i++ ) {
          $field = $root_node->getField( $root_matches[1][$i] );

          if ( ! $field ) {
            $field = '';
          }

          $text = str_replace( $root_matches[0][$i], $field, $text );
        }
      }
    }

    if ( strpos( $text, '[a]' ) === false ) {
      $text = '[a]' . $text . '[/a]';
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

    // FIXME: a bit hardcoded, no?
    $fields[ count($fields)-1 ] = '[a]' . $fields[ count($fields)-1 ] . '[/a]';

    return implode( $separator, $fields );
  }

  protected function expandTokens( $text, $index= null )
  {
    preg_match_all('/ \[ ([^\]]+) \] /x', $text, $token_matches);

    foreach ($token_matches[1] as $key => $field) {
      if ( $field == 'a' ||  $field == '/a' ) {
        // leave link tokens
        continue;
      }

      $metadata_value = $this->getMetadataForToken( $field );

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
      $fields_to_try = explode( '\|', $token );
    }
    else {
      $fields_to_try = array( $token );
    }

    foreach ( $fields_to_try as $current_field ) {
      // this loop handles each individual field within the token
      $value = $this->node->getField( $current_field );
      if ( $value ) {
        // TODO: implement mdoffset for Classifiers
        if ( is_array( $value ) ) {
          return $value[0];
        }

        return $value;
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
  }
}
