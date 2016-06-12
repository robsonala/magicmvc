<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

spl_autoload_register(
	function($className) {
    	if ( is_readable( ROOT_APP . DS . 'helpers' . DS . $className . '.php' ) )
    		require_once(ROOT_APP . DS . 'helpers' . DS . $className . '.php');
    	else if ( is_readable( ROOT_APP . DS . 'models' . DS . $className . '.php' ) )
    		require_once(ROOT_APP . DS . 'models' . DS . $className . '.php');
    	else if ( is_readable( ROOT_APP . DS . 'controllers' . DS . $className . '.php' ) )
    		require_once(ROOT_APP . DS . 'controllers' . DS . $className . '.php');
    }
);

require_once('config.php');