<?php

class NodeTreePager
{
  public static function html( Node_Document $node )
  {
    $output = '';
    
    $prev_node = $node->getPreviousNode();
    $next_node = $node->getNextNode();

    if ($prev_node) {
      $prev_url = DocumentSection::factory( $prev_node )->getUrl();
      $output .= myhtml::element(
        'a', L10n::_('Previous page'), array('href' => $prev_url)
      );
    }
    else {
      $output .= myhtml::element(
        'span', L10n::_('Previous page'), array('class' => 'inactive')
      );
    }

    if ($next_node) {
      $next_url = DocumentSection::factory( $next_node )->getUrl();
      $output .= myhtml::element(
        'a', L10n::_('Next page'), array('href' => $next_url)
      );
    }
    else {
      $output .= myhtml::element(
        'span', L10n::_('Next page'), array('class' => 'inactive')
      );
    }

    return $output;
  }
}