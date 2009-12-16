<?php

class myhtml_Core {
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

  public static function language_select( array $languages, $default = null )
  {
    if (count($languages) < 2) {
      return '';
    }

    $options = array();

    foreach ($languages as $l) {
      $options[ $l ] = EmeraldviewConfig::get("languages.$l");
    }

    $select_attr = array(
      'name' => 'language',
      'onchange' => 'return changeLanguage(this);',
    );

    $select_element = myhtml::select_element(
      $options, $select_attr, $default
    );

    $submit_attr = array(
      'id'    => 'language-select-submit',
      'type'  => 'submit',
      'value' => 'Submit',
      // FIXME 'value' => $l10n->_('Submit'),
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
