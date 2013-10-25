<?php

/**
 * Location: Service de localisation géographique de LF
 * @link http://lf.goodsenses.net/fw/services/Location
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 14/12/2012
 * @modified 
 */

namespace lf\Services;

class Location extends Service {
	public $position = "MEKNES";
	
	public function getLoc() {
		return "MEKNES";	
	}
}