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
 * @version 0.2.0-b4
 * @package greenstone
 *
 * This script processes metadata from the Greenstone build process for
 * ingest into Solr
 */
if ( $argc != 3 ) {
  echo 'Usage: solr_extract.php build_directory index_sub_directory' . chr(10);
  exit(1);
}

$index_level = $argv[2];

$build_dir   = $argv[1];
$output_file = $build_dir . '/solr-' . $index_level . '.xml';
$output_fh   = @fopen( $output_file, 'wb' );

if ( ! $output_fh ) {
  throw new Exception( 'Could not open output for appending' );
}

fwrite( $output_fh, '<add>' . chr(10) );

$xml_in_blob = '';

while ( $line = fgets( STDIN ) ) {
  if ( substr( $line, 0, 4 ) == '<Doc' ) {
    if ( $xml_in_blob ) {
      // already accumulated one
      process_xml_doc( $xml_in_blob, $output_fh, $index_level );
    }
    
    $xml_in_blob = '<?xml version="1.0" encoding="UTF-8"?>' . chr(10);
  }
  
  $xml_in_blob .= $line;
}

process_xml_doc( $xml_in_blob, $output_fh, $index_level );  // the last document

fwrite( $output_fh, '</add>' . chr(10) );


function process_xml_doc( $blob, $fh, $index_level )
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
  
  if ( $index_level == 'didx' ) {
    $querystring = '/Doc[@gs2:mode!="delete"]';
  }
  elseif ( $index_level == 'sidx' ) {
    $querystring = '/Doc/Sec[@gs2:mode!="delete"]';
  }
  else {
    echo 'Unrecognized index level' . chr(10);
    exit(1);
  }
  
  $sections = $xpath->query( $querystring );
  
  foreach ( $sections as $section ) {
    fwrite( $fh, '<doc>' . chr(10) );
  
    $fields = extract_section_fields( $section );
    
    foreach ( $fields as $field ) {
      $field_xml = '<field name="' . $field[0] . '">'
                   . $field[1] . '</field>' . chr(10);
      
      fwrite( $fh, $field_xml );
    }
  
    fwrite( $fh, '</doc>' . chr(10) );
  }
}

function extract_section_fields( DOMNode $dom_node )
{
  $fields = array();

  $docOID = $dom_node->attributes
            ->getNamedItemNS( 'http://www.greenstone.org/gs2', 'docOID' )
            ->value;
  
  $fields[] = array( 'docOID', $docOID );
                        
  foreach ( $dom_node->childNodes as $child ) {
    if ( $child instanceof DOMElement ) {
      $fields[] = array( $child->tagName, $child->textContent );
    }
  }
  
  return $fields;
}
