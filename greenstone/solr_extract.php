<?php

if ( $argc != 3 ) {
  echo 'Usage: solr_extract.php build_directory index_sub_directory' . "\n";
  exit(1);
}

$build_dir     = $argv[1];
$output_file   = $build_dir . '/solr.xml';

$output_fh = @fopen( $output_file, 'ab' );

if ( ! $output_fh ) {
  throw new Exception( 'Could not open output for appending' );
}

$xml_in_blob = '';

while ( $line = fgets( STDIN ) ) {
  if ( substr( $line, 0, 4 ) == '<Doc' ) {
    if ( $xml_in_blob ) {
      // already accumulated one
      process_xml_doc( $xml_in_blob, $output_fh );
    }
    
    $xml_in_blob = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  }
  
  $xml_in_blob .= $line;
}

process_xml_doc( $xml_in_blob, $output_fh );  // the last document

fwrite( $output_fh, '</add>' );


function process_xml_doc( $blob, $fh )
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
