<?php

/**
 * Service de base de LF
 * @link http://lf.goodsenses.net/fw/services/Service
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 14/12/2012
 * @modified
 */

namespace lf\Services;

class Service {
	
	public $services = array();
	public $uses = array();
	private static $servicesInUse = array();
	
	public function __construct() {
		
		$class = explode('\\', get_class($this));
		$srvName = $class[count($class) - 1];
		
		Service::$servicesInUse[$srvName] = $this;
		
		foreach($this->uses as $srv) {
			if(array_key_exists($srv,Service::$servicesInUse)) {
				 $this->services[$srv] = Service::$servicesInUse[$srv];
			} else {
				$className = Service::servicePath($srv);
				$this->services[$srv] = new $className();
				$this->services[$srv]->init();
			}
		}
			
	}
	
	public function init($params = null){
		return true;
	}
	
	public function inject() {
		$args = func_get_args();
		foreach($args as $srv) {
			$class = explode('\\', (is_string($srv) ? $srv : get_class($srv)));
			$srvName = $class[count($class) - 1];
			$this->services[$srvName] = $srv;
		}
	}
	
	public function __get($prop) {
		if(array_key_exists($prop,$this->services)) {
			return $this->services[$prop];
		}
	}
	
	public static function servicePath($srv) {
		if(file_exists(lfServicesPath( 'app\\Services\\'.$srv ) ) ) {
			return '\\app\\Services\\'. ucfirst($srv);
		} else {
			return '\\lf\\Services\\'. ucfirst($srv);
		}	
	}
	
}