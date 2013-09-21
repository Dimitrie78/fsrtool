<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$corps = $world->pos_getcorps();
if ( is_array( $corps ) ) {
	$corpids = array_keys($corps);
	$corpid  = $corpids[0];
}
if ( isset( $User->alts ) ) foreach ( $User->alts as $alt ) if ( $alt['pos_edit'] == 1 ) $editAlts[ $alt['corpID'] ] = true;


if ( $User->Manager || $corpid ){


	if ( $User->allyID != 0 || in_array( $_POST['corpid'], $corpids ) ) {
		if ( isset( $_POST['corpid'] ) ) 		 $_SESSION['corpID'] = $_POST['corpid'];
		#if ( !isset( $_SESSION['corpID'] ) ) $_SESSION['corpID'] = $User->corpID;
	}
	if ( !isset( $_SESSION['corpID'] ) ) $_SESSION['corpID'] = $User->corpID;
	if ( !in_array( $_SESSION['corpID'], $corpids ) ) $_SESSION['corpID'] = $corpid;
	
	if ( isset($editAlts[ $_SESSION['corpID'] ]) && $editAlts[ $_SESSION['corpID'] ] ) $editAlt = 1; else $editAlt = false;
	
	if (!$world->pos_getApiStatus()) {
		$smarty->assign('ApiStatus', 1);
	} else $smarty->assign('ApiStatus', false);
	
	if ( $action == 'settings' ) {
		if (!isset($editAlts[ $_SESSION['corpID'] ]) && ( !$User->PosEdit || $_SESSION['corpID'] != $User->corpID ) && !$User->Admin )
			$action = '';
	}
	
	if ( ($_SESSION['corpID'] == $User->corpID && $User->PosEdit) || $editAlt || $User->Admin )
		$smarty->assign('canEdit', 1);
	
	$smarty->assign("url_index_pos",  URL_INDEX .'?module='.ACTIVE_MODULE);
	$smarty->assign("url_dowork_pos", URL_DOWORK.'?module='.ACTIVE_MODULE);
	$smarty->assign("corps",          $corps);
	$smarty->assign("sel_corp",       $_SESSION['corpID']);
	$smarty->assign("action",         $action);

	switch ($action)
	{
		case "editPos":
			if ( isset($_REQUEST['id']) && $_REQUEST['id'] != '' ) {
				$pos = new Pos( $_REQUEST['id'], $world );
				$smarty->assign("Status",    $action);
				$smarty->assign("editpos",   $pos);
				$smarty->assign("sel_tower", $_REQUEST['id']);
				$smarty->assign("towers",    $world->pos_getTower(4,$_SESSION['corpID']));
				$smarty->display('file:['.ACTIVE_MODULE.']editpos.tpl');
			}
			else { 
				$smarty->assign("state",4);
				$smarty->assign("Status", 'online');
				$smarty->assign("poslist",$world->pos_get_possen(4,$_SESSION['corpID']));

				$smarty->display('file:['.ACTIVE_MODULE.']"Pos.tpl');
			}
		break;
		
		case "online":
		default:
			if ( $action == '' ) $action = 'online';
			$smarty->assign("state",4);
			$smarty->assign("Status", $action);
			if(isset($_REQUEST['sort']))	$smarty->assign("poslist",$world->pos_get_possen(4,$_SESSION['corpID'],$_REQUEST['sort']));
			else 							$smarty->assign("poslist",$world->pos_get_possen(4,$_SESSION['corpID']));
			$smarty->display('file:['.ACTIVE_MODULE.']Pos.tpl');
					
		break;
		
		case "offline":
			$smarty->assign("state",1);
			$smarty->assign("Status",$action);
			if(isset($_REQUEST['sort']))	$smarty->assign("poslist",$world->pos_get_possen(1,$_SESSION['corpID'],$_REQUEST['sort']));
			else 							$smarty->assign("poslist",$world->pos_get_possen(1,$_SESSION['corpID']));
			$smarty->display('file:['.ACTIVE_MODULE.']Pos.tpl');
					
		break;
		
		case "globalTower":
			$smarty->assign("state",4);
			$smarty->assign("Status",$action);
			if(isset($_REQUEST['sort']))	$smarty->assign("poslist",$world->pos_get_GlobalPossen(4,$_SESSION['corpID'],$_REQUEST['sort']));
			else 							$smarty->assign("poslist",$world->pos_get_GlobalPossen(4,$_SESSION['corpID']));
			$smarty->display('file:['.ACTIVE_MODULE.']GlobalTower.tpl');
		
		break;

		case "fuelBill":
			$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="modules/Pos/inc/styles.css" />'."\n",));
			
			$smarty->assign("Status",$action);
			$optregions = $world->pos_getRegions($_SESSION['corpID']);
			$optsystems = $world->pos_getSolarSystems($_SESSION['corpID']);
			$optconstel = $world->pos_getConstellations($_SESSION['corpID']);
								
			$regionID = isset($_POST['regionID']) ? $_POST['regionID'] : 0;
			$consteID = isset($_POST['consteID']) ? $_POST['consteID'] : 0;
			$systemID = isset($_POST['systemID']) ? $_POST['systemID'] : 0;
			
			if ($systemID != 0) {
				$regionID = 0;
				$consteID = 0;
			}
			if ($consteID != 0) {
				$regionID = 0;
			}
			$args['regionID'] = $regionID;
			$args['systemID'] = $systemID;
			$args['consteID'] = $consteID;
			$args['corpID']   = $_SESSION['corpID'];
			
			$args['use_current_level'] = isset($_POST['use_current_level']) ? $_POST['use_current_level'] : 1;
			
			$args['optimal'] = isset($_POST['optimal']) ? 1 : 0;
			
			$days_to_refuel = isset($_POST['days_to_refuel']) ? $_POST['days_to_refuel'] : 30;
			if (is_numeric($days_to_refuel) && !empty($days_to_refuel)) {
				$args['days_to_refuel'] = $days_to_refuel;
			}
			
			if ( isset($_POST['delFilter']) && isset($_POST['filter']) && $_POST['filter']!= 0 ) {
				$fil = $world->pos_getFuelFilter($_SESSION['corpID']);
				$res = $world->pos_delFuelFilter($_SESSION['corpID'], $fil['name'][$_POST['filter']]);
				if ($res) $Messages->addconfirm('Deleted');
			}
			
			if ( isset($_POST['saveF']) && is_array($_POST['pos_ids']) && !empty($_POST['saveF']) ) {
				$filter = serialize($_POST['pos_ids']);
				$res=$world->pos_setFuelFilter($_SESSION['corpID'], $_POST['saveF'], $filter);
				if ($res) $Messages->addconfirm('Saved');
				else $Messages->addwarning('Duplicate entry');
			}
			
			$xxx = $world->pos_getFuelFilter($_SESSION['corpID']);
			//echo '<pre>'; print_r($xxx); echo '</pre>';
			
			if (isset($_POST['filter']) && $_POST['filter']!=0) $pos_ids = $xxx['ids'][$_POST['filter']];
			else if(isset($_POST['pos_ids'])) $pos_ids = $_POST['pos_ids']; else $pos_ids = array();
			
			if (is_array($pos_ids)) {
				$optposids = $pos_ids;
				$pos_ids = array_keys($pos_ids);
				$args['pos_ids'] = $pos_ids;
			}
			$args['negative_fuel'] = isset($_POST['negative_fuel']) ? 1 : 0;
			
			$optlevels = array(1 => 'Current Level - Yes', 0 => 'Current Level - No');
			
			//echo'<pre>';print_r($args);echo'</pre>';
			if(!isset($_POST['corpSAG'])) $_POST['corpSAG'] = false;
			$world->pos_fuelBill($args, $_POST['corpSAG']);
			
			$smarty->assign('fuel_filter',		  $xxx['name']);
			$smarty->assign('sel_fuel_filter',    isset($_POST['filter']) ? $_POST['filter'] : false);
			$smarty->assign('days_to_refuel',	  $args['days_to_refuel']);
			$smarty->assign('regionID',		  	  $args['regionID']);
			$smarty->assign('optregions',	  	  $optregions);
			$smarty->assign('systemID',		  	  $args['systemID']);
			$smarty->assign('optsystems',	  	  $optsystems);
			$smarty->assign('consteID',		 	  $args['consteID']);
			$smarty->assign('optconstellations',  $optconstel);
			$smarty->assign('days_to_refuel',	  $days_to_refuel);
			$smarty->assign('use_current_level',  $args['use_current_level']);
			$smarty->assign('optimal_fuel',  	  $args['optimal']);
			$smarty->assign('negative_fuel',  	  $args['negative_fuel']);
			$smarty->assign('optlevels',		  $optlevels);
			$smarty->assign('optposids',		  $optposids);
			$smarty->display('file:['.ACTIVE_MODULE.']fuelBill.tpl');
					
		break;
		
		case "test":
			$smarty->assign("Status",  $action);
			$smarty->assign("ApiUser", $world->pos_getUserAPI());
			$smarty->assign("log",     $world->get_logs());
			$smarty->display('file:['.ACTIVE_MODULE.']settings.tpl');
		break;
		
		case "settings":
		$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="modules/Pos/inc/styles.css" />'."\n",
									'<link rel="stylesheet" type="text/css" href="classes/jqry_plugins/msgbox/Styles/msgBoxLight.css" />'."\n",
									'<script type="text/javascript" src="classes/jqry_plugins/msgbox/Scripts/jquery.msgBox.js"></script>'."\n",
									'<script type="text/javascript" src="modules/Pos/inc/test.js"></script>'."\n",
								));
			$smarty->assign("Status",  $action);
			$smarty->assign("apis", $world->pos_getUserAPI());
			$smarty->assign("emails", $world->getPosEmails());
			$smarty->assign("log",     $world->get_logs());
			$smarty->display('file:['.ACTIVE_MODULE.']test.tpl');
		break;
		
		case "showCharSelection":
			$smarty->assign("Status",$action);
			$smarty->assign("charList",$_SESSION['charList']);
			$smarty->display('file:['.ACTIVE_MODULE.']showCharSelection.tpl');
		break;
		
		case "phpBB":
			$smarty->assign("Status",$action);
			$smarty->assign("poslist",$world->pos_get_possen(4,$_SESSION['corpID'],'regionName'));
			$smarty->display('file:['.ACTIVE_MODULE.']phpBB.tpl');
		break;
	}
} else {
	switch ($action)
	{
		default:
			$smarty->assign("state",4);
			$smarty->assign("Status",$action);
			if(isset($_REQUEST['sort']))	$smarty->assign("poslist",$world->pos_get_GlobalPossen(4, $User->corpID, $_REQUEST['sort']));
			else 							$smarty->assign("poslist",$world->pos_get_GlobalPossen(4, $User->corpID));
			$smarty->display('file:['.ACTIVE_MODULE.']GlobalTower.tpl');
		break;
	}
}
?>