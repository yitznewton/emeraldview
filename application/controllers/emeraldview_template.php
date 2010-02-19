<?php

abstract class Emeraldview_Template_Controller extends Template_Controller
{
  protected $view;
  protected $theme;
  protected $availableLanguages;
  protected $language;
  protected $emeraldviewName;
  protected $collection;
  protected $session;

  public function __construct()
  {
    if (!EmeraldviewConfig::get('greenstone_collection_dir')) {
      $msg = 'Greenstone collection directory not specified '
           . 'in config/emeraldview.yml';
      throw new Exception( $msg );
    }
    
    // core stuff
    Controller::__construct();
		if ($this->auto_render == TRUE)
		{
			Event::add('system.post_controller', array($this, '_render'));
		}
    
    $this->session = Session::instance();
    
    $this->emeraldviewName = EmeraldviewConfig::get('emeraldview_name')
                           ? EmeraldviewConfig::get('emeraldview_name')
                           : 'EmeraldView'
                           ;
                           
    $this->theme = EmeraldviewConfig::get('default_theme')
                 ? EmeraldviewConfig::get('default_theme')
                 : 'default'
                 ;
                 
    $this->template = new View( $this->theme . '/template' );
    $this->template->theme = $this->theme;
    $this->template->addCss( "views/$this->theme/css/style" );
    $this->template->addCss( "views/$this->theme/css/style-print", 'print' );
    $this->template->set_global( 'languages', $this->getAvailableLanguages() );
    
    Event::add_before(
      'system.post_controller',
      array( $this, '_render' ),
      array( $this, '_injectContentIntoTemplate' )
    );

    if (
      $this->input->get('language')
      && in_array( $this->input->get('language'), $this->availableLanguages )
    ) {
      $this->session->set('language', $this->input->get('language'));
    }
    
    if ($this->session->get('language')) {
      $this->language = $this->session->get('language');
    }
    elseif (EmeraldviewConfig::get('default_language')) {
      $this->language = EmeraldviewConfig::get('default_language');
    }
    else {
      $this->language = 'en';
    }
    
    $this->loadGettextDomain( $this->language );
  }
  
  protected function loadCollection( $collection_name )
  {
    $this->collection = Collection::factory( $collection_name );
    
    if (!$this->collection) {
      return false;
    }
    
    // override global defaults if collection config values set
    if (
      ! $this->session->get('language')
      && $this->collection->getConfig('default_language')
    ) {
      $this->language = $this->collection->getConfig('default_language');
    }
    
    if ( $this->collection->getConfig('theme') ) {
      $this->theme = $this->collection->getConfig('theme');
    }
    
    return $this->collection;
  }
  
  protected function getAvailableLanguages()
  {
    if ($this->availableLanguages) {
      return $this->availableLanguages;
    }
    
    $locale_dir = realpath(
      PUBLICPATH . 'views/' . $this->theme . '/locale' );
    
    if (!$locale_dir) {
      $msg = "Unable to find locale directory "
           . "for this theme ($this->theme)";
      throw new Exception( $msg );
    }
    
    $locale_dir_iterator = new DirectoryIterator( $locale_dir );
    $languages = array();
    
    foreach ( $locale_dir_iterator as $file ) {
      if (
        !$file->isDot() 
        && preg_match('/^(\w+)\.mo$/', $file->getFilename(), $matches)
      ) {
        $languages[] = $matches[1];
      }
    }
    
    if (empty( $languages )) {
      throw new Exception('No gettext files found in locale directory');
    }
    
    return $this->availableLanguages = $languages;
  }
  
  protected function loadGettextDomain( $language, $domain_name = null )
  {
    $mofile = realpath(PUBLICPATH . 'views/' . $this->theme . '/locale/'
            . $language . '.mo');
              
    if (!$mofile) {
      throw new Exception("Could not find .mo file for language $language");
    }
    
    if ($domain_name === null) {
      L10n::load( $mofile );
      L10n::setLanguage( $language );
    }
    else {
      L10n::loadDomain( $domain_name, $mofile );
    }
  }
  
  public function getCollection()
  {
    return $this->collection;
  }

  public function _injectContentIntoTemplate()
  {
    $this->view->theme = $this->theme;
    $this->template->content = $this->view;
  }
}