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

$data_file   = $build_dir . '/solr-' . $index_level . '.xml';
$schema_file = $build_dir . '/solr-' . $index_level . '-schema.xml.txt';

$data_fh   = @fopen( $data_file, 'wb' );

if ( ! $data_fh ) {
  echo 'Could not open output for writing data' . chr(10);
  exit(1);
}

$collection_fields = array();

fwrite( $data_fh, '<add>' . chr(10) );

$xml_in_blob = '';

while ( $line = fgets( STDIN ) ) {
  if ( substr( $line, 0, 4 ) == '<Doc' ) {
    if ( $xml_in_blob ) {
      // already accumulated one
      process_xml_doc( $xml_in_blob, $data_fh, $index_level );
    }
    
    $xml_in_blob = '<?xml version="1.0" encoding="UTF-8"?>' . chr(10);
  }
  
  $xml_in_blob .= $line;
}

process_xml_doc( $xml_in_blob, $data_fh, $index_level );  // the last document

fwrite( $data_fh, '</add>' . chr(10) );
fclose( $data_fh );

$schema_fh = @fopen( $schema_file, 'wb' );

if ( ! $schema_fh ) {
  echo 'Could not open output for writing schema' . chr(10);
  exit(1);
}

fwrite( $schema_fh, get_schema_xml( $collection_fields ) );

fclose( $schema_fh );

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
      record_collection_field( $field[0] );

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

function record_collection_field( $field )
{
  global $collection_fields;

  if ( ! is_string( $field ) ) {
    echo 'Argument must be a string' . chr(10);
    exit(1);
  }

  if ( ! in_array( $field, $collection_fields ) ) {
    $collection_fields[] = $field;
  }
}

function get_schema_xml( array $collection_fields )
{
  $xml = '';

  $xml .= '<fields>' . chr(10);

  $xml .= '<field name="docOID" type="string" indexed="true" stored="true" '
       .  'required="true" />' . chr(10);
  
  $xml .= '<field name="timestamp" type="date" indexed="true" stored="true" '
       .  'default="NOW" multiValued="false"/>' . chr(10);

  $xml .= '<dynamicField name="*"  type="textgen"  multiValued="true" '
       .  'indexed="true"  stored="true"/>' . chr(10);

  foreach ( $collection_fields as $field ) {
    if ( $field == 'docOID' ) continue;
    
    $xml .= '<field name="' . $field . '" type="textgen" indexed="true" '
         .  'stored="true" multiValued="true" />' . chr(10);
  }

  $xml .= '</fields>' . chr(10);

  foreach ( $collection_fields as $field ) {
    if ( $field == 'docOID' || $field == 'TX' ) continue;

    $xml .= '<copyField source="' . $field . '" dest="TX" />' . chr(10);
  }

  $xml .= <<<EOF
<defaultSearchField>TX</defaultSearchField>
<solrQueryParser defaultOperator="AND"/>
<uniqueKey>docOID</uniqueKey>

EOF;

  return $xml;
}
