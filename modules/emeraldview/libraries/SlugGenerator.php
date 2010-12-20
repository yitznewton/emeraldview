<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.org/emeraldview/index.php?title=License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to yitznewton@hotmail.com so we can send you a copy immediately.
 *
 * @version 0.2.0
 * @package libraries
 */
/**
 * SlugGenerator transforms strings into URL slugs based on Collection config
 * settings
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class SlugGenerator
{
  /**
   * The Collection to use for configuration
   *
   * @var Collection
   */
  protected $collection;

  /**
   * @param Collection $collection The Collection to use for configuration
   */
  public function __construct( Collection $collection )
  {
    $this->collection = $collection;
  }

  /**
   * Returns a slug-transformed version of the input string
   *
   * @param string $string
   * @return string
   */
  public function toSlug( $string )
  {
    $max_length = $this->collection->getConfig( 'slug_max_length' );
    $spacer     = $this->collection->getConfig( 'slug_spacer' );

    if ( ! $max_length || ! is_int( $max_length ) ) {
      $max_length = 30;
    }

    if ( ! $spacer || ! is_string( $spacer ) ) {
      $spacer = '-';
    }

    if (function_exists('iconv')) {
      $string = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    }

    $slug = strtolower( $string );
    $slug = preg_replace( '/[^a-z0-9-]/', $spacer, $slug );
    $slug = trim( $slug, $spacer );
    $slug = preg_replace( "/$spacer+/", $spacer, $slug );
    $slug = $this->stripStopwords( $slug );

    if ( $max_length && is_int( $max_length ) ) {
      if (
        strlen( $slug ) > $max_length
        && substr( $slug, $max_length, 1 ) != '-'
      ) {
        // chopped in middle of word
        preg_match( "/^ .{0,$max_length} (?=-) /x", $slug, $matches );
        $slug = $matches[0];
      }
      else {
        $slug = substr( $slug, 0, $max_length );
      }
    }

    return $slug;
  }

  /**
   * Returns the input string with stopwords removed, based on Collection
   * config settings
   * 
   * @param string $string
   * @return string
   */
  protected function stripStopwords( $string )
  {
    $stopwords = $this->collection->getConfig( 'slug_stopwords' );

    if ( is_string( $stopwords ) ) {
      $stopwords = array( $stopwords );
    }

    if ( ! $stopwords || ! is_array( $stopwords ) ) {
      $stopwords = array(
        'an',
        'a',
        'the',
        'of',
        'and',
      );
    }

    $pattern = '/\b(' . implode( '|', $stopwords ) . ')-?\b/';

    return preg_replace( $pattern, '', $string );
  }
}
