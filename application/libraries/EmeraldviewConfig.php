<?php
require_once( Kohana::find_file('vendors', 'sfYaml/sfYaml') );

class EmeraldviewConfig
{
  static protected $arrayFromYaml;
  
  public static function get( $param_name )
  {
    if (empty( self::$arrayFromYaml )) {
      $yaml_filename = APPPATH . 'config/emeraldview.yml';
      
      if (!is_readable($yaml_filename)) {
        $msg = "Could not find EmeraldView config file ($yaml_filename)";
        throw new Exception( $msg );
      }
      
      self::$arrayFromYaml = sfYaml::load( $yaml_filename );
    }
    
    return Kohana::key_string( self::$arrayFromYaml, $param_name );
  }
}