<?php

class search_Core
{
  public static function form_simple(
    Collection $collection, SearchHandler $search_handler = null
  )
  {
    $text_attributes = array(
      'type'  => 'text',
      'name'  => 'q',
    );

    if ( $search_handler && $search_handler->getQueryBuilder() instanceof QueryBuilder_Simple ) {
      // this page is the result of a simple search, so fill in the form
      $params = $search_handler->getParams();
      // FIXME the params should be sanitized earlier, on SearchHandler construct
      $text_attributes['value'] = $params['q'];
    }

    $text_element = myhtml::element('input', null, $text_attributes);

    $submit_attributes = array(
      'type'  => 'submit',
      'value' => L10n::_('Search'),
    );

    $submit_element = myhtml::element('input', null, $submit_attributes);

    $form_attributes = array(
      'name'   => 'search',
      'id'     => 'search-form-simple',
      'class'  => 'search-form',
      'action' => $collection->getUrl() . '/search',
      'method' => 'GET',
    );

    $form_contents = $text_element . $submit_element;

    return myhtml::element('form', $form_contents, $form_attributes);
  }
  
  public static function form_fielded(
    Collection $collection, SearchHandler $search_handler = null
  )
  {
    if ( $search_handler && $search_handler->getQueryBuilder() instanceof QueryBuilder_Fielded ) {
      $params = $search_handler->getParams();
      $index_default = $params['i'];
      $level_default = $params['l'];
      $text_default = $params['q'];
    }
    else {
      $params = null;
      $index_default = null;
      $level_default = null;
      $text_default = null;
    }

    $index_select = myhtml::select_element(
      $collection->getIndexes(), array('name' => 'i'), $index_default
    );

    $level_select = search::level_select( $collection, $params );

    $text_attr = array(
      'type' => 'text',
      'name' => 'q',
      'value' => $text_default,
    );
            
    $text_input = myhtml::element( 'input', null, $text_attr );

    $submit_attr = array(
      'type' => 'submit',
      'value' => L10n::_('Search'),
    );

    $submit_input = myhtml::element( 'input', null, $submit_attr );

    if ($level_select) {
      $form_contents = sprintf(
        'Search %1$s at the %2$s level for %3$s',
        $index_select,
        $level_select,
        $text_input
      ) . $submit_input;
    }
    else {
      $form_contents = sprintf(
        'Search %1$s for %2$s',
        $index_select,
        $text_input
      ) . $submit_input;
    }

    $form_attributes = array(
      'name'   => 'search',
      'id'     => 'search-form-fielded',
      'class'  => 'search-form',
      'action' => $collection->getUrl() . '/search',
      'method' => 'GET',
    );

    return myhtml::element( 'form', $form_contents, $form_attributes );
  }
  
  public static function form_boolean( Collection $collection, SearchHandler $search_handler = null )
  {
    if ( $search_handler && $search_handler->getQueryBuilder() instanceof QueryBuilder_Boolean ) {
      $params = $search_handler->getParams();
    }
    else {
      $params = null;
    }

    $boolean_options = array(
      'AND' => strtoupper( L10n::_( 'and' ) ),
      'OR'  => strtoupper( L10n::_( 'or'  ) ),
      'NOT' => strtoupper( L10n::_( 'not' ) ),
    );

    for ( $i = 1; $i<4; $i++ ) {
      // Create three sets of inputs representing three search terms

      // Boolean selects
      $attrs = array( 'name' => "b$i" );

      if ( isset( $params["b$i"] ) ) {
        $default = $params["b$i"];
      }
      else {
        $default = null;
      }

      $varname = "boolean$i";
      $$varname = myhtml::select_element( $boolean_options, $attrs, $default );

      // query text inputs
      $attrs = array( 'type' => 'text', 'name' => "q$i" );

      if ( isset( $params["q$i"] ) ) {
        $attrs['value'] = $params["q$i"];
      }

      $varname = "text$i";
      $$varname = myhtml::element( 'input', null, $attrs );

      // index selects
      if ( isset( $params["i$i"] ) ) {
        $default = $params["i$i"];
      }
      else {
        $default = null;
      }

      $varname = "index$i";
      $$varname = myhtml::select_element(
        $collection->getIndexes(), array( 'name' => "i$i" ), $default
      );
    }

    $submit_attributes = array(
      'type'  => 'submit',
      'value' => L10n::_('Search'),
    );

    $reset_attributes = array(
      'type'  => 'reset',
      'value' => L10n::_('Reset'),
    );

    $submit = myhtml::element( 'input', null, $submit_attributes );
    $reset  = myhtml::element( 'input', null, $reset_attributes );

    $level_select = search::level_select( $collection, $params );

    if ( $level_select ) {
      $first_line
        = sprintf( L10n::_('Search at the %s level for'), $level_select );
    }
    else {
      $first_line = L10n::_('Search for');
    }

    $form_contents = '';
    $form_contents .= "<div>$first_line:</div>\n";
    $form_contents .= "<div>\n$text1 " . L10n::_('in') ." $index1</div>\n";
    $form_contents .= "<div>\n" . $boolean2 . $text2;
    $form_contents .= L10n::_('in') . " $index2 </div>\n";
    $form_contents .= "<div>\n" . $boolean3 . $text3;
    $form_contents .= L10n::_('in') . " $index3 </div>\n";

    $form_contents .= $submit . $reset;

    $form_attributes = array(
      'name'   => 'search',
      'id'     => 'search-form-boolean',
      'class'  => 'search-form',
      'action' => $collection->getUrl() . '/search',
      'method' => 'GET',
    );

    return myhtml::element( 'form', $form_contents, $form_attributes );
  }

  public static function level_select( Collection $collection, array $params = null )
  {
    if ( count( $collection->getIndexLevels() ) == 1 ) {
      return false;
    }

    if ( isset( $params['l'] ) && in_array( $params['l'], $collection->getIndexLevels() )) {
      $level_default = $params['l'];
    }
    else {
      $level_default = $collection->getDefaultIndexLevel();
    }

    $level_options = array();

    foreach ( $collection->getIndexLevels() as $level ) {
      $level_options[ $level ] = L10n::_( $level );
    }

    return myhtml::select_element( $level_options, array('name' => 'l'), $level_default );
  }
  
  public static function result_summary( HitsPage $hits_page, SearchHandler $search_handler )
  {
    $summary = sprintf(
      L10n::_( 'Results <strong>%d</strong> - <strong>%d</strong> of '
               . '<strong>%d</strong> for <strong>%s</strong>'
             ),
      $hits_page->firstHit, $hits_page->lastHit,
      $hits_page->totalHitCount, $search_handler->getQueryBuilder()->getDisplayQuery()
    );
    
    return $summary;
  }
  
  public static function pager( HitsPage $hits_page, Collection $collection )
  {
    // TODO: implement limiting of search pages, e.g. << < ... 3 4 5 6 7 ... > >>

    if ( ! $hits_page->links ) {
      return '';
    }

    $pages = '';
    
    if ($hits_page->links->first) {
      $pages .= myhtml::element(
        'li', html::anchor( $hits_page->links->first, '<<' )
      );
    }
    else {
      $pages .= myhtml::element(
        'li', '<<'
      );
    }
    
    if ($hits_page->links->previous) {
      $pages .= myhtml::element(
        'li', html::anchor( $hits_page->links->previous, '<' )
      );
    }
    else {
      $pages .= myhtml::element(
        'li', '<'
      );
    }

    foreach ( $hits_page->links->pages as $page_number => $page_link ) {
      if ( $page_link ) {
        $pages .= myhtml::element(
          'li', html::anchor( $page_link, $page_number )
        );
      }
      else {
        // no link; this is current page
        $pages .= myhtml::element(
          'li', $page_number
        );
      }
    }
    
    if ($hits_page->links->next) {
      $pages .= myhtml::element(
        'li', html::anchor( $hits_page->links->next, '>' )
      );
    }
    else {
      $pages .= myhtml::element(
        'li', '>'
      );
    }
    
    if ($hits_page->links->last) {
      $pages .= myhtml::element(
        'li', html::anchor( $hits_page->links->last, '>>' )
      );
    }
    
    if ($pages) {
      return myhtml::element(
        'ul', $pages, array( 'class' => 'hits-pager' )
      );
    }
    else {
      return false;
    }
  }
  
  protected static function snippet_truncate( $text )
  {
    
    return $matches[0] . ' ...';
  }
}