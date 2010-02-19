<?php
/**
 * EmeraldView
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://yitznewton.net/emeraldview/index.php/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@yitznewton.net so we can send you a copy immediately.
 *
 * @version 0.2.0b1
 * @package libraries
 */
/**
 * View extends the default Kohana session with themed CSS and JS linking support
 *
 * @package libraries
 * @copyright  Copyright (c) 2010 Benjamin Schaffer (http://yitznewton.net/)
 * @license    http://yitznewton.net/emeraldview/index.php/License     New BSD License
 */
class View extends View_Core
{
  /**
   * @var array
   */
  protected $css = array();
  /**
   * @var array
   */
  protected $js  = array();
  
  /**
   * @param string $file
   * @param string $media 
   */
  public function addCss( $file, $media = null )
  {
    $this->css[] = array( $file, $media );
  }
  
  /**
   * @param string $file
   * @param string $media
   */
  public function addJs( $file )
  {
    $this->js[] = $file;
  }
  
	/**
   * @return string
   */
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
