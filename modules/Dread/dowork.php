<?php
defined('ACTIVE_MODULE') or die( header("Location: ".URL_INDEX) );

switch ($action)
{
	case "back":
		if ( isset($_POST["id"]) && $_POST['rasse'] && $_POST['back']) {
			$world->dread_back($_POST["id"]);
			//$Messages->addconfirm("back");
		}
		$string = URL_INDEX."?module=Dread&action=ausgabe&rasse=".$_POST['rasse'];
		header("Location: ".$string);
		exit;
	break;
	
	case "ausgabe":
		if ( isset($_POST["id"]) && $_POST['rasse'] && $_POST['player']) {
			$world->dread_ausgabe($_POST['player'], $_POST["id"]);
			//$Messages->addconfirm("player");
		}
		$string = URL_INDEX."?module=Dread&action=ausgabe&rasse=".$_POST['rasse'];
		header("Location: ".$string);
		exit;
	break;
	
	case "search":
		if ( isset($_POST["search"]) && !empty($_POST["search"])) {
			$world->dread_skill_add($_POST['ship'],$_POST["search"],$_POST["level"]);
			//$Messages->addconfirm($_POST["level"]);
		}
		$string = URL_INDEX."?module=Dread&action=settings";
		header("Location: ".$string);
		exit;
	break;
	
	case "del":
		if ( isset($_REQUEST["skillid"]) && isset($_REQUEST["shipid"])) {
			$world->dread_skill_del($_REQUEST['shipid'],$_REQUEST["skillid"]);
			//$Messages->addconfirm($_POST["level"]);
		}
		$string = URL_INDEX."?module=Dread&action=settings";
		header("Location: ".$string);
		exit;
	break;
	
	case "adddread":
		if ( isset($_POST['dread']) && is_array($_POST['dread']) ) {
			$result = $world->dread_add_dread($_POST['dread']);
			//$Messages->addconfirm('jo it is');
		}
		$string = URL_INDEX."?module=Dread&action=settings";
		header("Location: ".$string);
		exit;
	break;
	
	case "editdread":
		if ( isset($_POST['dread']) && is_array($_POST['dread']) ) {
			if ( !empty($_POST['dread']['time']) && is_int(strtotime($_POST['dread']['time'])) || empty($_POST['dread']['time']) ) {
				//$Messages->addconfirm('time format ist ok ('.strtotime($_POST['dread']['time']).')');
			} else {
				$Messages->addwarning('time format ist wrong');
			} //echo '<pre>'; print_r($_POST); echo '</pre>';
			$result = $world->dread_editDread($_POST['dread']);
			//$Messages->addconfirm($result);
		}
		$string = URL_INDEX."?module=Dread";
		header("Location: ".$string);
		exit;
	break;
	
	case "editdeaddread":
		if ( isset($_POST['dread']) && is_array($_POST['dread']) ) {
			
			$result = $world->dread_editDeadDread($_POST['dread']);
			//$Messages->addconfirm($result);
		}
		$string = URL_INDEX."?module=Dread&action=tot";
		header("Location: ".$string);
		exit;
	break;
	
	case "addort":
		if ( isset($_POST['ort']) && !empty($_POST['ort']) ){
			$result = $world->dread_addStandort($_POST['ort']);
			//$Messages->addconfirm($result);
		}
		$string = URL_INDEX."?module=Dread&action=settings";
		header("Location: ".$string);
		exit;
	break;
	
	case "delort":
		if ( isset($_REQUEST['ort']) && !empty($_REQUEST['ort']) ) {
			$result = $world->dread_delStandort($_REQUEST['ort']);
			//$Messages->addconfirm($result);
		}
		$string = URL_INDEX."?module=Dread&action=settings";
		header("Location: ".$string);
		exit;
	break;
	
	case "ajaxSearch":
		echo $world->ajaxSearch();
		exit;
	break;
	
}

?>