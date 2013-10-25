<?php

/**
 * Http: Service Http de LF
 * @link http://lf.goodsenses.net/fw/services/Http
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 14/12/2012
 * @modified
 */

namespace lf\Services;

class Http extends Service {
	
	public $uses = array('Session');
	private $uri = null;
	
	public function redirect($url, &$R = null) {
		if($R != null) {
			$R->saveInSession();
		}
		header("Location: ".$this->url($url));
		die();	
	}
	
	public function url($url) {
		

		if(!is_array($url)) {
			if(!preg_match('/^\w+:\/\//i',$url)) {
				$url = SITE_ADDRESS.str_replace('//','/','/'.$url);
			}
		} else {
			$controller = DEFAULT_CONTROLLER;
			$action = DEFAULT_ACTION;
			$args ="";
			foreach($url as $k=>$v) {
				if(is_numeric($k)){
					$args .= "/".$v;
				} elseif($k == 'controller') {
					$controller = $v;
				} elseif($k == 'action') {
					$action = $v;
				}
			}
			
			$url = SITE_ADDRESS.'/'.$controller.'/'.$action.$args;
		}
		
		return $url;
	}
	
	public function uri() {
		
		if($this->uri != null) {
			return $this->uri;
		}
		
		if( isset( $_GET['url'] ) && "" != $_GET['url'] ) {
			$uri = $_GET['url'];
		} else {
			$uri = "";
		}

		$uri = str_replace('//', '/', $uri);
		$uri = preg_replace("#^/|/$#", '', $uri);
		
		$a = explode("/", $uri,3);
		$controller = DEFAULT_CONTROLLER;
		$action = DEFAULT_ACTION;
		$args = array();
		
		if(count($a) == 1 && $a[0] != "")  {
			$controller = $a[0];
		} elseif(count($a) == 2 && $a[1] != "")  {
			$controller = $a[0];
			$action = $a[1];
		} elseif(count($a)>2) {
			$controller = $a[0];
			$action = $a[1];
			$args = explode("/",$a[2]);
		}
		
		$action = str_replace(".","_",$action);
		
		$this->uri = array('uri'=>$uri, 'controller'=>$controller, 'action'=>$action, 'args'=>$args);
		return $this->uri;
	}
	
	public function getUri($key='uri') {
		if($this->uri != null) {
			$uri = $this->uri;
		} else {
			$uri = $this->uri();
		}
		
		return $uri[$key];	
	}

}