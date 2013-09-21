<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$smarty->assign( 'action', $action );

if ( isset($_POST['cid']) ) $_SESSION['cid'] = $_POST['cid'];
else if ( isset($_SESSION['cid']) ) $_POST['cid'] = $_SESSION['cid'];

if ($User->alts) {
	$users = array();
	$users[ $User->charID ] = $User->username;
	foreach ($User->alts as $var => $val) {
		$users[ $val['charID'] ] = $val['charName'];
	}
	$smarty->assign( 'users', $users );
	$smarty->assign( 'sel_char', $_POST['cid'] );
}
if ( isset($_POST['cid']) && $_POST['cid'] != $User->charID ) {
	$smarty->assign('mask', (float)$User->alts[$_POST['cid']]['accessMask']);
	$charID = $_POST['cid'];
}
else {
	$smarty->assign('mask', (float)$User->accessMask);
	$charID = $User->charID;
}
$smarty->assign('sel_char', (isset($_POST['cid']) ? $_POST['cid'] : $User->charID));
$smarty->assign('list', $world->assessMask());
$smarty->assign('uName', $users[$charID]);

switch ($action) {
	case "accStatus":
		if ( $User->accessMask($charID, 'AccountStatus') ) {
			$accStatus = new accStatus( $User, $ale );
			$smarty->assign('accStatus', $accStatus->getStatus());
		}
		else
			$smarty->assign('error', array('AccountStatus'));
		break;
	
	case "eveMails":
		if ( $User->accessMask($charID, 'MailMessages', 'MailingLists', 'MailBodies') ) {
			$mails = new eveMail( $User, $ale );
			$smarty->assign( 'mails', $mails->messages );
			$smarty->assign( 'offset', $mails->offset );
		}
		else
			$smarty->assign('error', array('MailMessages', 'MailingLists', 'MailBodies'));
		break;
	
	case "eveAssets":
		if ( $User->accessMask($charID, 'AssetList') ) {
			$assets = new eveAssets( $User, $ale );
			$smarty->assign('content', $assets->getContent());
		}
		else
			$smarty->assign('error', array('AssetList'));
		break;
}

$smarty->display('file:['.ACTIVE_MODULE.']main.tpl');

?>