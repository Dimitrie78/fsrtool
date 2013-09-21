<?php
defined('ACTIVE_MODULE') or die();

#$database = new MineralsDatabase();
#$world = new Minerals_world();
$miningbuddyDB = new MiningbuddyDB();

switch($action)
{
	case "update":
		
		if (isset($_POST["update"]))
		{
			$_POST['Minerals'] = preg_replace('/[^,0-9]/' ,'', $_POST['Minerals']);
			$_POST['Minerals'] = preg_replace('/,/', '.', $_POST['Minerals']);
			foreach ($_POST['Minerals'] as $mineralName => $mineralPrice)
			{
				$world->minerals_update_mineralprices($mineralPrice,$mineralName); // old script to update the old table (old table used for calculating the orevalues atm, will change soon[tm])
				$world->minerals_update_mineralprices_new($mineralPrice,$mineralName);
			}
			$miningbuddyDB->changeOreValues($_POST['Minerals'],$_POST['mb_id']);
		}
		$string = "Location: ".URL_INDEX .'?module='.ACTIVE_MODULE.'&action=MinsPreise';
		header($string);
		exit;
	break;
}

?>