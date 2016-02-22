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

if(is_file(CONFIG)) {
	$c = Settings::getInstance(CONFIG);
	define('TBL_PREFIX', $c->dbprefix);
	define('TBL_EVEDB', $c->dbeve);
	define('PWSALT', $c->salt);
	define('EMAIL', $c->email);
}

$c->dbeve = substr($c->dbeve, 0, -1);

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

$cacheDir = FSR_BASE.DIRECTORY_SEPARATOR."cache";

$fileName = $cacheDir.DIRECTORY_SEPARATOR."mysql-latest.tar.bz2";

$fileURL = "https://www.fuzzwork.co.uk/dump/mysql-latest.tar.bz2";

$escape = escapeshellarg($fileURL);
exec("wget " . $escape . " -P " . $cacheDir);

$shellBefehl = "tar jxvf $fileName -C ".$cacheDir;
$shellBefehl = escapeshellcmd($shellBefehl);

exec($shellBefehl,$nu);
exec("rm ".$fileName);
#print_r($nu);
#$nu = array("yc118-2-116998/mysql56-yc118-2-116998.sql");
#echo $nu[0];

dbimport($cacheDir.DIRECTORY_SEPARATOR.$nu[0], $c);

function dbimport($mysqlImportFilename, $c) {
	$db = new mysqli( $c->dbhost, $c->dbuname, $c->dbpass, $c->dbeve );
	
	$str = "SHOW TABLES";
	$res = $db->query($str) or die('fail query');
	while($row = $res->fetch_array()){
		echo 'DROP TABLE '.$row[0];
		$db->query('DROP TABLE '.$row[0]);
		echo '<br>';
	}
	
	$command='mysql -h' .$c->dbhost .' -u' .$c->dbuname .' -p' .$c->dbpass .' ' .$c->dbeve .' < ' .$mysqlImportFilename;
	$output=array();
	exec($command,$output,$worked);
	switch($worked){
		case 0:
			echo 'Import file <b>' .$mysqlImportFilename .'</b> successfully imported to database <b>' .$c->dbeve .'</b><br>';
			
			$str = "SHOW TABLES";
			$res = $db->query($str) or die('fail query');
			while($row = $res->fetch_array()){
				echo 'RENAME TABLE '.$row[0].' TO '.strtolower($row[0]);
				$db->query('RENAME TABLE '.$row[0].' TO '.strtolower($row[0]));
				echo '<br>';
			}
			
			break;
		case 1:
			echo 'There was an error during import.';
			break;
	}
	
}


?>