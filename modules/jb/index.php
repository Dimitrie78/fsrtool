<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$smarty->assign('action', $action);
$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="modules/jb/inc/styles.css" />'."\n",
									'<script type="text/javascript" src="modules/jb/inc/jb.js"></script>'."\n",
								));


if ( $User->Manager ){
	$jb = new jb($world);
	switch ($action) {
		
		case "main":
			$smarty->display('file:['.ACTIVE_MODULE.']main.tpl');
		break;
		
		case "options":
			$smarty->assign('apis', $jb->getApis());
			$smarty->display('file:['.ACTIVE_MODULE.']options.tpl');
		break;
		
		case "api":
			$smarty->display('file:['.ACTIVE_MODULE.']api.tpl');
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