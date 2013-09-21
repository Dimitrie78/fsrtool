<?php
defined('FSR_BASE') or die('Restricted access');

switch($action)
{
	case 'addchar':
		header('Content-type: application/json');
		#echo json_encode($_POST);
		#echo $_POST['keyid'];
		try {
			$ale->setKey($_POST['keyid'],$_POST['vcode']);
			$APIKeyInfo = $ale->account->APIKeyInfo();
			echo json_encode($APIKeyInfo->toarray());
		} catch (Exception $e) {
			$err = array('error' => $e->getCode().' - '.$e->getMessage());
			echo json_encode($err);
		}
		
	break;
	
	case 'insert':
		header('Content-type: application/json');
		$jb = new jb($world);
		echo json_encode($jb->saveApi($_POST));
	break;
	
	default:
		$string = URL_INDEX ."?module=jb";
		header("Location: ".$string."\n");
		exit;
	break;
}

?>