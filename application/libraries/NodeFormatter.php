<?php

class NodeFormatter
{
  public function format( Node $node )
  {
    $title = '';

    $title = $node->getField( 'dc.Title' )
      or $title = $node->getField( 'Title' );
    
    if (is_array( $title )) {
      $text = $title[0];
    }
    elseif ($title) {
      $text = $title;
    }
    else {
      $text = $node->getId();
    }

    return $text;
  }

  public static function factory( Node $node )
  {
    switch ( get_class( $node ) ) {
      case 'Node_Document':
        if ( $node->getCollection()->getConfig( 'document_tree_format' ) ) {
          return new NodeFormatter_String(
            $node->getCollection()->getConfig( 'document_tree_format' )
          );
        }
        elseif ( $node->getCollection()->getConfig( 'document_tree_format_function' ) ) {
          return new NodeFormatter_Function(
            $node->getCollection()->getConfig( 'document_tree_format_function' )
          );
        }
        else {
          return new NodeFormatter();
        }

      case 'Node_Classifier':
        $id = $node->getId();

        if ($node->getCollection()->getConfig( "classifiers.$id.format" )) {
          return new NodeFormatter_String(
            $node->getCollection()->getConfig( "classifiers.$id.format" )
          );
        }
        elseif ($node->getCollection()->getConfig( "classifiers.$id.format_function" )) {
          return new NodeFormatter_Function(
            $node->getConfig( "classifiers.$id.format_function" )
          );
        }
        else {
          return new NodeFormatter();
        }

      default:
        throw new Exception( 'Unexpected Node subclass' );
    }
  }
}