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
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * load sfYaml library
 */
require_once( Kohana::find_file('vendors', 'sfYaml/sfYaml') );
/**
 * EmeraldviewConfig is a static wrapper class for sfYaml
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class EmeraldviewConfig
{
  /**
   * An array of all configuration data
   *
   * @var array
   */
  static protected $arrayFromYaml;
  
  /**
   * Return a node from EmeraldView's configuration settings
   *
   * @param string $param_name
   * @return mixed
   */
  public static function get( $param_name, $default = null )
  {
    if (empty( self::$arrayFromYaml )) {
      if (file_exists(LOCALPATH . 'config/emeraldview.yml')) {
        $yaml_filename = LOCALPATH . 'config/emeraldview.yml';
      }
      else {
        $yaml_filename = APPPATH . 'config/emeraldview.yml';
      }
      
      if (!is_readable($yaml_filename)) {
        $msg = "Could not find EmeraldView config file ($yaml_filename)";
        throw new Exception( $msg );
      }
      
      self::$arrayFromYaml = sfYaml::load( $yaml_filename );
    }

    $value = Kohana::key_string( self::$arrayFromYaml, $param_name );

    return $value !== null ? $value : $default;
  }
}
