<?php
require('Streams.php');

/**
 * A quasi-Singleton container class for a single main Gettext instance and
 * optional child Gettext instances for alternate gettext domains
 *
 * @package libraries
 * @author yitzchas
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

  public static function _( $message )
  {
    if (!L10n::$gettext) {
      throw new Exception('Gettext file not yet loaded');
    }
    
    return L10n::$gettext->_( $message );
  }
  
  /**
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
   * @return void
   */
  public static function getLanguage()
  {
    return L10n::$language;
  }

  /**
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
   * @param string $domain_name The arbitrary assigned domain name
   * @param string $mo_file
   * @return boolean
   */
  public static function loadDomain( $domain_name, $mo_file )
  {
    return L10n::$alternateDomainGettexts[ $domain_name ]
           = new Gettext( new CachedFileReader( $mo_file ) );
  }

  /**
   * A virtual implementation of gettext's dcgettext()
   * @param string $domain_name The desired pseudo-domain
   * @param string $message
   * @return string Translated string from the specified pseudo-domain
   */
  public static function dcgettext( $domain_name, $message )
  {
    if (!isset( L10n::$alternateDomainGettexts[ $domain_name ] )) {
      // domain not loaded
      return false;
    }

    return L10n::$alternateDomainGettexts[ $domain_name ]->_( $message );
  }
}