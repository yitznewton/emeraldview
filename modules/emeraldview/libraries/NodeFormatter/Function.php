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
 * @version 0.2.0
 * @package libraries
 */
/**
 * NodeFormatter_String formulates a string representation of a Node's metadata,
 * based on a given function
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class NodeFormatter_Function extends NodeFormatter
{
  /**
   * The name of the lambda-style function created by the current NodeFormatter
   *
   * @var string
   */
  protected $function_name;

  /**
   * @param Node $node
   * @param NodeFormatterContext An object representing the situation where the string is needed
   * @param string $function_definition 
   */
  public function __construct( Node $node, NodeFormatterContext $context,
    $function_definition )
  {
    parent::__construct( $node, $context );

    $this->function_name = create_function( '$node', $function_definition );
  }

  /**
   * @return string
   */
  public function format()
  {
    $function_name = $this->function_name;
    $text = $function_name( $this->node );

    if ( ! is_string( $text ) ) {
      return false;
    }

    return $text;
  }

  /**
   * @param Node $node
   * @param NodeFormatterContext $context An object representing the situation where the string is needed; used for determining which format specification to use
   * @return void
   */
  public static function factory( Node $node, $context )
  {
    $msg = 'Can only call NodeFormatter::factory() from abstract parent class';
    throw new Exception( $msg );
  }
}
