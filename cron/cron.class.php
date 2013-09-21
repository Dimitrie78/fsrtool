<?php

class cron extends Database
{
	protected $ale = null;
	private $mail = null;
	
	private $absenderMail = 'noreplay@fsrtool.de';
	
	private $cronID = 99;
	protected $logdir;
	static $corpID = null;
	static $charID = null;
	
	public function __construct($parms) {
		parent::__construct();
		$this->_parms = $parms;
		$this->logdir = FSR_BASE . DIRECTORY_SEPARATOR . 'cache/cronelog/';
		$this->ale = AleFactory::getEVEOnline($parms);
		$this->mail = new phpmailer();
		$this->mail->IsHTML(true);
		$this->mail->IsMail();
		//$this->mail->IsSMTP();
		$this->mail->From = $this->absenderMail;
	}
	
	public function run() {
		$out = '';
		if ( $run === null && !self::$charID && !self::$corpID) {
			$res = $this->query("SELECT c.id, c.name from fsrtool_cron c WHERE DATE_SUB(NOW(), INTERVAL c.interwal MINUTE) >= c.time AND status = 1 ORDER BY c.id LIMIT 5;");
			while($row = $res->fetch_assoc()) {
				$this->cronID = $row['id'];
				if(method_exists($this, $row['name'])) { 
					$out .= $this->$row['name']();
				} else {
					require_once("cron-{$row['name']}.class.php");
					$cron = "cron{$row['name']}";
					$pos = new $cron($this->_parms);
					$out .= $pos->run();
					$this->queries += $pos->queries;
					$this->queries_time += $pos->queries_time;
				}
				$this->exec_query("UPDATE fsrtool_cron SET time=DATE_ADD(NOW(),INTERVAL -10 MINUTE) WHERE id={$row['id']};");
			}
		}
		
		
			//$out .= $this->checkMains();
			//$out .= $this->checkAlts();
			//$out .= $this->update_sov();
			//$out .= $this->update_outposts();
			//$out .= $this->update_refTypes();
			//$out .= $this->update_Corporation();
		
		if ( self::$corpID ) {
			require_once('cron-Pos.class.php');
			$pos = new cronPos($this->_parms);
			$out .= $pos->run();
			$this->queries += $pos->queries;
			$this->queries_time += $pos->queries_time;
			
			require_once('cron-Silos.class.php');
			$pos = new cronSilos($this->_parms);
			$out .= $pos->run();
			$this->queries += $pos->queries;
			$this->queries_time += $pos->queries_time;
		}
		
		/*
		require_once('cron-Member.class.php');
		$pos = new cronMember($this->_parms);
		$out .= $pos->run();
		$this->queries += $pos->queries;
		$this->queries_time += $pos->queries_time;
		*/
		
		//return $out;
	}
	
	private function checkMains() {
		$time_start = microtime(true);
		$mains = $this->getMains();
		$x=0;
		$out = '';
		foreach ( $mains as $char ) {
			if ( $char['vCODE'] == '' || $char['keyID'] == '' ) {
				$this->setInactive( $char['charID'] ); // Set acc inactive
				continue;
			}
			
			$this->ale->setKey($char['keyID'], $char['vCODE']);
			//all errors are handled by exceptions
			try {
				$APIKeyInfo = $this->ale->account->APIKeyInfo();
				$APIKeyInfo = $APIKeyInfo->toarray();
				
				foreach($APIKeyInfo['result']['key']['characters'] as $val) {
					
					if((string)$val['characterID'] == $char['charID']) {
						$this->setAccessMask( $char['charID'], (int)$APIKeyInfo['result']['key']['accessMask'] );
						$this->checkSkills($char['charID']);
						$this->dreadCanFly($char['charID'], (string)$val['corporationID']);
						
						if((int)$APIKeyInfo['result']['key']['accessMask'] == 0) {
							$this->setInactive( $char['charID'] ); // Set acc inactive
							continue;
						}
						
						if((string)$val['corporationID'] == $char['corpID']) {
							//  prüfen ob inactive...
							$inactive = $this->checkInactive($char['charID']);
							if($inactive) {
								$this->setActive($char['charID']); // acc activieren
							}
						} else {
							//  Corp wechseln.. 
							$this->changeCorp($char['charID'], $char['corpID'],(string)$val['corporationID'],(string)$val['corporationName']);
						}
					}
				}
				unset($APIKeyInfo);
				$x++;
			} catch (Exception $e) {
				$out .= $e->getCode().' - '.$e->getMessage();
				$this->errorHandler('Problem in checkMains::'.$e->getMessage(), $e->getCode(), $char['charID'], $char);
				//$this->logerror( $e->getMessage(), $this->cronID, 0 );
			}
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'checkMains: ' . $x . '/' . count($mains) . ' account(s), done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return self::format( $out );	
		
	}
	
	private function checkAlts() {
		$time_start = microtime(true);
		$alts = $this->getAlts();
		$x=0;
		$out = '';
		foreach($alts as $char) {
			if ( $char['userAPI'] == '' || $char['userID'] == '' ) {
				$this->query("DELETE FROM {$this->_table['fsrtool_alts']} WHERE charID='{$char['charID']}' AND mainCharID='{$char['mainCharID']}';");
				continue;
			}
			if ( $char['accessMask'] == '0' ) {
				$this->exec_query("DELETE FROM {$this->_table['fsrtool_alts']} WHERE charID='{$char['charID']}' AND mainCharID='{$char['mainCharID']}';");
				continue;
			}
			
			$this->ale->setKey( $char['userID'], $char['userAPI'] );
			//all errors are handled by exceptions
			try {
				$APIKeyInfo = $this->ale->account->APIKeyInfo();
				$APIKeyInfo = $APIKeyInfo->toarray();
				
				foreach($APIKeyInfo['result']['key']['characters'] as $val) {
					if((string)$val['characterID'] == $char['charID']) {
						$this->setAccessMask( $char['charID'], (int)$APIKeyInfo['result']['key']['accessMask'] );
						$this->checkSkills($char['charID']);
						$this->dreadCanFly($char['charID'], (string)$val['corporationID']);
						
						if((string)$val['corporationID'] != $char['corpID']) {
							//  Corp wechseln..
							$this->changeCorp( $char['charID'], $char['corpID'],(string)$val['corporationID'],(string)$val['corporationName'], true );
						}
						
					}
				}
				unset($APIKeyInfo);
				$x++;
			} catch (Exception $e) {
				$out .= $e->getCode().' - '.$e->getMessage();
				#throw new cronException('Problem in checkAlts::'.$e->getMessage(), $e->getCode(), $char['charID']);
				$this->errorHandler('Problem in checkAlts::'.$e->getMessage(), $e->getCode(), $char['charID'], $char);
				//$this->logerror( $e->getMessage(), $this->cronID, 0 );
			}
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'checkAlts: ' . $x . '/' . count($alts) . ' account(s), done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return self::format( $out );
	}
	
	private function update_sov() {
		$time_start = microtime(true);
		$out='';
		$query = "INSERT INTO {$this->_table['fsrtool_api_sovereignty']} 
					(solarSystemID, allianceID, factionID, solarSystemName, corporationID) VALUES (?, ?, ?, ?, ?) 
					ON DUPLICATE KEY UPDATE 
					allianceID=values(allianceID),
					factionID=values(factionID),
					solarSystemName=values(solarSystemName),
					corporationID=values(corporationID)";
		
		try {
			$Sovereignty = $this->ale->map->Sovereignty();
			$stmt = $this->prepare($query);
			$stmt->bind_param('iiisi', $solarSystemID, $allianceID, $factionID, $solarSystemName, $corporationID);
			foreach ($Sovereignty->result->solarSystems as $row) {
				foreach ($row->attributes() as $name => $value) {
					$$name = $value;
				}
				$stmt->execute();
			}
			$stmt->close();
			unset($Sovereignty);
		} catch (Exception $e) {
			//$this->logerror( $e->getMessage(), $this->cronID, 0 );
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'Sovereignty update:: done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return self::format($out);
	}
	
	private function update_outposts() {
		$time_start = microtime(true);
		$out='';
		$query = "INSERT INTO {$this->_table['fsrtool_api_outposts']}
				(stationID, stationName, stationTypeID, solarSystemID, corporationID, corporationName) VALUES (?, ?, ?, ?, ?, ?) 
					ON DUPLICATE KEY UPDATE 
					stationName=values(stationName),
					stationTypeID=values(stationTypeID),
					solarSystemID=values(solarSystemID),
					corporationID=values(corporationID),
					corporationName=values(corporationName)";
		
		try {
			$ConquerableStationList = $this->ale->eve->ConquerableStationList();
			$stmt = $this->prepare($query);
			$stmt->bind_param('isiiis', $stationID, $stationName, $stationTypeID, $solarSystemID, $corporationID, $corporationName);
			foreach ($ConquerableStationList->result->outposts as $row) {
				foreach ($row->attributes() as $name => $value) {
					$$name = $value;
				}
				$stmt->execute();
			}
			$stmt->close();
			unset($ConquerableStationList);
		} catch (Exception $e) {
			//$this->logerror( $e->getMessage(), $this->cronID, 0 );
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'ConquerableStationList update:: done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return self::format($out);
	}
	
	private function update_refTypes() {
		$time_start = microtime(true);
		$out='';
		$query = "INSERT INTO {$this->_table['fsrtool_api_reftypes']} (refTypeID, refTypeName) VALUES (?,?)
					ON DUPLICATE KEY UPDATE refTypeName=values(refTypeName)";
		try {
			$RefTypes = $this->ale->eve->RefTypes();
			$stmt = $this->prepare($query);
			$stmt->bind_param('is', $refTypeID, $refTypeName);
			foreach ($RefTypes->result->refTypes as $refType) {
				foreach ($refType->attributes() as $name => $value) {
					$$name = $value;
				}
				$stmt->execute();
			}
			$stmt->close();
			unset($RefTypes);
		} catch (Exception $e) {
			//$this->logerror( $e->getMessage(), $this->cronID, 0 );
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'refTypes update:: done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return self::format($out);
	}
	
	private function update_Corporation() {
		$time_start = microtime(true);
		$out='';
		
		$corpquery = "INSERT INTO {$this->_table['fsrtool_corps']} (id, name, ticker, ally) VALUES (?,?,?,?)
			ON DUPLICATE KEY UPDATE name=values(name), ticker=values(ticker), ally=values(ally)";
		$corpstmt = $this->prepare($corpquery);
		$corpstmt->bind_param('isss', $corpID, $corpName, $corpTicker, $allyID);
		
		$allyquery = "INSERT INTO {$this->_table['fsrtool_allys']} (id, name) VALUES (?,?)
			ON DUPLICATE KEY UPDATE name=values(name)";
		$allystmt = $this->prepare($allyquery);
		$allystmt->bind_param('ss', $allyID, $allyName);
		
		$res = $this->query("SELECT id FROM {$this->_table['fsrtool_corps']} order by name;");
		while ( $row = $res->fetch_assoc() ) {
			if ($row) {
				$params = array('corporationID' => $row['id']);
				try {
					$CorporationSheet = $this->ale->corp->CorporationSheet($params, ALE_AUTH_NONE);
					$allyID   	= (string) $CorporationSheet->result->allianceID == 0 ? 'None' : $CorporationSheet->result->allianceID;
					$allyName 	= $CorporationSheet->result->allianceName;
					$corpID	  	= $row['id'];
					$corpName 	= $CorporationSheet->result->corporationName;
					$corpTicker = $CorporationSheet->result->ticker;
					
					if ($corpName == '') {
						$this->exec_query("DELETE FROM {$this->_table['fsrtool_corps']} WHERE id='{$row['id']}';");
					} else {
						$corpstmt->execute();
						if ($allyName != 'None') {
							$allystmt->execute();
						}
					}
					//unset($CorporationSheet,$allyID,$allyName,$corpID,$corpName,$corpTicker);
				} catch (Exception $e) {
					//$this->logerror( $e->getMessage(), $this->cronID, 0 );
					$out .= $e->getCode().' - '.$e->getMessage()."\n";
				}
			}	
		}
		
		$res = $this->query("SELECT corpID FROM {$this->_table['fsrtool_user']} GROUP BY corpID ORDER BY corpID;");
		while ( $row = $res->fetch_assoc() ) {
			if ($row) {
				$params = array('corporationID' => $row['corpID']);
				try {
					$CorporationSheet = $this->ale->corp->CorporationSheet($params, ALE_AUTH_NONE);
					$allyID   	= (string) $CorporationSheet->result->allianceID == 0 ? 'None' : $CorporationSheet->result->allianceID;
					$allyName 	= $allyID == 'None' ? 'None' : $CorporationSheet->result->allianceName;
					$corpID	  	= $row['corpID'];
					$corpName 	= $CorporationSheet->result->corporationName;
					$corpTicker = $CorporationSheet->result->ticker;
					if ($corpName == '') {
						$this->exec_query("DELETE FROM {$this->_table['fsrtool_corps']} WHERE id='{$row['corpID']}';");
					} else {
						$corpstmt->execute();
						if ($allyName != 'None') {
							$allystmt->execute();
						}
					}
					//unset($CorporationSheet,$allyID,$allyName,$corpID,$corpName,$corpTicker);
				} catch (Exception $e) {
					//$this->logerror( $e->getMessage(), $this->cronID, 0 );
					$out .= $e->getCode().' - '.$e->getMessage()."\n";
				}
			}	
		}

		$res = $this->query("SELECT corpID FROM {$this->_table['fsrtool_alts']} GROUP BY corpID ORDER BY corpID;");
		while ( $row = $res->fetch_assoc() ) {
			if ($row) {
				$params = array('corporationID' => $row['corpID']);
				try {
					$CorporationSheet = $this->ale->corp->CorporationSheet($params, ALE_AUTH_NONE);
					$allyID   	= (string) $CorporationSheet->result->allianceID == 0 ? 'None' : $CorporationSheet->result->allianceID;
					$allyName 	= $allyID == 'None' ? 'None' : $CorporationSheet->result->allianceName;
					$corpID	  	= $row['corpID'];
					$corpName 	= $CorporationSheet->result->corporationName;
					$corpTicker = $CorporationSheet->result->ticker;
					if ($corpName == '') {
						$this->exec_query("DELETE FROM {$this->_table['fsrtool_corps']} WHERE id='{$row['corpID']}';");
					} else {
						$corpstmt->execute();
						if ($allyName != 'None') {
							$allystmt->execute();
						}
					}
					//unset($CorporationSheet,$allyID,$allyName,$corpID,$corpName,$corpTicker);
				} catch (Exception $e) {
					//$this->logerror( $e->getMessage(), $this->cronID, 0 );
					$out .= $e->getCode().' - '.$e->getMessage()."\n";
				}
			}	
		}
		$corpstmt->close();
		$allystmt->close();
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'Corporations update:: done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return self::format($out);
	}
	
	private function checkSkills($characterID) {
		$this->ale->setCharacterID($characterID);
        $query = "INSERT INTO {$this->_table['fsrtool_skills']} (charID, skillID, quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=?";
		try {
			$characterSheet = $this->ale->char->CharacterSheet();
			$stmt = $this->prepare($query);
			$stmt->bind_param('iiii', $characterID, $skillID, $quantity, $quantity);
				foreach( $characterSheet->result->skills as $skill ) {
					$skillID  = (int) $skill->typeID;
					$quantity = (int) $skill->level;
					$stmt->execute();
				}
			$stmt->close();
			unset($characterSheet);
		} catch (Exception $e) {
			throw new Exception('Problem in checkSkills::'.$e->getMessage(),$e->getCode());
			//$this->logerror( $e->getMessage() );
		}
	}
	
	private function dreadCanFly( $charID, $corpID ) {
		$grund=0; $grund_rep=0; $grund_rep_req=0;
		$moros=0; $moros_rep=0; $moros_rep_req=0;
		$naglf=0; $naglf_rep=0; $naglf_rep_req=0;
		$revel=0; $revel_rep=0; $revel_rep_req=0;
		$phoen=0; $phoen_rep=0; $phoen_rep_req=0;
		
		$skills = array();
		
		$res = $this->query( "SELECT * FROM {$this->_table['fsrtool_skills']} WHERE charID='".$charID."';" );
		if ( $res->num_rows > 0 ) {
			while ( $row = $res->fetch_assoc() ) {
				if($row) { 
					$skills['skillID'][] = $row['skillID'];
					$skills['quantity'][] = $row['quantity'];
				}
			}
			$res->close();
			
			$ship = array();
			
			$res = $this->query( "SELECT * FROM {$this->_table['fsrtool_ship_skills']} WHERE corpID='".$corpID."';" );
			if ( $res->num_rows > 0 ) {
				while ( $row = $res->fetch_assoc() ) {
					if($row) $ship[ $row['ship_id'] ][] = array($row['skill_id'] => $row['quantity']);
				}
					
				if ( isset($ship[0]) && is_array($ship[0]) ) {
					foreach($ship[0] as $value) {
						if (is_array($value)) {
							foreach($value as $skillid => $quantity) {
								if (in_array($skillid,$skills['skillID'])) {
									$skill_key = array_search($skillid,$skills['skillID']);
									if ($skills['skillID'][$skill_key] == 21802 or $skills['skillID'][$skill_key] == 21803) $grund_rep++;
									elseif ($skills['quantity'][$skill_key] >= $quantity) $grund++;
								}
							}
						}
					}
				} unset($value,$skillid,$quantity,$skill_key);
				if ( isset($ship[19724]) && is_array($ship[19724]) ) {
					foreach($ship[19724] as $value) {
						if (is_array($value)) {
							foreach($value as $skillid => $quantity) {
								if (in_array($skillid,$skills['skillID'])) {
									$skill_key = array_search($skillid,$skills['skillID']);
									if ($skills['skillID'][$skill_key] == 21802 or $skills['skillID'][$skill_key] == 21803) $moros_rep++;
									elseif ($skills['quantity'][$skill_key] >= $quantity) $moros++;
								}
							}
						}
					}
				} unset($value,$skillid,$quantity,$skill_key);
				if ( isset($ship[19722]) && is_array($ship[19722]) ) {
					foreach($ship[19722] as $value) {
						if (is_array($value)) {
							foreach($value as $skillid => $quantity) {
								if (in_array($skillid,$skills['skillID'])) {
									$skill_key = array_search($skillid,$skills['skillID']);
									if ($skills['skillID'][$skill_key] == 21802 or $skills['skillID'][$skill_key] == 21803) $naglf_rep++;
									elseif ($skills['quantity'][$skill_key] >= $quantity) $naglf++;
								}
							}
						}
					}
				} unset($value,$skillid,$quantity,$skill_key);
				if ( isset($ship[19720]) && is_array($ship[19720]) ) {
					foreach($ship[19720] as $value) {
						if (is_array($value)) {
							foreach($value as $skillid => $quantity) {
								if (in_array($skillid,$skills['skillID'])) {
									$skill_key = array_search($skillid,$skills['skillID']);
									if ($skills['skillID'][$skill_key] == 21802 or $skills['skillID'][$skill_key] == 21803) $revel_rep++;
									elseif ($skills['quantity'][$skill_key] >= $quantity) $revel++;
								}
							}
						}
					}
				} unset($value,$skillid,$quantity,$skill_key);
				if ( isset($ship[19726]) && is_array($ship[19726]) ) {
					foreach($ship[19726] as $value) {
						if (is_array($value)) {
							foreach($value as $skillid => $quantity) {
								if (in_array($skillid,$skills['skillID'])) {
									$skill_key = array_search($skillid,$skills['skillID']);
									if ($skills['skillID'][$skill_key] == 21802 or $skills['skillID'][$skill_key] == 21803) $phoen_rep++;
									elseif ($skills['quantity'][$skill_key] >= $quantity) $phoen++;
								}
							}
						}
					}
				} unset($value,$skillid,$quantity,$skill_key);
				
				if ( is_array($ship) ) {
					foreach($ship as $typ => $value) {
						if ( is_array($value) ) {
							foreach($value as $key => $val) {
								foreach($val as $id => $lvl) {
									if($typ == 0) {
										if ($id == 21803 or $id == 21802) { unset($ship[0][$key]); $grund_rep_req++; }
									}
									if($typ == 19724) {
										if ($id == 21803 or $id == 21802) { unset($ship[19724][$key]); $moros_rep_req++; }
									}
									if($typ == 19722) {
										if ($id == 21803 or $id == 21802) { unset($ship[19722][$key]); $naglf_rep_req++; }
									}
									if($typ == 19720) {
										if ($id == 21803 or $id == 21802) { unset($ship[19720][$key]); $revel_rep_req++; }
									}
									if($typ == 19726) {
										if ($id == 21803 or $id == 21802) { unset($ship[19726][$key]); $phoen_rep_req++; }
									}
								}
							}
						}
					}
				}
				
				if ( isset($ship[0]) && $grund == count($ship[0]) && $grund_rep_req >= $grund_rep) {
					
					if ( isset($ship[19724]) && $moros == count($ship[19724]) && $moros_rep_req >= $moros_rep) $m=true; else $m=false;
					if ( isset($ship[19722]) && $naglf == count($ship[19722]) && $naglf_rep_req >= $naglf_rep) $n=true; else $n=false;
					if ( isset($ship[19720]) && $revel == count($ship[19720]) && $revel_rep_req >= $revel_rep) $r=true; else $r=false;
					if ( isset($ship[19726]) && $phoen == count($ship[19726]) && $phoen_rep_req >= $phoen_rep) $p=true; else $p=false;
				} else {
					$m=false; $n=false; $r=false; $p=false;
				}
				$return = $this->updateDreadCanFly($charID, $m, $n, $r, $p);
			}
			$res->close();
		}
	}
	
	private function updateDreadCanFly($charID, $m, $n, $r, $p) {
		if($m) $moros = 'Moros=1'; 		else $moros = 'Moros=0';
		if($n) $naglf = 'Naglfar=1'; 	else $naglf = 'Naglfar=0';
		if($r) $revel = 'Revelation=1'; else $revel = 'Revelation=0';
		if($p) $phoen = 'Phoenix=1'; 	else $phoen = 'Phoenix=0';
		
		$res = $this->query("SELECT * FROM {$this->_table['fsrtool_ships_player']} WHERE user_id='".$charID."';");
		if ( $res->num_rows > 0 ) {
			$return = $this->exec_query( "UPDATE {$this->_table['fsrtool_ships_player']} SET ".$moros.", ".$naglf.", ".$revel.", ".$phoen." WHERE user_id='".$charID."';" );
		} else {
			$return = $this->exec_query( "INSERT INTO {$this->_table['fsrtool_ships_player']} SET user_id='".$charID."', ".$moros.", ".$naglf.", ".$revel.", ".$phoen.";" );
		}
		$res->close();
		
		return $return;
	}
	
	private function setInactive($charID) {
		return $this->exec_query("UPDATE {$this->_table['fsrtool_user']} SET errorcount = if(active = 0, 0, errorcount + 1), active = if(errorcount >= 3, 0, 1) WHERE charID='$charID';");
	}
	
	private function setActive($charID) {
		return $this->exec_query("UPDATE {$this->_table['fsrtool_user']} SET active=1 WHERE charID='$charID';");
	}
	
	private function checkInactive($charID) {
		$res = $this->query( "SELECT charID FROM {$this->_table['fsrtool_user']} WHERE charID='".$charID."' AND active=0;" );
		if ( $res->num_rows > 0 )
			return true;
		else
			return false;
	}
	
	private function checkEmail($charID) {
		$res = $this->query( "SELECT charID FROM {$this->_table['fsrtool_user']} WHERE charID='".$charID."' AND email='';" );
		if ( $res->num_rows > 0 )
			return true;
		else
			return false;
	}
	
	private function changeCorp($charID,$oldcorpID,$corpID,$corpName,$alt=false) {		
		$this->ale->setCharacterID( $charID );
		try {
			$CharacterInfo = $this->ale->eve->CharacterInfo();
			
			$allyID = (string) $CharacterInfo->result->allianceID == 0 ? 'None' : (string) $CharacterInfo->result->allianceID;
			$allyName = $allyID == '0' ? 'None' : (string) $CharacterInfo->result->alliance;
			
			if($allyID == "0") {
				$allyID = "None";
				$allyName = "None";
			}
		
			if(!$alt) {
				$this->exec_query( "UPDATE {$this->_table['fsrtool_user']} SET corpID='".$corpID."' WHERE charID='".$charID."';" );
				$res = $this->query( "SELECT * FROM {$this->_table['fsrtool_user_roles']} WHERE charID = '".$charID."' AND roleID=1;" ); //  ADMIN ???
				
				//  Kein Admin = alle roles entfernen weil neue corp
				if ( $res->num_rows == 0 ) { 
					$this->exec_query( "DELETE FROM {$this->_table['fsrtool_user_roles']} WHERE charID = '".$charID."';" );
				}
			} 
			else {
				$this->exec_query("UPDATE {$this->_table['fsrtool_alts']} SET corpID='".$corpID."', pos=0, pos_edit=0, silo=0 WHERE charID='".$charID."';");
			}
			$now=time();
			$str = "INSERT INTO {$this->_table['fsrtool_corpchange']} 
					SET userid	= '".$charID."',
					  oldcorpid	= '".$oldcorpID."',
					  newcorpid = '".$corpID."',
					  changed	= '".$now."';";
			$res = $this->exec_query( $str );
			
			if ( $this->addAlly( $allyID, $allyName ) ) {
				$this->logerror("Alliance: ".$allyName." wurde der Datenbank hinzugefügt", $this->cronID, 0);
			}
			
			if ( $this->addCorp( $corpID, $corpName, $allyID ) ) {
				$this->logerror("Corperation: ".$corpName." wurde der Datenbank hinzugefügt", $this->cronID, 0);
			}
			
		} catch (Exception $e) {
			//$out = $e->getCode().' - '.$e->getMessage();
			throw new Exception('Problem in changeCorp::'.$e->getMessage(),$e->getCode());
			//$this->logerror( $e->getMessage(), $this->cronID, 0 );
		}
	}
	
	private function addCorp($corpID,$corpName,$allyID)	{
		$res = $this->query("SELECT name FROM {$this->_table['fsrtool_corps']} WHERE id='".$corpID."';");
		if ( $res->num_rows > 0 ) {
			return false;
		} else {
			$this->exec_query("INSERT INTO {$this->_table['fsrtool_corps']} SET id='".$corpID."',name='".$corpName."',ally='".$allyID."',timestamp='".date("YmdHis")."';");
			return true;
		}
	}

	private function addAlly($allyID,$allyName)	{
		$res = $this->query("SELECT name FROM {$this->_table['fsrtool_allys']} WHERE id='".$allyID."';");
		if ( $res->num_rows > 0 ) {
			return false;
		} else {
			$this->exec_query("INSERT INTO {$this->_table['fsrtool_allys']} SET id='".$allyID."',name='".$allyName."',timestamp='".date("YmdHis")."';");
			return true;
		}
	}
	
	private function getMains() {
		$mains = array();
		if ( self::$charID )
			$res = $this->query( "SELECT charID, username, keyID, vCODE, accessMask, corpID, email FROM {$this->_table['fsrtool_user']} WHERE active=1 AND charID = '".self::$charID."';" );
		else
			$res = $this->query( "SELECT charID, username, keyID, vCODE, accessMask, corpID, email FROM {$this->_table['fsrtool_user']} WHERE active=1;" );
		while ( $row = $res->fetch_assoc() ) {
			if ( $row ) $mains[] = $row;
		} $res->close();
		
		return $mains;
	}
	
	private function getAlts() {
		$alts = array();
		if ( self::$charID )
			$res = $this->query( "SELECT * FROM {$this->_table['fsrtool_alts']} WHERE mainCharID = '".self::$charID."' GROUP BY charID;" );
		else
			$res = $this->query( "SELECT * FROM {$this->_table['fsrtool_alts']};" );
		while ( $row = $res->fetch_assoc() ) {
			if ( $row ) $alts[] = $row;
		} $res->close();
		
		return $alts;
	}
	
	private function setAccessMask( $charID, $mask, $alt=false ) {
		if($alt===false)
			return $this->exec_query( "UPDATE {$this->_table['fsrtool_user']} SET accessMask='{$mask}' WHERE charID='{$charID}';" );
		else
			return $this->exec_query( "UPDATE {$this->_table['fsrtool_alts']} SET accessMask='{$mask}' WHERE charID='{$charID}';" );
	}
	
	public function setCorpID( $corpID ) {
		if (!empty($corpID) && !is_numeric($corpID))
			self::$corpID = null;
		else
			self::$corpID = $corpID;
	}
	
	public function setCharID( $charID ) {
		if (!empty($charID) && !is_numeric($charID))
			$this->charID = null;
		else
			$this->charID = $charID;
	}
	
	protected function doLocations(array $key, $log=false) {
		$parserClass = $this->ale->setConfig('parserClass', 'AleParserXMLElement');
		$this->_table_pos = $this->_table['fsrtool_pos'];
		$this->_corpID = $key['corpID'];
		
		$out = '';
		$ids = array();
		
			//get ALE object
			try {
				//set api key
				$this->ale->setKey( $key['keyID'], $key['vCODE'], $key['charID'] );
				//all errors are handled by exceptions
				//let's check the key first.
				$keyinfo = $this->ale->account->APIKeyInfo();
				
				if( !(intval($keyinfo->result->key->attributes()['accessMask']) & 16777216) ) {
					$out .= 'You need a Key with access to Locations'."\n";
					return $out;
				}
				$str = "SELECT * FROM {$this->_tableLoc} WHERE corpID='{$this->_corpID}'";

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
					$str = "UPDATE {$this->_tableLoc} SET 
						itemName = '".$this->escape((string)$loc->itemName)."', 
						x = '".(string)$loc->x."', 
						y = '".(string)$loc->y."', 
						z = '".(string)$loc->z."' 
						WHERE itemID = '".(string)$loc->itemID."'";
					if (!$this->query($str)) { break; }
				}
				
			} catch (Exception $e) {
				$out .= $e->getMessage()."\n";
				throw new Exception('Problem in doLocations::'.$e->getMessage(),$e->getCode());
				//$this->logerror( $e->getMessage(), $this->cronID, 0 );
			}
			//if($log === true) $this->search_loc($ids);
			if($log === true) $this->search_loc();
		$this->ale->setConfig('parserClass', $parserClass);
		return $out;
	}
	
	private function search_loc() {
        $res = $this->query("SELECT itemID, locationID, x, y, z, moonID FROM ".$this->_table_pos." WHERE corpID = ".$this->_corpID."");
        $res_items =  $this->query("SELECT itemID, locationID, x, y, z FROM ".$this->_tableLoc." WHERE corpID = ".$this->_corpID." AND pos IS NULL");
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
                    $this->query("UPDATE ".$this->_tableLoc." SET pos = ".$pos['moonID']." WHERE itemID = ".$item['itemID']."");
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
	
	private function StarbaseFuel() {
		$time_start = microtime(true);
		
		$_min_time = 24; // Stunden
		
		$_table_tower 		= $this->_table['fsrtool_pos'];
		$_table_towerFuel 	= $this->_table['fsrtool_pos_fuel'];
		$_table_towerRes 	= $this->_table['invcontroltowerresources'];
		$_table_sov 		= $this->_table['fsrtool_api_sovereignty'];
		$_table_mail 		= $this->_table['fsrtool_pos_maillist'];
		
		$offset = date_offset_get(date_create());
		$now = (time()-$offset)/3600;
		
		$apis = array();
		$res = $this->query("SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE status=1;");
		while ( $row = $res->fetch_assoc() ) {
			if($row) $apis[] = $row;
		} $res->close();
		
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
								// echo $this->moonIDtoName($row['moonID'])." - low on Fuel<br>";
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
	
	private function sendEveNotivications() {
		$time_start = microtime(true);
		$out = '';
		$_table_eveNotifications = $this->_table['fsrtool_user_notifications'];
		
		$mains = $this->getMains();
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
		
		return self::format($out);
	}
	
	private function moonIDtoName($moonID) {
		$res = $this->query("SELECT itemName FROM {$this->_table['mapdenormalize']} WHERE itemID='".$moonID."' LIMIT 1;");
		$row = $res->fetch_assoc();
		$res->close();
		return $row['itemName'];
	}
	
	private function posItemIDtoMoonName($id) {
		$res = $this->query("SELECT map.itemName FROM {$this->_table['fsrtool_pos']} pos INNER JOIN {$this->_table['mapdenormalize']} map ON pos.moonID = map.itemID WHERE pos.itemID = {$id} LIMIT 1;");
		$row = $res->fetch_assoc();
		$res->close();
		return $row['itemName'];
	}
	
	public function serverStatus() {
		try {
			$status = $this->ale->server->ServerStatus();
		} catch (Exception $e) {
			return false;
		}
		return true;
	}
	
	private function errorHandler($message, $code, $charID, $data=array()) {
		switch(substr($code, 0, 1)) {
			case 1:
			break;
			
			case 2:
				if(!$this->setInactive($charID))
					$this->query("DELETE FROM {$this->_table['fsrtool_alts']} WHERE charID='{$charID}' AND mainCharID='{$data['mainCharID']}';");
			break;
			
			case 3:
			break;
			
			case 4:
				if(!$this->setInactive($charID))
					$this->query("DELETE FROM {$this->_table['fsrtool_alts']} WHERE charID='{$charID}' AND mainCharID='{$data['mainCharID']}';");
			break;
			
			case 5:
			break;
			
			default:
			break;
		}
	}
	
	// don't put html in a cron jobs output
	public function format($html) {
		if ( !isset($_SERVER['HTTP_USER_AGENT']) ) {
			return $html;
		} else {
			return nl2br( $html );
		}
	}

	public function print_it ( $content ) {
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

	public function __destruct() {
		parent::__destruct();
	}
}

?>