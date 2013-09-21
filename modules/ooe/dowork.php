<?php

defined('ACTIVE_MODULE') or die( header("Location: ".URL_INDEX) );


switch($action)
{
	case "eveMail":
		#require_once( './inc/eveMail.class.php' );
		$data = str_replace('id_', '', $_POST['messageID']);
		list ($messageID, $_POST['cid']) = preg_split('/_/', $data, 2); //preg_split
		$mails = new eveMail( $User, $ale );
		echo $mails->getMailBodies( $messageID );
		break;
	
}
?>