<?php

class myview_Core
{
  public static function metadata_list( array $metadata )
  {
    $items_html = '';

    foreach ( $metadata as $display_name => $data) {
      $items_html .= myhtml::element( 'dt', L10n::_($display_name) );

      $values = '';

      foreach ($data as $value) {
        $values .= myhtml::element( 'dd', $value );
      }
      $items_html .= $values;
    }

    return myhtml::element( 'dl', $items_html );
  }
}