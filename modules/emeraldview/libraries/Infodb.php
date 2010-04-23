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
 * @package libraries
 */
/**
 * Infodb is an interface to a Collection's metadata store
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
abstract class Infodb
{
  const TYPE_SQLITE = 0;

  /**
   * The parent Collection
   *
   * @var Collection
   */
  protected $collection;
  
  /**
   * @param Collection $collection
   */
  protected function __construct( Collection $collection )
  {
    $this->collection = $collection;
  }

  /**
   * Returns an array of metadata fields for the given node id
   *
   * @param string $id
   * @return array
   */
  abstract public function getNode( $id );
  /**
   * Returns an array of metadata fields for the given Greenstone docOID and
   * all child nodes
   *
   * @param string $id
   * @return array
   */
  abstract public function getDocumentMetadata( $id );
  /**
   * Returns an array of ids for all classifiers in the Collection, regardless
   * of whether they are enabled in EmeraldView
   *
   * @return array
   */
  abstract public function getClassifierIds();
  /**
   * Returns an array of metadata for all nodes
   *
   * @return array
   */
  abstract public function getAllNodes();
  /**
   * Returns id of related node given the docnum field of the desired node
   *
   * @param Node_Document $node
   * @param string $docnum
   * @return string
   */
  abstract public function getCousinIdByDocnum( Node_Document $node, $docnum );
  /**
   * Returns id of related node given the title field of the desired node
   *
   * @param Node_Document $node
   * @param string $title
   * @return string
   */
  abstract public function getCousinIdByTitle( Node_Document $node, $title );

  /**
   * @param Collection $collection
   * @return Infodb_Sqlite 
   */
  public static function factory( Collection $collection )
  {
    $type = $collection->getCollectCfg()->getInfodbtype();
    
    if ($type == Infodb::TYPE_SQLITE) {
      return new Infodb_Sqlite( $collection );
    }
    
    throw new Exception( 'Unsupported infodbtype for collection '
                         . $collection->getGreenstoneName() );
  }
}
