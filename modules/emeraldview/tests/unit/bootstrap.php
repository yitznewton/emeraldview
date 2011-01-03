<?php
define('MODPATH', '/www/websites/emeraldview/modules/');
define('APPPATH', dirname(__FILE__).'/../application/');
define('SYSPATH', '');

set_include_path( get_include_path().PATH_SEPARATOR
                  .'/www/websites/emeraldview/modules/emeraldview/libraries'
                  );

require_once MODPATH.'emeraldview/core/Kohana.php';

function load( $class )
{
  $class = str_replace( '_', '/', $class );
  require $class . '.php';
}

spl_autoload_register( 'load' );

