<?php
/**
 * @package EDK
 */

if(!$installrunning)
{
	header('Location: index.php');
	die();
}
$stoppage = true;
global $smarty;

$db = mysql_connect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
mysql_select_db($_SESSION['sql']['db']);

if (!$_SESSION['update']) $_SESSION['update'] = array();

$parms = array(
		'main' => array('host' => 'https://api.eveonline.com/'),
		'cache' => array('class' => 'Dummy'),
		'request' => array('class' => 'Curl'));
$ale = AleFactory::getEVEOnline($parms);

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'sov') {
	Sovereignty();
	$_SESSION['update'][0] = $_REQUEST['do'];
}
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'conq') {
	ConquerableStationList();
	$_SESSION['update'][1] = $_REQUEST['do'];
}
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'ref') {
	RefTypes();
	$_SESSION['update'][2] = $_REQUEST['do'];
}
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'ally') {
	AllianceList();
	$_SESSION['update'][3] = $_REQUEST['do'];
}
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'call') {
	CallList();
	$_SESSION['update'][4] = $_REQUEST['do'];
}

if( count($_SESSION['update']) == 5 ) $stoppage = false;

$smarty->assign('stoppage', $stoppage);
$smarty->assign('do', $_REQUEST['do']);
$smarty->assign('update', $_SESSION['update']);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step5.tpl');


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
	$smarty->assign('error', $error);
	$smarty->assign('errors', $errors);
}

?>