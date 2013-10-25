<?php

/**
* Gestion des Erreurs
*
* @author JAAFRA Adil jaafar.adil@gmail.com
*/

spl_autoload_register();
//----------------------------------------------------------
function lfServicesPath( $className ) {
	$className = ltrim($className, '\\');
	$fileName  = '';
	$namespace = '';
	if( 0 == strpos( $className , 'lf\\') ) $className = preg_replace("#^lf#", LF_PATH , $className);
	if( 0 == strpos( $className , 'app\\') ) $className = preg_replace("#^app#", APP_PATH , $className);
	if ($lastNsPos = strripos($className, '\\')) {
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}
	$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	return $fileName;
}

function autoload($className) {
	require lfServicesPath( $className );
}

spl_autoload_register('autoload');

//namespace Core;

class Error
{	
	public static function getFormatOut() {
			return function($result) {
				$result = array("error"=>$result);
				/*
				echo ")]}',\n";
				echo json_encode($result);
				//*/
				
				//*
				echo '<pre>';
				print_r($result);
				echo '</pre>';
				//*/
			};
	}
	
    // CATCHABLE ERRORS
    public static function captureNormal( $number, $message, $file, $line )
    {
        $error = array( 'type' => $number, 'message' => $message, 'file' => $file, 'line' => $line );  
		$f = self::getFormatOut();
		$f($error);
    }
    
    // EXCEPTIONS
    public static function captureException( $exception )
    {
		$error = array(
			'type' 		=> $exception->getCode(),
			'message' 	=> $exception->getMessage(),
			'file' 		=> $exception->getFile(),
			'line' 		=> $exception->getLine()
		);
        
		$f = self::getFormatOut();
		$f($error);
		
    }
    
	// UNCATCHABLE ERRORS
    public static function captureShutdown( )
    {
        $error = error_get_last( );
        if( $error ) {
            //ob_end_clean( );
			$f = self::getFormatOut();
			$f($error);
        } else { return true; }
    }
}

ini_set( 'display_errors', 0 );
error_reporting(E_ALL);
set_error_handler( array( 'Error', 'captureNormal' ) );
set_exception_handler( array( 'Error', 'captureException' ) );
register_shutdown_function( array( 'Error', 'captureShutdown' ) );
 
