<?php
defined('FSR_BASE') or die('Restricted access');

switch($action)
{
	case "update":
		date_default_timezone_set('UTC');
		require_once( 'cron/cron.class.php' );
		$cron = new cron($parms);
		$corpID = isset( $_SESSION['corpID'] ) ? $_SESSION['corpID'] : $User->corpID;
		$cron->setCorpID( $corpID );
		$out = $cron->run();
		
		echo $out;
	break;
	
	case "edit":		
		if ( isset( $_POST['pos'] ) ) {
			$result = $world->pos_update_tower( $_POST['pos'] );
		}
		$string = URL_INDEX ."?module=Pos&action=editPos&id=".$_POST['pos']['id'];
		header("Location: ".$string);
		exit;
	break;
		
	/* case "editAPI":		
		if (isset($_POST['user'])) {
			$api = $world->pos_getUserAPI();
			if ($_POST['user']['api'] != $api['vCODEx']) {
				
				$ale->setConfig('serverError', 'returnParsed');
				
				$ale->setKey($_POST['user']['id'],$_POST['user']['api']);
				try {	
					#$account = $ale->account->Characters();
					$APIKeyInfo = $ale->account->APIKeyInfo();
					
					if ( $APIKeyInfo->error ) {
						$error = $APIKeyInfo->error->toArray();
						$errorText = $error['nodeText'];
						$errorCode = $error['code'];
						$Messages->addwarning('API: [' . $errorCode . '] ' .$errorText);
						$Messages->addwarning("The entred KEY is fubar...");
						unset($APIKeyInfo);
						$string = URL_INDEX ."?module=Pos&action=settings";
					} 
					else {
						$xml = $APIKeyInfo->result->key->toArray();
						if ( (string)$xml['type'] == 'Corporation' 
							&& (int)$xml['accessMask'] & 2		#AssetList
							&& (int)$xml['accessMask'] & 8		#CorporationSheet
							&& (int)$xml['accessMask'] & 131072 #StarbaseDetail
							&& (int)$xml['accessMask'] & 524288	#StarbaseList
							&& (int)$xml['accessMask'] & 16777216	#Locations
						//	&& (int)$xml['accessMask'] & 2048 	#MemberTracking
						//	&& (int)$xml['accessMask'] & 1048576 #WalletJournal
						) {
						
							$charList = array();
							foreach ( $xml['characters'] as $character ){
								$charList[(string)$character['characterID']] = array("charID"=>(string)$character['characterID'],"charName"=>(string)$character['characterName'],"corpID"=>(string)$character['corporationID'],"corpName"=>(string)$character['corporationName']);
							}
							
							$_SESSION['userID']   	= $_POST['user']['id'];
							$_SESSION['apiKey']   	= $_POST['user']['api'];
							$_SESSION['accessMask'] = (int)$xml['accessMask'];
							$_SESSION['type'] 	  	= (string)$xml['type'];
							$_SESSION['expires'] 	= (string)$xml['expires'];
							$_SESSION['charList'] 	= $charList;
							$string = URL_INDEX ."?module=Pos&action=showCharSelection";
						}
						else {
							$x = array_keys($xml['characters']);
							$Messages->addwarning( 'You need a Key with more access.' );
							$Messages->addwarning( 'Klick <a href="https://support.eveonline.com/api/Key/CreatePredefined/1705994/'.(string)$xml['characters'][$x[0]]['characterID'].'/true" target="_blank">here</a> to generate a Predefined Key' );
							$string = URL_INDEX ."?module=Pos&action=settings";
						}
					}
				} catch (Exception $e) {
					$Messages->addwarning( $e->getMessage() );
					$string = URL_INDEX ."?module=Pos&action=settings";
				}
			} else $string = URL_INDEX ."?module=Pos&action=settings";
		} else $string = URL_INDEX ."?module=Pos&action=settings";
		
		header("Location: ".$string."\n");
		exit;
	break;
	
	case "addCharApi":
		if(isset($_POST['charID'])) {
			
			$ale->setConfig('serverError', 'returnParsed');
			
			$ale->setKey($_SESSION['userID'],$_SESSION['apiKey'],$_POST['charID']);
			
			try {	
				$CorporationSheet = $ale->corp->CorporationSheet();
				if ( !$CorporationSheet->error ) {
					$user	  = $_SESSION['charList'][$_POST['charID']];
					$userID   = $_SESSION['userID'];
					$charID   = $user['charID'];
					$userName = $user['charName'];
					$userAPI  = $_SESSION['apiKey'];
					$corpName = $user['corpName'];
					$allyid   = (string)$CorporationSheet->result->allianceID;
					$corpid   = (string)$CorporationSheet->result->corporationID;
					$accessMask = $_SESSION['accessMask'];
					
					$corpIDs = array( $User->corpID );
					if ( is_array($User->alts) ) foreach ( $User->alts as $alt ) $corpIDs[] = $alt['corpID'];
					
					if ( in_array($corpid, $corpIDs) || $User->Admin )
						$return = $world->pos_setUserApi($charID,$userName,$userID,$userAPI,$corpid,$allyid,$accessMask);
					else
						$Messages->addwarning("You are not in the same Crop!!!");
				} else
					$Messages->addwarning('API: [' . (int)$CorporationSheet->error->attributes() . '] ' . (string)$CorporationSheet->error);
				
				
				unset($charID,$userName,$userID,$userAPI,$corpid,$allyid,$dataxml,$CorporationSheet);
				unset($_SESSION['charList'],$_SESSION['userID'],$_SESSION['apiKey']);
			} catch (Exception $e) {
				$Messages->addwarning( $e->getMessage() );
			}
		}
		if ( $return ) 
			$Messages->addconfirm("All Good");
		else 
			$Messages->addwarning("Somethink is bad");
		
		$string = URL_INDEX ."?module=Pos&action=settings";
		header("Location: ".$string."\n");
		exit;
	break; */
	
	case 'addchar':
		header('Content-type: application/json');
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
		$corpID = $_POST['obj']['result']['key']['characters'][$_POST['obj']['charid']]['corporationID'];
		$corpIDs = array( $User->corpID );
		if(is_array($User->alts)) foreach($User->alts as $alt) $corpIDs[] = $alt['corpID'];
		if(in_array($corpID, $corpIDs) || $User->Admin) {
			try {
				$CorporationSheet = $ale->corp->CorporationSheet(array('corporationID' => $corpID), ALE_AUTH_NONE);			
				$_POST['obj']['allyid'] = (string)$CorporationSheet->result->allianceID;
				//echo print_r($_POST);
				echo json_encode($world->saveApi($_POST));
			} catch (Exception $e) {
				$err = array('error' => $e->getCode().' - '.$e->getMessage());
				echo json_encode($err);
			}
		}else{
			$err = array('error' => 'You are not in the same Crop!!!');
			echo json_encode($err);
		}
	break;
	
	case 'delall':
		header('Content-type: application/json');
		echo json_encode($world->delAllStuff($_POST));
		//echo print_r($_POST);
	break;
	
	case 'fueltime':
		header('Content-type: application/json');
		echo json_encode($world->saveLowFuelTime($_POST));
		//echo json_encode( print_r($_POST));
	break;
	
	case 'addmail':
		header('Content-type: application/json');
		echo json_encode($world->savePosMailList($_POST));
		//echo json_encode( print_r($_POST));
	break;
	
	case 'delmail':
		header('Content-type: application/json');
		echo json_encode($world->delPosMailList($_POST));
		//echo json_encode( print_r($_POST));
	break;
	
}

?>