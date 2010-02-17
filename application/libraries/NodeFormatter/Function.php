<?php

class NodeFormatter_Function extends NodeFormatter
{
  protected $function;

  public function __construct( Node $node, $context, $function_definition )
  {
    parent::__construct( $node, $context );

    $this->function = create_function( '$node', $function_definition );
  }

  public function format()
  {
    $text = $this->function( $this->node );

    if ( ! is_string( $text ) ) {
      return false;
    }

    return $text;
  }
}
