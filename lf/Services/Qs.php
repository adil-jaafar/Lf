<?php

/**
 * Qs: Service Base de données
 * @link http://lf.goodsenses.net/fw/services/Qs
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 19/08/2013
 * @modified 
 */

namespace lf\Services;

if( defined('QUERIES_FILE_PATH') && file_exists( QUERIES_FILE_PATH ) ) {
	include_once QUERIES_FILE_PATH ;
}

class Qs extends Service {
	
	public $name = "Qs";
	
	public $uses = array( 'Session' );

	public function __construct() {
		parent::__construct();
		$this->Session->clean("SQL.Queries");
	}
	
	public function getConnexion() {

		$db = new \PDO("mysql:host=".DBHOST.";dbname=".DBNAME, DBUSER, DBPWD);	
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->query('SET NAMES utf8');
		return $db;
	
	}
	
	public function execQuery($q, $conditions = array() , $mainModel = false , $debug = false) {
	
		if( is_array($q) ){
			$sql = $q;
		} else {
			if( false === ($sql = \Queries::get( $q , false )) ) return false;
		}
		
		try {
			$result = false;
			$cnd = $conditions;
			$db = $this->getConnexion();

			foreach ($sql as $key => $value) {
				if( is_array( $value) && isset( $value['conditions'] ) ) {
					$cnd = array_merge( (array)$value['conditions'] , (array)$cnd );
				}

				if( isset( $value['pre_condition'] ) && isset( \Queries::$pre_conditions[ $value['pre_condition'] ] ) && is_callable( \Queries::$pre_conditions[ $value['pre_condition'] ] ) ) {
					if ( !call_user_func( \Queries::$pre_conditions[ $value['pre_condition'] ] , $cnd ) )
						continue; 
				}

				$this->prequery( $value , $cnd );
				
				$cnd_filtrer = array();
				foreach ( $value['conditions'] as $cle => $param ) {
					if( preg_match("#".$cle."(\s+|[,\\=+-<>\*\)])#si", $value['sql']." ") ) {
						$cnd_filtrer[ $cle ] = $param;
					}
				}
				
				if( true == $debug ) {
					var_dump( $cnd );
					var_dump( $value['sql'] );
					var_dump( $cnd_filtrer );
				}
				
				$partial_return = 0;
				
				if( "" != trim( $value['sql'] ) ) {
					$req = $db->prepare($value['sql']);
					$result = $req->execute( $cnd_filtrer );
					if( preg_match("#^select#si", $value['sql'] ) ){
						$result = $req->fetchAll(\PDO::FETCH_ASSOC);
						if( $result ) {
							$partial_return = reset($result[0]);
						} else if( isset( $value['stop_if_eqz'] ) && true == $value['stop_if_eqz'] ) {
							return $result;
						}
					} else if( preg_match("#^update#si", $value['sql'] ) ){
						$partial_return = $req->rowCount();
						$result = $partial_return;
					} else if( preg_match("#^insert#si", $value['sql'] ) ){
						$partial_return = $db->lastInsertId();
						$result = $partial_return;
					} else if( preg_match("#^delete#si", $value['sql'] ) ){
						$partial_return = $req->rowCount();
						$result = $partial_return;
					}
				}
				
				//$this->reorganise( $result , $mainModel );
				if( isset( $value['return_in'] ) ) {
					$cnd[ $value['return_in'] ] = $partial_return;
				}

				if( isset( $value['return_cnd'] ) && true == $value['return_cnd'] ) {
					return $value['conditions'];
				}

				//*/
			}
			$db = null;
			return $result;
		} catch(PDOException $e) {
			throw $e; 
		}

	}

	private function prequery( &$sql , &$conditions ) {
		
		if( !is_array( $sql ) ) {
			$sql = array( 'sql'=> $sql , 'conditions'=>$conditions );
			return;
		}

		if( isset( $sql['sql'] ) ) {

			if( !isset( $sql['conditions'] ) ) {
				$sql['conditions'] = $conditions;
			} else {
				$sql['conditions'] = array_merge( (array) $sql['conditions'], (array) $conditions );
			}

			if( isset( $sql['prequery'] ) ) {
				foreach ($sql['prequery'] as $value) {
					switch ($value) {
						case 'SET_IN_SET':
							$sql['sql'] = preg_replace_callback(
												"#SET_IN_SET\((.*?),(.*?)\)#si", 
												function($matchs) use ( &$sql ) {
													if( isset( $sql['conditions'][ trim($matchs[1]) ] ) ) {
														$match = trim($matchs[1]);
														$cnd = $sql['conditions'][ $match ];
														if( "" == $cnd ) return "TRUE";
														if( !is_array( $cnd ) ) $cnd = explode(',', $cnd);
														if( 1 < count($cnd) ) {
															$replace = "(";
															foreach ($cnd as $v) {
																if( "(" != $replace) $replace .=" OR";
																$replace .= " FIND_IN_SET('".$v."',".$matchs[2].")";
															} 
															$replace .= ")";
														}else {
															$replace = " FIND_IN_SET('".$cnd[0]."',".$matchs[2].")";
														}
														return $replace;
													} else {
														return "TRUE";
													}
												}, 
												$sql['sql'] );
							break;
						case 'ISSET':
							$sql['sql'] = preg_replace_callback(
												"#ISSET\s*\[\s*(.*?),(.*?)\]#si", 
												function($matchs) use ( &$sql ) {
													if( isset( $sql['conditions'][ trim($matchs[1]) ] ) && "" != $sql['conditions'][ trim($matchs[1]) ] ) {
														return $matchs[2];
													} else {
														return "";
													}
												}, 
												$sql['sql'] );
							break;
					}
				}
				unset( $sql['prequery'] );
			}

		}

	}
	
	private function reorganise(&$tab , $mainModel = false) {		
		if(!is_array($tab)) return;
		foreach($tab as $k=>&$v) {
			$keys = explode(".",$k,2);
			if(count($keys)==1) {
				$this->reorganise($v , $mainModel );
			} else {
				if( false == $mainModel ) {
					if(!isset($tab[$keys[0]])) {
						$tab[$keys[0]] = array($keys[1]=>$v);
					} else {
						$tab[$keys[0]][$keys[1]] = $v;
					}
				} else {
					if( $mainModel == $keys[0] ) {
						$tab[$keys[1]] = $v;
					}
				}
				
				unset($tab[$k]);
			}
					
		}
	}
	
}