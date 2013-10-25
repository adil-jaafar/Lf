<?php

/**
 * LF
 * @link http://lf.goodsenses.net/fw/services/LF
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 13/12/2012
 * @modified 
 */

namespace lf\Core;

use \lf\Services\Service as Service;

class LF extends Service {
	
	public $uses = array('Http');
	
	public $out_fct = array();
	
	public $services_params = array();
			
	public function __construct( $out_fct = null , $params = false ) {
			parent::__construct();
			if( null == $out_fct ) {
				$this->out_fct = array(
					'f_none'=> function($result) {}
				);
			} else {
				$this->out_fct = $out_fct;
			}
			
			if( !is_array( $params ) ) {
				$this->services_params = array();
			} else {
				$this->services_params = array_merge( $this->services_params , $params );
			}
			
	}
	
	public function run() {
		$uri = $this->Http->uri();
		$controller = $uri['controller'];
		$action =  $uri['action'];
		$args = $uri['args'];

		if( "_" == $action[ 0 ] ) {
			throw new \Exception("Action $action not authorized.",2100);
		}
		
		$incPath = APP_PATH.DS."controller".DS.$controller.'.php';
		if(!file_exists($incPath)) {
			throw new \Exception("Controller $controller does not exist.",1000);
		}

		include_once $incPath;
		
		if( !isset( $$action ) ) {
			if( !isset( $default ) ) {
				throw new \Exception("Action $action does not exist.",2000);	
			} else {
				array_unshift( $args , $action );
				$action = "default";
			}
		}
		
		if(!is_callable($$action)) {
			if( true === $$action ) {
				$$action = $this->getDefaultAction( $uri );
				if( !is_callable( $$action ) ) {
					throw new \Exception("Action $action not a function.",2200);
				}
			} else {
				throw new \Exception("Action $action not a function.",2200);
			}
		}
		
		$reflectedFunc = new \ReflectionFunction($$action);
		$staticVars = array_keys($reflectedFunc->getStaticVariables());
		//$paramsVars = $reflectedFunc->getParameters();
		foreach($staticVars as $service) {
			if( '_' != $service[0] && strtoupper($service[0]) == $service[0] ) {
				$clsname = Service::servicePath($service);
				$$service = new $clsname();
				if( array_key_exists( $service , $this->services_params ) ) {
					$$service->init( $this->services_params[ $service ] );
				} else {
					$$service->init();
				}
			}
		}
		
		if( !isset($Auth) ) {	
			$return = call_user_func_array( $$action , $args );
			$this->callOutFct( $return );
		} elseif( $Auth->check( $uri ) ) {
			$return = call_user_func_array( $$action , $args );
			$this->callOutFct( $return );
		} else {
			$this->Http->redirect( $Auth->params[ 'redirect' ] );
		}
			
	}
	
	private function callOutFct( $return ) {
		
		if( is_array( $return ) && isset( $return['out_fct'] ) && array_key_exists( $return['out_fct'] , $this->out_fct ) ) {
			$fct = $return['out_fct'];
			unset( $return['out_fct'] );
			if( isset( $return['result'] ) ) {
				call_user_func( $this->out_fct[ $fct ] , $return['result'] );
			} else {
				call_user_func( $this->out_fct[ $fct ] , $return );
			}
		} else{
			call_user_func( current( $this->out_fct ) , $return );
		}
	}
	
	private function getDefaultAction( $uri ) {
		switch( $uri['action'] ) {
			case 'post':
				return function() use ( &$Html , &$Store , &$Request ) {};
			case 'put':
				return function() use ( &$Html , &$Store , &$Request ) {};
			case 'edit':
				return function( $id ) use ( &$Auth , &$Html , &$Store , &$Request ) {};
			case 'get':
				return function( $id = null ) use ( &$Auth , &$Html , &$Store ) {};
			case 'delete':
				return function( $id = null ) use ( &$Auth , &$Http , &$Store ) {};
		}
		return false;
	}
	
}