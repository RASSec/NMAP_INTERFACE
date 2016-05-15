<?php
/**
 * Some general settings
 */
date_default_timezone_set('Europe/Belgrade');
set_time_limit(0);
ini_set("memory_limit","-1");
/**
 * Error reporting settings
 */
ini_set('display_errors', 'On');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
/**
 * Database related configuration
 */
define('DB_HOST',	    'localhost');
define('DB_USERNAME',	'root');
define('DB_PASSWORD', 	'root1234567890-');
define('DB_DATABASE', 	'nmap_data');
/**
 * Include the db class
 */
require dirname(__FILE__).'/lib/class.db.php';