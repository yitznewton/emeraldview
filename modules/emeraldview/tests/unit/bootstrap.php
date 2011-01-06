<?php
define('MODPATH', dirname(__FILE__).'/../../../');
define('APPPATH', dirname(__FILE__).'/../application/');
define('SYSPATH', '');

set_include_path( get_include_path().PATH_SEPARATOR
                  .MODPATH.'emeraldview/libraries'
                  );

require_once MODPATH.'emeraldview/core/Kohana.php';

function load( $class )
{
  $class = str_replace( '_', '/', $class );
  require $class . '.php';
}

spl_autoload_register( 'load' );

