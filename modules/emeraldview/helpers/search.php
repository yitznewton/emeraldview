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
 * @version 0.2.0-b4
 * @package helpers
 */
/**
 * search_Core provides HTML composition functions for the search controller method
 *
 * @package helpers
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.org/)
 * @license    http://yitznewton.org/emeraldview/index.php?title=License     New BSD License
 */
class search_Core
{
  /**
   * Returns the HTML of a simple search form corresponding to the specified
   * Collection and SearchHandler
   *
   * @param Collection $collection
   * @param SearchHandler $search_handler
   * @return string
   */
  public static function form_simple(
    Collection $collection, SearchHandler $search_handler = null
  )
  {
    $text_attributes = array(
      'type'  => 'text',
      'name'  => 'q',
    );

    if ( $search_handler && $search_handler->getQuery() instanceof Query_Simple ) {
      // this page is the result of a simple search, so fill in the form
      $params = $search_handler->getQuery()->getParams();
      $value  = htmlentities( $params['q'], ENT_COMPAT, 'UTF-8' );
      $text_attributes['value'] = $value;
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
  
  /**
   * Returns the HTML of a fielded search form corresponding to the specified
   * Collection and SearchHandler
   *
   * @param Collection $collection
   * @param SearchHandler $search_handler
   * @return string
   */
  public static function form_fielded(
    Collection $collection, SearchHandler $search_handler = null
  )
  {
    if ( $search_handler && $search_handler->getQuery() instanceof Query_Fielded ) {
      $params = $search_handler->getQuery()->getParams();
      $index_default = isset( $params['i'] ) ? $params['i'] : null;
      $level_default = isset( $params['l'] ) ? $params['l'] : null;
      $text_default  = isset( $params['q'] ) ? $params['q'] : null;
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
      'value' => htmlentities( $text_default, ENT_COMPAT, 'UTF-8' ),
    );
            
    $text_input = myhtml::element( 'input', null, $text_attr );

    $submit_attr = array(
      'type' => 'submit',
      'value' => L10n::_('Search'),
    );

    $submit_input = myhtml::element( 'input', null, $submit_attr );

    if ($level_select) {
      $format = 'Search %1$s at the %2$s level for %3$s';
      $args = array( $index_select, $level_select, $text_input );
    }
    else {
      $format = 'Search %1$s for %2$s';
      $args = array( $index_select, $text_input );
    }

    $form_contents = L10n::vsprintf( $format, $args, true ). $submit_input;

    $form_attributes = array(
      'name'   => 'search',
      'id'     => 'search-form-fielded',
      'class'  => 'search-form',
      'action' => $collection->getUrl() . '/search',
      'method' => 'GET',
    );

    return myhtml::element( 'form', $form_contents, $form_attributes );
  }
  
  /**
   * Returns the HTML of a boolean search form corresponding to the specified
   * Collection and SearchHandler
   *
   * @param Collection $collection
   * @param SearchHandler $search_handler
   * @return string
   */
  public static function form_boolean(
    Collection $collection, SearchHandler $search_handler = null
  )
  {
    if ( $search_handler && $search_handler->getQuery() instanceof Query_Boolean ) {
      $params = $search_handler->getQuery()->getParams();
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
        $attrs['value'] = htmlentities( $params["q$i"], ENT_COMPAT, 'UTF-8' );
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
      $first_line = L10n::vsprintf( 'Search at the %s level for', array( $level_select ) );
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

  /**
   * Returns an HTML <<ul>> element with links for changing between the
   * types of search form
   *
   * @return string
   */
  public static function chooser()
  {
    $simple  = L10n::_('Simple');
    $fielded = L10n::_('Fielded');
    $boolean = L10n::_('Boolean');

    $html = <<<EOF
      <ul id="search-form-chooser">
        <li>
          <a id="search-form-link-simple" href="#">$simple</a>
        </li>
        <li>
          | <a id="search-form-link-fielded" href="#">$fielded</a>
        </li>
        <li>
          | <a id="search-form-link-boolean" href="#">$boolean</a>
        </li>
      </ul>
EOF;

    return $html;
  }

  /**
   * Returns an HTML <select> element corresponding to the available index
   * levels for the specified Collection
   *
   * @param Collection $collection
   * @param array $params The query params submitted by the user
   * @return string
   */
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

  /**
   * Returns a string with information about the current search results
   *
   * @param HitsPage $hits_page
   * @param SearchHandler $search_handler
   * @return string
   */
  public static function result_summary( HitsPage $hits_page, SearchHandler $search_handler )
  {
    if ( $hits_page->hits ) {
      $format = 'Results <strong>%d</strong> - <strong>%d</strong> of '
                . '<strong>%d</strong> for <strong>%s</strong>';

      $args = array(
        $hits_page->firstHit, $hits_page->lastHit,
        $search_handler->getTotalHitCount(),
        $search_handler->getQuery()->getDisplayQuery(),
      );
    }
    else {
      $format = 'No results were found for your search '
                . '<strong>%s</strong>';
      $args = array( $search_handler->getQuery()->getDisplayQuery() );
    }
    
    return L10n::vsprintf( $format, $args );
  }

  /**
   * Returns an HTML <<ul>> element corresponding to the search hits pages in the
   * current search
   *
   * @param HitsPage $hits_page
   * @param Collection $collection
   * @return string
   */
  public static function pager( HitsPage $hits_page, Collection $collection )
  {
    if ( ! $hits_page->links ) {
      return '';
    }

    $pages = '';
    
    if ($hits_page->links->previous) {
      $pages .= myhtml::element(
        'li', html::anchor( $hits_page->links->previous, '<' )
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
    
    if ($pages) {
      return myhtml::element(
        'ul', $pages, array( 'class' => 'hits-pager' )
      );
    }
    else {
      return false;
    }
  }

  /**
   * Returns an HTML <<ul>> element corresponding to the user's search history
   *
   * @param Collection $collection
   * @param array $search_history
   * @return string
   */
  public static function history( Collection $collection, array $search_history )
  {
    $items = '';
    $search_history = array_reverse( $search_history );

    foreach ( $search_history as $params ) {
      $query = Query::factory( $collection, $params );

      if ( ! $query ) {
        continue;
      }

      $url = $collection->getUrl() . '/search?' . http_build_query( $params );
      $display_query = $query->getDisplayQuery();

      $link = myhtml::element( 'a', $display_query, array( 'href' => $url ) );
      $items .= myhtml::element( 'li', $link );
    }

    return myhtml::element( 'ol', $items );
  }
}