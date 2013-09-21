<?php
/**
 * @package EDK
 */

if(!$installrunning) {header('Location: index.php'); die(); }
$stoppage = false;
global $smarty;

//start a new db connection with stored session info
$db = mysql_connect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
if (!$db) {
    die('Verbindung schlug fehl: ' . mysql_error());
}
mysql_select_db($_SESSION['sql']['db']);

// Create  Admin Char
extract($_SESSION['sql']);
$salat=md5($host.$user.$pass.$db.'fsrtool_');
$pw=md5($salat.sha1('admin'.$salat));
$id = mysql_query("INSERT INTO fsrtool_user (charID, username, password, userIP) VALUES (1, 'admin', '{$pw}', '{$_SERVER['REMOTE_ADDR']}')");
if ($id)
{
	$next = mysql_query("INSERT INTO fsrtool_user_roles (charID, roleID) VALUES (1, 1)");
	if (!$next) $stoppage = true;
} else {
	$smarty->assign('mError', mysql_error().' - '.mysql_errno());
	$stoppage = true;
}

$smarty->assign('stoppage', $stoppage);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step6.tpl');
?>