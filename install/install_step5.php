<?php
/**
 * @package FSRTOOL
 */

if(!$installrunning)
{
	header('Location: index.php');
	die();
}
$stoppage = true;
global $smarty;

//start a new db connection with stored session info
$db = mysql_connect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
if (!$db) {
    die('Verbindung schlug fehl: ' . mysql_error());
}
mysql_select_db($_SESSION['sql']['db']);

if (!$_SESSION['update']) $_SESSION['update'] = array();

$parms = array(
		'main' => array('host' => 'https://api.eveonline.com/'),
		'cache' => array('class' => 'Dummy'),
		'request' => array('class' => 'Curl'));
$ale = AleFactory::getEVEOnline($parms);

//forget progress in insertion which should force it back to step4 as if it started fresh
if (!empty($_REQUEST['do']) && $_REQUEST['do'] == 'reset')
{
	unset($_SESSION['sqlinsert']);
	unset($_SESSION['doopt']);
}

$import = array('Sovereignty', 'ConquerableStationList', 'RefTypes', 'AllianceList', 'CallList');

//advance one screen in the data insertion process
if (!empty($_REQUEST['sub']) && $_REQUEST['sub'] == 'data')
{
	if (!isset($_SESSION['do']))
	{
		$_SESSION['do'] = 0;
	}

	$i = 0;
	$did = false;
	$errors = false;
	if ($_SESSION['do'] <= 4)
	{
		
		@mysql_query("ALTER DATABASE ".$_SESSION['sql']['db']." CHARACTER SET utf8 COLLATE utf8_general_ci");
		
		echo "Reading {$import[$_SESSION['do']]} from EvE-Api... Please wait...<br/>";
		
		$error = $import[$_SESSION['do']]();
		
		if (!$error)
		{
			echo '<br/>Finished importing '.$import[$_SESSION['do']].'.<br/>';
			$_SESSION['do']++;
			echo '<meta http-equiv="refresh" content="1; URL=?step=5&sub=data&do='.$_SESSION['do'].'" />';
			echo 'Automatic reload in 1s for next chunk. <a href="?step=5&amp;sub=data&amp;do='.$_SESSION['do'].'">Manual Link</a><br/>';
			
		}
		else
		{
			$_SESSION['do']++;
			echo $error;
			echo '<meta http-equiv="refresh" content="20; URL=?step=5&sub=data&do='.$_SESSION['do'].'" />';
			echo 'Automatic reload in 20s for next chunk because an error occurred. <a href="?step=5&amp;sub=data&amp;do='.$_SESSION['do'].'">Manual Link</a><br/>';
		}

	} else {
		echo '<br/>All api-data have passed.<br/>';
		echo '<a href="?step=6">Next Step --&gt;</a><br/>';
		unset($_SESSION['do']);
	}
	
}

//if not inserting data (sub = data)
if ((empty($_REQUEST['sub']) || $_REQUEST['sub'] != 'data'))
{
	echo 'Import additional data into database..<br/>';
	echo 'Please proceed with <a href="?step=5&amp;sub=data&amp;do=0">importing additional Data</a><br/>';
}

 
$smarty->assign('stoppage', $stoppage);
$smarty->assign('do', $_REQUEST['do']);
$smarty->assign('update', $_SESSION['update']);
$smarty->assign('nextstep', $_SESSION['state']+1);
#$smarty->display('install_step5.tpl');


function Sovereignty() {
	global $ale,$smarty;
	$error = '';
	$errors = false;
	try {
		$ale->setConfig('parserClass', 'SimpleXMLElement');
		$Sovereignty = $ale->map->Sovereignty();
		$query = ("REPLACE INTO fsrtool_api_sovereignty SET
						solarSystemID   = '%solarSystemID%',
						allianceID      = '%allianceID%',
						factionID       = '%factionID%',
						solarSystemName = '%solarSystemName%',
						corporationID   = '%corporationID%';");
		foreach ($Sovereignty->result->rowset->row as $row){
			$str = $query;
			foreach ($row->attributes() as $name => $value){
				$str = str_replace("%".(string) $name."%", addslashes((string) $value), $str);
			}
			$id = mysql_query($str);
			if (!$id) {
				$error .= 'error: '.mysql_error().'<br/>';
				$errors++;
			}
		}
		unset($Sovereignty);
	} catch (Exception $e) {
		$error .= 'error: '.$e->getMessage().'<br/>';
		$errors++;
	}
	return $error;
	$smarty->assign('error', $error);
	$smarty->assign('errors', $errors);
}

function ConquerableStationList() {
	global $ale,$smarty;
	$error = '';
	$errors = false;	
	
	$query = ("REPLACE INTO fsrtool_api_outposts SET
				stationID       = '%stationID%',
				stationName     = '%stationName%',
				stationTypeID   = '%stationTypeID%',
				solarSystemID   = '%solarSystemID%',
				corporationID   = '%corporationID%',
				corporationName = '%corporationName%';");
	try {
		$ale->setConfig('parserClass', 'SimpleXMLElement');
		$ConquerableStationList = $ale->eve->ConquerableStationList();
		
		foreach ($ConquerableStationList->result->rowset->row as $row){
			$str = $query;
			foreach ($row->attributes() as $name => $value){
				$str = str_replace("%".(string) $name."%", addslashes((string) $value), $str);
			}
			$id = mysql_query($str);
			if (!$id) {
				$error .= 'error: '.mysql_error().'<br/>';
				$errors++;
			}
		}
		unset($ConquerableStationList);
	} catch (Exception $e) {
		$error .= 'error: '.$e->getMessage().'<br/>';
		$errors++;
	}
	return $error;
	$smarty->assign('error', $error);
	$smarty->assign('errors', $errors);
}

function RefTypes() {
	global $ale,$smarty;
	$error = '';
	$errors = false;
	
	$query = ("REPLACE INTO fsrtool_api_reftypes SET
				refTypeID       = '%refTypeID%',
				refTypeName     = '%refTypeName%';");
	try {
		$ale->setConfig('parserClass', 'SimpleXMLElement');
		$RefTypes = $ale->eve->RefTypes();
		foreach ($RefTypes->result->rowset->row as $refType) {
			$str = $query;
			foreach ($refType->attributes() as $name => $value) {
				$str = str_replace("%".(string) $name."%", addslashes((string) $value), $str);
			}
			$id = mysql_query($str);
			if (!$id) {
				$error .= 'error: '.mysql_error().'<br/>';
				$errors++;
			}
		}
		unset($RefTypes);
	} catch (Exception $e) {
		$error .= 'error: '.$e->getMessage().'<br/>';
		$errors++;
	}
	return $error;
	$smarty->assign('error', $error);
	$smarty->assign('errors', $errors);
}

function AllianceList() {
	global $ale,$smarty;
	$error = '';
	$errors = false;
	$time = date("YmdHis");
	$query = ("REPLACE INTO fsrtool_allys SET
				id       = '%allianceID%',
				name     = '%name%',
				timestamp= '$time';");
	try {
		$ale->setConfig('parserClass', 'SimpleXMLElement');
		$AllianceList = $ale->eve->AllianceList();
		
		foreach ($AllianceList->result->rowset->row as $Ally) {
			$str = $query;
			foreach ($Ally->attributes() as $name => $value) {
				$str = str_replace("%".(string) $name."%", addslashes((string) $value), $str);
			}
			$id = mysql_query($str);
			if (!$id) {
				$error .= 'error: '.mysql_error().'<br/>';
				$errors++;
			}
		}
		unset($RefTypes);
	} catch (Exception $e) {
		$error .= 'error: '.$e->getMessage().'<br/>';
		$errors++;
	}
	return $error;
	$smarty->assign('error', $error);
	$smarty->assign('errors', $errors);
}

function CallList() {
	global $ale,$smarty;
	$error = '';
	$errors = false;
	
	try {
		$ale->setConfig('parserClass', 'AleParserXMLElement');
		$xml = $ale->api->calllist();
		#echo '<pre>'; print_r($xml); echo '</pre>'; die;
		foreach ( $xml->result->callGroups->toArray() as $call ) {
			foreach ( $call as $key => $val ) 
				$call[$key] = mysql_real_escape_string($val);
			$str = ("INSERT IGNORE INTO fsrtool_api_callgroups (". implode( ", ", array_keys($call) ) .") VALUES ('". implode( "', '", array_values($call) ) ."');");
			$id = mysql_query($str);
			if (!$id) {
				$error .= 'error: '.mysql_error().'<br/>';
				$errors++;
			}
		}
		
		foreach ( $xml->result->calls->toArray() as $call ) {
			if (isset($call['Character'])) {
				foreach ( $call['Character'] as $key => $val )
					$calle[$key] = mysql_real_escape_string($val);
				$str = ("INSERT IGNORE INTO fsrtool_api_calls (". implode( ", ", array_keys($calle) ) .") VALUES ('". implode( "', '", array_values($calle) ) ."');");
				$id = mysql_query($str);
				if (!$id) {
					$error .= 'error: '.mysql_error().'<br/>';
					$errors++;
				}
			}
			
			if (isset($call['Corporation'])) {
				foreach ( $call['Corporation'] as $key => $val )
					$calle[$key] = mysql_real_escape_string($val);
				$str = ("INSERT IGNORE INTO fsrtool_api_calls (". implode( ", ", array_keys($calle) ) .") VALUES ('". implode( "', '", array_values($calle) ) ."');");
				$id = mysql_query($str);
				if (!$id) {
					$error .= 'error: '.mysql_error().'<br/>';
					$errors++;
				}
			}	
		}
	} catch (Exception $e) {
		$error .= 'error: '.$e->getMessage().'<br/>';
		$errors++;
	}
	return $error;
	$smarty->assign('error', $error);
	$smarty->assign('errors', $errors);
}

?>