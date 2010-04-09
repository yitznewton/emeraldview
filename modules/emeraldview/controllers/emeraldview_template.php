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
  protected $globals = array();

  public function __construct()
  {
    if ( ! EmeraldviewConfig::get('greenstone_collection_dir') ) {
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
    
    $this->emeraldviewName = EmeraldviewConfig::get('emeraldview_name', 'EmeraldView');
                           
    $this->setTheme( EmeraldviewConfig::get('default_theme', 'default') );

    $this->passDown( 'languages', $this->getAvailableLanguages() );

    if (
      $this->input->get('language')
      && in_array( $this->input->get('language'), $this->availableLanguages )
    ) {
      $this->session->set('language', $this->input->get('language'));
    }

    if ($this->session->get('language')) {
      $this->language = $this->session->get('language');
    }
    else {
      $this->language = EmeraldviewConfig::get('default_language', 'en');
    }

    $this->loadGettextDomain( $this->language );

    Event::add_before(
      'system.post_controller',
      array( $this, '_render' ),
      array( $this, '_transferGlobals' )
    );

    Event::add_before(
      'system.post_controller',
      array( $this, '_render' ),
      array( $this, '_injectContentIntoTemplate' )
    );

    Event::add_before(
      'system.post_controller',
      array( $this, '_render' ),
      array( $this, '_addThemeCss' )
    );
  }

  /**
   * Allows us to set variables to be passed globally to the View objects,
   * before the View objects are created
   *
   * @param string $name
   * @param mixed $value 
   */
  protected function passDown( $name, $value )
  {
    $this->globals[ $name ] = $value;
  }

  protected function setTheme( $name )
  {
    $this->theme = $name;
    $this->passDown( 'theme', $name );
  }

  protected function loadView( $name )
  {
    // set view name as method for use in View code
    $this->passDown( 'method', $name );

    // first load the template...

    $l10n_template = $this->theme . "/locale/$this->language/template";
    
    if ( file_exists( PUBLICPATH . 'views/' . $l10n_template . '.php' )) {
      $this->template = new View( $l10n_template );
    }
    else {
      $this->template = new View( $this->theme . '/template' );
    }

    $this->template->addCss( 'css/reset' );

    if ( L10n::_('ltr') == 'rtl' ) {
      $this->template->addCss( "views/$this->theme/css/rtl" );
    }

    // TODO: move this to individual theme files?
    $this->template->addJs( 'libraries/jquery' );
    $this->template->addJs( "views/$this->theme/js/$this->theme" );

    // ... and now load the specific view

    $l10n_view = $this->theme . "/locale/$this->language/$name";

    if ( file_exists( PUBLICPATH . 'views/' . $l10n_view . '.php' )) {
      $this->view = new View( $l10n_view );
    }
    else {
      $this->view = new View( $this->theme . '/' . $name );
    }
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
      $this->setTheme( $this->collection->getConfig('theme') );
    }
    
    $this->passDown( 'collection', $this->collection );
    $this->passDown( 'collection_display_name',    $this->collection->getDisplayName( $this->language ) );

    return $this->collection;
  }
  
  protected function getAvailableLanguages()
  {
    if ($this->availableLanguages) {
      return $this->availableLanguages;
    }
    
    $locale_dir = realpath(
      PUBLICPATH . 'views/' . $this->theme . '/locale' );
    
    if ( ! $locale_dir ) {
      $msg = "Unable to find locale directory "
           . "for theme ($this->theme)";
      throw new Exception( $msg );
    }
    
    $locale_dir_iterator = new DirectoryIterator( $locale_dir );
    $languages = array();
    
    foreach ( $locale_dir_iterator as $file ) {
      $mo_file = $locale_dir . '/' . $file->getBasename() . '/'
                 . $file->getBasename() . '.mo';
      if (
        ! $file->isDot()
        && $file->isDir()
        && file_exists( $mo_file )
      ) {
        $languages[] = $file->getBasename();
      }
    }
    
    if (empty( $languages )) {
      throw new Exception('No gettext files found in locale directory');
    }
    
    return $this->availableLanguages = $languages;
  }

  protected function loadGettextDomain( $language, $domain_name = null )
  {
    $mofile = realpath( PUBLICPATH . 'views/' . $this->theme . '/locale/'
              . "$language/$language.mo" );
              
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

  public function _transferGlobals()
  {
    foreach ( $this->globals as $name => $value ) {
      $this->template->set_global( $name, $value );
    }
  }

  public function _injectContentIntoTemplate()
  {
    $this->template->content = $this->view;
  }

  public function _addThemeCss()
  {
    $this->template->addCss( "views/$this->theme/css/style" );
    $this->template->addCss( "views/$this->theme/css/style-print", 'print' );
  }
}
