<?php

class HitsPager
{
  protected $controller;
  protected $hits;
  protected $processedHits;
  protected $totalHits;
  protected $startHit;
  protected $endHit;
  protected $linkPages;
  
  public function __construct( Search_Controller $controller, array $hits )
  {
    $this->controller = $controller;
    
    $this->startHit = 1 + (($controller->getCurrentPage() - 1)
                     * $controller->getHitsPerPage());

    $this->totalHits = count( $hits );
    
    $this->hits = array_slice(
      $hits, ($this->startHit - 1), $controller->getHitsPerPage()
    );
    
    $this->endHit = $this->startHit + ( count( $this->hits ) - 1 );
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function getHits()
  {
    return $this->hits;
  }
  
  public function getProcessedHits()
  {
    if (isset($this->processedHits)) {
      return $this->processedHits;
    }
    
    return $this->processedHits
           = array_map( array('HitsPager', 'process'), $this->hits );
  }
  
  protected function process( $hit )
  {
    if ( strpos($hit->docOID, '.') === false ) {
      // document-level search
      $doc_id = $hit->docOID;
      $node_id = null;
    }
    else {
      // section-level search
      $doc_id  = substr( $hit->docOID, 0, strpos($hit->docOID, '.') );
      $node_id = substr( $hit->docOID, strpos($hit->docOID, '.') + 1 );
    }

    $node = Node_Document::factory( $this->controller->getCollection(), $hit->docOID );

    $processed_hit = new stdClass;
    $processed_hit->node_id  = $node_id;  //FIXME necessary?
    $processed_hit->title = $node->getField( 'Title' );
    
    $slug = 'FIXME';
    
    $docOID = $hit->docOID;

    if ($node->isPaged()) {
      $docOID = str_replace(".$node_id", '.' . $label, $docOID);
    }

    $display_query = $this->controller->getQueryBuilder()->getDisplayQuery();

    $page = NodePage::factory( $node );
    
    $processed_hit->url = $page->getUrl()
                        . '?search=' . htmlspecialchars( $display_query );

    $processed_hit->thumb_url = $page ->getThumbnailUrl( $node_id );
    
    try {
      $processed_hit->text = $hit->getDocument()->getField('TX')->value;
    }
    catch (Zend_Search_Lucene_Exception $e) {
      // EmeraldView modified LuceneWrapper.jar for snippets not implemented
      $processed_hit->text = '';
    }

    return $processed_hit;
  }
  
  public function getCurrentPage()
  {
    $current_page = $this->controller->getCurrentPage();
    
    if ((int) $current_page <= (int) $this->getTotalPages()) {
      return $current_page;
    }
    else {
      return $this->getTotalPages();
    }
  }
  
  public function getStartHit()
  {
    // TODO: replace these properties with calls to Controller functions?
    return $this->startHit;
  }
  
  public function getEndHit()
  {
    return $this->endHit;
  }
  
  public function getTotalHits()
  {
    return $this->totalHits;
  }
  
  public function getTotalPages()
  {
    $total_pages = ceil(
      $this->totalHits / $this->controller->getHitsPerPage()
    );
    
    return $total_pages;
  }
  
  public function getLinkPages()
  {
    if ($this->linkPages) {
      return $this->linkPages;
    }
    
    $curr  = $this->controller->getCurrentPage();
    $per   = $this->controller->getHitsPerPage();
    $total = ceil( $this->totalHits / $per );
    
    // TODO: config
    $pages_in_pager = 11;  // number of pages to link to explicitly
    
    $links = new stdClass;
    
    $links->current = $curr;
    $links->total   = $total;
    
    $links->first = $curr > 1 ? 1 : null;
    $links->prev  = $curr > 1 ? $curr - 1 : null;
    $links->next  = $curr < $total ? $curr + 1 : null;
    $links->last  = $curr < $total ? $total : null;
    
    return $this->linkPages = $links;
  }
}