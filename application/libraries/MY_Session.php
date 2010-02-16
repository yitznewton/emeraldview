<?php

class Session extends Session_Core
{
  public function getSearchHistory( Collection $collection )
  {
    $global_history = $this->get( 'search_history' );

    if ( isset( $global_history[ $collection->getName() ] ) ) {
      return $global_history[ $collection->getName() ];
    }
    else {
      return array();
    }
  }

  protected function setSearchHistory( Collection $collection, array $history )
  {
    $global_history = $this->get( 'search_history' );
    $global_history[ $collection->getName() ] = $history;
    $this->set( 'search_history', $global_history );

    return $history;
  }

  public function recordSearch( SearchHandler $search_handler )
  {
    $collection = $search_handler->getCollection();
    $params     = $search_handler->getParams();
    $history    = $this->getSearchHistory( $collection );
    
    $already_there = array_search( $params, $history );
    
    if ( $already_there !== false ) {
      if ( $already_there != count( $history ) - 1 ) {
        // move this search to the top
        array_splice( $history, $already_there, 1 );
        array_push( $history, $params );
      }

      return $this->setSearchHistory( $collection, $history );
    }

    array_push( $history, $params );

    $max_searches = $collection->getConfig( 'search_history_length', 5 );

    while ( count( $history ) > $max_searches ) {
      array_shift( $history );
    }

    return $this->setSearchHistory( $collection, $history );
  }
}