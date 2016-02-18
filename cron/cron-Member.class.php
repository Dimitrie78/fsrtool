<?php
date_default_timezone_set('UTC');

class cronMember extends cron
{
	
	private $killdir = "";
	
	private $time30daysago;
	
	public $output;

	private $xml = '';
	
	private $_userID = null;
	private $_apiKey = null;
	private $_charID = null;
	
	private $_charName;
	private $_corpID = null;
	private $_corpName = null;
	private $_corpTax = null;
	
	private $extended = false;
	
	private $numRows = 0;
	
	public function __construct($parms) {
		parent::__construct($parms);
		$this->killdir = FSR_BASE . DIRECTORY_SEPARATOR . 'cache/kills/';
	}
	
	public function run() {
		if ( !$this->check_cron_log() ) {
			print "Scotty the docking manager talking shit about you....";
			exit;
		} else {
			//$this->log_cron();
		}
		
		$this->time30daysago = $this->eveTime() - 60*60*24*30;
		if ($this->serverStatus()) {
			while ( $this->getApis( $row ) < $this->numRows ) {
				//set_time_limit(200);
				//if ($this->_corpID != 147849586) continue;
				if ( $xml = $this->getApiResponse() ) {
				//	print_it( $xml ); die;
					
					/*** EMPTY TEMP TABLE ***/
					$this->query("TRUNCATE TABLE ".$this->_table['snow_tempchars']."");
					
					/*** ADD CHARACTERS TO TEMP TABLE ***/
					$index = 0;
					$query = "INSERT INTO ".$this->_table['snow_tempchars']." (charID, arrayIndex, name, startDateTime, logoffDateTime) VALUES";
					//if( $this->extended ){ print_it($xml); die; }
					foreach ( $xml as $member ) {
						if ($index != 0) $query .= ",";
						$name = addslashes($member['name']);
						if( $this->extended )
							$query .= "('{$member['characterID']}', '{$index}', '{$name}', '{$member['startDateTime']}', '{$member['logoffDateTime']}')";
						else $query .= "('{$member['characterID']}', '{$index}', '{$name}', '0', '0')";
						$index++;
					}
					//if( $this->extended ){ echo $query.'<br>'; }
					$this->exec_query( $query );
					
					/*** LOOK FOR NEW EX-MEMBERS ***/
					$res = $this->query("SELECT c.charID, c.name 
						FROM ".$this->_table['snow_characters']." c
						LEFT JOIN ".$this->_table['snow_tempchars']." t ON c.charID = t.charID
						WHERE c.inCorp = 1
						AND c.corpID = {$this->_corpID}
						AND t.charID IS NULL;");
					if ( $res->num_rows > 0 ) {
						$time = $this->eveTime();
						while ( $char = $res->fetch_assoc() ) {
							$newsQuery = "INSERT INTO ".$this->_table['snow_news']." (dateTime, charID, type)
								VALUES ({$time}, {$char['charID']}, 1);";
							$this->exec_query( $newsQuery );
						
							$updateQuery = "UPDATE ".$this->_table['snow_characters']." SET inCorp = 0 WHERE charID = {$char['charID']};";
							$this->exec_query( $updateQuery );
							
			//				echo "{$char['name']} left corp...<br>";
						}	
					}
					$res->close();
					
					/*** LOOK FOR NEW FORMER MEMBERS ***/
					$res = $this->query("SELECT * FROM ".$this->_table['snow_tempchars'].";");
					while ( $char = $res->fetch_assoc() ) {
						$res2 = $this->query("SELECT charID FROM ".$this->_table['snow_characters']." WHERE charID = {$char['charID']} AND corpID = {$this->_corpID} AND inCorp = 0;");
						if ( $res2->num_rows > 0 ) {
							$dateJoined = strtotime($char['startDateTime']);
							$updateQuery = "UPDATE ".$this->_table['snow_characters']." SET inCorp = 1, joined = {$dateJoined} WHERE charID = {$char['charID']};";
							$this->exec_query( $updateQuery );
			//				echo $updateQuery.'<br>';
						}
						$res2->close();
					}
					$res->close();
					
					/*** LOOK FOR NEW MEMBERS form Other Corp ***/
					$res = $this->query("SELECT temp.* FROM ".$this->_table['snow_tempchars']." temp LEFT JOIN ".$this->_table['snow_characters']." c ON temp.charID = c.charID WHERE c.corpID != {$this->_corpID};");
					$time = $this->eveTime();
					while ( $char = $res->fetch_assoc() ) {
						$newsQuery = "INSERT INTO ".$this->_table['snow_news']." (dateTime, charID, type)
							VALUES ({$time}, {$char['charID']}, 2)";
						$this->exec_query( $newsQuery );
							
						$name       = addslashes($char['name']);
						$lastSeen   = strtotime($char['logoffDateTime']);
						$dateJoined = strtotime($char['startDateTime']);
						$updateQuery = "UPDATE ".$this->_table['snow_characters']." SET lastSeen = '{$lastSeen}', joined = '{$dateJoined}', corpID = '{$this->_corpID}', inCorp = 1
							WHERE charID = {$char['charID']}";
						$this->exec_query( $updateQuery );
		//				echo "{$name} joined the corp...<br>"; 
					}
					$res->close(); 
					
					/*** LOOK FOR NEW MEMBERS ***/
					$res = $this->query("SELECT t.* FROM ".$this->_table['snow_tempchars']." t
						LEFT JOIN ".$this->_table['snow_characters']." c ON t.charID = c.charID
						WHERE c.charID IS NULL;");
					
					$time = $this->eveTime();
					
					while ( $char = $res->fetch_assoc() ) {
						$newsQuery = "INSERT INTO ".$this->_table['snow_news']." (dateTime, charID, type)
							VALUES ({$time}, {$char['charID']}, 2)";
						$this->exec_query( $newsQuery );
							
						$name       = addslashes($char['name']);
						$lastSeen   = strtotime($char['logoffDateTime']);
						$dateJoined = strtotime($char['startDateTime']);
						$updateQuery = "INSERT INTO ".$this->_table['snow_characters']." (charID, corpID, name, lastSeen, joined)
							VALUES ('{$char['charID']}', '{$this->_corpID}', '{$name}', '{$lastSeen}', '{$dateJoined}')";
						$this->exec_query( $updateQuery );
		//				echo "{$name} joined the corp...<br>";
					}
					$res->close();	
					
					/*** UPDATE EXISTING MEMBERS ***/
					$res = $this->query("SELECT t.* FROM ".$this->_table['snow_tempchars']." t
						INNER JOIN ".$this->_table['snow_characters']." c ON c.charID = t.charID
						WHERE c.inCorp = 1
						AND c.corpID = {$this->_corpID};");
					
					while ( $char = $res->fetch_assoc() ) {	
						$lastSeen   = strtotime($char['logoffDateTime']);
						$dateJoined = strtotime($char['startDateTime']);
						$updateQuery = "UPDATE ".$this->_table['snow_characters']." SET lastSeen = '{$lastSeen}', joined = '{$dateJoined}'
							WHERE charID = {$char['charID']}";
						$this->exec_query( $updateQuery );
						//echo "{$char['name']} has been updated...<br>";
					}
					$res->close();
					
					/*** AUTO-FLAG INACTIVE ***/
					$res = $this->query("SELECT charID, name FROM ".$this->_table['snow_characters']."
						WHERE lastSeen < {$this->time30daysago}
						AND inactive != 1
						AND inCorp = 1
						AND corpID = {$this->_corpID};");
					
					$time = $this->eveTime();
					
					while ( $char = $res->fetch_assoc() ) {
						$newsQuery = "INSERT INTO ".$this->_table['snow_news']." (dateTime, charID, type)
							VALUES ({$time}, {$char['charID']}, 3)";
						$this->exec_query( $newsQuery );
						
			//			echo "{$char['name']} is inactive...<br>";
					}
					$res->close();
					
					$updateQuery = "UPDATE ".$this->_table['snow_characters']." SET inactive = 1
						WHERE lastSeen < {$this->time30daysago}
						AND corpID = {$this->_corpID};";
					$this->exec_query( $updateQuery );
					
					/*** AUTO-UNFLAG INACTIVE ***/
					$res = $this->query("SELECT charID, name FROM ".$this->_table['snow_characters']."
						WHERE lastSeen >= {$this->time30daysago}
						AND inactive = 1
						AND inCorp = 1
						AND corpID = {$this->_corpID};");
					
					while ( $char = $res->fetch_assoc() ) {
						$newsQuery = "INSERT INTO ".$this->_table['snow_news']." (dateTime, charID, type)
							VALUES ({$time}, {$char['charID']}, 5)";
						$this->exec_query( $newsQuery );
						
			//			echo "{$char['name']} is active...<br>";
					}
					$res->close();
					
					$updateQuery = "UPDATE ".$this->_table['snow_characters']." SET inactive = 0
						WHERE lastSeen >= {$this->time30daysago}
						AND inactive = 1
						AND corpID = {$this->_corpID};";
					$this->exec_query( $updateQuery );
					
					$this->exec_query("DELETE FROM ".$this->_table['snow_time']." WHERE updateTIME > 0");
					$this->exec_query("INSERT INTO ".$this->_table['snow_time']." values('".time()."')");
				}
				
				//break;
			}
		}
		
		$this->unlog_cron();
		
		return parent::format($this->output);
	}
	
	private function getApis(&$row=0) {
		if ( $row == 0 ) {
			$this->res = $this->query("SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE status=1 GROUP BY corpID;");
			$this->numRows = $this->res->num_rows;
		}
		else if ( $row < $this->numRows ) {
			$this->res->data_seek( $row );
		}
		$api = $this->res->fetch_assoc();
		$this->_userID = $api['keyID'];
		$this->_apiKey = $api['vCODE'];
		$this->_charID = $api['charID'];
		$this->charName = $api['userName'];
		
		$this->_corpID   = $api['corpID'];
		//$this->_corpName = $api['charID'];
		
		return $row++;
	}
	
	private function getApiResponse($fromID = null) {
		$parms = array('extended' => 1);
		$this->extended = false;
		try {
			$this->ale->setKey($this->_userID, $this->_apiKey, $this->_charID);
			
			$APIKeyInfo = $this->ale->account->APIKeyInfo();
			if ( $APIKeyInfo->error ) {
				return false;
			}
			if( intval($APIKeyInfo->result->key->accessMask) & 33554432 ) {
				$MemberTracking = $this->ale->corp->MemberTracking($parms);
				$this->extended = true;
				if ( $MemberTracking->error ) {
					return false;
				}
			}
			else if( intval($APIKeyInfo->result->key->accessMask) & 2048 ) {
				$MemberTracking = $this->ale->corp->MemberTracking();
				$this->extended = false;
				if ( $MemberTracking->error ) {
					return false;
				}
			}
			else return false;
			
			
		} catch(Exception $e) {
			$this->output .= $e->getCode().' - '.$e->getMessage()."\n";
			$this->errorHandler($e->getMessage(), $e->getCode());
			return false;
		}
		
		return $MemberTracking->result->members->toArray();
	}
	
	private function getCorpTax() {
		try {
			$this->ale->setKey($this->_userID, $this->_apiKey, $this->_charID);
			
			$corpData = $this->ale->corp->CorporationSheet();
	 
			if ($corpData->error) {
				return false;
			} else {
				$this->_corpID   = (string) $corpData->result->corporationID;
				$this->_corpName = (string) $corpData->result->corporationName;
				$this->_corpTax  = (string) $corpData->result->taxRate;
				return true;
			} // else error end
		} catch(Exception $e) {
			$this->output .= $e->getCode().' - '.$e->getMessage()."\n";
			$this->errorHandler($e->getMessage(), $e->getCode());
			return false;
		}
		return false;
	}
	
	private function log_cron() {
		$this->exec_query( "UPDATE {$this->_table['snow_run']} SET run = 1" );
	}
	
	private function check_cron_log() {
		$res = $this->query( "SELECT run FROM {$this->_table['snow_run']}" );
		$row = $res->fetch_assoc();
		if ($row['run'] == 0)
			return true;
		
		return false;
	}
	
	public function unlog_cron() {
		$this->exec_query( "UPDATE {$this->_table['snow_run']} SET run = 0" );
	}
	
	public function serverStatus() {
		try {
			$status = $this->ale->server->ServerStatus();
			if ($status->error) 
				return false;
			return true;
		} catch(Exception $e) {
			$this->output .= $e->getCode().' - '.$e->getMessage()."\n";
			$this->errorHandler($e->getMessage(), $e->getCode());
			return false;
		}
	}
	
	private function logerror($mesg) {
		//$callee = next(debug_backtrace());
		//$msg = $mesg."in ".$callee['file']." on line: ".$callee['line'];
		//if ( !is_dir( $this->logdir ) ) mkdir( $this->logdir );
		//$logfile = $this->logdir . 'SnowCron' . date('dmY') . '.log';
		//file_put_contents( $logfile, "\n".date('c')."\n".$msg."\n", FILE_APPEND );
		//die('Error: see log file');
	}
	
	private function eveTime() {
		return time();
	}
	
	private function deactivateAPI() {
		$errorcount = $this->apiErrorCount;
		$result = $this->exec_query("UPDATE {$this->_table['fsrtool_user_fullapi']} SET errorcount = if(status = 0, 0, errorcount + 1), status = if(errorcount >= {$errorcount}, 0, 1) WHERE charID='{$this->_charID}';");
		
		return $result;
	}
	
	private function errorHandler($message, $code, $charID=0, $data=array()) {
		cron::errorLOG($message, $code, $this->_corpID);
		
		switch(substr($code, 0, 1)) {
			case 1:
			break;
			
			case 2:
			break;
			
			case 3:
			break;
			
			case 4:
				$this->deactivateAPI();
			break;
			
			case 5:
			break;
			
			default:
			break;
		}
	}

}

?>