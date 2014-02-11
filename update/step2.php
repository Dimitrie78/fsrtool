<?php
/**
 * @package FSRTOOL
 */

if(!$installrunning) {header('Location: index.php');die();}

$checkVer = true;


$instver = $db->fetch_one("SELECT value FROM {$db->_table['fsrtool_config']} WHERE name='Version'", 'value');
$newver = $instver + 0.1;
if($newver <= $version) {
	$checkVer = false;
	$res = $db->exec_query("ALTER TABLE {$db->_table['eveorder_user_types']} ADD COLUMN `deleted` tinyint(3) NOT NULL DEFAULT 0;");
	$res = $db->exec_query("UPDATE {$db->_table['fsrtool_config']} SET value='{$newver}' WHERE name='Version'");
}

$instver = $db->fetch_one("SELECT value FROM {$db->_table['fsrtool_config']} WHERE name='Version'", 'value');
if($instver == $version) {
	$checkVer = true;
} else {
	$checkVer = false;
	$newver = $instver + 0.1;
}

$smarty->assign('instver', $instver);
$smarty->assign('version', $newver);
$smarty->assign('checkVer', $checkVer);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('step2.tpl');
?>