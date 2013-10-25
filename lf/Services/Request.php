<?php

/**
 * Request: Service Request de LF
 * @link http://lf.goodsenses.net/fw/services/Request
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 14/12/2012
 * @modified 19/02/2012 16:52
 */

namespace lf\Services;

class Request extends Service {
	
	public $uses = array('Session');
	private $data = array();
	private $headers = array();
	private $validationErrors = array();
	private $files = array();
	private $argv = array();
	private $method = "";
	
	public function __construct(){
		parent::__construct();
	}
	
	public function init($params = null) {
		$this->method = strtoupper($_SERVER['REQUEST_METHOD']);
		$this->data = $_POST;
		$payload = json_decode(file_get_contents('php://input'), true);
		if(is_array($payload)) $this->data = array_merge($this->data, $payload);

		foreach($_SERVER as $h=>$v)
			if( preg_match('/HTTP_(.+)/',$h,$hp) )
				$this->headers[$hp[1]]=$v;
		/*
		if($this->is('post')) {
			$this->data = $_POST;
		} else {
			$this->data =json_decode(file_get_contents('php://input'), true);// $_POST;
		}
		*/
		$this->files = $_FILES;
		
		$this->argv = $_GET;// $_SERVER['argv'];
		
		$this->restoreFromSession();
	}
	
	public function saveInSession() {	
		$this->Session->write('Request.data',$R->data);
		$this->Session->write('Request.argv',$R->argv);
		$this->Session->write('Request.method',$R->method);	
		$this->Session->write('Request.validationErrors',$R->validationErrors);	
	}
	
	public function restoreFromSession() {
		
		$this->data = $this->Session->read("Request.data", $this->data);
		$this->argv = $this->Session->read("Request.argv", $this->argv);
		$this->method = $this->Session->read("Request.method", $this->method);
		$this->validationErrors = $this->Session->read("Request.validationErrors", $this->validationErrors);
		
		$this->Session->clean("Request.data");
		$this->Session->clean("Request.argv");
		$this->Session->clean("Request.method");
		$this->Session->clean("Request.validationErrors");
	}
	
	public function getData($key, $default = null) {
		if(array_key_exists($key,$this->data)) return $this->data[$key];
		return $default;
	}

	public function getHttpHeader( $key , $default = null ) {
		if(array_key_exists($key,$this->headers)) return $this->headers[$key];
		return $default;
	}
	
	public function __get($prop) {
		if($prop === 'data') {
			return $this->data;
		}elseif($prop === 'method') {
			return $this->method;
		}elseif($prop === 'files') {
			return $this->files;
		}elseif($prop === 'validationErrors') {
			return $this->validationErrors;
		}
		return parent::__get($prop);
	}
	
	public function __set($prop,$value) {
		if('data' === $prop) {
			$this->data = $value;
		}elseif($prop === 'validationErrors') {
			$this->validationErrors = $value;
		} else {
			parent::__set($prop, $value);	
		}
	}
	
	public function argv($key, $default = "") {
		if(isset($this->argv[$key])) {
			return $this->argv[$key];
		} else {
			return $default;
		}
	}
	
	public function is($type) {
		return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper($type);	
	}
	
	public function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) == strtoupper('XMLHttpRequest');	
	}
		
}