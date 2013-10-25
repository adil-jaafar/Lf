<?php

/**
 * Inflector: Service linguistique de LF
 * @link http://lf.goodsenses.net/fw/services/Html
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 27/08/2013
 * @modified
 */

namespace lf\Services;

class Inflector extends Service {

	protected static $_plural = array(
			'/(eau|eu|ou)$/i' => '\1x',
			'/(al|ail)$/i' => '\1aux',
			'/^$/' => '',
			'/$/' => 's'
	);
	
	protected static $_irregular = array(
			'bal'=>'bals',
			'carnaval'=>'carnavals',
			'chacal'=>'chacals',
			'travail'=>'travaux'
	);
	
	protected static $_singular = array(
			'/(eau|eu|ou)x$/i' => '\1',
			'/aux$/i' => '\1al',
			'/s$/i' => ''
	);

	public function slug( $string ) {
		$a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ'; 
    	$b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr'; 
	    $string = utf8_decode($string);     
	    $string = strtr($string, utf8_decode($a), $b); 
	    $string = strtolower($string); 
	    $string = str_replace(" ", "-", $string );
	    $string = str_replace(".", "-", $string );
	    $string = str_replace("/", "-", $string );
	    return utf8_encode($string);
	}

	public function pluralize($word) {
		
		if( array_key_exists( $word , self::$_irregular ) ) {
			return self::$_irregular[ $word ];
		}

		foreach (self::$_plural as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}

	}

	public function singularize($word) {

		if( false !== ($value = array_search($word , self::$_irregular) ) ) {
			return $value;
		}

		foreach (self::$_singular as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}
	}

}