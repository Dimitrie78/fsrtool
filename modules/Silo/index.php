<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="modules/Silo/inc/silo.css" />'."\n",
									'<script type="text/javascript" src="classes/jqry_plugins/jquery.ba-dotimeout.min.js"></script>'."\n",
									'<script type="text/javascript" src="classes/jqry_plugins/timer.js"></script>'."\n",
									'<script type="text/javascript" src="modules/Silo/inc/silo.js"></script>'."\n",
									'<script type="text/javascript" src="modules/Silo/inc/settings.js"></script>'."\n",
									'<link rel="stylesheet" type="text/css" href="classes/jqry_plugins/msgbox/Styles/msgBoxLight.css" />'."\n",
									'<script type="text/javascript" src="classes/jqry_plugins/msgbox/Scripts/jquery.msgBox.js"></script>'."\n",
));

$corps = $world->getcorps();
if (is_array($corps)) {
	$corpids = array_keys($corps);
	$corpid  = $corpids[0];
}
if ($User->Manager || $corpid){

	if ( $User->allyID != 0 || in_array( $_POST['corpid'], $corpids ) ) {
		if ( isset( $_POST['corpid'] ) ) 		 $_SESSION['corpID'] = $_POST['corpid'];
		#if ( !isset( $_SESSION['corpID'] ) ) $_SESSION['corpID'] = $User->corpID;
	}
	if ( !isset( $_SESSION['corpID'] ) ) $_SESSION['corpID'] = $User->corpID;
	if ( !in_array( $_SESSION['corpID'], $corpids ) ) $_SESSION['corpID'] = $corpid;
	
	if (!$world->pos_getApiStatus()) {
		$smarty->assign('ApiStatus', 1);
	} else $smarty->assign('ApiStatus', false);
	
	$corpID = $_SESSION['corpID'];
	$world->makeMenue($corpID);
	$smarty->assign("manager",   $world->getManager($corpID));
	$smarty->assign("corps",     $corps);
	$smarty->assign("sel_corp",  $_SESSION['corpID']);
	$smarty->assign('CacheTime', $world->getAssetsCacheTime(  $_SESSION['corpID'] ));
	
	
	if ( isset($_POST['auto']) ) {
		$loc = new Locations($corpID, $world);
		$loc->assignLocations();
	}
	
	if ( isset($_GET['reactors'])) {
		$ass = new Reactors($corpID, $world);
		$ass->assign($_GET['reactors']);
	}
	
	if ( isset($_GET['allreactors'])) {
		$ass = new Reactors($corpID, $world);
		$ass->assignAll($_GET['id']);
	}
	
	$silos  = new Silos($corpID, $world);
	
	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$man = isset($_GET['manager']) ? $_GET['manager'] : '';
	if((empty($id) && empty($man)) && $action =='system')
		$action = 'Silos';
	
	switch ($action) {
		# Hauptseite
		
		default:
		case 'Silos':
			if ( isset($_POST['moonID'], $_POST['itemID']) ) {
				$world->assignSilo($_POST['moonID'],$_POST['itemID']);
			}
			$unassigned = $world->getUnassignSilos($corpID);
			if($unassigned)
				$smarty->assign('Silos', $unassigned);
			$smarty->assign('MySelectetMenue', 'Silos');
			$smarty->assign('Towers', $silos->getSilosByAlarm());
			$smarty->assign('minTime', $silos->getMinTimeLeft());
			$smarty->display('file:['.ACTIVE_MODULE.']index.tpl');
		break;
	
		case 'system':
			if(!empty($id)) $assets = $silos->getSilosByLocation($id);
			if(!empty($man)) $assets = $silos->getSilosByManager($man);
			$smarty->assign('MySelectetMenue', $id);
			$smarty->assign('MySelectetManager', $man);
			$smarty->assign('Towers', $assets);
			$smarty->display('file:['.ACTIVE_MODULE.']system.tpl');
		break;
		
		case 'help':
			$smarty->assign('MySelectetMenue', 'Silos');
			$smarty->display('file:['.ACTIVE_MODULE.']help.'.$_SESSION["chosenLanguage"].'.tpl');
		break;
		
		case 'settings':
			$test = new test($corpID, $world);
			$smarty->assign('MySelectetMenue', 'Silos');
			$smarty->assign('pre', print_r($test->test(), true));
			$smarty->display('file:['.ACTIVE_MODULE.']settings.tpl');
		break;
		
		case 'calendar':
			$smarty->assign('MySelectetMenue', 'Silos');
			$smarty->display('file:['.ACTIVE_MODULE.']calendar.tpl');
		break;
	}

} else {
	switch ($action) {
		default:
			$smarty->display("notallow.tpl");
		break;
	}
}

?>
