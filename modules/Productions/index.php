<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$smarty->assign('action', $action);
$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="modules/Productions/inc/styles.css" />'."\n", ));


if ($User->Manager || $User->InduJobs){
	$Productions = new Productions($world);
	switch ($action) {
		case 'main':
			$smarty->display('file:['.ACTIVE_MODULE.']jobs.tpl');
		break;
		
		default:
			$smarty->display('file:['.ACTIVE_MODULE.']index.tpl');
		break;
	
	}
} else {
	switch ($action) {
		default:
			$smarty->display('notallow.tpl');
		break;
	}
}
?>