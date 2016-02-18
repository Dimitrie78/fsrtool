<?php

require_once (FSR_BASE . DIRECTORY_SEPARATOR . 'classes/mail/phpmailer.inc.php');

class Fetcher
{
	private $db = null;
	private $ale = null;
	private $mail = null;
	
	private $absenderMail = 'noreplay@fsrtool.de';
	
	private $cronID = 99;
	private $logdir;
	private $corpID = null;
	private $charID = null; 
	
	public $numQueries = 0;
	
	public function __construct($parms) {
		date_default_timezone_set('UTC');
		$this->logdir = FSR_BASE . DIRECTORY_SEPARATOR . 'cache/cronelog/';
		$this->connect($parms);
		$this->ale = AleFactory::getEVEOnline($parms);
		$this->ale->setConfig('serverError', 'returnParsed');
		$this->mail = new phpmailer();
		$this->mail->IsHTML(true);
		$this->mail->IsMail();
		//$this->mail->IsSMTP();
		$this->mail->From = $this->absenderMail;
	}
	
	public function run( $run=null ) {
		$out = '';
		if ( $run === null && !$this->charID && !$this->corpID) {
			if ($res = $this->query("SELECT c.id, c.name from ".db_tab_cron." c WHERE DATE_SUB(NOW(), INTERVAL c.interwal MINUTE) >= c.time AND status = 1 ORDER BY c.id LIMIT 5;")) {
				while($row = $res->fetch_assoc()) {
					$this->cronID = $row['id'];
					$out .= $this->$row['name']();
					$this->exec_query("UPDATE ".db_tab_cron." SET time=DATE_ADD(NOW(),INTERVAL -10 MINUTE) WHERE id={$row['id']};");
				}
			}
		} 
		
		if ( $this->charID ) {
			$run = 'none';
			$this->cronID = 6;
			$out .= $this->checkMains();
			$this->cronID = 7;
			$out .= $this->checkAlts();
		}
		
		if ( $this->corpID ) {
			$run = 'none';
			$this->cronID = 0;
			$out .= $this->StarbaseList();
			$this->cronID = 1;
			$out .= $this->getAssets();
			$this->cronID = 2;
			$out .= $this->StarbaseDetail();
			//$out .= $this->StarbaseFuel();
		}
		
		return $out;
	}
	
	public function setCorpID( $corpID ) {
		if (!empty($corpID) && !is_numeric($corpID))
			$this->corpID = null;
		else
			$this->corpID = $corpID;
	}
	
	public function setCharID( $charID ) {
		if (!empty($charID) && !is_numeric($charID))
			$this->charID = null;
		else
			$this->charID = $charID;
	}
	
	public function serverStatus() {
		$status = $this->ale->server->ServerStatus();
		if ($status->error) 
			return false;
		return true;
	}
	
	private function connect($parms) {
		if ( !$this->db ) $this->db = new mysqli( $parms['cache']['host'], $parms['cache']['user'], $parms['cache']['password'], $parms['cache']['database'] );
		if ( $this->db->connect_error ) {
			$this->logerror("SQL-Error Verbindung zum Server nicht erfolgreich! Beende Script.");
			die;
		}
	}
	
	private function logerror($msg, $typ=0, $corpID=0) {
		if ( $typ!=0 || $corpID!=0 ) {
			$str = "INSERT INTO ".db_tab_log."
					SET typ	  = '".$typ."',
					  corpID  = '".$corpID."',
					  date	  = UNIX_TIMESTAMP(),
					  comment = '".$this->escape($msg)."';";
			$this->exec_query( $str );
		}
		else {
			if ( !is_dir( $this->logdir ) ) mkdir( $this->logdir );
			$logfile = $this->logdir . 'crone.log';
			file_put_contents( $logfile, "\n".date('c')."\n".$msg."\n", FILE_APPEND );
		}
	}
	
	private function query( $query ) {
		if( !$result = $this->db->query( $query ) ) { 
			if( $this->db->error )
				$this->logerror( $this->my_error( sprintf("Errormessage: %s\n", $this->db->error) ) );
			return 0;
		}
		$this->numQueries ++;
		return ( $result );
	}
	
	private function exec_query( $query ) {
		if( !$this->db->query( $query ) ) {
			if( $this->db->error )
				$this->logerror( $this->my_error( sprintf("Errormessage: %s\n", $this->db->error) ) );
			return 0;
		}
		$this->numQueries ++;
		return ( $this->db->affected_rows );
	}
	
	private function escape($string) {
		if ( is_array ( $string ) ) {
			foreach( $string as $key => $value ){
				if ( is_array ( $value ) )
					$new_arr [ $key ] = $this->escape($value);
				else
					$new_arr [ $key ] = get_magic_quotes_gpc()?$this->db->real_escape_string( stripslashes($value) ):$this->db->real_escape_string( $value );
			}
			return $new_arr;
		} else {
			$string = get_magic_quotes_gpc()?$this->db->real_escape_string( stripslashes($string) ):$this->db->real_escape_string( $string );
			return $string;
		}
	}
	
	private function getMains() {
		$mains = array();
		if ( $this->charID )
			$res = $this->query( "SELECT charID, username, keyID, vCODE, accessMask, corpID, email FROM ".db_tab_user." WHERE active=1 AND charID = '".$this->charID."';" );
		else
			$res = $this->query( "SELECT charID, username, keyID, vCODE, accessMask, corpID, email FROM ".db_tab_user." WHERE active=1;" );
		while ( $row = $res->fetch_assoc() ) {
			if ( $row ) $mains[] = $row;
		} $res->close();
		
		return $mains;
	}
	
	private function getAlts() {
		$alts = array();
		if ( $this->charID )
			$res = $this->query( "SELECT * FROM ".db_tab_alts." WHERE mainCharID = '".$this->charID."' GROUP BY charID;" );
		else
			$res = $this->query( "SELECT * FROM ".db_tab_alts.";" );
		while ( $row = $res->fetch_assoc() ) {
			if ( $row ) $alts[] = $row;
		} $res->close();
		
		return $alts;
	}
	
	private function get_tower_count() {
		$res = $this->query("SELECT Count(itemID) AS alle FROM ".db_tab_pos.";");
		$row = $res->fetch_assoc();
		$res->close();
		
		return $row['alle'];
	}
	
	private function get_fullApis() {
		$apis = array();
		if ( $this->corpID )
			$res = $this->query("SELECT * FROM ".db_tab_user_fullapi." WHERE status=1 AND corpID = '".$this->corpID."';");
		else
			$res = $this->query("SELECT * FROM ".db_tab_user_fullapi." WHERE status=1;");
		while ( $row = $res->fetch_assoc() ) {
			if($row) $apis[] = $row;
		} $res->close();
		
		return $apis;
	}
	
	private function del_fullApi( $charID ) {
		//$result = $this->exec_query("UPDATE ".db_tab_user_fullapi." SET status=0 WHERE charID='$charID';");
		$result = $this->exec_query("UPDATE ".db_tab_user_fullapi." SET errorcount = if(status = 0, 0, errorcount + 1), status = if(errorcount >= 3, 0, 1) WHERE charID='$charID';");
		
		return $result;
	}
	
	private function checkMains() {
		$time_start = microtime(true);
		$this->ale->setConfig('parserClass', 'AleParserXMLElement');
		$mains = $this->getMains();
		$x=0;
		$out = '';
		foreach ( $mains as $char ) {
			if ( $char['vCODE'] == '' || $char['keyID'] == '' ) {
				$this->setInactive( $char['charID'] ); // Set acc inactive
				continue;
			}
			if ( $char['accessMask'] == '0' ) {
				$this->setInactive( $char['charID'] ); // Set acc inactive
				continue;
			}
			
			$data = array( 'name' => 'checkMains', 'apifull' => 2 );
			$data['corpID'] = $corpID = $char['corpID'];
			$data['api'] = $char;
			$this->ale->setKey( $char['keyID'], $char['vCODE'] );
			//all errors are handled by exceptions
			try {
				//let's fetch characters first.
				$APIKeyInfo = $this->ale->account->APIKeyInfo();
				
				if ( $APIKeyInfo->error ) {
					$xml = $APIKeyInfo->getSimpleXMLElement();
					if ( $this->apiErrorHandler( $xml->error, $data ) ) {
						$out .= $char['username']. " - set incative\n";
						unset ( $APIKeyInfo );
						continue;
					}
				}
				
				$xml = $APIKeyInfo->result->key->toArray();
				foreach ( $xml['characters'] as $character ) {
					if( (string)$character['characterID'] == $char['charID'] ) {
						$this->setAccessMask( $char['charID'], (int)$xml['accessMask'] );
				
						if( (string)$character['corporationID'] == $char['corpID'] ) {
							//  prüfen ob inactive...
							$inactive = $this->checkInactive( $char['charID'] );
							if( $inactive ) {
								$this->setActive( $char['charID'] ); // acc activieren
							}
						} 
						else {
							//  Corp wechseln.. und Email an User...
							$this->changeCorp( $char['charID'], $char['corpID'],(string)$character['corporationID'],(string)$character['corporationName'] );
							$this->sendMail( $char['charID'], true );
						}
					}
				}
				unset ( $APIKeyInfo, $xml );
				$x++;
			} catch (Exception $e) {
				$out .= $e->getMessage();
				$this->logerror( $e->getMessage(), $this->cronID, 0 );
			}
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'checkMains: ' . $x . '/' . count($mains) . ' account(s), done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return format( $out );	
		
	}
	
	private function checkAlts() {
		$time_start = microtime(true);
		$this->ale->setConfig('parserClass', 'AleParserXMLElement');
		$alts = $this->getAlts();
		$x=0;
		$out = '';
		foreach ( $alts as $char ) {
			if ( $char['userAPI'] == '' || $char['userID'] == '' ) {
				$this->exec_query("DELETE FROM ".db_tab_alts." WHERE charID='{$char['charID']}' AND mainCharID='{$char['mainCharID']}';");
				continue;
			}
			if ( $char['accessMask'] == '0' ) {
				$out .= $char['charName']. " - del\n";
				$this->exec_query("DELETE FROM ".db_tab_alts." WHERE charID='{$char['charID']}' AND mainCharID='{$char['mainCharID']}';");
				continue;
			}
			$data = array( 'name' => 'checkAlts', 'apifull' => 3 );
			$data['corpID'] = $corpID = $char['corpID'];
			$data['api'] = $char;
			$this->ale->setKey( $char['userID'], $char['userAPI'] );
			//all errors are handled by exceptions
			try {
				//let's fetch characters first.
				$APIKeyInfo = $this->ale->account->APIKeyInfo();
				
				if ( $APIKeyInfo->error ) {
					$xml = $APIKeyInfo->getSimpleXMLElement();
					if ( $this->apiErrorHandler( $xml->error, $data ) ) {
						$out .= $char['charName']. " deleted\n";
						unset ( $APIKeyInfo );
						continue;
					}
				}
				$xml = $APIKeyInfo->result->key->toArray();
				foreach ( $xml['characters'] as $character ) {
					if( (string)$character['characterID'] == $char['charID'] ) {
						$this->setAccessMask( $char['charID'], (int)$xml['accessMask'] );
						
						if( (string)$character['corporationID'] != $char['corpID'] ) {
							//  Corp wechseln..
							$this->changeCorp( $char['charID'], $char['corpID'],(string)$character['corporationID'],(string)$character['corporationName'], true );
						}
					}
				}
				unset ( $APIKeyInfo, $xml );
				$x++;
			} catch (Exception $e) {
				$out .= $e->getMessage();
				$this->logerror( $e->getMessage(), $this->cronID, 0 );
			}
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'checkAlts: ' . $x . '/' . count($alts) . ' account(s), done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return format( $out );
	}
	
	private function setAccessMask( $charID, $mask ) {
		return $this->exec_query( "UPDATE ".db_tab_user." SET accessMask='{$mask}' WHERE charID='{$charID}';" );
	}
	
	private function sendMail( $charID, $corp=false ) {
		return false;
	}
	
	private function setInactive($charID) {
		return $this->exec_query( "UPDATE ".db_tab_user." SET active=0, vCODE='' WHERE charID='".$charID."';" );
	}
	
	private function setActive($charID) {
		return $this->exec_query( "UPDATE ".db_tab_user." SET active=1 WHERE charID='".$charID."';" );
	}
	
	private function checkInactive($charID) {
		$res = $this->query( "SELECT charID FROM ".db_tab_user." WHERE charID='".$charID."' AND active=0;" );
		if ( $res->num_rows > 0 )
			return true;
		else
			return false;
	}
	
	private function checkEmail($charID) {
		$res = $this->query( "SELECT charID FROM ".db_tab_user." WHERE charID='".$charID."' AND email='';" );
		if ( $res->num_rows > 0 )
			return true;
		else
			return false;
	}
	
	private function changeCorp($charID,$oldcorpID,$corpID,$corpName,$alt=false) {		
		$this->ale->setCharacterID( $charID );
		try {
			$CharacterInfo = $this->ale->eve->CharacterInfo();
			if ( !$CharacterInfo->error ) {
				$allyid = (string) $CharacterInfo->result->allianceID == 0 ? 'None' : (string) $CharacterInfo->result->allianceID;
				$allyname = $allyid == '0' ? 'None' : (string) $CharacterInfo->result->alliance;
				
				if ($allyid == "0") {
					$allyid = "None";
					$allyname = "None";
				}
			
				if( !$alt ) {
					$this->exec_query( "UPDATE ".db_tab_user." SET corpID='".$corpID."' WHERE charID='".$charID."';" );
					$res = $this->query( "SELECT * FROM ".db_tab_user_roles." WHERE charID = '".$charID."' AND roleID=2;" ); //  ADMIN ???
					
					//  Kein Admin = alle roles entfernen weil neue corp
					if ( $res->num_rows == 0 ) { 
						$this->exec_query( "DELETE FROM ".db_tab_user_roles." WHERE charID = '".$charID."';" );
					}
				} 
				else {
					$this->exec_query("UPDATE ".db_tab_alts." SET corpID='".$corpID."', pos=0, pos_edit=0, silo=0 WHERE charID='".$charID."';");
				}
				$now=time();
				$str = "INSERT INTO ".db_tab_corpchange." 
						SET userid	= '".$charID."',
						  oldcorpid	= '".$oldcorpID."',
						  newcorpid = '".$corpID."',
						  changed	= '".$now."';";
				$res = $this->exec_query( $str );
				
				if ( $this->addAlly( $allyid, $allyname ) ) {
					$this->logerror("Alliance: ".$allyname." wurde der Datenbank hinzugefügt", $this->cronID, 0);
				}
				
				if ( $this->addCorp( $corpID, $corpName, $allyid ) ) {
					$this->logerror("Corperation: ".$corpName." wurde der Datenbank hinzugefügt", $this->cronID, 0);
				}
			}
		} catch (Exception $e) {
			$this->logerror( $e->getMessage(), $this->cronID, 0 );
		}
	}
	
	private function addCorp($corpid,$corpname,$allyid)	{
		$res = $this->query("SELECT name FROM ".db_tab_corps." WHERE id='".$corpid."';");
		if ( $res->num_rows > 0 ) {
			return false;
		} else {
			$this->exec_query("INSERT INTO ".db_tab_corps." SET id='".$corpid."',name='".$corpname."',ally='".$allyid."',timestamp='".date("YmdHis")."';");
			return true;
		}
	}

	private function addAlly($allyid,$allyname)	{
		$res = $this->query("SELECT name FROM ".db_tab_allys." WHERE id='".$allyid."';");
		if ( $res->num_rows > 0 ) {
			return false;
		} else {
			$this->exec_query("INSERT INTO ".db_tab_allys." SET id='".$allyid."',name='".$allyname."',timestamp='".date("YmdHis")."';");
			return true;
		}
	}
	
	private function update_sov() {
		$this->ale->setConfig('parserClass', 'SimpleXMLElement');
		$query = ("REPLACE INTO ".db_tab_sovereignty." SET
					solarSystemID   = '%solarSystemID%',
					allianceID      = '%allianceID%',
					factionID       = '%factionID%',
					solarSystemName = '%solarSystemName%',
					corporationID   = '%corporationID%';");
		try {
			$Sovereignty = $this->ale->map->Sovereignty();
		
			foreach ($Sovereignty->result->rowset->row as $row){
				$str = $query;
				foreach ($row->attributes() as $name => $value){
					$str = str_replace("%".(string) $name."%", addslashes((string) $value), $str);
				}
				$return = $this->exec_query( $str );
			}
			unset ( $Sovereignty );
		} catch (Exception $e) {
			$this->logerror( $e->getMessage(), $this->cronID, 0 );
		}
		return format("Update Sovereignty...\n");
	}
	
	private function update_outposts() {		
		$this->ale->setConfig('parserClass', 'SimpleXMLElement');
		$query = ("REPLACE INTO ".db_tab_outposts." SET
					stationID       = '%stationID%',
					stationName     = '%stationName%',
					stationTypeID   = '%stationTypeID%',
					solarSystemID   = '%solarSystemID%',
					corporationID   = '%corporationID%',
					corporationName = '%corporationName%';");
		try {
			$ConquerableStationList = $this->ale->eve->ConquerableStationList();
			
			foreach ($ConquerableStationList->result->rowset->row as $row){
				$str = $query;
				foreach ($row->attributes() as $name => $value){
					$str = str_replace("%".(string) $name."%", addslashes((string) $value), $str);
				}
				$return = $this->exec_query( $str );
			}
			unset ( $ConquerableStationList );
		} catch (Exception $e) {
			$this->logerror( $e->getMessage(), $this->cronID, 0 );
		}
		return format("Update ConquerableStationList...\n");
	}
	
	private function update_refTypes() {
		$this->ale->setConfig('parserClass', 'SimpleXMLElement');
		$query = ("REPLACE INTO ".db_tab_refTypes." SET
					refTypeID       = '%refTypeID%',
					refTypeName     = '%refTypeName%';");
		try {
			$RefTypes = $this->ale->eve->RefTypes();
			foreach ($RefTypes->result->rowset->row as $refType) {
				$str = $query;
				foreach ($refType->attributes() as $name => $value) {
					$str = str_replace("%".(string) $name."%", addslashes((string) $value), $str);
				}
				$return = $this->exec_query( $str );
			}
			unset ($RefTypes); // manual garbage collection
		} catch (Exception $e) {
			$this->logerror( $e->getMessage(), $this->cronID, 0 );
		}
		
		return format("Update refTypes...\n");
	}
	
	private function update_Corporation() {
		$this->ale->setConfig('parserClass', 'SimpleXMLElement');
		$res = $this->query("SELECT id FROM ".db_tab_corps." order by name;");
		while ( $row = $res->fetch_assoc() ) {
			if ($row) {
				$params = array('corporationID' => $row['id']);
				try {
					$CorporationSheet = $this->ale->corp->CorporationSheet($params, ALE_AUTH_NONE);
					// print_r($CorporationSheet); 
					$allyID   = (string) $CorporationSheet->result->allianceID == 0 ? 'None' : (string) $CorporationSheet->result->allianceID;
					$allyName = $allyID == 'None' ? 'None' : addslashes((string) $CorporationSheet->result->allianceName);
					$corpName = addslashes((string) $CorporationSheet->result->corporationName);
					$ticker   = addslashes((string) $CorporationSheet->result->ticker);
					
					if (!$corpName) {
						$this->exec_query("DELETE FROM ".db_tab_corps." WHERE id='{$row['id']}';");
					} else {
						// echo $corpName.' - ';
						// echo $allyName.'<br>';
						$this->exec_query("REPLACE INTO ".db_tab_corps." SET
									id     = '{$row['id']}',
									name   = '{$corpName}',
									ticker = '{$ticker}',
									ally   = '{$allyID}';");
						if ($allyName != 'None') {
							$this->exec_query("REPLACE INTO ".db_tab_allys." SET
									id     = '{$allyID}',
									name   = '{$allyName}';");
						}
					}
					unset($CorporationSheet,$allyID,$allyName,$corpName,$ticker);
				} catch (Exception $e) {
					$this->logerror( $e->getMessage(), $this->cronID, 0 );
				}
			}	
		}
		
		$res = $this->query("SELECT corpID FROM ".db_tab_user." GROUP BY corpID ORDER BY corpID;");
		while ( $row = $res->fetch_assoc() ) {
			if ($row) {
				$params = array('corporationID' => $row['corpID']);
				try {
					$CorporationSheet = $this->ale->corp->CorporationSheet($params, ALE_AUTH_NONE);
					// print_r($CorporationSheet); 
					$allyID   = (string) $CorporationSheet->result->allianceID == 0 ? 'None' : (string) $CorporationSheet->result->allianceID;
					$allyName = $allyID == 'None' ? 'None' : addslashes((string) $CorporationSheet->result->allianceName);
					$corpName = addslashes((string) $CorporationSheet->result->corporationName);
					$ticker   = addslashes((string) $CorporationSheet->result->ticker);
					
					if (!$corpName) {
						$this->exec_query("DELETE FROM ".db_tab_corps." WHERE id='{$row['corpID']}';");
					} else {
						// echo $corpName.' - ';
						// echo $allyName.'<br>';
						$this->exec_query("REPLACE INTO ".db_tab_corps." SET
									id     = '{$row['corpID']}',
									name   = '{$corpName}',
									ticker = '{$ticker}',
									ally   = '{$allyID}';");
						if ($allyName != 'None') {
							$this->exec_query("REPLACE INTO ".db_tab_allys." SET
									id     = '{$allyID}',
									name   = '{$allyName}';");
						}
					}
					unset($CorporationSheet,$allyID,$allyName,$corpName,$ticker);
				} catch (Exception $e) {
					$this->logerror( $e->getMessage(), $this->cronID, 0 );
				}
			}	
		}
		
		$result = $this->query("SELECT corpID FROM ".db_tab_alts." GROUP BY corpID ORDER BY corpID;");
		while ( $row = $res->fetch_assoc() ) {
			if ($row) {
				$params = array('corporationID' => $row['corpID']);
				try {
					$CorporationSheet = $this->ale->corp->CorporationSheet($params, ALE_AUTH_NONE);
					// print_r($CorporationSheet); 
					$allyID   = (string) $CorporationSheet->result->allianceID == 0 ? 'None' : (string) $CorporationSheet->result->allianceID;
					$allyName = $allyID == 'None' ? 'None' : addslashes((string) $CorporationSheet->result->allianceName);
					$corpName = addslashes((string) $CorporationSheet->result->corporationName);
					$ticker   = addslashes((string) $CorporationSheet->result->ticker);
					
					if (!$corpName) {
						$this->exec_query("DELETE FROM ".db_tab_corps." WHERE id='{$row['corpID']}';");
					} else {
						// echo $corpName.' - ';
						// echo $allyName.'<br>';
						$this->exec_query("REPLACE INTO ".db_tab_corps." SET
									id     = '{$row['corpID']}',
									name   = '{$corpName}',
									ticker = '{$ticker}',
									ally   = '{$allyID}';");
						if ($allyName != 'None') {
							$this->exec_query("REPLACE INTO ".db_tab_allys." SET
									id     = '{$allyID}',
									name   = '{$allyName}';");
						}
					}
					unset($CorporationSheet,$allyID,$allyName,$corpName,$ticker);
				} catch (Exception $e) {
					$this->logerror( $e->getMessage(), $this->cronID, 0 );
				}
			}	
		}
		
		return format("Update Corporation...\n");
	}
	
	private function doLocations(array $key, $log=false) {
		$this->_table_pos = db_tab_pos;
		$this->_corpID = $key['corpID'];
		
		$out = '';
		$ids = array();
		
		//print_r($key);
		$this->ale->setConfig('parserClass', 'AleParserXMLElement');
		$this->ale->setConfig('serverError', 'throwException');
		
			//get ALE object
			try {
				//set api key
				$this->ale->setKey( $key['keyID'], $key['vCODE'], $key['charID'] );
				//all errors are handled by exceptions
				//let's check the key first.
				$keyinfo = $this->ale->account->APIKeyInfo();
				if( !(intval($keyinfo->result->key->accessMask) & 16777216) ) {
					$out .= 'You need a Key with access to Locations'."\n";
					return $out;
				}
				$str = "SELECT * FROM {$this->_table} WHERE corpID='{$this->_corpID}'";

				if ($res = $this->query( $str )) {
					while ($row = $res->fetch_assoc()) {
						$ids[] = $row['itemID'];
						$item[$row['itemID']] = $row['locationID'];
					}
				}

				$parmsID = array('ids' => implode(',', $ids)); 
				$Locations = $this->ale->corp->Locations($parmsID);
				// print_it($Locations->asXML());
				
				foreach($Locations->result->locations as $loc) {
					$str = "UPDATE {$this->_table} SET 
						itemName = '".$this->escape((string)$loc->itemName)."', 
						x = '".(string)$loc->x."', 
						y = '".(string)$loc->y."', 
						z = '".(string)$loc->z."' 
						WHERE itemID = '".(string)$loc->itemID."'";
					if (!$this->query($str)) { break; }
				}
				
			} catch (Exception $e) {
				$out .= $e->getMessage()."\n";
				$this->logerror( $e->getMessage(), $this->cronID, 0 );
				return $out;
			}
			//if($log === true) $this->search_loc($ids);
			if($log === true) $this->search_loc();
		
		return $out;
	}
	
	private function search_loc() {
        $res = $this->query("SELECT itemID, locationID, x, y, z, moonID FROM ".$this->_table_pos." WHERE corpID = ".$this->_corpID."");
        $res_items =  $this->query("SELECT itemID, locationID, x, y, z FROM ".$this->_table." WHERE corpID = ".$this->_corpID." AND pos IS NULL");
        $pos_location = array();
        while($pos = $res->fetch_assoc()){
            $pos_location[0] = $pos['x'];
            $pos_location[1] = $pos['y'];
            $pos_location[2] = $pos['z'];
            while($item = $res_items->fetch_assoc()){
                $item_location[0] = $item['x'];
                $item_location[1] = $item['y'];
                $item_location[2] = $item['z'];
                if($this->entfernung($pos_location, $item_location) <= 200000){
                    $this->query("UPDATE ".$this->_table." SET pos = ".$pos['moonID']." WHERE itemID = ".$item['itemID']."");
                }
            }
			$res_items->data_seek(0);
        }
    }
	
	private function entfernung($xyz1, $xyz2) {
        $xges = $xyz1[0]-$xyz2[0];
        $yges = $xyz1[1]-$xyz2[1];
        $zges = $xyz1[2]-$xyz2[2];
        $entf = sqrt((pow($xges,2) + pow($yges,2) + pow($zges,2)));
        return ceil($entf);
    }
	
	private function StarbaseList() {
		$this->_table = db_tab_pos; /* set $this->_table for doLocations() */
		
		$time_start = microtime(true);
		
		$out = '';
		$apis = $this->get_fullApis();
		
		foreach( $apis as $key ) {
			$data = array( 'name' => 'StarbaseList', 'apifull' => 1 );
			$this->ale->setConfig('parserClass', 'AleParserXMLElement');
			$this->ale->setConfig('serverError', 'returnParsed');
			$this->ale->setKey( $key['keyID'], $key['vCODE'], $key['charID'] );
			$data['corpID'] = $corpID = $key['corpID'];
			$data['allyID'] = $allyID = $key['allyID'];
			$data['api'] = $key;
			
			try {
				$StarbaseList = $this->ale->corp->StarbaseList();
				
				if ( $StarbaseList->error ) {
					$xml = $StarbaseList->getSimpleXMLElement();
					if ( $this->apiErrorHandler( $xml->error, $data ) ) {
						unset ( $StarbaseList );
						continue;
					}
				}
				// $st = microtime(true);
				$towers = array();
				$pos = array();
				$str = ("SELECT p.itemID, p.moonID
					   FROM ".db_tab_pos." p
					   WHERE p.corpID='".$data['corpID']."';");
				$res = $this->query( $str );
				while ( $row = $res->fetch_assoc() ) {
					if($row) $towers[ $row['itemID'] ] = $row['moonID'];
				} $res->close();
				// $out .= round((microtime(true) - $st),4)." sec\n";

				if ( !empty($StarbaseList) ) {
					foreach ( $StarbaseList->result->starbases as $Starbase ) { 
						$pos[] = (string) $Starbase->itemID;
					}
					
					if ( is_array($towers) ) {
						foreach ( $towers as $itemID => $moonID ) {
							if ( !in_array($itemID, $pos) ) {
								$this->exec_query("DELETE FROM ".db_tab_pos." WHERE itemID={$itemID};");
								$this->exec_query("DELETE FROM ".db_tab_pos_fuel." WHERE itemID={$itemID}");
								//$this->logerror( "Starbase removed (".$this->moonIDtoName($moonID).")", 2, $data['corpID'] );
								$this->logerror( "Starbase removed (".$this->moonIDtoName($moonID).")", $this->cronID, $data['corpID'] );
							}
						}
					}
					
					foreach ( $StarbaseList->result->starbases as $Starbase ) {
						$baseid = (string) $Starbase->itemID;
						//  echo $baseid.'<br>';
						if ( !empty($baseid) ) {
							$res = $this->query( "SELECT itemID FROM ".db_tab_pos." WHERE itemID='$baseid';" );
							if ( $res->num_rows > 0 ) {					
								$str = "UPDATE ".db_tab_pos." SET 
									typeID			= '".(string) $Starbase->typeID."',
									locationID		= '".(string) $Starbase->locationID."', 
									moonID			= '".(string) $Starbase->moonID."', 
									state			= '".(string) $Starbase->state."',
									onlineTimestamp	= '".(string) $Starbase->onlineTimestamp."'
									WHERE itemID	= '".(string) $Starbase->itemID."'";
							} else {
								$str = "INSERT INTO ".db_tab_pos." SET 
									itemID			= '".(string) $Starbase->itemID."',
									corpID			= '".$corpID."',
									typeID			= '".(string) $Starbase->typeID."',
									locationID		= '".(string) $Starbase->locationID."', 
									moonID			= '".(string) $Starbase->moonID."', 
									state			= '".(string) $Starbase->state."', 
									stateTimestamp	= '".(string) $Starbase->stateTimestamp."', 
									onlineTimestamp = '".(string) $Starbase->onlineTimestamp."'
								ON DUPLICATE KEY UPDATE
									corpID			= '".$corpID."',
									typeID			= '".(string) $Starbase->typeID."',
									locationID		= '".(string) $Starbase->locationID."', 
									moonID			= '".(string) $Starbase->moonID."', 
									typeID			= '".(string) $Starbase->typeID."',
									state			= '".(string) $Starbase->state."', 
									stateTimestamp	= '".(string) $Starbase->stateTimestamp."', 
									onlineTimestamp = '".(string) $Starbase->onlineTimestamp."';";
								//$this->logerror( "Starbase add (".$this->moonIDtoName((string) $Starbase->moonID).")", 2, $data['corpID'] );
								$this->logerror( "Starbase add (".$this->moonIDtoName((string) $Starbase->moonID).")", $this->cronID, $data['corpID'] );
							} 
							$this->exec_query( $str );
						}
					}
				} 
				unset ( $StarbaseList );
				$out .= $this->doLocations($key);
			} catch (Exception $e) {
				$out .= $e->getMessage()."\n";
				$this->logerror( $e->getMessage(), $this->cronID, 0 );
			}
		} //  foreach $apis end
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		$out .= 'StarbaseList: ' . count($apis) . ' account(s) done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return format( $out );
	}
	
	private function StarbaseDetail() {
		$time_start = microtime(true);
		
		$apis = $this->get_fullApis();
		$towercount = $this->get_tower_count();
		if ( $this->corpID === null )
			$out = $towercount . " Starbases to Fuel...\n\n";
		else $out = '';

		foreach ( $apis as $key ) {
			// set_time_limit( 90 );
			$data = array( 'name' => 'StarbaseDetail', 'apifull' => 1 );
			$this->ale->setConfig('serverError', 'returnParsed');
			$this->ale->setConfig('parserClass', 'AleParserXMLElement');
			$this->ale->setKey( $key['keyID'], $key['vCODE'], $key['charID'] );
			$data['corpID'] = $corpID = $key['corpID'];
			$data['allyID'] = $allyID = $key['allyID'];
			$data['api'] = $key;
			$tower = $this->get_tower( $corpID );
			$y=0;
			$time_tower_start = microtime(true);
			if ($tower) { 
				foreach ( $tower as $pos ) {
					set_time_limit( 30 ); 
					if ( $pos != '' ) { 
						try {
							$StarbaseDetail = $this->ale->corp->StarbaseDetail( array('itemID' => $pos) );
							// echo $pos.'<br>'; print_r($StarbaseDetail); break;
			
							if ( $StarbaseDetail->error ) {
								$data['itemID'] = $pos;
								$xml = $StarbaseDetail->getSimpleXMLElement();
								if ( $this->apiErrorHandler( $xml->error, $data ) ) {
									unset ( $StarbaseDetail );
									break;
								}
							}
							$res = $this->query("SELECT * FROM ".db_tab_pos." WHERE itemID='".$pos."' LIMIT 1;");
							$row = $res->fetch_assoc();
							$res->close();
							
							$combatSettings = $StarbaseDetail->result->combatSettings->toArray();
							
							$first = "UPDATE ".db_tab_pos." 
								SET state					= '".(string) $StarbaseDetail->result->state."',
									stateTimestamp			= '".(string) $StarbaseDetail->result->stateTimestamp."',
									onlineTimestamp			= '".(string) $StarbaseDetail->result->onlineTimestamp."',
									usageFlags				= '".(string) $StarbaseDetail->result->generalSettings->usageFlags."',
									deployFlags				= '".(string) $StarbaseDetail->result->generalSettings->deployFlags."',
									allowCorporationMembers	= '".(string) $StarbaseDetail->result->generalSettings->allowCorporationMembers."',
									allowAllianceMembers	= '".(string) $StarbaseDetail->result->generalSettings->allowAllianceMembers."',
									useStandingsFrom		= '".(string) $combatSettings['useStandingsFrom']['ownerID']."',
									onStandingDrop			= '".(string) $combatSettings['onStandingDrop']['standing']."',
									onStatusDrop_enabled	= '".(string) $combatSettings['onStatusDrop']['enabled']."',
									onStatusDrop_standing	= '".(string) $combatSettings['onStatusDrop']['standing']."',
									onAggression			= '".(string) $combatSettings['onAggression']['enabled']."',
									onCorporationWar		= '".(string) $combatSettings['onCorporationWar']['enabled']."'
								WHERE itemID='".$pos."';";
							$this->exec_query( $first );
							
							$lo = $hw = 0;
							$this->exec_query("DELETE FROM ".db_tab_pos_fuel." WHERE itemID={$pos}");
							foreach ( $StarbaseDetail->result->fuel as $fuel ) {
								$this->exec_query("INSERT INTO ".db_tab_pos_fuel." 
									SET itemID='".$pos."', typeID='".(int)$fuel->typeID."', quantity='".(int)$fuel->quantity."'
									ON DUPLICATE KEY UPDATE quantity='".(int)$fuel->quantity."';");
							}
							
							$y++;
							unset ( $StarbaseDetail );
						} catch (Exception $e) {
							$out .= $e->getMessage()."\n";
							$this->logerror( $e->getMessage(), $this->cronID, 0 );
						}
					}
				}
			}
			$time_tower_end = microtime(true);
			$time = $time_tower_end - $time_tower_start;
			$out .= 'APIChar: ' . $key['userName'] . ' - ' . $y . '/' . count($tower) . ' Starbase(s) done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . 'kb memory used.';
			$out .= "\n";
			unset ($tower);
			//$this->logerror("Starbase Fuel update", 1, $corpID);
			$this->logerror("Starbase Fuel update", $this->cronID, $corpID);
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if ( $this->corpID === null )
			$out .= "\n StarbaseDetail: ". count($apis) . ' account(s), done in ' . round($time, 4) . ' seconds, ' . round((memory_get_peak_usage()/1024), 2) . "kb memory total used.\n";
		
		return format( $out );
		
	}
	
	private function StarbaseFuel() {
		$time_start = microtime(true);
		
		$_min_time = 24; // Stunden
		
		$_table_tower = 'fsrtool_pos';
		$_table_towerFuel = 'fsrtool_pos_fuel';
		$_table_towerRes = 'fsrtool_eve_invcontroltowerresources';
		$_table_sov = 'fsrtool_api_sovereignty';
		$_table_mail = 'fsrtool_pos_maillist';
		
		$offset = date_offset_get(date_create());
		$now = (time()-$offset)/3600;
		
		$apis = $this->get_fullApis();
		
		$res = $this->query("SELECT * FROM {$_table_towerRes}");
		while($row = $res->fetch_assoc()) {
			$towerRes[$row['controlTowerTypeID']][$row['resourceTypeID']] = $row['quantity'];
		}
		// print_it($towerRes);
		
		foreach ( $apis as $key ) {
			$lowFuel = array();
			
			$corpID = $key['corpID'];
			$allyID = $key['allyID'];
			$_min_time = $key['lowfueltime'];
			
			$resmail = $this->query("SELECT email FROM {$_table_mail} WHERE corpID = {$corpID}");
			if($resmail->num_rows >= 1) {
				while($rowmail = $resmail->fetch_assoc()){
					$mailrow[] = $rowmail['email'];
				}
			
				$sovres = $this->query("SELECT solarSystemID FROM {$_table_sov} WHERE allianceID = {$allyID}");
				if($sovres->num_rows >= 1) {
					while($sovrow = $sovres->fetch_assoc()){
						$sov[$row['solarSystemID']] = true; 
					}
				} else $sov = array();
				
				$res = $this->query("SELECT p.itemID, p.locationID, p.state, p.typeID as towerTypeID, p.moonID, p.stateTimestamp, f.typeID as fuelTypeID, f.quantity
					FROM {$_table_tower} as p
					INNER JOIN {$_table_towerFuel} as f ON p.itemID = f.itemID
					WHERE p.corpID = {$corpID} ORDER BY p.itemID, f.quantity DESC;");
				while($row = $res->fetch_assoc()) {
					if($row['fuelTypeID'] != 16275 && $row['state'] == 4) {
						if(isset($sov[$row['locationID']]))
							$row['quantity'] = $row['quantity'] * 0.75;
						if($towerRes[$row['towerTypeID']][$row['fuelTypeID']] >= 0) {
							$hoursago = ceil($now-(strtotime($row['stateTimestamp'])/3600));
							$time = ($row['quantity'] / $towerRes[$row['towerTypeID']][$row['fuelTypeID']]) - $hoursago;
							if($time <= $_min_time) {
								$lowFuel[$row['moonID']] = $time;
								#echo $this->moonIDtoName($row['moonID'])." - low on Fuel<br>";
							}
						}
					}
				}
				$text = '';
				if(count($lowFuel) >= 1) {
					foreach($lowFuel as $moonID => $time) {
						$text .= $this->moonIDtoName($moonID)." - low on Fuel ({$time} hours)<br />";
					}
					$this->send_lowFuelMail($mailrow, $text);
				}
			}
		}
	}
	
	private function send_lowFuelMail(array $emails, $text) {
		$this->mail->to = array();
		foreach($emails as $to) {
			$this->mail->AddAddress($to);
		}
		$Subject = 'Control Tower low on Fuel';
		$Body = 'Hello,<br />'
			.'<br />'
			.$text
			.'<br />'
			.'-- <br />'
			.'Regards Site Admin<br />';
		
		$this->mail->FromName  = 'FSR-Tool';
		$this->mail->Subject   = $Subject;
		$this->mail->Body      = $Body;
		
		return $this->mail->send();
	}
	
	private function moonIDtoName($moonID) {
		$res = $this->query("SELECT itemName FROM ".db_tab_mapdenormalize." WHERE itemID='".$moonID."' LIMIT 1;");
		$row = $res->fetch_assoc();
		$res->close();
		return $row['itemName'];
	}
	
	private function posItemIDtoMoonName($id) {
		$res = $this->query("SELECT map.itemName FROM ".db_tab_pos." pos INNER JOIN ".db_tab_mapdenormalize." map ON pos.moonID = map.itemID WHERE pos.itemID = {$id} LIMIT 1;");
		$row = $res->fetch_assoc();
		$res->close();
		return $row['itemName'];
	}
	
	private function get_tower($corpID) {
		$tower = array();
		$res = $this->query("SELECT itemID FROM ".db_tab_pos." WHERE corpID='".$corpID."';");
		while ( $row = $res->fetch_assoc() ) {
			if($row) $tower[]=$row['itemID'];
		} $res->close();
		
		return $tower;
	}
	
	private function assetsContent(SimpleXMLElement $contents, $fromItemID, $corpID, $lastFetch) {
		//$insert = "INSERT INTO fsrtool_assets_contents (fromItemID, itemID, typeID, quantity, flag, singleton, rawQuantity, contents) VALUES (?,?,?,?,?,?,?,?)";
		$insert = "INSERT INTO fsrtool_assets_contents (fromItemID, itemID, corpID, typeID, quantity, flag, singleton, rawQuantity, contents, lastFetch) 
			VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE 
				typeID = VALUES(typeID),
				quantity = VALUES(quantity),
				flag = VALUES(flag),
				singleton = VALUES(singleton),
				rawQuantity = VALUES(rawQuantity),
				contents = VALUES(contents),
				lastFetch = VALUES(lastFetch)";
		$update = "UPDATE fsrtool_assets_contents SET typeID=?, quantity=?, flag=?, singleton=?, rawQuantity=?, contents=? WHERE itemID=?";
		
		$stmt = $this->db->prepare($insert);

		$stmt->bind_param("ssiiiiiiii", $fromItemID, $itemID, $corpID, $typeID, $quantity, $flag, $singleton, $rawQuantity, $contents, $lastFetch);
		
		foreach ($contents as $row) {
			$itemID 	 = (string)$row['itemID'];
			$typeID		 = (string)$row['typeID'];
			$quantity	 = (string)$row['quantity'];
			$flag		 = (string)$row['flag'];
			$singleton	 = (string)$row['singleton'];
			$rawQuantity = (string)$row['rawQuantity'];
			$contents    = 0;
			//$lastFetch	 = $lastFetch;
			
			if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
				$contents = 1;
				$this->assetsContent($row->children()->rowset->row, $itemID, $corpID, $lastFetch);
			}
			/* Execute the statement */
			$stmt->execute();
			
			if ($stmt->error) {
				$out .= $stmt->error."\n";
			}
		}
		/* close statement */
		$stmt->close();
	}
	
	private function getAssets() {
		$time_start = microtime(true);
		$out = '';
		$x=0;
		
		$insert = "INSERT INTO fsrtool_assets (itemID, corpID, locationID, typeID, quantity, flag, singleton, rawQuantity, contents, lastFetch) 
			VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE 
				locationID = VALUES(locationID),
				typeID = VALUES(typeID),
				quantity = VALUES(quantity),
				flag = VALUES(flag),
				singleton = VALUES(singleton),
				rawQuantity = VALUES(rawQuantity),
				contents = VALUES(contents),
				lastFetch = VALUES(lastFetch)";
		$update = "UPDATE fsrtool_assets SET locationID=?, typeID=?, quantity=?, flag=?, singleton=?, rawQuantity=?, contents=? WHERE itemID=?";
		
		// $fuelarray = array(4051,4246,4247,4312,44,3683,3689,9832,9848,16272,16273,16274,16275,17887,17888,17889,24592,24593,24594,24595,24596,24597);
		// $fuelarray = array(44,3683,3689,9832,9848,16272,16273,16274,16275,17887,17888,17889,24592,24593,24594,24595,24596,24597);
		$fuelarray = array(4051,4246,4247,4312,16275,24592,24593,24594,24595,24596,24597);
		$reactors = array(16869,20175,22634,24684,30656);
		$silos = array(17982,14343,25270,25271,25280,25821,30655);
		$mins = array(34,35,36,37,38,39,40,11399, 29659,29660,29661,29662,29663,29664,32821,32822,32823,32824,32825,32826,32827,32828,32829,
			16633,16634,16635,16636,16637,16638,16639,16640,16641,16642,16643,16644,16646,16647,16648,16649,16650,16651,16652,16653); // RAW mins
		
		
		$apis = $this->get_fullApis();
		
		foreach( $apis as $apikey ) {
			set_time_limit(60);
			$data = array( 'name' => 'AssetList', 'apifull' => 1 );
			$this->ale->setConfig('parserClass', 'SimpleXMLElement');
			$this->ale->setConfig('serverError', 'returnParsed');
			$this->ale->setKey( $apikey['keyID'], $apikey['vCODE'], $apikey['charID'] );
			$data['corpID'] = $corpID = $apikey['corpID'];
			$data['allyID'] = $allyID = $apikey['allyID'];
			$data['api'] = $apikey;
			$apiSilos = array();
			$apiReactors = array();
			try {
				$xml = $this->ale->corp->AssetList();
				if ( $xml->error ) {
					if ( $this->apiErrorHandler( $xml->error, $data ) ) {
						unset ( $xml );
						continue;
					}
				}
				$res = $this->db->query("SELECT cacheTime FROM ".db_tab_silos_cachetimes." WHERE type = '1' AND corpID = '$corpID';");
				$row = $res->fetch_assoc();
				$cachetime = strtotime((string) $xml->cachedUntil) - 21600;
				if ( $row['cacheTime'] == $cachetime ) {
					unset ( $xml );
					continue;
				}
				
				//$this->exec_query("REPLACE INTO ".db_tab_silos_cachetimes." SET type = '1', corpID = '$corpID', cacheTime = '".strtotime((string) $xml->currentTime)."';");
				$this->exec_query("REPLACE INTO ".db_tab_silos_cachetimes." SET type = '1', corpID = '$corpID', cacheTime = '".$cachetime."';");
				$this->exec_query("DELETE FROM ".db_tab_corphanger." WHERE corpID = '$corpID';");
				//$this->exec_query("TRUNCATE fsrtool_assets");
				//$this->exec_query("TRUNCATE fsrtool_assets_contents");
				
				$res = $this->query("SELECT itemID, quantity, suspect FROM ".db_tab_silos." WHERE corpID = '$corpID';");
				while ( $row = $res->fetch_assoc() ) {
					if ($row) {
						$silobefore[$row['itemID']]['quantity'] = $row['quantity'];
						$silobefore[$row['itemID']]['suspect'] = $row['suspect'];
					}
				}
				$res->close();
				/* test */
				/* $stmt = $this->db->prepare($insert);
				$stmt->bind_param("ssiiiiiiii", $itemID, $corpID, $locationID, $typeID, $quantity, $flag, $singleton, $rawQuantity, $contents, $lastFetch); */
				/* test */
				foreach ($xml->result->rowset->row as $row)	{
					
					/* test */
					
					/* $itemID 	 = (string)$row['itemID'];
					$locationID	 = (string)$row['locationID'];
					$typeID		 = (string)$row['typeID'];
					$quantity	 = (string)$row['quantity'];
					$flag		 = (string)$row['flag'];
					$singleton	 = (string)$row['singleton'];
					$rawQuantity = (string)$row['rawQuantity'];
					$contents    = 0;
					$lastFetch   = $cachetime;
					
					if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
						$contents = 1;
						$this->assetsContent($row->children()->rowset->row, $itemID, $corpID, $cachetime);
					}
					// Execute the statement 
					$stmt->execute();
					
					if ($stmt->error) {
						$out .= $stmt->error."\n";
					} */
					
					/* test */
					
					/* CorpHanger */
					if ( ((string) $row['typeID'] == '17621' || (string) $row['typeID'] == '27') && (int) $row['singleton'] == 1 ) {
						foreach ($row->attributes() as $key => $val) {
							$output[(string) $key] = (string) $val;					
						}
						if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
						//$out .= count((array)$row->children())."\n";
						//$out .= (string) $row['itemID']."\n";
						//$out .= gettype($row->children()->rowset->row)."\n";
							
							foreach ($row->children()->rowset->row as $row) {
								if ( in_array((string) $row['typeID'],$fuelarray) ) {
									foreach ($row->attributes() as $name => $value) {
										$output['in'][(string) $name] = (string) $value;
									}
									$query = ("REPLACE INTO ".db_tab_corphanger." SET
										corpID		= '".$corpID."',
										itemID      = '".$output['in']['itemID']."',
										locationID  = '".$output['locationID']."',
										type      	= '".$output['typeID']."',
										typeID   	= '".$output['in']['typeID']."',
										quantity 	= '".$output['in']['quantity']."',
										flag     	= '".$output['in']['flag']."' ;");
									$this->exec_query( $query ); //  'Set Asset -> CorpHanger'
									unset ($output['in']);
								}
							}
						}
						unset ($output);
					}
					
					/* Silos */
					if ( in_array((string) $row['typeID'], $silos) && (int) $row['singleton'] == 1 && (int) $row['flag'] == 0 ) {
						foreach ($row->attributes() as $key => $val) {
							$output[(string) $key] = (string) $val;
						}
						if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
							foreach ($row->children()->rowset->row as $row) {
								foreach ($row->attributes() as $name => $value) {
									$output['silo'][(string) $name] = (string) $value;
								}
							}
						}
						// sort Couplin Arrays out that contain no mineral in groupID 18
						if ($output['typeID'] == 17982 && in_array($output['silo']['typeID'], $mins) || $output['typeID'] != 17982) {
							
							$apiSilos[] = $output['itemID'];
							// print_it ( $output );
							$res = $this->query("SELECT itemID, locationID, typeID FROM ".db_tab_silos." WHERE itemID = '".$output['itemID']."';");
							if ( $res->num_rows > 0 ) {
								
								$silores = $res->fetch_assoc();
								if( $silores['locationID'] != $output['locationID'] ) 
									$location = "locationID = '".$output['locationID']."', pos = NULL,"; 
								else $location = "";
								if ( isset($output['silo']['typeID']) && $silores['typeID'] == $output['silo']['typeID'] ) {
									$query = "UPDATE ".db_tab_silos." 
											  SET typeID	 = '".$output['silo']['typeID']."',
												  ".$location."
												  quantity	 = '".$output['silo']['quantity']."',
												  emptyTime	 = '0'
											  WHERE itemID = '".$output['itemID']."';";
								}
								elseif( isset($output['silo']['typeID']) && $silores['typeID'] != $output['silo']['typeID']) {
									$query = "UPDATE ".db_tab_silos." 
											  SET ".$location."
												  typeID	 = '".$output['silo']['typeID']."',
												  alarm		 = '0',
												  quantity   = '".$output['silo']['quantity']."',
												  emptyTime  = '0'
											  WHERE itemID = '".$output['itemID']."';";
								}
								elseif( isset($output['silo']['typeID']) && $silores['typeID'] != $output['silo']['typeID'] && $output['silo']['typeID'] != 0) {
									$query = "UPDATE ".db_tab_silos." 
											  SET typeID	 = '".$output['silo']['typeID']."',
												  locationID = '".$output['locationID']."',
												  pos 		 = NULL,
												  turn		 = '0',
												  input		 = '0',
												  stack		 = '0',
												  alarm		 = '0',
												  quantity   = '".$output['silo']['quantity']."',
												  emptyTime  = '0'
											  WHERE itemID = '".$output['itemID']."';";
								}
								else {
								// echo $output['silo']['typeID'].' x<br>';
									$query = "UPDATE ".db_tab_silos." 
											  SET locationID = '".$output['locationID']."',
												  /*typeID	 = '0',*/
												  quantity   = '0',
												  emptyTime  = '0'
											  WHERE itemID = '".$output['itemID']."';";
								}
								$this->exec_query( $query );
								//print $query. '<br>';
							} else {
								$query = "INSERT INTO ".db_tab_silos." 
										  SET itemID 	 = '".$output['itemID']."',
											  corpID 	 = '".$corpID."',
											  locationID = '".$output['locationID']."',
											  siloTypeID = '".$output['typeID']."',
											  typeID	 = '".$output['silo']['typeID']."',
											  quantity	 = '".$output['silo']['quantity']."';";
								// echo $query.'<br>';
								$this->exec_query( $query );
							}
							$res->close();
						}
						unset ($output);
					}
					
					/* Reactors */
					if ( in_array((string) $row['typeID'], $reactors) && (int) $row['singleton'] == 1 && (int) $row['flag'] == 0 ) {
						foreach ($row->attributes() as $key => $val) {
							$output[(string) $key] = (string) $val;
						}
						$apiReactors[] = $output['itemID'];
						if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
							foreach ($row->children()->rowset->row as $row) {
								foreach ($row->attributes() as $name => $value) {
									$output['reactor'][(string) $name] = (string) $value;
								}
								$res = $this->query("SELECT itemID FROM ".db_tab_silos_reactors." WHERE itemID = '".$output['itemID']."';");
								if ( $res->num_rows == 0 ) {
									$query = "INSERT INTO ".db_tab_silos_reactors." 
											  SET itemID 	 = '".$output['itemID']."',
												  corpID 	 = '".$corpID."',
												  locationID = '".$output['locationID']."',
												  typeID	 = '".$output['typeID']."',
												  typeIDx	 = '".$output['reactor']['typeID']."';";
									// echo $query.'<br>';
									$this->exec_query( $query );
								} else {
									$query = "UPDATE ".db_tab_silos_reactors." 
											  SET corpID 	 = '".$corpID."',
												  locationID = '".$output['locationID']."',
												  typeID	 = '".$output['typeID']."',
												  typeIDx	 = '".$output['reactor']['typeID']."'
											  WHERE itemID 	 = '".$output['itemID']."';";
									// echo $query.'<br>';
									$this->exec_query( $query );
								}
								$res->close();
							}
							unset ($output);
						}

					}
				}
				/* close statement */
				/* $stmt->close(); */
				
				/* Reactors cleaning */
				$res = $this->query("SELECT itemID FROM ".db_tab_silos_reactors." WHERE corpID = '$corpID';");
				while ( $row = $res->fetch_assoc() ) {
					if ($row) {
						if (!in_array($row['itemID'], $apiReactors)) {
							$this->exec_query("DELETE FROM ".db_tab_silos_reactors." WHERE itemID = '{$row['itemID']}';");
						}
					}
				}
				$res->close();
				
				/* Silos cleaning */
				$res = $this->query("SELECT itemID, quantity, suspect FROM ".db_tab_silos." WHERE corpID = '$corpID';");
				while ( $row = $res->fetch_assoc() ) {
					if ($row) {
						if (!in_array($row['itemID'], $apiSilos)) {
							$this->exec_query("DELETE FROM ".db_tab_silos." WHERE itemID = '{$row['itemID']}';");
						}
						if (isset($silobefore[$row['itemID']]) && $silobefore[$row['itemID']]['quantity'] == $row['quantity']) {
							//$this->exec_query("UPDATE ".db_tab_silos." SET suspect = 1 WHERE itemID = '{$row['itemID']}';");
						}
						elseif ($row['suspect'] == 1) {
							$this->exec_query("UPDATE ".db_tab_silos." SET suspect = 0 WHERE itemID = '{$row['itemID']}';");
						}
					}
				}
				$res->close();
				
				unset ($apiSilos, $apiReactors, $silobefore, $xml);
				
				/* Assign Locations from API */
				$this->_table = db_tab_silos_reactors; /* set $this->_table for doLocations() first. */
				$out .= $this->doLocations($apikey, true);
				
				$this->_table = db_tab_silos; /* set $this->_table for doLocations() first. */
				$out .= $this->doLocations($apikey, true);
				
			} catch (Exception $e) {
				$out .= $e->getMessage()."\n";
				$this->logerror( $e->getMessage(), $this->cronID, 0 );
			}
			$x++;
		} //  foreach $apis end
		
		$fetchedAccs = $x == 1 ? $x.' account' : $x . '/' . count($apis) . ' account(s)';
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'AssetList: ' . $fetchedAccs . ' done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return format( $out );	
	}
	
	private function sendEveNotivications() {
		$time_start = microtime(true);
		$out = '';
		$_table_eveNotifications = "fsrtool_user_notifications";
		
		$mains = $this->getMains();
		$this->ale->setConfig('parserClass', 'AleParserXMLElement');
		$this->ale->setConfig('serverError', 'throwException');
		/* charID, username, keyID, vCODE, accessMask, corpID, email */
		
		$User = new user();
		
		$str = "SELECT * FROM {$_table_eveNotifications}";
		$res = $this->query($str);
		
		foreach($mains as $char) {
			$User->charID = $char['charID'];
			$User->keyID = $char['keyID'];
			$User->vCODE = $char['vCODE'];
			while($row = $res->fetch_assoc()) {
				if( $row['charID'] == $char['charID']) {
					if($row['email_valid'] == 1 || $row['push_valid'] == 1) {
						$noti = new eveNotifications($User);
						if($noti->error) {
							$out .= $noti->error."\n";
							continue;
						}
						$notes = NULL;
						if($row['notivications'] !== NULL) 
							$notes = explode(',', $row['notivications']);
						
						if(is_array($notes)) {
							$message = "";
							foreach($noti->Notifications as $n) {
								if(in_array($n['typeID'], $notes) && $n['read'] == 0) {
									//$message .= $n['sentDate']." \n".$n['typeName']." \n\n";
									$message .= $n['sendTime']['day'].' '.$n['sendTime']['time']." \n".$n['typeName']." \n\n";
								}
							}
							if($row['push_valid'] == 1 && $message != '') {
								$push = new Pushover();
								$push->setToken($row['push_token']);
								$push->setUser($row['push_user']);
								$push->setTitle('FSR-Tool');
								$push->setMessage($message);
								if(!$push->send()) {
									$this->exec_query("UPDATE {$_table_eveNotifications} SET push_valid=0 WHERE charID={$char['charID']}");
								}
							}
							if($row['email_valid'] == 1 && $message != '') {
								$this->mail->to = array();
								$this->mail->AddAddress($row['email']);
		
								$Subject = 'eveNotivications';
								$Body = 'Hello,<br />'
									.'<br />'
									.$message
									.'<br />'
									.'-- <br />'
									.'Regards Site Admin<br />';
								
								$this->mail->FromName  = 'FSR-Tool';
								$this->mail->Subject   = $Subject;
								$this->mail->Body      = $Body;
								
								if(!$this->mail->send()) {
									$this->exec_query("UPDATE {$_table_eveNotifications} SET email_valid=0 WHERE charID={$char['charID']}");
								}
							}
						}
					}
				}
			}
			$res->data_seek(0);
			
		}
		$res->close();
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'sendEveNotivications: done in ' . round($time, 4) . ' sec, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return format( $out );
	}
	
	private function my_error( $message ) {
		$debug = debug_backtrace();
		$callee = next($debug);
		$msg = $message."in ".$callee['file']." on line: ".$callee['line'];
		// $msg = $message.'<br />in <strong>'.$callee['file'].'</strong> on line <strong>'.$callee['line'].'</strong><br />';
		// $msg .= $callee['function'].', [class] => '.$callee['class'];
		return $msg;
	}
	
	private function apiErrorHandler( $error, array $data ) {
		$errorText = (string) $error;
		$errorCode = (int) $error->attributes();
		
		if ( substr($errorCode, 0, 1) == 5 or substr($errorCode, 0, 1) == 9 ) { //  API Server DOWN !!!!
			$this->logerror("CCP_API_ERROR -> ".$errorCode." - ".$errorText." | Cron ".$data['name']." Stopped");
			die( format("CCP_API_ERROR -> ".$errorCode." - ".$errorText." | Cron ".$data['name']." Stopped") );
		}
		else if ( substr($errorCode, 0, 1) == 2 ) {
			$msg = $errorCode . " - " . $errorText . " | ".$data['name'];
			if ( $data['apifull'] == 1 ) {
				$this->logerror( $msg, $this->cronID, $data['corpID'] );
				$this->logerror( $errorCode . " - " . $errorText . " | User: ".$data['api']['userName']." Cron: ".$data['name'] );
				$this->del_fullApi( $data['api']['charID'] );
			} 
			else if ( $data['apifull'] == 2 ) { //  check mains
				$this->logerror( $errorCode . " - " . $errorText . " | User: ".$data['api']['username']." Cron: ".$data['name'] );
				//  SEND Email here
				if ( $this->sendMail( $data['api']['charID'] ) ) {
					$this->logerror("Email Send to ".$data['api']['username']." zwegs API Daten Falsch");
				}
				//  Set account inactive
				if ( !$this->checkInactive( $data['api']['charID'] ) ) {
					$this->setInactive( $data['api']['charID'] );
					$this->logerror("CCP_API_ERROR -> ".$errorCode." - ".$errorText." | From User ".$data['api']['username']." (now inactiv)");
				}
			}
			else if ( $data['apifull'] == 3 ) { //  check alts
				$this->logerror( $errorCode . " - " . $errorText . " | User: ".$data['api']['charName']." Cron: ".$data['name'] );
				$this->exec_query("DELETE FROM ".db_tab_alts." WHERE charID='{$data['api']['charID']}' AND mainCharID='{$data['api']['mainCharID']}';");
			}
		}
		else if ( $errorCode == 114 ) { //  114-Invalid itemID provided
			$name = $this->posItemIDtoMoonName( $data['itemID'] );
			$msg = "fuel API fehler on (". $name ." - ID:". $data['itemID'] .") ".$errorCode . "-" . $errorText;
			$this->logerror( $msg, $this->cronID, $data['corpID'] );
		}
		else {
			$this->logerror("CCP_API_ERROR -> ".$errorCode." - ".$errorText." | ".$data['name'] );
		}
		return true;
		
	}
	
	public function __destruct() {
		$this->db->close();
	}
}

// don't put html in a cron jobs output
function format($html) {
	if ( !isset($_SERVER['HTTP_USER_AGENT']) ) {
		return $html;
	} else {
		return nl2br( $html );
	}
}

function print_it ( $content ) {
	if ( !is_object( $content ) && !is_array( $content ) && !empty( $content ) ) {
		ob_start();
		header('Content-Length: '.strlen($content));
		header('Content-type: text/xml; charset=utf-8'); 
		echo $content;
		ob_end_flush();
	}
	else {
		printf( '<pre> %s </pre>', print_r($content,true) );
	}
}

?>