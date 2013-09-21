<?php

class cronPos extends cron
{
	
	public function __construct($parms) {
		parent::__construct($parms);
	}
	
	public function run() {
		$out='';
		$out .= $this->StarbaseList();
		$out .= $this->StarbaseDetail();
		
		return $out;
	}
	
	private function get_fullApis() {
		$apis = array();
		if ( parent::$corpID )
			$res = $this->query("SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE status=1 AND corpID = '".parent::$corpID."';");
		else
			$res = $this->query("SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE status=1;");
		while ( $row = $res->fetch_assoc() ) {
			if($row) $apis[] = $row;
		} $res->close();
		
		return $apis;
	}
	
	private function del_fullApi( $charID ) {
		$result = $this->exec_query("UPDATE {$this->_table['fsrtool_user_fullapi']} SET errorcount = if(status = 0, 0, errorcount + 1), status = if(errorcount >= 3, 0, 1) WHERE charID='$charID';");
		
		return $result;
	}
	
	private function get_tower($corpID) {
		$tower = array();
		$res = $this->query("SELECT itemID FROM {$this->_table['fsrtool_pos']} WHERE corpID='$corpID';");
		while ( $row = $res->fetch_assoc() ) {
			if($row) $tower[]=$row['itemID'];
		} $res->close();
		
		return $tower;
	}
	
	private function StarbaseList() {
		$this->_tableLoc = $this->_table['fsrtool_pos']; /* set $this->_table for doLocations() */
		$time_start = microtime(true);
		$out = '';
		$apis = $this->get_fullApis();
		
		$query = "INSERT INTO {$this->_table['fsrtool_pos']} (itemID, corpID, typeID, locationID, moonID, state, stateTimestamp, onlineTimestamp) VALUES (?,?,?,?,?,?,?,?) 
			ON DUPLICATE KEY UPDATE corpID=values(corpID), typeID=values(typeID), locationID=values(locationID), moonID=values(moonID), state=values(state), stateTimestamp=values(stateTimestamp), onlineTimestamp=values(onlineTimestamp);";
		$stmt = $this->prepare($query);
		$stmt->bind_param('ssssssss', $itemID, $corpID, $typeID, $locationID, $moonID, $state, $stateTimestamp, $onlineTimestamp);
		
		foreach( $apis as $key ) {
			$this->ale->setKey( $key['keyID'], $key['vCODE'], $key['charID'] );
			
			try {
				$StarbaseList = $this->ale->corp->StarbaseList();
				
				$towers = array();
				$pos 	= array();
				$str = "SELECT p.itemID, p.moonID
					   FROM {$this->_table['fsrtool_pos']} p
					   WHERE p.corpID='{$key['corpID']}'";
				$res = $this->query( $str );
				while ( $row = $res->fetch_assoc() ) {
					if($row) $towers[ $row['itemID'] ] = $row['moonID'];
				} $res->close();

				if ( !empty($StarbaseList) ) {
					foreach ( $StarbaseList->result->starbases as $Starbase ) { 
						$pos[] = (string) $Starbase->itemID;
					}
					
					if ( is_array($towers) ) {
						foreach ( $towers as $itemID => $moonID ) {
							if ( !in_array($itemID, $pos) ) {
								$this->exec_query("DELETE FROM {$this->_table['fsrtool_pos']} WHERE itemID={$itemID};");
								$this->exec_query("DELETE FROM {$this->_table['fsrtool_pos_fuel']} WHERE itemID={$itemID}");
								
//								$this->logerror( "Starbase removed (".$this->moonIDtoName($moonID).")", $this->cronID, $data['corpID'] );
							}
						}
					}
					
					foreach($StarbaseList->result->starbases as $Starbase) {
						$itemID			 = $Starbase->itemID;
						$corpID			 = $key['corpID'];
						$typeID			 = $Starbase->typeID;
						$locationID		 = $Starbase->locationID; 
						$moonID			 = $Starbase->moonID; 
						$state			 = $Starbase->state; 
						$stateTimestamp	 = $Starbase->stateTimestamp; 
						$onlineTimestamp = $Starbase->onlineTimestamp;
						
						$stmt->execute();
						// $this->logerror( "Starbase add (".$this->moonIDtoName((string) $Starbase->moonID).")", 2, $data['corpID'] );
						// $this->logerror( "Starbase add (".$this->moonIDtoName((string) $Starbase->moonID).")", $this->cronID, $data['corpID'] );
					}
				} 
				unset($StarbaseList);
				$out .= $this->doLocations($key);
			} catch (Exception $e) {
				$out .= $e->getCode().' - '.$e->getMessage()."\n";
				$this->errorHandler('Problem in StarbaseDetail::'.$e->getMessage(), $e->getCode(), $key['charID'], $key);
//				$this->logerror( $e->getMessage(), $this->cronID, 0 );
			}
		} //  foreach $apis end
		
		$stmt->close();
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		$out .= 'StarbaseList: ' . count($apis) . ' account(s) done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return parent::format( $out );
	}
	
	private function StarbaseDetail() {
		$time_start = microtime(true);
		
		$apis = $this->get_fullApis();
		
		if(parent::$corpID === null) {
			$towercount = $this->fetch_one("SELECT Count(itemID) AS alle FROM {$this->_table['fsrtool_pos']}", 'alle');
			$out = $towercount . " Starbases to Fuel...\n\n";
		} else $out = '';
		
		$query = "UPDATE {$this->_table['fsrtool_pos']} 
			SET state=?,stateTimestamp=?,onlineTimestamp=?,usageFlags=?,deployFlags=?,allowCorporationMembers=?,allowAllianceMembers=?,
				useStandingsFrom=?,onStandingDrop=?,onStatusDrop_enabled=?,onStatusDrop_standing=?,onAggression=?,onCorporationWar=?
			WHERE itemID=?;";
		
		$stmt = $this->prepare($query);
		$stmt->bind_param('ssssssssssssss', $state, $stateTimestamp, $onlineTimestamp, $usageFlags, $deployFlags, $allowCorporationMembers, $allowAllianceMembers,
			$useStandingsFrom, $onStandingDrop, $onStatusDrop_enabled, $onStatusDrop_standing, $onAggression, $onCorporationWar, $itemID);
		
		$fuelQuery = "INSERT INTO {$this->_table['fsrtool_pos_fuel']} (itemID, typeID, quantity) VALUES (?,?,?)	ON DUPLICATE KEY UPDATE quantity=values(quantity);";
		
		$fuelStmt = $this->prepare($fuelQuery);
		$fuelStmt->bind_param('sii', $itemID, $typeID, $quantity);
		
		foreach ( $apis as $key ) {
			// set_time_limit( 90 );
			$this->ale->setKey( $key['keyID'], $key['vCODE'], $key['charID'] );
			$tower = $this->get_tower( $key['corpID'] );
			$y=0;
			$time_tower_start = microtime(true);
			if(is_array($tower)) { 
				foreach ( $tower as $pos ) {
					if($pos != '') { 
						try {
							$StarbaseDetail = $this->ale->corp->StarbaseDetail( array('itemID' => $pos) );
							// echo $pos.'<br>'; print_r($StarbaseDetail); break;
							
							$combatSettings = $StarbaseDetail->result->combatSettings->toArray();
							
							$state						= $StarbaseDetail->result->state;
							$stateTimestamp				= $StarbaseDetail->result->stateTimestamp;
							$onlineTimestamp			= $StarbaseDetail->result->onlineTimestamp;
							$usageFlags					= $StarbaseDetail->result->generalSettings->usageFlags;
							$deployFlags				= $StarbaseDetail->result->generalSettings->deployFlags;
							$allowCorporationMembers	= $StarbaseDetail->result->generalSettings->allowCorporationMembers;
							$allowAllianceMembers		= $StarbaseDetail->result->generalSettings->allowAllianceMembers;
							$useStandingsFrom			= $combatSettings['useStandingsFrom']['ownerID'];
							$onStandingDrop				= $combatSettings['onStandingDrop']['standing'];
							$onStatusDrop_enabled		= $combatSettings['onStatusDrop']['enabled'];
							$onStatusDrop_standing		= $combatSettings['onStatusDrop']['standing'];
							$onAggression				= $combatSettings['onAggression']['enabled'];
							$onCorporationWar			= $combatSettings['onCorporationWar']['enabled'];
							$itemID						= $pos;
							
							$stmt->execute();
							
							$this->exec_query("DELETE FROM {$this->_table['fsrtool_pos_fuel']} WHERE itemID={$pos}");
							foreach ( $StarbaseDetail->result->fuel as $fuel ) {
								$typeID 	= $fuel->typeID;
								$quantity	= $fuel->quantity;
								
								$fuelStmt->execute();
							}
							
							$y++;
							unset($StarbaseDetail);
						} catch (Exception $e) {
							$out .= $e->getCode().' - '.$e->getMessage()."\n";
							$this->errorHandler('Problem in StarbaseDetail::'.$e->getMessage(), $e->getCode(), $key['charID'], $key);
							//$this->logerror( $e->getMessage(), $this->cronID, 0 );
						}
					}
				}
			}
			$time_tower_end = microtime(true);
			$time = $time_tower_end - $time_tower_start;
			$out .= 'APIChar: ' . $key['userName'] . ' - ' . $y . '/' . count($tower) . ' Starbase(s) done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . 'kb memory used.';
			$out .= "\n";
			unset($tower);
			//$this->logerror("Starbase Fuel update", 1, $corpID);
			//$this->logerror("Starbase Fuel update", $this->cronID, $corpID);
		}
		
		$stmt->close();
		$fuelStmt->close();
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if ( parent::$corpID === null )
			$out .= "StarbaseDetail: ". count($apis) . ' account(s), done in ' . round($time, 4) . ' seconds, ' . round((memory_get_peak_usage()/1024), 2) . "kb memory total used.\n";
		
		return parent::format( $out );
		
	}
	
	private function errorHandler($message, $code, $charID, $data=array()) {
		switch(substr($code, 0, 1)) {
			case 1:
			break;
			
			case 2:
				$this->del_fullApi($charID);
			break;
			
			case 3:
			break;
			
			case 4:
				$this->del_fullApi($charID);
			break;
			
			case 5:
			break;
			
			default:
			break;
		}
	}
}
?>