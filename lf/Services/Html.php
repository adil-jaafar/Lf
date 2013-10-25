<?php

/**
 * Html: Service Html de LF
 * @link http://lf.goodsenses.net/fw/services/Html
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 14/12/2012
 * @modified
 */

namespace lf\Services;

class Html extends Service {
	
	public $uses = array('Http','Session');
	
	public $contents = array();
	public $viewCss = array();
	public $viewJs = array();
	public $scripts = array();
	
	public function render($view, $params = null, $layout = DEFAULT_LAYOUT, $return = false) {
		
		if($params == null ) {
			$params = array('Html'=>$this);	
		}
		
		if(is_array($params)) {
			$params["Html"] = $this;
			extract($params);
		} else {	
			extract(array('Html'=>$this, $params=>$params));	
		}
		
		if(!is_array($view) && null != $view ) {
			$view = array('content'=>$view);	
		}
		
		if( '' == $layout || false == $layout || null == $layout ) {
			
			if($return) ob_start();
			if(file_exists(VIEWS_PATH.DS.$view['content'].".php")) {
				include VIEWS_PATH.DS.$view['content'].".php";
			} else {
				include VIEWS_PATH.DS."errors".DS."404.php";
			}
			if($return) return ob_get_clean();
			
		} else {
			
			if( null == $view ) {
				$this->contents["content"] = $params;
			} else {
				foreach($view as $z=>$cnt) {
					ob_start();
					if(file_exists(VIEWS_PATH.DS.$cnt.".php")) {
						include VIEWS_PATH.DS.$cnt.".php";
					} else {
						include VIEWS_PATH.DS."errors".DS."404.php";
					}
					$this->contents[$z] = ob_get_clean();
				}
			}
			
			
			if($return) ob_start();
			include VIEWS_PATH.DS.$layout.".php";
			if($return) return ob_get_clean();
		}
		
	}
	
	public function zone($z, $default = "") {
		if(array_key_exists($z, $this->contents)) {
			echo $this->contents[$z];
		} else {
			echo $default;	
		}
	}
	
	public function content($default = "") {
			echo $this->zone('content', $default);
	}
	
	public function getCookie( $name , $default = "" ) {
		return isset( $_COOKIE[ $name ] )? $_COOKIE[ $name ] : $default;
	}

	public function css($files , $media = "screen" , $relative = false ) {
		
		$base = SITE_ADDRESS.'/css/';
		if( true == $relative ) {
			$base = "css/";
		}

		if(!is_array($files)) {
			if( preg_match("#^(http|https)://#si", $files ) ) {
				return '<link href="'.$files.'.css" type="text/css" rel="stylesheet" media="'.$media.'"/>'."\n";
			} else {
				return '<link href="'.$base.$files.'.css" type="text/css" rel="stylesheet" media="'.$media.'"/>'."\n";
			}
		} else {
			$links = "";
			foreach($files as $link) {
				if( preg_match("#^(http|https)://#si", $link ) ) {
					$links .= '<link href="'.$link.'.css" type="text/css" rel="stylesheet" media="'.$media.'"/>'."\n";
				} else {
					$links .= '<link href="'.$base.$link.'.css" type="text/css" rel="stylesheet" media="'.$media.'"/>'."\n";
				}
			}
			return $links;
		}
	}

	public function rel($files , $rel = "stylesheet" , $relative = false ) {
		
		$base = SITE_ADDRESS.'/';
		if( true == $relative ) {
			$base = "";
		}

		if(!is_array($files)) {
			return '<link href="'.$base.$files.'" rel="'.$rel.'" />'."\n";
		} else {
			$links = "";
			foreach($files as $link) {
				$links .= '<link href="'.$base.$link.'" rel="'.$rel.'" />'."\n";
			}
			return $links;
		}
	}
	
	public function image($file, $attrs = array() , $relative = false ) {
		$base = SITE_ADDRESS.'/images/';
		if( true == $relative ) {
			$base = 'images/';
		}
		
		$attr = "";
		foreach ($attrs as $key => $value) {
			$attr .=' '.$key.'="'.$value.'"';
		}
		return '<img src="'.$base.$file.'" '.$attr.'/>';
	}

	public function linkToImage($file, $relative = false ) {
		$base = SITE_ADDRESS.'/images/';
		if( true == $relative ) {
			$base = 'images/';
		}
		return $base.$file;
	}

	public function mobileIcone( $file , $relative = false ) {
		$base = SITE_ADDRESS.'/images/';
		if( true == $relative ) {
			$base = 'images/';
		}
		return '<link rel="apple-touch-icon" href="'.$base.$file.'" />';
	}
	
	public function linkToJs( $file , $relative = false) {
		$base = SITE_ADDRESS.'/images/';
		if( true == $relative ) {
			$base = 'images/';
		}
		return  $base.$file.'.js';
	}
	
	public function js($files , $relative = false) {

		$base = SITE_ADDRESS.'/js/';
		if( true == $relative ) {
			$base = 'js/';
		}
		
		if(!is_array($files)) {
			if( preg_match("#^(http|https)://#si", $files ) ) {
				return '<script src="'.$files.'" type="application/javascript"></script>'."\n"; 
			} else {
				return '<script src="'.$base.$files.'.js" type="application/javascript"></script>'."\n";
			}
		} else {
			$links = "";
			foreach($files as $link) {
				if( preg_match("#^(http|https)://#si", $link ) ) {
					$links .= '<script src="'.$link.'" type="application/javascript"></script>'."\n";
				} else {
					$links .= '<script src="'.$base.$link.'.js" type="application/javascript"></script>'."\n";
				}
			}
			return $links;
		}
	}
	
	public function viewCss($files = null) {
		if($files == null) {
			return $this->css($this->viewCss);	
		} else {
			if(!is_array($files)) {
				$this->viewCss[] = $files;
			} else {
				$this->viewCss += $files;
			}
		}
	}
	
	public function viewJs($files = null) {
		if($files == null) {
			return $this->js($this->viewJs);	
		} else {
			if(!is_array($files)) {
				$this->viewJs[] = $files;
			} else {
				$this->viewJs += $files;
			}
		}
	}

	public function appendScript( $group = null ) {
		if( null == $group ) {
			ob_start();
		} else {
			$js = ob_get_clean();
			$js = preg_replace("#^\s*<\s*script(.*?)>\s*#si", "", $js );
			$js = preg_replace("#\s*<\s*/\s*script(.*?)>\s*$#si", "", $js );
			if( array_key_exists( $group , $this->scripts ) ){
				$this->scripts[ $group ] .= "\n".$js;
			} else {
				$this->scripts[ $group ] = $js;
			}
		}
	}

	public function getScript( $group , $default = "" ) {
		if( array_key_exists( $group , $this->scripts ) ){
			return "\n".$this->scripts[ $group ]."\n";
		} else {
			return $default;
		}
	}
	
	public function link($text, $url, $options = array()) {
		$called_url = $this->Http->url($url);
		$link = '<a href="'.$called_url.'"';
		foreach($options as $op=>$val) {
			if(substr($op,0,3) == 'lf-') {
				if(!is_array($val)){
					$this_url = $this->Http->url($this->Http->getUri());
					if($called_url == $this_url) {
						$link .= ' '.substr($op,3).' = "'.$val.'"';	
					}
				} else {
					$ctrl = $val[0];
					$cls = $val[1];
					if($ctrl == $this->Http->getUri('controller')){
						$link .= ' '.substr($op,3).' = "'.$cls.'"';	
					}
				}
			} else {
				$link .= ' '.$op.' = "'.$val.'"';
			}
		}
		$link .= '>'.$text.'</a>';
		
		return $link;	
	}
	
	
}