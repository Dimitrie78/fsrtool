<?php
/**
 * @package FSRTOOL
 */

if(!$installrunning) {header('Location: index.php');die();}

global $smarty;
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step1.tpl');
?>