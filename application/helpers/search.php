<?php

class search_Core
{
  public static function form_simple(
    Collection $collection, QueryBuilder $query_builder = null
  ) {
    $text_attributes = array(
      'type'  => 'text',
      'name'  => 'q',
    );

    if ( $query_builder instanceof QueryBuilder_Simple ) {
      // this page is the result of a simple search, so fill in the form
      $text_attributes['value'] = htmlspecialchars(
        $query_builder->getDisplayQuery()
      );
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
  
  public static function form_fielded( Collection $collection )
  {
  }
  
  public static function form_boolean( Collection $collection )
  {
  }
  
  public static function result_summary( HitsPager $pager, $display_query )
  {
    $summary = sprintf(
      L10n::_( 'Results <strong>%d</strong> - <strong>%d</strong> of '
               . '<strong>%d</strong> for <strong>%s</strong>'
             ),
      $pager->getStartHit(), $pager->getEndHit(),
      $pager->getTotalHits(), $display_query
    );
    
    return $summary;
  }
  
  public static function snippet( $hit, QueryBuilder $query_builder, $limit = 100 )
  {
    if (!$hit->text) {
      return false;
    }

    $terms = $query_builder->getRawTerms();
    
    $text = $hit->text;
    $text = preg_replace('/\s{2,}/u', ' ', $text);
    
    $first_hit_position = strlen( $text ) - 1;
    
    foreach ($terms as $term) {
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
  
  public static function highlight( $text, QueryBuilder $query_builder )
  {
    foreach ($query_builder->getRawTerms() as $term) {
      $text = preg_replace( "/$term.*?\b/iu", "<strong>\\0</strong>", $text );
    }
    
    return $text;
  }
  
  public static function pager( HitsPager $hits_pager )
  {
    $base_url = $hits_pager->getController()->getCollection()->getUrl()
              . 'search' . Router::$query_string;
    
    $base_url = preg_replace('/&p=\d+/', '', $base_url);
    
    $pages = '';
    
    if ($first = $hits_pager->getLinkPages()->first) {
      $pages .= myhtml::element(
        'li', html::anchor( $base_url . '&p=' . $first, '<<' )
      );
    }
    
    if ($prev = $hits_pager->getLinkPages()->prev) {
      $pages .= myhtml::element(
        'li', html::anchor( $base_url . '&p=' . $prev, '<' )
      );
      
      if ($hits_pager->getLinkPages()->first_number != 1) {
        $pages .= myhtml::element( 'li', '...' );
      }
    }
    
    if ($next = $hits_pager->getLinkPages()->next) {
      if ($last_number != $hits_pager->getLinkPages()->last) {
        $pages .= myhtml::element( 'li', '...' );
      }
      
      $pages .= myhtml::element(
        'li', html::anchor( $base_url . '&p=' . $next, '>' )
      );
    }
    
    if ($last = $hits_pager->getLinkPages()->last) {
      $pages .= myhtml::element(
        'li', html::anchor( $base_url . '&p=' . $last, '>>' )
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