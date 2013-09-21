<?php
@error_reporting( E_ALL ^ E_NOTICE );
@ini_set('display_errors', 1);
set_time_limit(0);
date_default_timezone_set('UTC');

if (!defined('FSR_BASE')) {
	define('FSR_BASE', dirname(dirname(__FILE__)));
}

require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes/settings.class.php');
require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes/db.conf.php');
require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes/ale/factory.php');
require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'inc/eveNotifications.class.php');

$parms = array('cache' => array(
	'class' 	=> 'MySQL',
	'host'		=> $c->dbhost,
	'user'		=> $c->dbuname,
	'password'	=> $c->dbpass,
	'database'	=> $c->dbname,
	'table' 	=> $c->dbprefix.'alecache'));
	
$ale = AleFactory::getEVEOnline($parms);

?>