<?php
defined('ACTIVE_MODULE') or die( header("Location: ".URL_INDEX) );

switch ($action)
{
		case "changeMyOrderStatus":
			$world->eveorder_updateOrder($_POST['id'],$_POST['status'],$User->charID,$_POST['comment'],$_POST['check'],$_POST['target']);
			$string = URL_INDEX ."?module=eveorder&action=myOrders";
			#print_r($_POST);die;
			header("Location: ".$string);
			exit;
		break;
		
		case "changeOrderStatus":
			$world->eveorder_updateOrder($_POST['id'],$_POST['status'],$User->charID,$_POST['comment'],$_POST['check'],$_POST['target']);
			$string = URL_INDEX ."?module=eveorder&action=openOrders&orderStatus=".$_REQUEST['orderStatus']."&corpid=".$_REQUEST['corpid'];
			//print_r($_POST['check']);//
			header("Location: ".$string);
			exit;
		break;
		
		case "ajaxSearch":
			echo $world->ajaxSearch();
			exit;
		break;
		
		case "search":
			if ($_REQUEST['search'] != "")
			{
				$search = trim($_REQUEST['search']);
				$pos = strpos($search,"showinfo:");
				if (!($pos === false))
				{
					$search = substr($search,$pos);
					$pos = strpos($search,"/");
					if (!($pos === false))
						$search = substr($search,0,$pos);
					$pos = strpos($search,"\\");
					if (!($pos === false))
						$search = substr($search,0,$pos);
					$search = substr($search,9);
					$_SESSION['searchIDs'] = $search;
					$string = URL_INDEX ."?module=eveorder&action=searchResult";
				}
				else
				{
					$searchIDs = $world->eveorder_doSearch($search);
					$_SESSION['searchIDs'] = $searchIDs;
					$string = URL_INDEX ."?module=eveorder&action=searchResult&searchIDs=".$searchIDs;
				}
			}
			else
			{
				$Messages->addwarning($language['error_nothing_to_search']);
				$string = URL_INDEX ."?module=eveorder&action=main&open=".$_REQUEST['open'];
			}
			header("Location: ".$string."\n");
			exit;
		break;

		case "addToFav":
			if (isset($_REQUEST['typeID']))
			{
				$world->eveorder_addToFavorites($_REQUEST['typeID'],$_REQUEST['groupID']);
				//$Messages->addconfirm($language['added_to_favorites']);
				$string = URL_INDEX ."?module=eveorder&action=main&open=".$_REQUEST['open'];
			}
			else
			{
				$Messages->addwarning($language['error_no_item_specified']);
				$string = URL_INDEX ."?module=eveorder&action=main&open=".$_REQUEST['open'];
			}
			header("Location: ".$string."\n");
			exit;
		break;

		case "delFavorits":
			if (isset($_REQUEST['typeID']))
			{
				$world->eveorder_delFromFavorites($_REQUEST['typeID']);
				$string = URL_INDEX ."?module=eveorder&action=myFavorites";
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "delOrder":
			if (isset($_REQUEST['orderID']))
			{
				$world->eveorder_delOrder($_REQUEST['orderID']);
				$Messages->addwarning($language['order_deleted']);
				$string = URL_INDEX ."?module=eveorder&action=myOrders";
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "delallDeliverys":
			if(isset($_POST['delall']))
			{
				if (isset($_SESSION['order_user'])) $userID = $_SESSION['order_user']; else $userID = $User->charID;
				$world->eveorder_delallDeliverys($_POST['delall'],$userID);
				$Messages->addwarning($language['order_deleted']);
				$string = URL_INDEX ."?module=eveorder&action=myOrders";
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "delCorpOrder":
			if (isset($_REQUEST['orderID']))
			{
				$world->eveorder_delOrder($_REQUEST['orderID']);
				$Messages->addwarning($language['order_deleted']);
				$string = URL_INDEX ."?module=eveorder&action=corpOrders";
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "saveOrder":
			if ((isset($_POST['typeID'])) and ($_POST['amount'] != "") and ($User->charID != ""))
			{
				if (isset($_SESSION['order_user'])) $userID = $_SESSION['order_user']; else $userID = $User->charID;
				if ($_POST['corp'])
				{	
					if($world->eveorder_saveOrder($userID,$_POST['typeID'],$_POST['amount'],$_POST['corp'],$User->corpID)) {
						foreach($_POST['amount'] as $value) {
							$stueck += str_replace('.', '', $value);
						}
						$Messages->addconfirm($stueck." ".$language['pieces_ordered']);
					}
					else
						$Messages->addwarning("Something is wrong!!");						
				}
				else
				{
					if($world->eveorder_saveOrder($userID,$_POST['typeID'],$_POST['amount'])) {
						foreach($_POST['amount'] as $value) {
							$stueck += str_replace('.', '', $value);
						}
						$Messages->addconfirm($stueck." ".$language['pieces_ordered']);
					}
					else
						$Messages->addwarning("Something is wrong!!");
				}
				//$string = URL_INDEX ."?module=eveorder&action=main&open=".$_POST['open'];
				$string = URL_INDEX ."?module=eveorder&action=myOrders";
			}
			else
			{
				$Messages->addwarning("Something is wrong!!");
				$string = URL_INDEX ."?module=eveorder&action=main";
			}
//			echo $string;
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "saveFittingOrder":
			if ((isset($_POST['fitting'])) and ($_POST['amount'] != "") and ($_POST['amount'] > 0) and ($User->charID != ""))
			{
				if (isset($_SESSION['order_user'])) $userID = $_SESSION['order_user']; else $userID = $User->charID;
				//print_r($_POST);
				$fit = new parseFit($world);
				switch($_POST['type']){
					case 'xml':
						if($_FILES['xml']['type']=='text/xml'){
							#print_r($_FILES['xml']);
							$fit->xml($_FILES['xml']['tmp_name']);
						}else{
							$Messages->addwarning("Angegebene Datei ist keine XML-Datei!");
						}
					break;
					case 'igf':
						$fit->igf($_POST['fitting']);
					break;
					default:
					case 'eft':
						$fit->eft($_POST['fitting']);
					break;
				}
				$temp = $fit->output();
				
				if($temp){
					foreach($temp as $k => $v){
						$type[] = $k;
						$amount[] = $v*$_POST['amount'];
					}					
					if ($_POST['corp']){	
						if($world->eveorder_saveOrder($userID,$type,$amount,true,$User->corpID)){
							foreach($amount as $value) {
								$stueck += $value;
							}
							$Messages->addconfirm($stueck." ".$language['pieces_ordered']);
						} else $Messages->addwarning("Something is wrong!!");						
					} else {
						if($world->eveorder_saveOrder($userID,$type,$amount)){
							foreach($amount as $value) {
								$stueck += $value;
							}
							$Messages->addconfirm($stueck." ".$language['pieces_ordered']);
						} else $Messages->addwarning("Something is wrong!!");
					}
				}else{
					$Messages->addwarning("Fitting enthielt keine verwertbaren Daten!");
				}
				$string = URL_INDEX ."?module=eveorder&action=myOrders";
			}
			else
			{
				$Messages->addwarning("Something is wrong!!");
				$string = URL_INDEX ."?module=eveorder&action=main";
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "saveOrderFav":
			if ((isset($_POST['typeID'])) and ($_POST['amount'] != "") and ($User->charID != ""))
			{
				if (isset($_SESSION['order_user'])) $userID = $_SESSION['order_user']; else $userID = $User->charID;
				if ($_POST['corp'])
				{	
					if($world->eveorder_saveOrder($userID,$_POST['typeID'],$_POST['amount'],$_POST['corp'],$User->corpID)){
						foreach($_POST['amount'] as $value) {
							$stueck += str_replace('.', '', $value);
						}
						$Messages->addconfirm($stueck." ".$language['pieces_ordered']);
					}
					else
						$Messages->addwarning("Something is wrong!!");						
				}
				else
				{
					if($world->eveorder_saveOrder($userID,$_POST['typeID'],$_POST['amount'])){
						foreach($_POST['amount'] as $value) {
							$stueck += str_replace('.', '', $value);
						}
						$Messages->addconfirm($stueck." ".$language['pieces_ordered']);
					}
					else
						$Messages->addwarning("Something is wrong!!");
				}
				$string = URL_INDEX ."?module=eveorder&action=myFavorites";
			}
			else
			{
				$Messages->addwarning("Something is wrong!!");
				$string = URL_INDEX ."?module=eveorder&action=myFavorites";
			}
//			echo $string;
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "saveOrderFromFitmenu":
			#echo '<pre>'; print_r($_POST); echo '</pre>';
			if (isset($_SESSION['order_user'])) $userID = $_SESSION['order_user']; else $userID = $User->charID;
			if (isset($_POST['items'])){
				foreach($_POST['items'] as $id => $val){
					$x = array_keys($val);
					$type[]   = $id;
					$amount[] = $x[0] * $_POST['amount'];
					$stueck += $x[0] * $_POST['amount'];
				}
				if( $world->eveorder_saveOrder($userID,$type,$amount,$_POST['corp'],$User->corpID) ){
					$Messages->addconfirm($stueck." ".$language['pieces_ordered']);
				} else $Messages->addwarning("Something is wrong!!");
				$string = URL_INDEX ."?module=eveorder&action=myOrders";
			} else {
				$Messages->addwarning("Something is wrong!!");
				$string = URL_INDEX ."?module=eveorder&action=myOrders";
			}
			
			header("Location: ".$string."\n");
			exit;
			
		break;
		
		case "ajaxfit":
			require_once ( MODULE_DIR . ACTIVE_MODULE . '/classes/class.Item.php' );
			require_once ( MODULE_DIR . ACTIVE_MODULE . '/classes/class.fitting.php' );
			
			if ($_GET['user'] == 'User') $id = $User->charID;
			elseif ($_GET['user'] == 'Corp') $id = $User->corpID;
			elseif ($_GET['user'] == 'Ally') $id = $User->allyID;
			
			if ( isset($_POST['fitID']) ) {
				
				$fit = new fitting( $world, $_POST['fitID'], $id );
				echo json_encode( $fit->attrib );
			}
			if ( isset($_POST['fitArray']) ){
				
				$fit = new fitting($world);
				$fit->addFit( $_POST['fitArray'], $id );
				$fit->fetchFits( $id );
				echo $fit->jsonFits;
			}
			if ( isset($_POST['user']) ) {
				
				$fit = new fitting( $world, false, $id, $world );
				echo $fit->jsonFits;
			}
			if ( isset($_POST['delFit']) ) {
				
				$fit = new fitting( $world, false, $id );
				$fit->delFit( $_POST['delFit'] );
				$fit->fetchFits( $id );
				echo $fit->jsonFits;
			}
			break;
		
		case "searchItem":
			echo $world->eveorder_search_item();
			exit;
			break;
		
		case "addReplacement":
			if (isset($_POST['typeID']) && !empty($_POST['typeID'])) {
				$world->eveorder_addReplacement();
			}
			$string = URL_INDEX ."?module=eveorder&action=shipRep&state=".$_POST['state'];
			header("Location: ".$string."\n");
			exit;
			break;
		
		case "addLocation":
			$world->eveorder_addLocation();
			#echo '<pre>'; print_r($_POST); echo '</pre>'; die;
			$string = URL_INDEX ."?module=eveorder&action=shipRep&state=".$_POST['state'];
			header("Location: ".$string."\n");
			exit;
			break;
		
		case "updateLocation":
			echo $world->eveorder_updateLocation();
			exit;
			break;
		
		case "updateVal":
			echo $world->eveorder_updateVal();
			exit;
			break;
			
		case "delValue":
			echo $world->eveorder_delValue();
			exit;
			break;
			
		case "upAssets":
			echo $world->eveorder_updateAssets();
			exit;
			break;
		
		case "importFitts":
			echo $world->eveorder_importShipReplaceFittings();
			exit;
			break;
	}
?>