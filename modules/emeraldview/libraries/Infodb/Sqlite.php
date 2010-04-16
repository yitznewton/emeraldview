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
 * @version 0.2.0-b3
 * @package libraries
 */
/**
 * Infodb_Sqlite is an interface to the Sqlite metadata store as implemented
 * in Greenstone2
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class Infodb_Sqlite extends Infodb
{
  /**
   * @var PDO
   */
  protected $pdo;
  /**
   * An array of all metadata nodes
   *
   * @var array
   */
  protected $allNodes;

  /**
   * @param Collection $collection
   */
  protected function __construct( Collection $collection )
  {
    $infodb_file = $collection->getGreenstoneDirectory() . '/index/text/'
                 . $collection->getGreenstoneName() . '.db'
                 ;

    if (!is_readable( $infodb_file )) {
      throw new Exception("Could not open infodb $infodb_file for "
                          . 'collection ' . $collection->getGreenstoneName());
    }

    $this->pdo = new PDO('sqlite:' . $infodb_file);

    parent::__construct( $collection );
  }

  /**
   * @param string $id
   * @return array
   */
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
  
  /**
   * @return array
   */
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
  
  /**
   * @param string $id
   * @return array
   */
  public function getNode( $id )
  {
    /**
     * Removed: bad with large collections, and the problem this was made
     * to fix may be addressed by r181:b959f8f55648

    if ( $this->collection->getConfig( 'preload_all_nodes' ) === true ) {
      // this speeds up node-heavy pages like search results and classifiers,
      // but with a memory cost; bad for large collections
      $this->getAllNodes();
      
      if ( isset( $this->allNodes[ $id ] ) ) {
        return $this->allNodes[ $id ];
      }
      else {
        return false;
      }
    }
     *
     */

    $q = 'SELECT value FROM data WHERE key=?';
    $stmt = $this->pdo->prepare( $q );
    $stmt->execute( array( $id ) );
    $data = $stmt->fetchColumn();
    
    return Infodb_Sqlite::parseFields( $data );
  }
  
  /**
   * @return array
   */
  public function getAllNodes()
  {
    if ( isset( $this->allNodes ) ) {
      return $this->allNodes;
    }

    $q = 'SELECT key, value FROM data';
    $stmt = $this->pdo->query( $q );
    $data = $stmt->fetchAll( PDO::FETCH_ASSOC );
    
    $all_nodes = array();
    
    foreach ($data as $node) {
      $key = $node['key'];
      
      if (
        substr( $key, 0, 4 ) == 'HASH'
        || substr( $key, 0, 1 ) == 'D'
        || substr( $key, 0, 1 ) == 'J'
        || substr( $key, 0, 2 ) == 'CL')
      {
        // this is a document or classifier node, so store it
        $all_nodes[ $key ] = self::parseFields( $node['value'] );
      }
    }
    
    return $this->allNodes = $all_nodes;
  }
  
  /**
   * @param Node_Document $node
   * @param String $docnum
   * @return string
   */
  public function getCousinIdByDocnum( Node_Document $node, $docnum)
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
        return $node_data['key'];
      }
    }

    return false;
  }

  /**
   *
   * @param Node_Document $node
   * @param string $title
   * @return string
   */
  public function getCousinIdByTitle( Node_Document $node, $title )
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

  /**
   * Parses the text-blob field format of Greenstone2 into fields
   *
   * @param string $blob
   * @return array
   */
  protected static function parseFields( $blob )
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
          array_push( $fields[ $element_name ], $element_value );
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
