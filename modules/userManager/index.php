<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="modules/userManager/inc/style.css" />'."\n",
									'<script type="text/javascript" src="classes/jqry_plugins/jquery.quicksearch.js"></script>'."\n",
									'<script type="text/javascript" src="modules/userManager/inc/user.js"></script>'."\n",
								));

if ( $User->Manager ) {	
	if ( $User->Admin )	$smarty->assign( 'corps', $world->corps );
	if( isset($_POST['corpID']) ) $corpID = $_SESSION['userManager']['corpID'] = $_POST['corpID'];
	else $corpID = $User->corpID;
	if( isset($_SESSION['userManager']['corpID']) ) $corpID = $_SESSION['userManager']['corpID'];
	$smarty->assign( 'selectedCorp', $corpID );
	
	switch ($action) {
		case "userList":
		default:
			$smarty->assign( 'users', $world->getUsers( $corpID ) );
		break;
		
		case "roleList":
			$smarty->assign( 'roles', $world->getUsers( $corpID, true ) );
		break;
		
		case "roleListAlts":
			$smarty->assign( 'roles', $world->getUsers( $corpID, false, true ) );
		break;
		
		case "cron":
			if ($User->Admin) {
				$smarty->assign( 'jobs', $world->getCronJobs() );
			} else {
				$action = '';
				$smarty->assign( 'users', $world->getUsers( $corpID ) );
			}
		break;
	}
	$smarty->assign('action', $action);
	$smarty->display('file:['.ACTIVE_MODULE.']main.tpl'); 
} else {
	header("Location: ".URL_INDEX."\n");
	exit;
}

?>