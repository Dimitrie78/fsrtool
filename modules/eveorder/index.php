<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$smarty->assign('url', CONFIG_URL);

$smarty->assign('addheader', array(
				'<link type="text/css" href="modules/eveorder/inc/eveorder.css" rel="stylesheet" />',
				'<script type="text/javascript" src="modules/eveorder/inc/eveorder.js"></script>'));

$status = ReadConfigFile(MODULE_DIR . ACTIVE_MODULE . '/templates/status.' .$_SESSION['chosenLanguage'] . '.txt');
$newstatus = array();
foreach ($status as $key=>$value)
	$newstatus[$key] = array("value"=>$key,"name"=>$value);
$status = $newstatus;

if ( $User->Manager || $User->Producer || $User->Supplier )
	$smarty->assign("openOrders","1");
if ( $User->Manager )
{
	$smarty->assign("manager","1");
	$smarty->assign("producer","1");
	$smarty->assign("supplier","1");
}
if ( $User->Producer )
	$smarty->assign("producer","1");
if ( $User->Supplier )
	$smarty->assign("supplier","1");
if ( $User->CorpOrder )
	$smarty->assign("corporder","1");
$smarty->assign( "status", $status );
if (isset($_REQUEST['orderStatus'])) $_SESSION['orderStatus'] = $_REQUEST['orderStatus'];
else $_SESSION['orderStatus'] = 0;

$smarty->assign("whichStatus",$_SESSION['orderStatus']);

$smarty->assign('targetSystems', array(0 => 'Home', 1 => 'Home'));

	$users = array ( $User->charID => $User->username );
	if (isset($User->alts) && is_array($User->alts)) {
		foreach ( $User->alts as $alt )
			$users[ $alt['charID'] ] = $alt['charName'];
	}
	if ( isset( $_REQUEST['userID'] ) ) {
		$sel_user = $_REQUEST['userID'];
		$_SESSION['order_user'] = $sel_user;
	}
	else if ( isset( $_SESSION['order_user'] ) ) {
		$sel_user = $_SESSION['order_user'];
	}
	else {
		$sel_user = $User->charID;
		$_SESSION['order_user'] = $sel_user;
	}
	
	$smarty->assign("users", $users);
	$smarty->assign("sel_user", $sel_user);

if (empty($action)) $action = 'main';
$smarty->assign("action", $action);
//$smarty->assign('menue',  '.' . MODULE_DIR . ACTIVE_MODULE . '/templates/menu');
	
switch ($action)
{
	case 'shipRep':
		if( $User->CorpOrder ) {
			$smarty->assign('addheader', array(
				'<link type="text/css" href="modules/eveorder/inc/shipReplace.css" rel="stylesheet" />',
				'<script type="text/javascript" src="modules/eveorder/inc/shipReplace.js"></script>'));
			
			$smarty->assign('state', $_GET['state']);
			$smarty->assign('cacheTime', $world->eveorder_AssetsCacheTime());
			
			if ( $_GET['state'] != 9)
				$smarty->assign('list', $world->eveorder_ShipReplacement($User->corpID));
			else
				$smarty->assign('loc', $world->eveorder_getLocations($User->corpID));
			//$smarty->assign('pre', print_r($_SERVER, true));
			$smarty->display('file:['.ACTIVE_MODULE.']sandbox.tpl');
		} else {
			header('Location:'.URL_INDEX);
			exit;
		}
		break;
	
	case 'stats':
		if (isset($_GET['sort'])) $sort = $_GET['sort']; else $sort = 1;
		$stats = $world->eveorder_stats($sort);
		$gesammt=0;
		if (is_array($stats)) {
			foreach ($stats as $key => $value) {
				$gesammt += $value['price'];
			}
		}
		$smarty->assign('sort', $sort);
		$smarty->assign('stats', $stats);
		$smarty->assign('summe', $gesammt);
		$smarty->display('file:['.ACTIVE_MODULE.']stats.tpl');
	break;
	
	case "openOrders":
		if ( isset($_REQUEST['corpid']) &&  $_REQUEST['corpid'] != '' ) $corpID = $_REQUEST['corpid']; else $corpID = $User->corpID;
		if ( $User->allyID != 0 ) {
			$corps = $world->eveorder_getCorps($User->allyID);
			
			$smarty->assign('corps', $corps);
			$smarty->assign('sel_corp', $corpID);
		}
		if (isset($_REQUEST['orderStatus'])) {
			if (isset($_REQUEST['orderby'])) {
				$orders = $world->eveorder_getOpenOrders($corpID,$_REQUEST['orderStatus'],$_REQUEST['orderby']);
				$smarty->assign('sort', $_REQUEST['orderby']);
			} else {
				$orders = $world->eveorder_getOpenOrders($corpID,$_REQUEST['orderStatus']);
			}
		} else {
			$orders = $world->eveorder_getOpenOrders($corpID,0);
		}
		$volume = 0;
		if($orders){
			//echo '<pre>';print_r($orders);die;
			$i=0;
			foreach($orders as $order){
				if(isset($_REQUEST['type'])){
					if($_REQUEST['type']=='t1' && $order['metaLVL'] != 0){
						continue;
					}
					if($_REQUEST['type']=='gt1' && ($order['metaLVL'] == 0 || $order['metaLVL'] == 'mins')){
						continue;
					}
					$smarty->assign('type',$_REQUEST['type']);
				}
				if(isset($_REQUEST['for']) && $_REQUEST['for']!='all'){
					if($_REQUEST['for']=='user' && $order['corpid']){
						continue;
					}
					if($_REQUEST['for']=='corp' && !$order['corpid']){
						continue;
					}
				}
				$neworders[$order['userid']]['username'] = $order['username'];
				$neworders[$order['userid']]['shipvol'] += $order['ship'] != NULL ? ($order['volume'] * $order['amount']) : 0;
				$neworders[$order['userid']]['modvol'] += $order['ship'] == NULL ? ($order['volume'] * $order['amount']) : 0;
				$neworders[$order['userid']]['price'] += $order['price'] != 0.00 ? ($order['price'] * $order['amount']) : 0;
				#if($order['userid'] == 285591396) echo $order['price'].'- ';
				//$neworders[$order['userid']]['price'] += ($order['price'] * $order['amount']);
				$neworder['id'] =        	$order['id'];
				$neworder['typeID'] =    	$order['typeID'];
				$neworder['metaLVL'] = 	 	$order['metaLVL'];
				$neworder['typeName'] =  	$order['typeName'];
				$neworder['amount'] = 	 	$order['amount'];
				$neworder['timestamp'] = 	$order['timestamp'];
				$neworder['lastchange'] = 	$order['lastchange'];
				$neworder['comment'] = 		$order['comment'];
				$neworder['supplierName'] = $order['supplierName'];
				$neworder['corpID'] = 		$order['corpid'];
				$neworder['targetSys'] = 	$order['targetSys'];
				$neworder['status'] = 		$order['status'];
				$neworder['volume'] = 		$order['volume'];
				$neworders[$order['userid']]['order'][] = $neworder;
				$volume += ($order['volume'] * $order['amount']);
				$price += ($order['price'] * $order['amount']);
				$i++;
			}
		}
		
		$smarty->assign("orders",			$neworders);
		$smarty->assign("volume",			$volume);
		$smarty->assign("price",			$price);
		$smarty->assign("quantity_orders",	$i);
		$smarty->assign('for',				$_REQUEST['for']);
		$smarty->assign("orderStatus",		$_REQUEST['orderStatus']);
		$smarty->assign("url_index_eveorder",URL_INDEX .'?module='.ACTIVE_MODULE);
		$smarty->assign("url_dowork_changeOrderStatus",URL_DOWORK);
		$smarty->display('file:['.ACTIVE_MODULE.']openOrders.tpl');
	break;

	case "myOrders":
		$smarty->assign("url_index_eveorder",  URL_INDEX .'?module='.ACTIVE_MODULE);
		$smarty->assign("url_dowork_eveorder", URL_DOWORK.'?module='.ACTIVE_MODULE);
		$smarty->assign("url_dowork_delOrder", URL_DOWORK.'?module='.ACTIVE_MODULE."&amp;action=delOrder");
		$smarty->assign("orders",			   $world->eveorder_getMyOrders($sel_user));
		$smarty->display('file:['.ACTIVE_MODULE.']myOrders.tpl');
	break;
	
	case "corpOrders":
		$smarty->assign("url_index_eveorder",  URL_INDEX .'?module='.ACTIVE_MODULE);
		$smarty->assign("url_dowork_delOrder", URL_DOWORK.'?module='.ACTIVE_MODULE."&amp;action=delCorpOrder");
		$smarty->assign("orders",			   $world->eveorder_getCorpOrders());
		$smarty->display('file:['.ACTIVE_MODULE.']corpOrders.tpl');
	break;
	
	case "myFavorites":
		$result = $world->eveorder_getFavorits();
		$items =  $world->eveorder_getSearchResult($result);
		if (count($items) != 0)
		{
			$smarty->assign("items",$items);
		}
		$smarty->assign("url_index_eveorder",   URL_INDEX .'?module='.ACTIVE_MODULE);
		$smarty->assign("url_dowork_saveOrder", URL_DOWORK.'?module='.ACTIVE_MODULE);
		$smarty->assign("url_dowork_delFromMyFavorites",URL_DOWORK.'?module='.ACTIVE_MODULE."&amp;action=delFavorits");
		$smarty->display('file:['.ACTIVE_MODULE.']myFavorites.tpl');
	break;
	
	case "Fittings":
		require_once ( MODULE_DIR . ACTIVE_MODULE . '/classes/class.Item.php' );
		require_once ( MODULE_DIR . ACTIVE_MODULE . '/classes/class.fitting.php' );
		
		$fit = new fitting( $world, false, $User->charID );
		
		$smarty->assign("url_index_eveorder",   URL_INDEX .'?module='.ACTIVE_MODULE);
		$smarty->display('file:['.ACTIVE_MODULE.']Fittings.tpl');
	break;
	
	case "searchResult":
		if(isset($_GET['searchIDs']))
			$items = $world->eveorder_getSearchResult($_GET['searchIDs']);
		else $items = $world->eveorder_getSearchResult($_SESSION['searchIDs']);
		if (count($items) == 0)	{
			$Messages->addwarning($language['no_item_found']);
		}
		else {
			$smarty->assign("items", $items);
		}
	
	default:
	case "main":
		if (($User->uname != "guest") AND (isset($User)))
		{
			if (!isset($_REQUEST['open']))
			{
				$smarty->assign("open","0");
				$open = array("0");
				$detail = 0;
			}
			else
			{
				$smarty->assign("open",$_REQUEST['open']);
				$open = explode(",",$_REQUEST['open']);
				$detail = $open[count($open)-1];
			}
			
			$market = $world->eveorder_getMarket($open);
			if ($detail != 0)
			{
				$items = $world->eveorder_getTypesByMarketCategory($detail);
				$smarty->assign("items",$items);
			}
			$smarty->assign("url_dowork_search",		URL_DOWORK.'?module='.ACTIVE_MODULE."&amp;action=search");
			$smarty->assign("url_dowork_addToFavorites",URL_DOWORK.'?module='.ACTIVE_MODULE."&amp;action=addToFav");
			$smarty->assign("market",					$market);
			$smarty->assign("url_dowork_logout",		URL_DOWORK."?action=logout");
			$smarty->assign("url_dowork_saveOrder",		URL_DOWORK.'?module='.ACTIVE_MODULE);
			$smarty->assign("url_index_main",	    	URL_INDEX .'?module='.ACTIVE_MODULE."&amp;action=main");
			$smarty->assign("url_index_eveorder",   	URL_INDEX .'?module='.ACTIVE_MODULE);
			$smarty->display('file:['.ACTIVE_MODULE.']main.tpl');
		}
		else
		{
			header("Location: ".$url_index."\n");
			exit;
		}
	break;
}
?>