<?php

class url extends url_Core
{
  public static function current( $qs = FALSE )
  {
    $url     = parent::current( $qs );
    $default = Router::routed_uri( '_default' );

    if ( $url == $default ) {
      return $qs ? Router::$query_string : '';
    }
    else {
      return $url;
    }
  }
}