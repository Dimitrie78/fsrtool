<?php
/**
 * @package FSRTOOL
 */

@error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', 1);
date_default_timezone_set('UTC');

if (!defined('FSR_BASE')) {
	define('FSR_BASE', dirname(dirname(__FILE__)));
}
require_once (FSR_BASE . DIRECTORY_SEPARATOR . "classes/class.php");

$db = new Database();

//May be a bit overkill to use smarty here, but this way the html is in the template
$smarty = new Smarty();
$smarty->setTemplateDir('./templates');
//as much as i don't want it, the compiled templates directory is needed
$smarty->setCompileDir('../cache/templates_c');


$installrunning = true;
session_start();

if (!isset($_SESSION['state'])) {
	$_SESSION['state'] = 1;
}
elseif (isset($_GET['step']) && $step = intval($_GET['step'])) {
	$_SESSION['state'] = $step;
}
elseif (!isset($_GET['step'])) {
	$_SESSION['state'] = 1;
}

//set the smarty stuff, and render
$smarty->assign('date', date("Y"));
$smarty->assign('stepnumber', $_SESSION['state']);
$smarty->assign('inst_locked', file_exists('install.lock'));
$smarty->display('index.tpl');

//won't load the page parts unless the lockfile's gone
if(!file_exists('install.lock')) {
	include('step'.$_SESSION['state'].'.php');
}

$smarty->display('index_bottom.tpl');
?>