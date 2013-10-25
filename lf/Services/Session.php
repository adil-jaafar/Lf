<?php

/**
 * Session: Service Session de LF
 * @link http://lf.goodsenses.net/fw/services/Session
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 13/12/2012
 * @modified 
 */

namespace lf\Services;

class Session extends Service {
	
	public function read($key, $default = false) {
		if(isset($_SESSION[$key])){
			$value = $_SESSION[$key];
			if(substr(strtoupper($key),0,5) == 'FLASH') {
				unset($_SESSION[$key]);
			}
			return $value;
		} else {
			return $default;
		}
	}
	
	public function is($key) {
		return isset($_SESSION[$key]);
	}
	
	public function write($key, $value) {
		$_SESSION[$key] = $value;
	}
	
	public function push( $key , $value ) {
		if( ! $this->is($key) ) $_SESSION[$key] = array();
		array_push($_SESSION[$key], $value);
	}

	public function pop( $key ) {
		if( ! $this->is($key) ) return false;
		return array_pop( $_SESSION[$key] );
	} 

	public function clean($key = null) {
		if($key == null) {
			$_SESSION = array();	
		} else {
			$_SESSION[$key] = null;
			unset($_SESSION[$key]);
		}
	}
	
}
