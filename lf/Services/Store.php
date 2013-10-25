<?php

/**
 * Store: Service Base de données
 * @link http://lf.goodsenses.net/fw/services/Store
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 13/12/2012
 * @modified 
 */

namespace lf\Services;

class Store extends Service {
	
	public $name = "Store";
	
	public $uses = array('Config', 'Session');
	
	private $schema = null;
	
	private $relations = array('belongsTo', 'hasMany', 'hasOne', 'belongsToMany');
	
	/*
	private $fields = "";
	private $table = "";
	private $jointures = "";
	private $where = array();
	private $orderBy = "";
	//*/

	public function __construct() {
		parent::__construct();
		$this->Session->clean("SQL.Queries");
		$this->schema = $this->Config->schema();
	}
	
	public function getConnexion() {	
		$db = new \PDO("mysql:host=".DBHOST.";dbname=".DBNAME, DBUSER, DBPWD);	
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->query('SET NAMES utf8');
		return $db;
	}
	
	public function find($mainModel, $params = null, $typeSortie = 'FETCH_ASSOC') {
		
		$req = $this->query($mainModel, $params, 'all');
		$sql = $req['sql'];
		$a_cnds = $req['conditions'];
		$afterFind = $req['afterFind'];
		
		//echo "<br/>";
		//var_dump($req);
		
		try {
			$db = $this->getConnexion();
			$req = $db->prepare($sql);
			$req->execute($a_cnds);
			switch($typeSortie) {
				case 'FETCH_OBJ':
					$result = $req->fetchAll(\PDO::FETCH_OBJ);
					break;
				default:
				$result = $req->fetchAll(\PDO::FETCH_ASSOC);
				//$this->reorganise($result);
			}
			
			$db = null;
			if(count($afterFind)!=0 && $result != false) {
				foreach($result as &$r){
					foreach($afterFind as $alias=>$aft){
						$mdl = $aft['model'];
						$mdlId = $this->schema[$mdl]['primary'][0];
						$fld = $aft['params']['foreignkey'];
						switch($aft['relType']) {
							case 'belongsToMany':
							case 'hasMany':
								if(isset($aft['params']['concatIds']) && $aft['params']['concatIds']!="") {
									$values = explode($aft['params']['concatIds'], preg_replace('/^'.$aft['params']['concatIds'].'|'.$aft['params']['concatIds'].'$/','',$result[$mainModel.".".$fld]));
								} else {
									$mdlId = $fld;
									$values = $r[$mainModel.'.'.$this->schema[$mainModel]['primary'][0]];
								}
								break;
							
						}
						
						$r[$alias] = $this->find($mdl, array(
											'conditions'=>array(
												$mdlId=>$values,
											),
											'alias'=>array(
												$mdl=>$params['alias'][$alias]
											),
											'orderBy'=>isset( $params['orderBy'] )? $params['orderBy']:array()
										),$typeSortie);
					}
				}
			}
			$this->reorganise($result);
			return $result;
		} catch(PDOException $e) {
			throw $e; 
		}
		
	}
	
	public function findFirst($mainModel, $params = null, $typeSortie = 'FETCH_ASSOC') {
		
		$req = $this->query( $mainModel , $params , 'first');
		
		$sql = $req['sql'];
		$a_cnds = $req['conditions'];
		$afterFind = $req['afterFind'];

		try {
			$db = $this->getConnexion();
			$req = $db->prepare($sql);
			$req->execute($a_cnds);
			
			switch($typeSortie) {
				case 'FETCH_OBJ':
					$result = $req->fetch(\PDO::FETCH_OBJ);
					break;
				default:
				$result = $req->fetch(\PDO::FETCH_ASSOC);
			}
			
			$db = null;

			if(count($afterFind)!=0 && $result!==false) {
				foreach($afterFind as $alias=>$aft){
					$mdl = $aft['model'];
					$mdlId = $this->schema[$mdl]['primary'][0];
					$fld = $aft['params']['foreignkey'];
					switch($aft['relType']) {
						case 'belongsToMany':
						case 'hasMany':
							if(isset($aft['params']['concatIds']) && $aft['params']['concatIds']!="") {
								$values = explode($aft['params']['concatIds'], preg_replace('/^'.$aft['params']['concatIds'].'|'.$aft['params']['concatIds'].'$/','',$result[$mainModel.".".$fld]));
							} else {
								$mdlId = $fld;
								$values = $result[$mainModel.'.'.$this->schema[$mainModel]['primary'][0]];
							}
							break;
						
					}
					
					$result[$alias] = $this->find($mdl, array(
										'conditions'=>array(
											$mdlId=>$values,
										),
										'alias'=>array(
											$mdl=>$params['alias'][$alias]
										),
										'orderBy'=>isset( $params['orderBy'] )? $params['orderBy']:array()
									),$typeSortie);
				}
			}
			$this->reorganise($result , $mainModel );
			return $result;
		} catch(\PDOException $e) {
			throw $e;
		}
		
	}
	
	public function liste($mainModel, $params = null, $typeSortie = 'FETCH_ASSOC') {
		
		$req = $this->query($mainModel, $params );
		$sql = $req['sql'];
		$a_cnds = $req['conditions'];

		try {
			$db = $this->getConnexion();
			$req = $db->prepare($sql);
			$req->execute($a_cnds);
			switch($typeSortie) {
				case 'FETCH_OBJ':
					$result = $req->fetchAll(\PDO::FETCH_OBJ);
					break;
				default:
				$result = $req->fetchAll(\PDO::FETCH_ASSOC);
				$this->reorganise($result , $mainModel );
			}
			$db = null;
			return $result;
		} catch(PDOException $e) {
			throw $e; 
		}
		
	}
	
	public function execQuery($sql, $typeSortie = 'FETCH_ASSOC') {
		
		try {
			$db = $this->getConnexion();
			$req = $db->prepare($sql);
			$req->execute();
			switch($typeSortie) {
				case 'FETCH_OBJ':
					$result = $req->fetchAll(\PDO::FETCH_OBJ);
					break;
				default:
				$result = $req->fetchAll(\PDO::FETCH_ASSOC);
				$this->reorganise($result);
			}
			$db = null;
		} catch(PDOException $e) {
			throw $e; 
		}
		
	}
	
	public function query($mainModel , $params = null , $type="all" ) {
		
		$fields = "";
		$table = "";
		$jointures = "";
		$groupements = "";
		$conditions = "";
		$ordreBy = "";
		$limits = "first" == $type?  " LIMIT 0, 1" : "";
		
		$Model = $this->schema[$mainModel];
		$table = "`".$Model['table']."` AS `".$mainModel."`";

		if( is_string( $params ) ) {
			$type = $params;
			$params = null;
		}

		if( null == $params ) $params = array();

		if( ! isset( $params [ 'alias' ] ) ) {
			$params['alias'] = array($mainModel=>array_keys($Model['fields']));
		}

		if( ! isset( $params[ 'alias' ][ $mainModel ] ) ) {
			$params['alias'][$mainModel] = $Model['primary'];
		}

		if(isset($params['conditions'])) {
			$n = 1;
			foreach($params['conditions'] as $k=>$v) {
				
				if(preg_match('/^([_A-Za-z0-9]+)\.([_A-Za-z0-9]+)(\s*|\s+[<>=_A-Za-z0-9]+)/', $k, $regs)){
					$mdl = $regs[1];
					$fld = $regs[2];
					$opr = $regs[3]==""? "=":$regs[3];
				}elseif(preg_match('/^([_A-Za-z0-9]+)\s+([<>=_A-Za-z0-9]+)\s*/', $k, $regs)){
					$mdl = $mainModel;
					$fld = $regs[1];
					$opr = $regs[2];
				/*}elseif(preg_match('#^\s*([_A-Za-z0-9]+)\s*\(\s*([_A-Za-z0-9]+)\s*\)\s*([<>=_A-Za-z0-9]+)\s*#', $k, $regs)){
					if(preg_match('/^([_A-Za-z0-9]+)\.([_A-Za-z0-9]+)/', $regs[1] , $sous_regs)){
						$mdl = $sous_regs[1];
						$fld = $regs[1]."(".$.")";
					} else {
						$mdl = $mainModel;
						$fld = $regs[2];
					}
					
					$opr = $regs[3]==""? "=":$regs[3];*/
				}else{
					$mdl = $mainModel;
					$fld = trim($k);
					$opr = "=";	
				}

				
				if($conditions !="") $conditions .= " AND ";
				
				if(is_numeric($k)) {
					$conditions .= $v;
				} else {
					if(is_array($v)){
						if(count($v)>=1) {
							$value = join(",",$v);
							$opr = "IN";
							$conditions .= $mdl.".".$fld." $opr (".$value.")";
						} else {
							$conditions .= $mdl.".".$fld."<>".$mdl.".".$fld;
						}
					} else {
						$hashed = ( isset($this->schema[$mdl]['fields'][$fld]) && is_array($this->schema[$mdl]['fields'][$fld]) && isset($this->schema[$mdl]['fields'][$fld]['hashed']) ) ? $this->schema[$mdl]['fields'][$fld]['hashed'] : "";
						if($hashed!=""){
							$value = md5( $v . $hashed );
						} else {
							$value = $v;
						}
						$conditions .= $mdl.".".$fld." $opr :param".$n;	
						$a_cnds["param".$n] = $value;
						$n ++;
					}
					
					
				}
			}
			
		}

		$patterns = array(
				'#^([_A-Za-z0-9]+)\s*\(\s*(\w+)\s*\)\s*AS\s*(\w+)$#si',
				'#^([_A-Za-z0-9]+)\s*\(\s*(\w+)\s*\)$#si',
				'#^(\w+)$#si'
			);
		
		foreach ($params['alias'] as $key => $value) {
			
			$alias = $key;
			/*
			if ( is_numeric( $key ) ) {

				if ( $value == $mainModel ) {
					$params['alias'][$value] = array_keys($Model['fields']);
				} else {
					$params['alias'][$value] = array_keys( $this->schema[ $Model['relations'][$value]['model'] ]['fields'] );
				}

				unset( $params['alias'][$key] );
				$alias = $value;
			}*/


			if( $alias == $mainModel ) {
				$primary_keys = $Model['primary'];
			} else {
				//$primary_keys = $this->schema[ $Model['relations'][$alias]['model'] ]['primary'];

			}
			
			//$params['alias'][$alias] = array_merge($params['alias'][$alias], array_diff($primary_keys, $params['alias'][$alias]));
			
			$fields .= ( "" != $fields )? ", " : "" ;

			$replacements = array(
				"$1(`".$alias."`.`$2`) AS `$3`",
				"$1(`".$alias."`.`$2`) AS `".$alias.".$1_$2`",
				"`".$alias."`.`$1` AS `".$alias.".$1`"
			);

			$nb_replacements = 0;
			
			if ( $alias == $mainModel ) {

				$fields .= implode(", ", 
								preg_replace(
									$patterns,
									$replacements,
									$params['alias'][$alias],
									1
								)
						);
			} else {
				/*
				$fields .= implode(", ", 
								preg_replace(
									$patterns,
									$replacements,
									$params['alias'][$alias],
									1,
									$nb_replacements
								)
						);*/
			}

		}

		//var_dump($nb_replacements);

		$sql = "SELECT ".$fields." FROM ".$table;//.$jointures.$groupements.$conditions.$ordreBy.$limits;
		if($conditions !="") $sql .= " WHERE ".$conditions;
		$sql .= $limits;

		return array('sql'=>$sql, 'conditions'=>$a_cnds , 'afterFind'=>array() );
	}
	
	public function query0($mainModel, $params = null, $type = 'all') {
		
		$fields = "";
		$tables = "";
		$conditions = "";
		$a_cnds = array();
		$afterFind = array();
		
		$mainTable = $this->schema[$mainModel]['table'];
		$mainId = $this->schema[$mainModel]['primary'][0];
		
		if(isset($params['alias'])):
			
			$tables .= " `" . $mainTable . "` AS `" . $mainModel . "`";
			$relations = $this->schema[$mainModel]['relations'];
			
			foreach($params['alias'] as $k=>$v):
				if($k == $mainModel) {
					foreach($v as $c) {
						$fields .= ", `" . $k . "`.`" . $c . "` AS `" . $k . "." . $c . "`";
					}
				} else {
					$rel = $relations[$k];
					$relType = $rel['relation'];
					$relModel = $rel['model'];
					$relParams = $rel['params'];
					
					switch($relType):
						case 'belongsTo':
						case 'hasOne':
							foreach($v as $c) {
								$fields .= ", `" . $k . "`.`" . $c . "` AS `" . $k . "." . $c . "`";
							}
							$tables .= " LEFT JOIN `" . $this->schema[$relModel]['table'] . "` AS `" . $k
									. "` ON `" . $mainModel . "`.`" . $relParams['foreignkey']."` = `".$k."`.`".$mainId."`  ";
							break;
						case 'belongsToMany':
						case 'hasMany':
							if(isset($relParams['concatIds']) && $relParams['concatIds']!="") {
								$fields .= ", `".$mainModel."`.`".$relParams['foreignkey'] . "` AS `" . $mainModel . "." . $relParams['foreignkey'] . "`";							
							}
							$afterFind[$k] = array("relType"=>$relType,"model"=>$relModel, "params"=>$relParams);
							break;
					endswitch;
					
				}
			endforeach;
			
		endif;
		
		if(isset($params['conditions'])) {
			$n = 1;
			foreach($params['conditions'] as $k=>$v) {
				
				if(preg_match('/^([_A-Za-z0-9]+)\.([_A-Za-z0-9]+)(\s*|\s+[<>=_A-Za-z0-9]+)/', $k, $regs)){
					$mdl = $regs[1];
					$fld = $regs[2];
					$opr = $regs[3]==""? "=":$regs[3];
				}elseif(preg_match('/^([_A-Za-z0-9]+)\s+([_A-Za-z0-9]+)\s*/', $k, $regs)){
					$mdl = $mainModel;
					$fld = $regs[1];
					$opr = $regs[2];
				}else{
					$mdl = $mainModel;
					$fld = trim($k);
					$opr = "=";	
				}
				
				if($conditions !="") $conditions .= " AND ";
				
				if(is_numeric($k)) {
					$conditions .= $v;
				} else {
					if(is_array($v)){
						if(count($v)>=1) {
							$value = join(",",$v);
							$opr = "IN";
							$conditions .= $mdl.".".$fld." $opr (".$value.")";
						} else {
							$conditions .= $mdl.".".$fld."<>".$mdl.".".$fld;
						}
					} else {
						$hashed = $this->schema[$mdl]['fields'][$fld]['hashed'];
						if($hashed!=""){
							$value = hash('md5', $v.$hashed);
						} else {
							$value = $v;
						}
						$conditions .= $mdl.".".$fld." $opr :param".$n;	
						$a_cnds["param".$n] = $value;
						$n ++;
					}
					
					
				}
			}
			
		}
		
		$fields = preg_replace('/^\s*,/i','',$fields);
		$sql = "SELECT ".$fields." FROM ".$tables;
		if($conditions != "") $sql .= " WHERE ".$conditions;
		
		if(isset($params['orderBy']) && array_key_exists( $mainModel , $params['orderBy'] ) ) {
			$sql .= " ORDER BY ". $params['orderBy'][ $mainModel ];
		}

		if(isset($params['limits']) && isset( $params['limits'][$mainModel] ) ) {
			$sql .= " LIMIT ". $params['limit'][$mainModel];
		} elseif($type=='first') {
			$sql .= " LIMIT 0 , 1" ;
		}
		
		$this->Session->push('SQL.Queries', array('sql'=>$sql, 'conditions'=>$a_cnds, 'afterFind'=>$afterFind));
		return array('sql'=>$sql, 'conditions'=>$a_cnds, 'afterFind'=>$afterFind);
		
	}
	
	public function persist( $mainModel , &$data , $partialSave = true , $relations_keys = null ) {

		if( !array_key_exists( $mainModel ,  $this->schema ) ) return false;

		$ids = array();

		$actsAs = array( 'data' => true );
		if( isset( $this->schema[$mainModel]['actsAs'] )  ) {
			$actsAs = $this->schema[$mainModel]['actsAs'];
			if( !array_key_exists( 'data' , $actsAs ) ) {
				$actsAs = array( 'data' => true ) + $actsAs;
			}
		}
		
		if ( array_key_exists( $mainModel , $data ) ) {
			foreach ($actsAs as $key => $value) {
				if( false !== $value && method_exists( $this , 'persist'. ucfirst($key) ) ) {
					$ids[ $mainModel ] = $this->{ 'persist'. ucfirst($key) }( $mainModel , $data , $partialSave , $relations_keys , $value );
				}
			}
			
		} elseif ( is_array( $data ) ) {
		
			foreach ( $data as $key => $value) {
				if( is_numeric( $key ) ){
					if( !is_array( $ids[ $mainModel ] ) )
					$ids[ $key ] = $this->persist( $mainModel , $value , $partialSave , $relations_keys );
				}
			}
		}

		return $ids;
	}

	public function persistData( $mainModel , &$data , $partialSave = true , $relations_keys = null , $params = null ) {
		var_dump( 'persistData' );
		var_dump( $params );
		if( !isset( $this->schema[$mainModel] ) || !isset( $data[ $mainModel ] ) ) return false;

		$mainTable = $this->schema[$mainModel]['table'];
		$primaryId = $this->schema[$mainModel]['primary'];
		$fields = $this->schema[$mainModel]['fields'];

		$a_cnds = array();
		$sql = "";

		$insertId = false;

		if( isset( $data[ $mainModel ][ $primaryId ] ) && is_numeric( $data[ $mainModel ][ $primaryId ] ) && 0 != $data[ $mainModel ][ $primaryId ] ) {
			//UPDATE
			var_dump('UPDATE');

		} else {
			//INSERT
			$champs = "";
			$valeurs = "";
			foreach ($data[ $mainModel ] as $key => $value) {
				if( isset( $fields[ $key ] ) ) {
					if( !is_array( $fields[ $key ] ) || !isset( $fields[ $key ]['auto'] ) || true !== $fields[ $key ]['auto'] ){
						if( "" != $champs ) {
							$champs .=", ";
							$valeurs .=", ";
						}
						$champs .= "`".$key."`";
						$valeurs .= ":".$mainTable."_".$key;
						$a_cnds[ $mainTable."_".$key ] = $value;
					}
				}
			}
			if( "" != $champs ) {
				$sql = "INSERT INTO ".$mainTable." (".$champs.") VALUES (".$valeurs.")";
				try {
					$db = $this->getConnexion();
					$req = $db->prepare( $sql );
					$req->execute( $a_cnds );
					$insertId = $db->lastInsertId();
					$data[ $mainModel ][ $primaryId ] = $insertId;
					$db = null;
				} catch(PDOException $e) {
					throw $e;
				}
			}
		}
		return $insertId;
	}

	public function persistNetwork( $mainModel , &$data , $partialSave = true , $relations_keys = null , $params = null ) {
		var_dump( 'persistNetwork');
		var_dump( $params );
		if( !isset( $this->schema[$mainModel] ) || !isset( $data[ $mainModel ] ) ) return false;

		$mainTable = $this->schema[$mainModel]['table'];
		$primaryId = $this->schema[$mainModel]['primary'];
		$fields = $this->schema[$mainModel]['fields'];

		$a_cnds = array();
		$sql = "";

		$insertId = false;

		if( isset( $data[ $mainModel ][ $primaryId ] ) && is_numeric( $data[ $mainModel ][ $primaryId ] ) && 0 != $data[ $mainModel ][ $primaryId ] ) {
			//UPDATE
			var_dump('UPDATE');
			$sets = "";
			foreach ($data[ $mainModel ] as $key => $value) {

				if( isset( $fields[ $key ] ) ) {
					var_dump( $key ."**". $primaryId );
					if( $key != $primaryId ) {
						
						if( array_key_exists( $key , $params ) ) {
							var_dump( $value );
							if( preg_match('#^;([0-9]+;)*(\|;([0-9]+;)*)?$#si', $value ) ) {
								var_dump( "ok" );
							} else {
								var_dump( "no" );
							}
						}

						if( "" != $sets ) {
							$sets .=", ";
						}
						$sets .= "`".$key."` = :".$mainTable."_".$key;
					}
					$a_cnds[ $mainTable."_".$key ] = $value;
				}

			}

			if( "" != $sets ) {
				$sql = "UPDATE ".$mainTable." SET ".$sets." WHERE `".$primaryId."` = :".$mainTable."_".$primaryId;
				try {
					$db = $this->getConnexion();
					$req = $db->prepare( $sql );
					$req->execute( $a_cnds );
					$insertId = $data[ $mainModel ][ $primaryId ];
					$db = null;
				} catch(PDOException $e) {
					throw $e;
				}
			}

		} else {
			//PRE-INSERT
			if( isset( $data[ $mainModel ]['parents'] ) ) {
				$a_parents = explode( ";" , $data[ $mainModel ]['parents'] );
				//$a_parents = array_unique( $a_parents );
				$prts = $this->find( $mainModel , array(
								'conditions'=>array( 
									$mainModel.".".$primaryId => $a_parents 
								)
							) 
						);
				
			}
			//INSERT
			$champs = "";
			$valeurs = "";
			foreach ($data[ $mainModel ] as $key => $value) {
				if( isset( $fields[ $key ] ) ) {
					if( array_key_exists( $key , $params ) ) {
						var_dump( $value );
						if( preg_match("#([0-9]+;)*(|([0-9]+;)*)?#si", $value ) ) {
							var_dump( "ok" );
						} else {
							var_dump( "no" );
						}
					}
					if( !is_array( $fields[ $key ] ) || !isset( $fields[ $key ]['auto'] ) || true !== $fields[ $key ]['auto'] ){
						if( "" != $champs ) {
							$champs .=", ";
							$valeurs .=", ";
						}
						$champs .= "`".$key."`";
						$valeurs .= ":".$mainTable."_".$key;
						$a_cnds[ $mainTable."_".$key ] = $value;
					}
				}
			}
			if( "" != $champs ) {
				$sql = "INSERT INTO ".$mainTable." (".$champs.") VALUES (".$valeurs.")";
				try {
					$db = $this->getConnexion();
					$req = $db->prepare( $sql );
					$req->execute( $a_cnds );
					$insertId = $db->lastInsertId();
					$data[ $primaryId ] = $insertId;
					$db = null;
				} catch(PDOException $e) {
					throw $e;
				}
			}
		}
		var_dump( $sql );
		return $insertId;
	}

	public function persistTree( $mainModel , &$data , $partialSave = true , $relations_keys = null , $params = null ) {
		var_dump( 'persistTree' );
	}


	/*
	public function persist( $data , $relations_keys = null , $mainModel = null ) {
		
		if( isset( $data[0] ) && is_array( $data[0] ) ) {
			$ids = array();
			foreach ($data as $i => $value) {
				if ( is_numeric( $i ) ) {
					$value += ( null != $relations_keys) ? $relations_keys : array();
					$ids[] = $this->persist( array( $mainModel => $value ) );
				}
			}

			$mainTable = $this->schema[$mainModel]['table'];
			$mainId = $this->schema[$mainModel]['primary'][0];
			
			$where = "";
			$a_cnd = array();

			if ( null != $relations_keys ) {
				foreach ($relations_keys as $k => $v) {
					if ( "" != $where ) $where .=" AND ";
					$where .="`".$k."` =:".$k;
					$a_cnd[$k] = $v;
				}
			}
			
			if ( count($ids) > 0 ) {
				$in = "";
				foreach ($ids as $i ) {
					if ( "" != $in ) $in .=", ";
					$in .= $i[$mainModel][0];
				}

				if ( "" != $where ) $where .=" AND ";
				$where .= "`".$mainId."` NOT IN(".$in.")";
			}

			$sql = "DELETE FROM ".$mainTable." WHERE ".$where;
			
			try {
				
				$db = $this->getConnexion();
				$req = $db->prepare($sql);
				$req->execute($a_cnd);
				$db = null;

			} catch(PDOException $e) {
				throw $e;
			}

			return $ids;
		}

		$ids = array();
		$relations = array();
		foreach ($data as $alias => $values ) {
			
			if ( !is_array( $values ) ) {
				continue;
			}

			$values += ( null != $relations_keys) ? $relations_keys : array();

			$sql = "";
			$fields = "";
			$valeurs = "";
			$sets = "";
			$where = "";
			$a_cnd = array();

			$mainTable = $this->schema[$alias]['table'];
			$mainId = $this->schema[$alias]['primary'][0];

			foreach ($values as $key => $value) {
				if ( is_array( $value ) ) {
					$relations[ $key ] = $value;
				} else {
					
					if ( $mainId != $key ) {
						if( "" != $sets ) $sets .= ", ";
						$sets .= "`".$key."` =:".$key;
						if($fields!="") $fields .=", ";
						$fields .= "`".$key."`";
						
						if($valeurs!="") $valeurs .=", ";
						$valeurs .= ":".$key;
					}

					$a_cnd[$key] = $value;
					
				}	
			}

			$req_type = "insert";
			if ( array_key_exists( $mainId , $values ) ){
				//UPDATE
				$req_type = "update";
				if ( "" != $where ) {
					$where .=" AND ";
				}
				$where .="`".$mainId."` =:".$mainId;

				$sql = "UPDATE ".$mainTable." SET ".$sets." WHERE ".$where;

			} else {
				//INSERT
				$req_type = "insert";
				$sql = "INSERT INTO ".$mainTable." (".$fields.") VALUES (".$valeurs.")";
			}

			$ids[ $alias ] = array();
			
			try {
				
				$db = $this->getConnexion();
				$req = $db->prepare($sql);
				$req->execute($a_cnd);
				if ( "insert" == $req_type ) {
					$ids[ $alias ][0] = $db->lastInsertId();
				} else {
					$ids[ $alias ][0] = $values[ $mainId ];
				}
				
				$db = null;

			} catch(PDOException $e) {
				throw $e;
			}

			foreach ($relations as $key => $value) {
				
				if ( isset( $this->schema[$alias]['relations'] ) && isset( $this->schema[$alias]['relations'][ $key ] ) ) {
					if ( 'hasMany' == $this->schema[$alias]['relations'][ $key ][ 'relation' ] ) {

						$foreignkey = $this->schema[$alias]['relations'][ $key ][ 'params' ][ 'foreignkey' ];
						$theModel = $this->schema[$alias]['relations'][ $key ][ 'model' ];

						$relations_keys = array($foreignkey => $ids[ $alias ][0]);
						$ids[ $alias ][ $key ] = $this->persist( $value , $relations_keys , $theModel ); 
					}
				}

			}


			
		}

		return $ids;

	}
	//*/
	
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