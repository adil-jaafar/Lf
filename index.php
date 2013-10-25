<?php
//list($usec, $sec) = explode(' ', microtime());
//$t = ((float)$usec + (float)$sec);

	define('APP_DIR', 'app');
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', dirname(__FILE__) );
	define('WEB_DIR', 'web');
	define('WEB_PATH', ROOT . DS . APP_DIR . DS . WEB_DIR );
	define('VIEWS_DIR', 'partials');
	define('VIEWS_PATH', ROOT . DS . APP_DIR . DS . VIEWS_DIR );
	define('CONFIG_DIR', 'config' );
	define('CONFIG_FILE', 'config.php');

	define('DEFAULT_LAYOUT', 'layouts/default' );

	define('DEFAULT_CONTROLLER', 'pages');
	define('DEFAULT_ACTION', 'index');

	define('LF_DIR', ROOT);

	if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_PATH . DS . CONFIG_DIR );
	if (!defined('CONFIG_FILE_PATH')) define('CONFIG_FILE_PATH', CONFIG_PATH . DS . CONFIG_FILE );

	if( file_exists( CONFIG_FILE_PATH ) ) {
		include_once CONFIG_FILE_PATH;
	}

	// protocol.host
    if(isset($_SERVER['HTTPS']) && ("on" == $_SERVER['HTTPS']))
        $SITE_ADDRESS = "https://".$_SERVER['HTTP_HOST'];
    else
        $SITE_ADDRESS = "http://".$_SERVER['HTTP_HOST'];
    
    // path
    $path = dirname( $_SERVER['SCRIPT_NAME'] );
    if("/" != $path) {
        $SITE_ADDRESS .= $path;
    }
    $SITE_ADDRESS = preg_replace("#/".APP_DIR."/".WEB_DIR."$#", "", $SITE_ADDRESS);

    define( 'SITE_ADDRESS' , $SITE_ADDRESS );

    require WEB_PATH . DS . 'index.php';

//list($usec, $sec) = explode(' ', microtime());
//$t = ((float)$usec + (float)$sec) - $t;
//echo $t;