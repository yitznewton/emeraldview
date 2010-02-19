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
 * NodeFormatter formulates a string representation of a Node's metadata,
 * based on a given specification
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class NodeFormatter
{
  /**
   * The Node
   *
   * @var Node
   */
  protected $node;

  /**
   * @param Node $node
   */
  protected function __construct( Node $node )
  {
    $this->node = $node;
  }

  /**
   * Returns the string representation of the Node
   *
   * @return string
   */
  public function format()
  {
    $field_names = array( 'dc.Title', 'Title' );
    $title = $this->node->getFirstFieldFound( $field_names );
    
    if (is_array( $title )) {
      $text = $title[0];
    }
    elseif ($title) {
      $text = $title;
    }
    else {
      $text = $this->node->getId();
    }

    // FIXME: is this the best way to deal with links?  see also child classes
    return '[a]' . $text . '[/a]';
  }

  /**
   * @param Node $node
   * @param mixed $context An object representing the situation where the string is needed; used for determining which format specification to use
   * @return NodeFormatter
   */
  public static function factory( Node $node, $context )
  {
    switch ( get_class( $context ) ) {
      case 'NodePage_Classifier':
        $prefix = 'classifiers.' . $context->getId() . '.';
        break;
        
      case 'NodePage_DocumentSection':
        $prefix = 'document_tree_';
        break;
      
      case 'SearchHandler':
        $prefix = 'search_results_';
        break;
      
      default:
        throw new InvalidArgumentException( 'Invalid $caller' );
    }

    $format_string = $context->getCollection()->getConfig( $prefix . 'format' );
    if ( $format_string ) {
      return new NodeFormatter_String( $node, $format_string );
    }

    $function_definition = $context->getCollection()->getConfig( $prefix . 'format_function' );
    if ( $function_definition ) {
      return new NodeFormatter_Function( $node, $function_definition );
    }

    // no supplied formatter configuration
    return new NodeFormatter( $node );
  }
}
