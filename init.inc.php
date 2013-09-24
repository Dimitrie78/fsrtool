<?php
defined('FSR_BASE') or die('Restricted access');

if (!defined('FSR_CONFIG_DIR')) {
	define('FSR_CONFIG_DIR', FSR_BASE . DIRECTORY_SEPARATOR . 'config');
}

require_once (FSR_BASE . DIRECTORY_SEPARATOR . "classes/class.php");
require_once (FSR_BASE . DIRECTORY_SEPARATOR . "config.inc.php");

$version = '1.0';

$smarty = loadSmarty();
//$smarty->testInstall();

init_session_objects();

$language = array();

$url_dowork 	 = URL_DOWORK . "?a=1";
$url_index 		 = URL_INDEX  . "?a=1";
$url_index_noSID = URL_INDEX;

$parms = array(
	'main' => array('host' => $c->alehost),
	'cache' => array(
		'class' 	=> 'MySQL',
		'host'		=> $c->dbhost,
		'user'		=> $c->dbuname,
		'password'	=> $c->dbpass,
		'database'	=> $c->dbname,
		'table' 	=> $c->dbprefix.'alecache'),
	'request' 	=> array('class' => $c->request));

//$Messages = &$_SESSION["messages"];
$Messages = new Messages();
$database = new Database($Messages);
$User 	  = new User($database);
$world    = new World($User);
$ale 	  = AleFactory::getEVEOnline($parms);
// DBSqlite::init();

if (isset($_REQUEST['language'])) {
	$User->setLang($_REQUEST['language']);
	$_SESSION["chosenLanguage"] = $_REQUEST['language'];
}
if ($User->lang) $_SESSION["chosenLanguage"] = $User->lang;
$language = ReadConfigFile(smarty_template."language.".$_SESSION["chosenLanguage"].".lang");

$smarty->assign("Messages",			$Messages);
$smarty->assign("url_index", 		URL_INDEX);
$smarty->assign("url_dowork",		URL_DOWORK);
$smarty->assign("url_logout",	 	URL_DOWORK."?action=logout");
$smarty->assign("language",			$language);
$smarty->assign("chosenLanguage", 	$_SESSION["chosenLanguage"]);

//$smarty->plugins_dir[] = 'classes/plugins';

$smarty->assign("curUser", $User);
$smarty->assign("SID", session_id());
$smarty->assign("path_dowork_login",URL_DOWORK."?action=login");

if ( $_SERVER['QUERY_STRING'] ) {
	$smarty->assign("request_url", URL_INDEX.'?'.urlencode($_SERVER['QUERY_STRING']));
}

if (isset ($_REQUEST["action"])) $action = $_REQUEST["action"]; else $action = "";

$langURL = URL_INDEX;
if ( isset( $action ) && !empty( $action ) && isset( $_REQUEST['module'] ) )
	$langURL .= "?action=".$action."&module=".$_REQUEST['module']."&amp;language";
else if ( isset( $action ) && !empty( $action ) )
	$langURL .= "?action=".$action."&amp;language";
else if ( isset( $_REQUEST['module'] ) )
	$langURL .= "?module=".$_REQUEST['module']."&amp;language";
else 
	$langURL .= "?language";
$smarty->assign("url_index_language",$langURL);

if ( $User->active == '0' && $User->charID != '' ) $Messages->addwarning($language['acc_inactive']);

if ( !isset($_SERVER['HTTP_EVE_TRUSTED']) ) $igb = "0"; else $igb = "1";

if ( isset($_SERVER['HTTP_EVE_CHARID']) ) {
	$smarty->assign("UserID", $_SERVER['HTTP_EVE_CHARID']);
}

if (!isset($_SERVER['HTTP_EVE_TRUSTED'])) {
	$smarty->assign("igb","0");
	$smarty->assign("charName",'');
} else {
	if ($_SERVER['HTTP_EVE_TRUSTED']!="Yes") {
		$body = " onLoad=\"CCPEVE.requestTrust('".CONFIG_URL."');\"";
		unset($_REQUEST['module']);
		$action = "trustMe";
		$smarty->assign("bodytrust", $body);
		$smarty->assign("igb","1");
	} else {
		$smarty->assign("charName",$_SERVER['HTTP_EVE_CHARNAME']);
		$smarty->assign("igb","1");
	}
}
$smarty->assign('action', $action);

if($User->Admin == 1) {
	if(!$world->versionCheck($version))
		$Messages->showerror('Sie müssen ein update ausführen um die Database änderungen zu übernehmen <a href="update">Link</a>');
}

$current_file_name = basename($_SERVER['REQUEST_URI'], ".php");

$currentFile = $_SERVER["SCRIPT_NAME"];
$parts = Explode('/', $currentFile);
$currentFile = $parts[count($parts) - 1];

if ( (isset($_REQUEST['module']) and $User->charID != '' and $User->active != '0') 
  or (isset($_REQUEST['module']) and $User->charID != '' and $User->active == '0' and $_REQUEST['module'] == 'Member')
  or (isset($_REQUEST['module']) and $User->charID != '' and $User->active == '0' and $User->Admin == '1') ) {
	
	define('ACTIVE_MODULE', $_REQUEST['module']);
	$smarty->assign("activeModule", $_REQUEST['module']);
	define('TPL_DIR', '.' . MODULE_DIR . ACTIVE_MODULE . '/templates/');
	if ( file_exists( MODULE_DIR . ACTIVE_MODULE . '/classes/class.php' ) ) {
		require_once ( MODULE_DIR . ACTIVE_MODULE . '/classes/class.php' );
		$NEW_language = ReadConfigFile(MODULE_DIR . ACTIVE_MODULE . '/templates/language.'.$_SESSION["chosenLanguage"].'.lang');
		$language 	  = array_merge($language, $NEW_language);
		$smarty->assign("language",   $language);
		$smarty->assign("index",      URL_INDEX .'?module='.ACTIVE_MODULE);
		$smarty->assign("dowork",     URL_DOWORK.'?module='.ACTIVE_MODULE);
		
		$moduleWorld = ACTIVE_MODULE.'World';
		$moduelDB	 = ACTIVE_MODULE.'DB';
		
		#$database = new $moduelDB( $Messages );
		$world = new $moduleWorld( $User );
		
		require_once("./modules/".$_REQUEST['module']."/".$currentFile);
	}
	else {
		$Messages->addwarning('Soory, work in progress...');
		unset( $_REQUEST['module'] );
	}
} else unset( $_REQUEST['module'] );


function loadSmarty() {
	$smarty = new Smarty();
	$smarty->setTemplateDir('templates');
	$smarty->setCompileDir('cache/templates_c');
	$smarty->setCacheDir('cache/cache');
	$smarty->setConfigDir('cache/configs');
	
	/* add Modules TemlatesDirs */
	foreach(getModules() as $name => $dirs) {
		$smarty->addTemplateDir($dirs, $name);
	}
	//---- Plugins handling -----//
	if (is_dir('classes/plugins')) {
		$smarty->addPluginsDir('classes/plugins');
	}
	return $smarty;
}

function getModules() {
	$array = array();
	if($handle = opendir(FSR_BASE."/modules/")) {
		while (false !== ($file = readdir($handle))) {
			if (($file != "..") AND ($file != ".")) {
				if (is_dir("./modules/".$file))	{
					$array[$file] = "./modules/$file/templates";
				}
			}
		}
		closedir($handle);
	}
	return $array;
}

function init_session_objects() {
	if( !isset( $_SESSION ) ) session_start();
	
	// Wenn Objekt noch nicht existiert, dann neu erzeigen
	/* if ((!isset ($_SESSION["messages"])) OR (!is_object($_SESSION["messages"])))
		$_SESSION['messages'] = new Messages(); */
	
	if ((!isset ($_SESSION["messages"])) OR (!is_array($_SESSION["messages"])))
		$_SESSION['messages'] = array();
	if ((!isset ($_SESSION["save"])) OR (!is_array($_SESSION["save"])))
		$_SESSION["save"] = array();
	if ((!isset ($_SESSION['modules'])) OR (!is_array($_SESSION['modules'])))
		$_SESSION['modules'] = array();
	if ((!isset ($_SESSION["chosenLanguage"])) OR ($_SESSION["chosenLanguage"] == ""))
		$_SESSION["chosenLanguage"] = "DE";
}
?>