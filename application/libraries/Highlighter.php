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
 * @version 0.2.0-b1
 * @package libraries
 */
/**
 * Highlighter applies a given treatment to a supplied document based on
 * supplied search terms
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
abstract class Highlighter
{
  /**
   * An array of search terms
   *
   * @var array
   */
  protected $terms;
  
  /**
   * Set the search terms that the Highlighter will use
   *
   * @param array $raw_terms 
   */
  public function setTerms( $raw_terms )
  {
    if ( ! is_array( $raw_terms ) ) {
      $raw_terms = array( $raw_terms );
    }

    $this->terms = array();

    foreach ( $raw_terms as $term ) {
      if ( ! is_string( $term ) ) {
        $msg = 'Argument must be a string or array of strings';
        throw new InvalidArgumentException( $msg );
      }
      // replace wildcards with their regex equivalents
      $term = preg_quote( $term );
      $term = str_replace( array('\\*', '\\?'), array('.*?', '.'), $term );

      $this->terms[] = $term;
    }
  }
  
  /**
   * Executes highlighting and returns the output string
   *
   * @return string;
   */
  abstract public function execute();
  /**
   * Returns the input document
   *
   * @return string
   */
  abstract public function getDocument();
  /**
   * Sets the input document
   *
   * @param mixed $document
   */
  abstract public function setDocument( $document );
}
