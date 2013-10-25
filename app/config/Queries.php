<?php

class Queries {

	public static $pre_conditions;

	public static function setup() {
		
	}

	private static $q= array(
			
	);

	public static function get( $key , $default = "" ) {
		return (isset( \Queries::$q[ $key ] )) ? \Queries::$q[ $key ] : $default;
	}
	
}

Queries::setup();