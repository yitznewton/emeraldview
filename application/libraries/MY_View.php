<?php

class View extends View_Core
{
  protected $css = array();
  protected $js  = array();
  
  public function addCss( $file, $media = null )
  {
    $this->css[] = array( $file, $media );
  }
  
  public function addJs( $file )
  {
    $this->js[] = $file;
  }
  
	public function render($print = FALSE, $renderer = FALSE)
  {
    $this->kohana_local_data['css_includes'] = '';
    foreach ($this->css as $css) {
      $this->kohana_local_data['css_includes'] .=
        html::stylesheet( $css[0], $css[1] );
    }
    
    $this->kohana_local_data['js_includes'] = '';
    foreach ($this->js as $js) {
      $this->kohana_local_data['js_includes'] .=
        html::script( $js, false );
    }

    return parent::render($print, $renderer);
  }
}