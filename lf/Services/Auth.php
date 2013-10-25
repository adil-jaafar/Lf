<?php

/**
 * Auth: Service Authentification de LF
 * @link http://lf.goodsenses.net/fw/services/Auth
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 13/12/2012
 * @modified 
 */

namespace lf\Services;

class Auth extends Service {
	
	public $user = null;
	public $uses = array('Session','Http');
	public $params = array(
			'login' => array(
				'controller' => 'users',
				'action' => 'login'
			),
			'logout' => array(
				'controller' => 'users',
				'action' => 'logout'
			),
			'access' => 'form',
			'redirect' => array(
				'controller' => 'users',
				'action' => 'login'
			)
	);
	
	public function __construct(){
		parent::__construct();
	}
	
	public function init($params = null) {
		parent::init($params);
		if(is_array($params)) {
			$this->params = array_merge($this->params, $params);
		}
		$this->user = $this->Session->read('Auth.user', null);
		return true;
	}
	
	public function __get($prop) {
		if($prop === 'user'){
			return $this->user;
		} else {
			return parent::__get($prop);
		}
	}
	
	public function __set($prop,$value) {
		if($prop === 'user') {
			if($value!=null) {
				$this->Session->write('Auth.user', $value);
			} else {
				$this->Session->clean('Auth.user');	
			}
			
			$this->user = $value;
		} else {
			parent::__set($prop, $value);	
		}
	}
	
	public function redirect() {
		$this->Http->redirect($this->params['redirect']);	
	}
	
	public function login($compte="", $pwd="") {
		
	}
	
	public function logout() {
		$this->user = null;
		$this->Session->clean('Auth.user');
	}
	
	public function check( $uri ) {
		$action = $uri['action'];
		$controller = $uri['controller'];

		if(
			($this->params['login']['controller'] === $controller && $this->params['login']['action'] === $action)
			|| ($this->params['logout']['controller'] === $controller && $this->params['logout']['action'] === $action)
			) {
			return true;
		}

		if( !$this->user ) {
			return false;
		}

		if( is_array( $this->params['access'] ) && isset( $this->params['access']['mode'] ) && "permissions" == $this->params['access']['mode'] ) {
			
			$exclusif = isset( $this->params['access']['exclusif'] )? $this->params['access']['exclusif'] : true;
			if( !isset( $this->params['access']['checks'] ) ) {
				$this->params['access']['checks'] = array( "*" );
			}
			if( !is_array( $this->params['access']['checks'] ) ) {
				$this->params['access']['checks'] = array( $this->params['access']['checks'] );
			}

			if( in_array( $controller , $this->params['access']['checks'] ) || in_array( "*" , $this->params['access']['checks'] ) ) {
				return ( isset( $this->user[ $action ] ) && 1 == $this->user[ $action ] ) || ( !$exclusif && !isset( $this->user[ $action ] ) );
			} else {
				return !$exclusif;
			}

		} else {
			return true;
		}
	}
	
}



