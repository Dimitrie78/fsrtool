<?php
/**
 * @package FSRTOOL
 */

if(!$installrunning) {header('Location: index.php');die();}

$checkVer = true;


$instver = $db->fetch_one("SELECT value FROM {$db->_table['fsrtool_config']} WHERE name='Version'", 'value');
$newver = $instver + 0.1;
if(strval($newver) == 1.2) {
	$checkVer = false;
	$res = $db->exec_query("DROP TABLE IF EXISTS {$db->_table['fsrtool_log']};");
	$res = $db->exec_query("
	CREATE TABLE `{$db->_table['fsrtool_log']}` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `logtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  `typeID` bigint(20) unsigned DEFAULT NULL,
	  `code` int(11) DEFAULT NULL,
	  `message` varchar(255) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;");
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