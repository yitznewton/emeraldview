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
 * @package helpers
 */
/**
 * myhtml_Core provides several functions for HTML generation
 *
 * @package helpers
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class myhtml_Core {
  /**
   * Returns specified HTML enclosed by HTML tags as specified by $tag, with
   * specified aattributes
   *
   * @param string $tag
   * @param string $contents The HTML to be contained within this element
   * @param array $attributes An associative array of attribute keys and values
   * @return string
   */
  public static function element(
    $tag, $contents, array $attributes = array()
  )
  {
    $attribute_string = '';

    foreach ($attributes as $key => $value) {
      if ($value !== null) {
        $attribute_string .= ' ' . $key . '="' . $value . '"';
      }
    }

    $element  = '<' . $tag . $attribute_string;

    if ($contents === null) {
      $element .= " />\n";
    }
    else {
      $element .= ">\n" . $contents . "</$tag>\n";
    }

    return $element;
  }

  /**
   * Returns an HTML <select> element including <option> elements corresponding
   * to specified options, and with specified attributes
   *
   * @param array $options An associative array containing keys and values of the options
   * @param array $attributes
   * @param string $default The key of the option to set as selected
   * @return string
   */
  public static function select_element(
    array $options, array $attributes = array(), $default = null
  )
  {
    $option_string = '';

    foreach ( $options as $key => $name ) {
      $option_attributes = array(
        'value' => $key,
      );

      if ( $key === $default ) {
        $option_attributes['selected'] = 'selected';
      }

      $option_string .= myhtml::element('option', $name, $option_attributes);
    }

    return myhtml::element('select', $option_string, $attributes);
  }

  /**
   * Returns an HTML <select> element corresponding to given interface languages
   *
   * @param array $languages An associative array of language keys and values
   * @param string $default The key of the language to set as selected
   * @return string
   */
  public static function language_select( array $languages, $default = null )
  {
    if ( count( $languages ) < 2 ) {
      return '';
    }

    $options = array();

    foreach ($languages as $l) {
      $language_name = EmeraldviewConfig::get("languages.$l");

      if ( $language_name ) {
        $options[ $l ] = L10n::_( $language_name );
      }
    }

    $select_attr = array(
      'name' => 'language',
      'id'   => 'language-select-select',
    );

    $select_element = myhtml::select_element(
      $options, $select_attr, $default
    );

    $submit_attr = array(
      'id'    => 'language-select-submit',
      'type'  => 'submit',
      'value' => L10n::_('Submit'),
    );

    $submit_element = myhtml::element('input', null, $submit_attr);

    $form_attr = array(
      'id'     => 'language-select-form',
      'action' => '',
    );

    $form_element = myhtml::element(
      'form', $select_element . $submit_element, $form_attr
    );

    $div_element = myhtml::element(
       'div', $form_element, array('id' => 'language-select')
    );

    return $div_element;
  }
}
