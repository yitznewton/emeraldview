<?php

if ( $argc != 3 ) {
  echo 'Usage: raw_text_extract.php build_directory index_sub_directory' . "\n";
  exit(1);
}

$build_dir        = $argv[1];
$index_sub_dir    = $argv[2];
$raw_text_dir     = $build_dir . '/raw-text';
$raw_text_sub_dir = $build_dir . '/raw-text/' . $index_sub_dir;

if ( ! is_dir( $raw_text_dir ) ) {
  $mkdir = @mkdir( $raw_text_dir );

  if ( ! $mkdir ) {
    throw new Exception( 'Could not create raw text directory' );
  }
}

if ( ! is_dir( $raw_text_sub_dir ) ) {
  $mkdir = @mkdir( $raw_text_sub_dir );

  if ( ! $mkdir ) {
    throw new Exception( 'Could not create raw text subdirectory' );
  }
}

$xml_blob = '';

while ( $line = fgets( STDIN ) ) {
  if ( substr( $line, 0, 4 ) == '<Doc' ) {
    if ( $xml_blob ) {
      // already accumulated one
      process_xml_blob( $xml_blob, $raw_text_sub_dir );
    }

    $xml_blob = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  }

  $xml_blob .= $line;
}

process_xml_blob( $xml_blob, $raw_text_sub_dir );  // the last document


function process_xml_blob( $blob, $directory )
{
  $first_chunk = substr( $blob, 0, 400 );

  if (
    strpos( $first_chunk, 'gs2:mode="add"' ) === false
    && strpos( $first_chunk, 'gs2:mode="update"' ) === false
  ) {
    // looks like a delete
    return false;
  }

  $doc_dom = DOMDocument::loadXML( $blob );
  $xpath = new DOMXPath( $doc_dom );
  $sections = $xpath->query( '/Doc/Sec[@gs2:mode!="delete"]' );

  foreach ( $sections as $section ) {
    process_section( $section, $directory );
  }
}

function process_section( DOMNode $dom_node, $directory )
{
  foreach ( $dom_node->childNodes as $child ) {
    if ( $child->nodeName == 'TX' ) {
      $sec_id = $dom_node->attributes
                ->getNamedItemNS( 'http://www.greenstone.org/gs2', 'docOID' )
                ->value;

      $text = $child->textContent;

      $fh = fopen( $directory . '/' . $sec_id . '.txt', 'wb' );
      fwrite( $fh, $text );

      return;
    }
  }
}
