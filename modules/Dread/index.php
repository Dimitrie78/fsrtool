<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$world->dread_check_versicherung();

$color = ReadConfigFile(MODULE_DIR . ACTIVE_MODULE . "/templates/color.cfg");
$smarty->assign("color", $color);
$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="modules/Dread/inc/styles.css" />'."\n",
));

$ships_array = array( "Moros" 		=> "Moros",
					  "Naglfar" 	=> "Naglfar",
					  "Revelation" 	=> "Revelation",
					  "Phoenix" 	=> "Phoenix");

$status_de = array("einsatzbereit" => "einsatzbereit",
				"verliehen" 	=> "verliehen",
				"not_ready" 	=> "not ready",
				"verstorben" 	=> "verstorben");

$status_en = array("einsatzbereit" => "ready",
				"verliehen" 	=> "awarded",
				"not_ready" 	=> "not ready",
				"verstorben" 	=> "departed");

$standort = $world->dread_get_standorte();
#echo '<pre>'; print_r($world->dread_get_ships()); die;

if ( $User->Manager || $User->DreadManager ) {

	switch ($action)
	{
		case "main":
		default:
			$smarty->assign("ships",      $world->dread_get_ships($_GET['sort']));
			$smarty->assign("vorhanden",  $world->dread_vorhanden());
			$smarty->display('file:['.ACTIVE_MODULE.']Main.tpl');
		break;
		
		case "ausgabe":
			$smarty->assign("ships",      $ships_array);
			if (isset($_REQUEST['rasse']) && $_REQUEST['rasse']) {
				$smarty->assign("mySelect", $_REQUEST['rasse']);
				$ships = $world->dread_get_dreadTyp($_REQUEST['rasse'],$_GET['sort']);
				$smarty->assign("shipTyp", $ships);
				$smarty->assign("canFly",  $world->dread_canFly($_REQUEST['rasse']));
			}
			$smarty->display('file:['.ACTIVE_MODULE.']raus.tpl');
		break;
		
		case "settings":
			$smarty->assign("url_dowork_search", URL_DOWORK."?module=Dread&amp;action=search");
			$smarty->assign("url_dowork_del", 	 URL_DOWORK."?module=Dread&amp;action=del");
			$smarty->assign("ships", 			 $ships_array);
			$smarty->assign("standort", 		 $standort);
			$smarty->assign("skills", 			 $world->dread_get_skills());
			$smarty->display('file:['.ACTIVE_MODULE.']settings.tpl');
					
		break;
		
		case "edit":
			$smarty->assign("dread", 	  $world->dread_getDreadByID($_REQUEST['id']));
			$smarty->assign("standort",   $standort);
			if ($_SESSION["chosenLanguage"] == 'DE')
				$smarty->assign("status", 	  $status_de);
			else
				$smarty->assign("status", 	  $status_en);
			$smarty->display('file:['.ACTIVE_MODULE.']edit.tpl');
		break;
		
		case "tot":
			$smarty->assign("ships", 	  $world->dread_get_deadDreads($_GET['sort']));
			$smarty->display('file:['.ACTIVE_MODULE.']friedhof.tpl');
		break;
		
		case "deadedit":
			$smarty->assign("dread", 	  $world->dread_getDreadByID($_REQUEST['id']));
			$smarty->display('file:['.ACTIVE_MODULE.']DeadEdit.tpl');
		break;
	}
} else {
	$smarty->display("notallow.tpl");
}
?>