#!/usr/bin/php
<?php
date_default_timezone_set('UTC');
set_time_limit(0);
error_reporting( E_ALL ^ E_NOTICE );
@ini_set('display_errors', 1);

if (!defined('FSR_BASE')) {
	define('FSR_BASE', dirname(dirname(__FILE__)));
	define('CONFIG', FSR_BASE . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR .'dbconfig.ini');
}

require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'settings.class.php');
require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'class.Pushover.php');
require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'database.class.php');
require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'ale'. DIRECTORY_SEPARATOR .'factory.php');
require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'mail'. DIRECTORY_SEPARATOR .'phpmailer.inc.php');
require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'inc'. DIRECTORY_SEPARATOR .'eveNotifications.class.php');
require_once ('cron.class.php');
require_once ('cron-user.class.php');
require_once ('class.httprequest.php');


if(is_file(CONFIG)) {
	$c = Settings::getInstance(CONFIG);
	define('TBL_PREFIX', $c->dbprefix);
	define('TBL_EVEDB', $c->dbeve);
	define('PWSALT', $c->salt);
	define('EMAIL', $c->email);
}

$parms = array(
	'main' => array('host' => $c->alehost),
	'cache' => array(
		'class' 	=> 'MySQL',
		'host'		=> $c->dbhost,
		'user'		=> $c->dbuname,
		'password'	=> $c->dbpass,
		'database'	=> $c->dbname,
		'table' 	=> $c->dbprefix.'alecache'),
	'request' 	=> array('class' => $c->request)
);

$cron = new cron($parms);

if ($cron->serverStatus()) {
	echo $cron->run();
} else {
	echo 'API Server down!';
}
//$cron->print_it($cron->queries);
//echo FSR_BASE;
//echo "\n";
?>