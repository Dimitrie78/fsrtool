<?php
if (!defined('FSR_BASE')) {
	define('FSR_BASE', dirname(__FILE__));
}

require_once ("init.inc.php");


if ( !isset($_REQUEST['module']) )
{
	switch ($action)
	{
		case "addCharApi":
			if ((isset($_REQUEST['password'])) AND ($_REQUEST['password'] != "")) {
				$user = $_SESSION['modules']['login']['charList'][$_REQUEST['charID']];
				
				$data = array();
				$data['charID']   = $user['charID'];
				$data['username'] = $user['charName'];
				$data['password'] = $_REQUEST['password'];
				$data['vCODE']    = $_SESSION['modules']['login']['apiKey'];
				$data['keyID']    = $_SESSION['modules']['login']['userID'];
				$data['accessMask'] = $_SESSION['modules']['login']['accessMask'];
				$data['userIP']   = $_SERVER['REMOTE_ADDR'];
				$data['description'] = '';
				$data['email']    = $_REQUEST['mail'];
				$data['active']   = "1";
				$data['corpID']   = $user['corpID'];
				$data['timestamp'] = date("YmdHis");
				
				$stuff = array();
				$stuff['corpID']   = $user['corpID'];
				$stuff['corpName'] = $user['corpName'];
				
				$ale->setConfig('serverError', 'returnParsed');
				$ale->setKey($data['keyID'],$data['vCODE'],$_REQUEST['charID']);
				
				try {
					$CharacterInfo = $ale->eve->CharacterInfo();
					if ( $CharacterInfo->error ) {
						$error = $CharacterInfo->error->toArray();
						$errorText = $error['nodeText'];
						$errorCode = $error['code'];
						$Messages->addwarning( 'You need a key with at least: Group -> Public Information -> CharacterInfo' );
						$Messages->addwarning( $errorCode . '-' . $errorText );
					} 
					
					else {
						$stuff['allyID'] = (string) $CharacterInfo->result->allianceID == 0 ? 'None' : (string) $CharacterInfo->result->allianceID;
						$stuff['allyName'] = $allyid == 'None' ? 'None' : (string) $CharacterInfo->result->alliance;
					}
					
					if ( !preg_match( "/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $data['email'] ) ) {
						$Messages->addwarning("Your email address does not appear to be valid");
						$string = URL_INDEX."?action=showCharSelection";
					}
					else if ( $CharacterInfo->error ) {
						$string = URL_INDEX.'?action=accessMask';
						#unset($_SESSION['modules']);
					}
					
					else if ( !$User->insertUser( $data, $stuff ) ) {
						$Messages->addwarning($language['account_already_in_database']);
						$string = URL_INDEX;
						unset($_SESSION['modules']);
					}
					
					else {
						$Messages->addconfirm($language['account_created']);
						$string = URL_INDEX;
						unset($_SESSION['modules']);
					}
				} catch (Exception $e) {
					$Messages->addwarning( $e->getMessage() );
				}
				
				
			}
			else {
				$Messages->addwarning("You must enter a Password");
				$string = URL_INDEX."?action=showCharSelection";
			}
			header("Location: ".$string."\n");
			exit;
		break;

		case "assignAPI":
			if ((isset($_REQUEST['apiKey'])) AND ($_REQUEST['apiKey'] != "") AND (isset($_REQUEST['userID'])) AND ($_REQUEST['userID'] != "")) {
				if (!is_numeric($_REQUEST['userID'])) {
					$Messages->addwarning($language['userid_or_apikey_wrong']);
					$string = URL_INDEX;
				} 
				else if(empty($_REQUEST['apiKey'])) {
					$Messages->addwarning($language['userid_or_apikey_wrong']);
					$string = URL_INDEX;
				}
				else {
					$ale->setConfig('serverError', 'returnParsed');
					$ale->setKey($_REQUEST['userID'],$_REQUEST['apiKey']);
					try {
						$APIKeyInfo = $ale->account->APIKeyInfo();
						if ( $APIKeyInfo->error ) {
							$error = $APIKeyInfo->error->toArray();
							$errorText = $error['nodeText'];
							$errorCode = $error['code'];
							#echo $errorCode . '-' . $errorText;
						} else {
							$xml = $APIKeyInfo->result->key->toArray();
							if ( (int)$xml['accessMask'] & 8388608 || (int)$xml['accessMask'] & 16777216 ) #CharacterInfo
							{
								$charList = array();
								foreach ( $xml['characters'] as $character ){
									$charList[(string)$character['characterID']] = array("charID"=>(string)$character['characterID'],"charName"=>(string)$character['characterName'],"corpID"=>(string)$character['corporationID'],"corpName"=>(string)$character['corporationName']);
								}
							} else {
								$_SESSION['modules']['login']['accessMask'] = (int)$xml['accessMask'];
								$string = URL_INDEX.'?action=accessMask';
								header("Location: ".$string."\n");
								exit;
							}
						}
						if ( $APIKeyInfo->error ) {
							$Messages->addwarning($errorCode.'-'.$errorText);
							$Messages->addwarning($language['userid_or_apikey_wrong']);
							$string = URL_INDEX;
						}
						else {
							//$Messages->addwarning($api->getApiError());
							$_SESSION['modules']['login']['userID'] = $_REQUEST['userID'];
							$_SESSION['modules']['login']['apiKey'] = $_REQUEST['apiKey'];
							$_SESSION['modules']['login']['charList'] = $charList;
							$_SESSION['modules']['login']['accessMask'] = (int)$xml['accessMask'];
							$_SESSION['modules']['login']['type'] = (string)$xml['type'];
							$_SESSION['modules']['login']['expires'] = (string)$xml['expires'];
							$string = URL_INDEX."?action=showCharSelection";
						}
					} catch (Exception $e) {
						$Messages->addwarning( $e->getMessage() );
					}
					
				}
			}
			else {
				$Messages->addwarning($language['userid_or_apikey_not_entered']);
				$string = URL_INDEX;
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "assignaltAPI":
			if ( !isset( $User->charID ) ) {
				$Messages->addwarning( 'You must Logged in!!!' );
				header("Location: ".URL_INDEX."\n");
				exit;
			}
			if ((isset($_POST['apiKey'])) AND ($_POST['apiKey'] != "") AND (isset($_POST['userID'])) AND ($_POST['userID'] != "")) {
				if (!is_numeric($_POST['userID'])) {
					$Messages->addwarning($language['userid_or_apikey_wrong']);
					$string = URL_INDEX.'?action=addalt';
				}
				else if(mb_strlen($_POST['apiKey']) < 64) {
					$Messages->addwarning($language['userid_or_apikey_wrong']);
					$string = URL_INDEX.'?action=addalt';
				}
				else {
					
					$ale->setConfig('serverError', 'returnParsed');
					$ale->setKey($_POST['userID'],$_POST['apiKey']);
					try {
						$APIKeyInfo = $ale->account->APIKeyInfo();
						if ( $APIKeyInfo->error ) {
							$error = $APIKeyInfo->error->toArray();
							$errorText = $error['nodeText'];
							$errorCode = $error['code'];
							#echo $errorCode . '-' . $errorText;
						} else {
							$xml = $APIKeyInfo->result->key->toArray();
							if ( (int)$xml['accessMask'] & 8388608 || (int)$xml['accessMask'] & 16777216 ) #CharacterInfo
							{
								$charList = array();
								foreach ( $xml['characters'] as $character ){
									$charList[(string)$character['characterID']] = array("charID"=>(string)$character['characterID'],"charName"=>(string)$character['characterName'],"corpID"=>(string)$character['corporationID'],"corpName"=>(string)$character['corporationName']);
								}
							} else {
								$_SESSION['modules']['login']['accessMask'] = (int)$xml['accessMask'];
								$string = URL_INDEX.'?action=accessMask';
								header("Location: ".$string."\n");
								exit;
							}
						}
						if ( $APIKeyInfo->error ) {
							$Messages->addwarning($errorCode.'-'.$errorText);
							$Messages->addwarning($language['userid_or_apikey_wrong']);
							$string = URL_INDEX;
						}
						else {
							//$Messages->addwarning($api->getApiError());
							$_SESSION['modules']['login']['userID'] = $_REQUEST['userID'];
							$_SESSION['modules']['login']['apiKey'] = $_REQUEST['apiKey'];
							$_SESSION['modules']['login']['charList'] = $charList;
							$_SESSION['modules']['login']['accessMask'] = (int)$xml['accessMask'];
							$_SESSION['modules']['login']['type'] = (string)$xml['type'];
							$_SESSION['modules']['login']['expires'] = (string)$xml['expires'];
							$string = URL_INDEX."?action=showAltCharSelection";
						}
					} catch (Exception $e) {
						$Messages->addwarning( $e->getMessage() );
					}
					
				}
			}
			else {
				$Messages->addwarning($language['userid_or_apikey_not_entered']);
				$string = URL_INDEX.'?action=addalt';
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "addAltCharApi":
			if ( !isset( $User->charID ) ) {
				$Messages->addwarning( 'You must Logged in!!!' );
				header("Location: ".URL_INDEX."\n");
				exit;
			}
			if ((isset($_POST['charID'])) AND ($_POST['charID'] != "")) {
				$user = $_SESSION['modules']['login']['charList'][$_POST['charID']];
				
				$data = array();
				$data['charID']		= $_POST['charID'];
				$data['mainCharID']	= $User->charID;
				$data['charName']	= $user['charName'];
				$data['userID']		= $_SESSION['modules']['login']['userID'];
				$data['userAPI']	= $_SESSION['modules']['login']['apiKey'];
				$data['accessMask'] = $_SESSION['modules']['login']['accessMask'];
				$data['corpID']		= $user['corpID'];
				
				$stuff = array();
				$stuff['corpID']   = $user['corpID'];
				$stuff['corpName'] = $user['corpName'];
				
				
				$ale->setConfig('serverError', 'returnParsed');
				#$Messages->addwarning($data['userID'].','.$data['userAPI'].','.$data['charID']);
				$ale->setKey($data['userID'],$data['userAPI'],$data['charID']);
				$data['newAPI'] = 1;
				
				try {
					$CharacterInfo = $ale->eve->CharacterInfo();
					if ( $CharacterInfo->error ) {
						$error = $CharacterInfo->error->toArray();
						$errorText = $error['nodeText'];
						$errorCode = $error['code'];
						$Messages->addwarning( 'You need a key with at least: Group -> Public Information -> CharacterInfo' );
						$Messages->addwarning( $errorCode . '-' . $errorText );
					} 
					else {
						$stuff['allyID'] = (string) $CharacterInfo->result->allianceID == 0 ? 'None' : (string) $CharacterInfo->result->allianceID;
						$stuff['allyName'] = $allyid == 'None' ? 'None' : (string) $CharacterInfo->result->alliance;
					}
					
					if ( $CharacterInfo->error ) {
						$string = URL_INDEX.'?action=accessMask';
						#unset($_SESSION['modules']);
					}
					
					else if ( !$User->addAlt( $data, $stuff ) ) {
						$Messages->addwarning('acc already exists');
						$string = URL_INDEX;
						unset($_SESSION['modules']);
					}
					else {
						$Messages->addconfirm('accound added');
						$string = URL_INDEX;
						unset($_SESSION['modules']);
					}
				} catch (Exception $e) {
					$Messages->addwarning( $e->getMessage() );
				}
				
				
			}
			else {
				$string = URL_INDEX."?action=showAltCharSelection";
			}
			header("Location: ".$string."\n");
			exit;
		break;
		

		case "changePassword":
			if (($_POST['old'] == "") or ($_POST['new1'] == "") or ($_POST['new2'] == "")) {
				$Messages->addwarning("Warnung: keines der Passwort-Felder darf leer sein.");
				$string = URL_INDEX."?action=passwd";
			}
			else if ($_POST['new1'] != $_POST['new2']) {
				$Messages->addwarning("Warnung: Neue Passw&ouml;rter stimmen nicht &uuml;berein");
				$string = URL_INDEX."?action=passwd";
			} 
			else if ( $User->editPass( array( 'oldpass' => $_POST['old'], 'newpass' => $_POST['new1'] ) ) ) {
				$Messages->addconfirm("Passwort ge&auml;ndert");
				$string = URL_INDEX;
			}
			else {
				$Messages->addwarning("Warnung: Altes Passwort ist nicht korrekt");
				$string = URL_INDEX."?action=passwd";
			}
			header("Location: ".$string."\n");
			exit;
		break;
  
		case "getPassword":
			if (($_POST['username'] == "") or ($_POST['mail'] == "")) {
				$Messages->addwarning("Warnung: Sie haben nicht alle Felder ausgef&uuml;llt");
				$string = URL_INDEX."?action=email";
			}
			else if($User->sendMail($_POST['username'], $_POST['mail'])) {
				$Messages->addconfirm("Passwort per Email versendet");
				$string = URL_INDEX;
			}
			else {
				$Messages->addwarning("Warnung: Email oder Username stimmen nicht &uuml;berein");
				$string = URL_INDEX."?action=email";
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "activate":
			if( $_GET['u'] != '' && $_GET['k'] != '' ) {
				$data = array(
					'charID' => $_GET['u'],
					'code'	 => $_GET['k']
				);
				$res = $User->resetPass($data);
				if( $res ) $Messages->addconfirm("Password reset");
				else $Messages->addwarning("FAIL");
			}
			header("Location: ".URL_INDEX."\n");
			exit;
		break;

		case "logout":
			$User->logout(URL_INDEX); 
		break;

		case "login":
			if (($_POST['username'] != "") AND ($_POST['password'] != "")) {
				$result = $User->login($_POST['username'],$_POST['password'],$_POST['check']);
				if( $result ) {
					$Messages->addconfirm($language['login_successful']);
					if ( $User->active == 0 ) 
						$string = URL_INDEX; 
					else if ( $_POST['request_url'] )
						$string = urldecode($_POST['request_url']);
					else 
						$string = URL_INDEX; 
				} else {
					$Messages->addwarning($language['login_failed']);
					$string = URL_INDEX;
				}
			} else {
				$Messages->addwarning($language['login_failed']);
				$string = URL_INDEX;
			}
			header("Location: ".$string."\n");
			exit;
		break;
		
		case "editUser":
			if(isset($_POST['user']['mail'],$_POST['user']['id'],$_POST['user']['api'])) {
				$usermail = $User->email;
				$userapi  = $User->apiX;
				if (!preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $_POST['user']['mail'])) {
					$Messages->addwarning("Ihre eingegebene E-Mail Adresse hatt ein ung&uuml;ltiges format.");
					$string = URL_INDEX;
				}
				else if($usermail != $_POST['user']['mail']) {
					$email = $database->escape( $_POST['user']['mail'] );
				}
				if ( $userapi != $_POST['user']['api'] || $User->keyID != $_POST['user']['id'] ) {
					$ale->setConfig('serverError', 'returnParsed');
					$ale->setKey($_POST['user']['id'],$_POST['user']['api']);
					try {
						$APIKeyInfo = $ale->account->APIKeyInfo();
						if ( $APIKeyInfo->error ) {
							$error = $APIKeyInfo->error->toArray();
							$errorText = $error['nodeText'];
							$errorCode = $error['code'];
							$Messages->addwarning($errorCode.'-'.$errorText);
							$Messages->addwarning("Den Keks mag ich ned :) or CCP's API SERVER is FAIL atm -.-");
							$string = URL_INDEX;
						} else {
							$xml = $APIKeyInfo->result->key->toArray();
							if ( (int)$xml['accessMask'] & 262144	#SkillQueue
							  && (int)$xml['accessMask'] & 131072	#SkillInTraining
							  && (int)$xml['accessMask'] & 8	    #CharacterSheet
							  && (int)$xml['accessMask'] & 8388608	#CharacterInfo
							) {
								$vCODE = $database->escape( $_POST['user']['api'] );
								$keyID = $database->escape( $_POST['user']['id'] );
								$res = $database->exec_query("UPDATE ".db_tab_user." SET keyID='{$keyID}', vCODE='{$vCODE}', accessMask='{$xml['accessMask']}', active=1 WHERE charID='{$User->charID}';");
								if ($res) $Messages->addconfirm("account Updated");
							} else {
								$_SESSION['modules']['login']['accessMask'] = (int)$xml['accessMask'];
								$string = URL_INDEX.'?action=accessMask';
								header("Location: ".$string."\n");
								exit;
							}
						}
					} catch (Exception $e) {
						$Messages->addwarning( $e->getMessage() );
					}
				}
				
			
				if ( isset($email) ) {
					$result = $database->exec_query("UPDATE ".db_tab_user." SET email='".$email."' WHERE charID='".$User->charID."';");
					if ($result)
						$Messages->addconfirm("Email updated.");
					else
						$Messages->addwarning("Some update issue.");
				}
				
			} elseif (isset($_POST['alt']['id'],$_POST['alt']['api'],$_POST['alt']['up'])) {
				$altapi = $User->alts[ $_POST['alt']['char'] ]['apiX'];
				$charID = $_POST['alt']['char'];
				if ($altapi != $_POST['alt']['api']) {
					
					$ale->setConfig('serverError', 'returnParsed');
					$ale->setKey($_POST['alt']['id'],$_POST['alt']['api']);
					try {
						$APIKeyInfo = $ale->account->APIKeyInfo();
						if ( $APIKeyInfo->error ) {
							$error = $APIKeyInfo->error->toArray();
							$errorText = $error['nodeText'];
							$errorCode = $error['code'];
							$Messages->addwarning($errorCode.'-'.$errorText);
							$Messages->addwarning("Den Keks mag ich ned :) or CCP's API SERVER is FAIL atm -.-");
							$string = URL_INDEX;
						} else {
							$xml = $APIKeyInfo->result->key->toArray();
							if ( (int)$xml['accessMask'] & 262144	#SkillQueue
							  && (int)$xml['accessMask'] & 131072	#SkillInTraining
							  && (int)$xml['accessMask'] & 8	    #CharacterSheet
							  && (int)$xml['accessMask'] & 8388608	#CharacterInfo
							) {
								$api = $database->escape( $_POST['alt']['api'] );
								$keyID = $database->escape( $_POST['alt']['id'] );
								$query   = "UPDATE ".db_tab_alts." SET userID='".$keyID."', userAPI='".$api."', accessMask='{$xml['accessMask']}', newAPI=1 WHERE charID='".$charID."' AND mainCharID = '".$User->charID."';";
								$result  = $database->exec_query( $query );
								if ($result) $Messages->addconfirm("API up to date");
							} else {
								$_SESSION['modules']['login']['accessMask'] = (int)$xml['accessMask'];
								$string = URL_INDEX.'?action=accessMask';
								header("Location: ".$string."\n");
								exit;
							}
						}
					} catch (Exception $e) {
						$Messages->addwarning( $e->getMessage() );
					}
					
				}
			} elseif (isset($_POST['alt']['id'],$_POST['alt']['api'],$_POST['alt']['del']) && $_POST['alt']['del'] == 'delete') {
				$charID = $_POST['alt']['char'];
				$query = "DELETE FROM ".db_tab_alts." WHERE charID='".$charID."' AND mainCharID = '".$User->charID."';";
				$result = $database->exec_query( $query );
				if ($result) 
					$Messages->addconfirm("Alt deleted");
			}
			$string = URL_INDEX;
			header("Location: ".$string."\n");
			exit;
		break;
		
		case 'setPushMail':
			require_once('inc/eveNotifications.class.php');
			$eveNotifications = new eveNotifications($User);
			header('Content-type: application/json');
			echo json_encode($eveNotifications->setPushMail());
		break;
		
		case 'delPushMail':
			require_once('inc/eveNotifications.class.php');
			$eveNotifications = new eveNotifications($User);
			header('Content-type: application/json');
			echo json_encode($eveNotifications->delPushMail());
		break;
		
		default:
			echo 'fail action';
		exit;
	}
}

?>