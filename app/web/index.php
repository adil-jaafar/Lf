<?php
/*
namespace lf;
ini_set('memory_limit', '-1');
*/

	session_start();
	
	if (isset($_GET['url']) && $_GET['url'] === 'favicon.ico') {
		return;
	} else {

		if ( !defined('DS') ) define('DS', DIRECTORY_SEPARATOR);
		
		if ( !defined('APP_DIR') ) {

			define('APP_DIR', basename(dirname(dirname(__FILE__))));
			define('VIEWS_DIR', 'partials');
			define('LF_DIR', 'lf' );
			define('WEB_DIR', basename(dirname(__FILE__)));
			define('CONFIG_DIR', 'config' );
			define('CONFIG_FILE', 'config.php');

			define('DEFAULT_LAYOUT', 'layouts/default' );

			define('ROOT', dirname(dirname(dirname(__FILE__))));
			define('LF_PATH', ROOT . DS . LF_DIR );

			define('APP_PATH', ROOT . DS . APP_DIR );
			define('WEB_PATH', APP_PATH . DS . WEB_DIR );
			define('VIEWS_PATH', APP_PATH . DS . VIEWS_DIR );

			define('DEFAULT_CONTROLLER', 'pages');
			define('DEFAULT_ACTION', 'index');
			define('CONFIG_PATH', APP_PATH . DS . CONFIG_DIR );
			define('CONFIG_FILE_PATH', CONFIG_PATH . DS . CONFIG_FILE );
			
		}

		if ( !defined('SITE_ADDRESS') ) {
			// protocol.host
		    if(isset($_SERVER['HTTPS']) && ("on" == $_SERVER['HTTPS'])) {
		        $SITE_ADDRESS = "https://".$_SERVER['HTTP_HOST'];
		    } else {
		        $SITE_ADDRESS = "http://".$_SERVER['HTTP_HOST'];
		    }
		    
		    // path
		    $path = dirname($_SERVER['SCRIPT_NAME']);
		    if("/" != $path) {
		        $SITE_ADDRESS .= $path;
		    }

    		$SITE_ADDRESS = preg_replace("#/".APP_DIR."/".WEB_DIR."$#", "", $SITE_ADDRESS);
		    define( 'SITE_ADDRESS' , $SITE_ADDRESS );
		}
		
		if( file_exists( CONFIG_FILE_PATH ) ) {
			include_once CONFIG_FILE_PATH;
		}
		
		$fct_out = array(
			'f_none'=> function($result) {},
			'f_json'=> function($result) {
					echo ")]}',\n"; //Anti-injection json
					if($result == "" || $result == null) {
						echo "{}";
					} else {
						echo json_encode($result);
					}
			}
		);

		include_once LF_PATH . DS .'Core'.DS.'Error.php';
				
		$lf = new lf\Core\LF($fct_out, array('Auth'=> array('redirect' => array(
			'controller' => 'pages',
			'action' => 'index'
		))));
		
		$lf->run();
	}
