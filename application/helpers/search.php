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
      $index_default = null;
      $level_default = null;
      $text_default = null;
    }

    $index_select = myhtml::select_element(
      $collection->getIndexes(), array('name' => 'i'), $index_default
    );

    if ( ! $level_default || ! in_array( $level_default, $collection->getIndexLevels() )) {
      $level_default = $collection->getDefaultIndexLevel();
    }

    if ( count( $collection->getIndexLevels() > 1 ) ) {
      // FIXME if paragraph is included, Lucene doesn't support
      $level_options = array();

      foreach ( $collection->getIndexLevels() as $level ) {
        $level_options[ $level ] = L10n::_( $level );
      }

      $level_select = myhtml::select_element(
        $level_options, array('name' => 'l'), $level_default
      );
    }
    else {
      $level_select = null;
    }
    
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
  
  public static function form_boolean( Collection $collection )
  {
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
  
  public static function snippet( $hit, QueryBuilder $query_builder, $limit = 100 )
  {
    if (!$hit->text) {
      return false;
    }

    $text = $hit->text;
    $text = preg_replace('/\s{2,}/u', ' ', $text);
    
    $first_hit_position = strlen( $text ) - 1;
    
    foreach ($query_builder->getRawTerms() as $term) {
      // remove special search characters
      $term = str_replace( array('*', '?'), '', $term );
      
      if (
        stripos( $text, $term ) !== false
        && stripos( $text, $term ) < $first_hit_position
      ) {
        $first_hit_position = strpos( $text, $term );
      }
    }
    
    $first_hit_reverse_position = 0 - strlen($text) + $first_hit_position;
    $prev_sentence_end = strripos( $text, '. ', $first_hit_reverse_position );
    
    // ignore earlier sentences
    $sentence_start = $prev_sentence_end ? $prev_sentence_end + 2 : 0;
    $first_hit_position -= $sentence_start;
    $text = substr( $text, $sentence_start );
    
    // TODO: de-hardcode the truncation limit?
    if ($first_hit_position > 150) {
      // only start a bit before first hit
      $snippet_start = strpos( $text, ' ', $first_hit_position - 50 );
    }
    else {
      // we have room; start from beginning of sentence
      $snippet_start = 0;
    }
    
    $snippet = substr( $text, $snippet_start );
    
    // TODO: de-hardcode the truncation limit?
    preg_match('/^ .{0,200} .*? \b /iux', $snippet, $matches);
    
    if ($matches[0] != $snippet) {
      // we needed to truncate at the end
      $snippet = $matches[0] . ' ...';
    }
    
    if ($snippet_start > 0) {
      // we truncated from the beginning
      $snippet = '... ' . $snippet;
    }
    
    return search::highlight( $snippet, $query_builder );
  }
  
  public static function highlight( $text, SearchHandler $search_handler )
  {
    foreach ($search_handler->getQueryBuilder()->getRawTerms() as $term) {
      $text = preg_replace( "/$term.*?\b/iu", "<strong>\\0</strong>", $text );
    }
    
    return $text;
  }
  
  public static function pager( HitsPage $hits_page, Collection $collection )
  {
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

      // FIXME implement this
      //if ($hits_page->getLinkPages()->first_number != 1) {
      //  $pages .= myhtml::element( 'li', '...' );
      //}
    }
    else {
      $pages .= myhtml::element(
        'li', '<'
      );
    }

    // TODO add support for truncation of page links
    foreach ($hits_page->links->pages as $page_number => $page_link) {
      if ($page_link) {
        $pages .= myhtml::element(
          'li', html::anchor( $page_link, $page_number )
        );
      }
      else {
        $pages .= myhtml::element(
          'li', $page_number
        );
      }
    }
    
    if ($hits_page->links->next) {
      // FIXME implement this
      //if ($last_number != $hits_page->getLinkPages()->last) {
      //  $pages .= myhtml::element( 'li', '...' );
      //}
      
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