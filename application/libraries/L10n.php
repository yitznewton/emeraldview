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
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0-b2
 * @package libraries
 */
/**
 * load PHP-gettext reader library
 */
require( Kohana::find_file('vendors', 'php-gettext/Gettext') );
require( Kohana::find_file('vendors', 'php-gettext/Streams') );
/**
 * L10n is a static container class for a single main Gettext instance and
 * optional child Gettext instances for alternate gettext domains
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class L10n
{
  /**
   * Holds the core Gettext
   * @var L10n
   */
  protected static $gettext;
  /**
   * An array of additional Gettext instances
   * @var array
   */
  protected static $alternateDomainGettexts = array();
  /**
   * The display language
   * @var string
   */
  protected static $language;

  /**
   * Returns the translation of the specified string using the default Gettext
   *
   * @param string $message
   * @return string 
   */
  public static function _( $message )
  {
    if (!L10n::$gettext) {
      throw new Exception('Gettext file not yet loaded');
    }
    
    return L10n::$gettext->_( $message );
  }

  /**
   * Translates a format, and applies it to arguments using vsprintf
   *
   * @param string $format
   * @param array $args
   * @param boolean $translate_args
   * @return string
   */
  public static function vsprintf( $format, array $args, $translate_args = false )
  {
    $format = L10n::_( $format );

    if ( $translate_args ) {
      array_walk( $args, array( 'L10n', '_' ) );
    }

    return vsprintf( $format, $args );
  }
  
  /**
   * Loads the default Gettext
   *
   * @param string $mofile
   * @return L10n
   */
  public static function load( $mofile = null )
  {
    if ($mofile) {
      // this will overlay an existing reader
      $reader = new CachedFileReader( $mofile );
      L10n::$gettext = new Gettext( $reader );
      return true;
    }
    elseif (L10n::$gettext) {
      return true;
    }
    else {
      $msg = 'Trying to load gettext instance without specifying .mo file';
      throw new Exception( $msg );
    }
  }

  /**
   * Returns the default language
   *
   * @return string
   */
  public static function getLanguage()
  {
    return L10n::$language;
  }

  /**
   * Sets the default language
   *
   * @param string $language
   * @return boolean
   */
  public static function setLanguage( $language )
  {
    if (
      !is_string($language)
      || strlen($language) < 2
      || strlen($language) > 7
    ) {
      throw new InvalidArgumentException('Invalid language code');
    }

    return L10n::$language = $language;
  }

  /**
   * Load an additional Gettext and assign an arbitrary pseudo-domain
   *
   * @param string $domain_name
   * @param string $mo_file
   * @return boolean
   */
  public static function loadDomain( $domain_name, $mo_file )
  {
    return L10n::$alternateDomainGettexts[ $domain_name ]
           = new Gettext( new CachedFileReader( $mo_file ) );
  }

  /**
   * A virtual implementation of gettext's dcgettext(): returns translated
   * string from the specified pseudo-domain
   *
   * @param string $domain_name
   * @param string $message
   * @return string
   */
  public static function dcgettext( $domain_name, $message )
  {
    if ( ! isset( L10n::$alternateDomainGettexts[ $domain_name ] ) ) {
      throw new InvalidArgumentException( 'Domain not loaded' );
    }

    return L10n::$alternateDomainGettexts[ $domain_name ]->_( $message );
  }
}
