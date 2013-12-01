<?php
/**
 * @package FSRTOOL
 */

if(!$installrunning) {header('Location: index.php');die();}


$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('step1.tpl');
?>