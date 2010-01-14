<?php

class Infodb_Sqlite extends Infodb
{
  protected $pdo;
  protected $allNodes;
  
  public function getDocumentMetadata( $id )
  {
    // fetch all nodes for this Document
    $query  = 'SELECT key, value FROM data WHERE key=? OR key LIKE ?';
    $stmt = $this->pdo->prepare($query);
    $stmt->execute( array($id, $id . '.%') );
    $result_set = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$result_set) {
      // no such nodes
      return false;
    }
    
    $data = array();
    
    foreach ($result_set as $row) {
      $data[ $row['key'] ] = Infodb_Sqlite::parseFields( $row['value'] );
    }

    return $data;
  }
  
  public function getClassifierMetadata() {}
  public function getCollectionMetadata() {}
  
  protected function __construct( Collection $collection )
  {
    $infodb_file = $collection->getGreenstoneDirectory() . '/index/text/'
                 . $collection->getName() . '.db'
                 ;
                 
    if (!is_readable( $infodb_file )) {
      throw new Exception("Could not open infodb $infodb_file for "
                          . 'collection ' . $collection->getName());
    }
    
    $this->pdo = new PDO('sqlite:' . $infodb_file);

    parent::__construct( $collection );
  }
  
  public function getClassifierIds()
  {
    $q = 'SELECT key FROM data WHERE key LIKE "CL%" AND key NOT LIKE "%.%"';
    $stmt = $this->pdo->query($q);

    $ids = array();
    
    while ($id = $stmt->fetchColumn()) {
      $ids[] = $id;
    }
    
    return $ids;
  }
  
  public function getNode( $key )
  {
    $q = 'SELECT value FROM data WHERE key=?';
    $stmt = $this->pdo->prepare( $q );
    $stmt->execute( array( $key ) );
    $data = $stmt->fetchColumn();
    
    return Infodb_Sqlite::parseFields( $data );
  }
  
  public function getAllNodes()
  {
    if ($this->allNodes) {
      return $this->allNodes;
    }

    $q = 'SELECT key, value FROM data';
    $stmt = $this->pdo->query( $q );
    $data = $stmt->fetchAll( PDO::FETCH_ASSOC );
    
    $all_nodes = array();
    
    foreach ($data as $node) {
      $key = $node['key'];
      
      if (
        // FIXME: what do the other OIDtypes generate for documents?
        substr( $key, 0, 4 ) == 'HASH'
        || substr( $key, 0, 1 ) == 'D'
        || substr( $key, 0, 2 ) == 'CL')
      {
        // this is a document or classifier node, so store it
        $all_nodes[ $key ] = self::parseFields( $node['value'] );
      }
    }
    
    return $all_nodes;
  }
  
  public function getRelatedNodeByDocnum( Node_Document $node, $docnum)
  {
    if (!is_int( $docnum )) {
      throw new Exception( 'Second argument must be an integer' );
    }

    $params = array(
      strlen( $node->getRootId() ),
      $node->getRootId(),
      "%<docnum>$docnum%"
    );

    $query  = 'SELECT key, value FROM data '
              . "WHERE SUBSTR(key, 1, ?)=? "
              . "AND value LIKE ?";

    $stmt = $this->pdo->prepare( $query );
    $stmt->execute( $params );
    
    $results = $stmt->fetchAll( PDO::FETCH_ASSOC );

    if (!$results) {
      return false;
    }

    foreach ($results as $node_data) {
      if (strpos($node_data['value'], "<docnum>$docnum\n")) {
        return Node_Document::factory( $node->getCollection(), $node_data['key'] );
      }
    }

    return false;
  }

  public function getRelatedNodeIdByTitle( Node_Document $node, $title )
  {
    if (!$title) {
      return false;
    }

    $params = array(
      strlen( $node->getRootId() ),
      $node->getRootId(),
      "%<Title>$title%"
    );

    $query  = 'SELECT key, value FROM data '
              . "WHERE SUBSTR(key, 1, ?)=? "
              . "AND value LIKE ?";

    $stmt = $this->pdo->prepare( $query );
    $stmt->execute( $params );

    return $stmt->fetchColumn();
  }

  public static function parseFields( $blob )
  {
    $metadata_pattern = '/ \< ([^\>]+) \> (.*) /x';
    preg_match_all( $metadata_pattern, $blob, $matches );

    $fields = array();
    for ( $i = 0; $i < count($matches[0]); $i++ ) {
      $element_name  = $matches[1][$i];
      $element_value = $matches[2][$i];
      // change multi-level values
      $element_value = str_replace('|', ' &ndash; ', $element_value);

      if (isset( $fields[ $element_name ] )) {
        if (is_array( $fields[ $element_name ] )) {
          $fields[ $element_name ] = array_push(
            $fields[ $element_name ], $element_value
          );
        }
        else {
          $fields[ $element_name ] = array(
            $fields[ $element_name ], $element_value
          );
        }
      }
      else {
        $fields[ $element_name ] = $element_value;
      }
    }
    
    return $fields;
  }
}