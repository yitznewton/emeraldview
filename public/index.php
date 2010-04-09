<?php
define('IN_PRODUCTION', FALSE);

$kohana_local       = 'local';
$kohana_application = 'application';
$kohana_modules     = 'modules';
$kohana_system      = 'system';
$kohana_public      = 'public';

/**
 * Test to make sure that Kohana is running on PHP 5.2 or newer. Once you are
 * sure that your environment is compatible with Kohana, you can comment this
 * line out. When running an application on a new server, uncomment this line
 * to check the PHP version quickly.
 */
version_compare(PHP_VERSION, '5.2', '<') and exit('Kohana requires PHP 5.2 or newer.');

/**
 * Set the error reporting level. Unless you have a special need, E_ALL is a
 * good level for error reporting.
 */
error_reporting(E_ALL & ~E_STRICT);

/**
 * Turning off display_errors will effectively disable Kohana error display
 * and logging. You can turn off Kohana errors in application/config/config.php
 */
ini_set('display_errors', TRUE);

/**
 * If you rename all of your .php files to a different extension, set the new
 * extension here. This option can left to .php, even if this file has a
 * different extension.
 */
define('EXT', '.php');

//
// DO NOT EDIT BELOW THIS LINE, UNLESS YOU FULLY UNDERSTAND THE IMPLICATIONS.
// ----------------------------------------------------------------------------
// $Id: index.php 3915 2009-01-20 20:52:20Z zombor $
//

$kohana_pathinfo = pathinfo(__FILE__);
// Define the front controller name and docroot
define('DOCROOT', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
define('KOHANA',  $kohana_pathinfo['basename']);

// If the front controller is a symlink, change to the real docroot
is_link(KOHANA) and chdir(dirname(realpath(__FILE__)));

// If kohana folders are relative paths, make them absolute.
$kohana_local = file_exists($kohana_local) ? $kohana_local : DOCROOT.$kohana_local;
$kohana_application = file_exists($kohana_application) ? $kohana_application : DOCROOT.$kohana_application;
$kohana_modules = file_exists($kohana_modules) ? $kohana_modules : DOCROOT.$kohana_modules;
$kohana_system = file_exists($kohana_system) ? $kohana_system : DOCROOT.$kohana_system;
$kohana_public = file_exists($kohana_public) ? $kohana_public : DOCROOT.$kohana_public;

// Define application and system paths
define('LOCALPATH', $kohana_local . '/');
define('MODPATH', $kohana_modules .'/');
define('APPPATH', str_replace('\\', '/', realpath($kohana_application)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($kohana_system)).'/');
define('PUBLICPATH', str_replace('\\', '/', realpath($kohana_public)).'/');

$vendor_dir_name = MODPATH . 'emeraldview/vendors';

if ( is_dir( $vendor_dir_name ) ) {
  set_include_path( get_include_path() . PATH_SEPARATOR . $vendor_dir_name );
}

// Clean up
unset($kohana_application, $kohana_modules, $kohana_system);

if ( ! file_exists( LOCALPATH . 'config/kohana.php' ) ) {
	// Load the installation tests
	require DOCROOT.'install'.EXT;
}
else {
	// Initialize Kohana
  require APPPATH.'core/Bootstrap'.EXT;
}
