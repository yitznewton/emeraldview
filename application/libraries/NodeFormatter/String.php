<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.net/emeraldview/index.php/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0b1
 * @package libraries
 */
/**
 * NodeFormatter_String formulates a string representation of a Node's metadata,
 * based on a given tokenized specification string
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class NodeFormatter_String extends NodeFormatter
{
  /**
   * The format string to use for branch Nodes
   *
   * @var string
   */
  protected $branchFormat;
  /**
   * The format string to use for leaf Nodes
   *
   * @var string
   */
  protected $leafFormat;
  
  /**
   *
   * @param Node $node
   * @param array|string $format_config 
   */
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

  /**
   * @return string
   */
  //public function format( $index = null )
  public function format()
  {
    if ( $this->node->getChildren() ) {
      $text = $this->branchFormat;
    }
    else {
      $text = $this->leafFormat;
    }

    $node_page = NodePage::factory( $this->node );

    $text = str_ireplace( '[href]', $node_page->getUrl(), $text );


    if ($this->node->getChildren()) {
      $text = str_ireplace('[numleafdocs]', count( $this->node->getChildren() ), $text);
    }

    if ( $this->node instanceof Node_Document ) {
      // TODO: move a bunch of these specific things into getMetadataForToken()
      $text = str_ireplace( '[DocOID]', $this->node->getId(), $text );
      $text = str_ireplace( '[DocTopOID]', $this->node->getRootId(), $text );
      $text = str_ireplace( '[DocImage]', $node_page->getCoverUrl(), $text );

      $srclink_search  = array( '[srclink]', '[/srclink]' );
      $srclink_replace = array( '<a href="' . $node_page->getSourceDocumentUrl() . '">', '</a>' );
      $text = str_ireplace( $srclink_search, $srclink_replace, $text );

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

      // implement Greenstone parent format
      $parent_pattern = '/ \[ parent : ([^\]]+) \] /x';
      if ( preg_match_all( $parent_pattern, $text, $parent_matches ) ) {
        $parent_node = $this->node->getParent();

        for ( $i = 0; $i < count( $parent_matches[0] ); $i++ ) {
          $field = $parent_node->getField( $parent_matches[1][$i] );

          if ( ! $field ) {
            $field = '';
          }

          $text = str_replace( $parent_matches[0][$i], $field, $text );
        }
      }
    }

    $link_search  = array( '[link]', '[/link]' );
    $link_replace = array( '[a]', '[/a]' );
    $text = str_replace( $link_search, $link_replace, $text );

    if ( strpos( $text, '[a]' ) === false ) {
      $text = '[a]' . $text . '[/a]';
    }

    // parse for remaining, generic metadata tokens
    $text = $this->expandTokens( $text );

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

  /**
   * Returns a string of the metadata values of a given field, from each of
   * the current Node's ancestor nodes, separated by a given string
   *
   * @param string $fieldname
   * @param string $separator
   * @return string
   */
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

  /**
   * Returns the input string, with bracketed Greenstone format tokens replaced
   * with the appropriate content
   *
   * @param string $text
   * @return string
   */
  //protected function expandTokens( $text, $index = null )
  protected function expandTokens( $text )
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

  /**
   * Returns a string corresponding to the appropriate metadata value(s) for
   * the current Node, based on the given token
   *
   * @param string $token
   * @return string
   */
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

      // date formatting
      if ( $current_field == 'format:Date' ) {
        $date = $this->node->getField( 'Date' );

        if ($date) {
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

          return trim( $date_formatted );
        }
      }
    }

    return false;
  }
}
