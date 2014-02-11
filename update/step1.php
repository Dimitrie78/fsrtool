<?php
/**
 * @package FSRTOOL
 */

if(!$installrunning) {header('Location: index.php');die();}

$instver = $db->fetch_one("SELECT value FROM {$db->_table['fsrtool_config']} WHERE name='Version'", 'value');
if($instver == $version) $checkVer = true; else $checkVer = false;

$smarty->assign('instver', $instver);
$smarty->assign('version', $version);
$smarty->assign('checkVer', $checkVer);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('step1.tpl');
?>