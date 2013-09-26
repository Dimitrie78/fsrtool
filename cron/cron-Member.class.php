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
				#if ($this->_corpID != 147849586) continue;
				if ( $xml = $this->getApiResponse() ) {
				#	print_it( $xml ); die;
					
					/*** EMPTY TEMP TABLE ***/
					$this->query("TRUNCATE TABLE ".$this->_table['snow_tempchars']."");
					
					/*** ADD CHARACTERS TO TEMP TABLE ***/
					$index = 0;
					$query = "INSERT INTO ".$this->_table['snow_tempchars']." (charID, arrayIndex, name, startDateTime, logoffDateTime) VALUES";
					#if( $this->extended ){ print_it($xml); die; }
					foreach ( $xml as $member ) {
						if ($index != 0) $query .= ",";
						$name = addslashes($member['name']);
						if( $this->extended )
							$query .= "('{$member['characterID']}', '{$index}', '{$name}', '{$member['startDateTime']}', '{$member['logoffDateTime']}')";
						else $query .= "('{$member['characterID']}', '{$index}', '{$name}', '0', '0')";
						$index++;
					}
					#if( $this->extended ){ echo $query.'<br>'; }
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
				$this->getKills();
				
				#break;
			}
		}
		else {
			while ( $this->getApis( $row ) < $this->numRows ) {
				$this->getKills();
			}
		}
		$this->unlog_cron();
	}
	
	private function getKills() {
		/*
		if ($this->serverStatus()) {
			/*** Get Corp TaxRate ***
			if ( $this->getCorpTax() ) {
				$this->exec_query("INSERT INTO corptax SET corpID='{$this->_corpID}', taxRate='{$this->_corpTax}' ON DUPLICATE KEY UPDATE taxRate='{$this->_corpTax}'");
			}
		}
		*/
		/*** Get Kills  ***/
		$file = $this->killdir . 'kills' . $this->_corpID . '.xml';
		$url  = 'eve-kill.net';
		#$url  = 'feed.evsco.net';
	
		/* get the lastkillid form DB */
		$res = $this->query("SELECT Max(k.kill_id) AS lastkillid FROM ".$this->_table['snow_kills']." k WHERE corpID = '{$this->_corpID}';");
		if ( $res->num_rows > 0 ) {
			$row = $res->fetch_assoc();
		}
		$res->close();
	#http://eve-kill.net/?a=idfeed&corp=147849586&allkills=1&lastintID=10640730
		if ($row) {
			$lastkillID = $row['lastkillid']+1;
			$data = array('corp'      => $this->_corpID,
						  'allkills'  => 1,
						  'lastintID' => $lastkillID);
		} else {
			$data = array('corp'      => $this->_corpID,
						  'allkills'  => 1,
						  'startdate' => time()-60*60*24*10);
		}

		$query = http_build_query($data);
	
		$url = 'http://' . $url . '/?a=idfeed&' . $query;
	
//			echo $url.'<br>';

		if (!file_exists($file)) {			
			if ( $this->request($url) ) {
				if(strpos($this->xml,"<?xml") !== false) {
					file_put_contents($file, $this->xml);
					unset ($this->xml);
					$this->prase_mail($file);
				}
			}
		}
		
		else if ( file_exists($file) ){
			$x = time() - filemtime($file);
			if ($x > 3500) {
				if ( $this->request($url) ) {
					if(strpos($this->xml,"<?xml") !== false) {
						file_put_contents($file, $this->xml);
						unset ($this->xml);
						$this->prase_mail($file);
					}
				}
			}
		}
		
		if ( $this->_corpID == 147849586) 
			$this->prase_mail($file);
		#echo $this->_corpID; echo '<br>';
	}
	
	private function prase_mail($file) {
		#echo $file."\n";
	  	$sxe = simplexml_load_file($file);
		#print_it($sxe); die;
		if(isset($sxe->error))
		{	
			$this->output .= intval($sxe->error['code']) . ' - ' . strval($sxe->error);
			return false;
		}
		if(isset($sxe->result)) foreach($sxe->result->rowset->row as $row) $this->processKill($row);
		return true;
	}
	
	private function processKill($row) {
		#$this->output .= "<a href=\"http://eve-kill.net/?a=kill_detail&kll_id=".$row['killInternalID']."\">".$row['killInternalID']."</a>\n";
		if( !$this->killExists($row) )
		{
			$this->output .= $row['killInternalID']."\n";
			#print_it($row); die;
			
			$this->processVictim($row, strval($row['killTime']));
			foreach($row->rowset[0]->row as $inv) $this->processInvolved($inv, strval($row['killTime']), $row);
		}
		return true;
	}
	
	private function processVictim($row, $time) {
		$victim = $row->victim;
		if(!strval($victim['characterName'])
			&& !intval($victim['characterID']))
				return false;
		$time = strtotime($time);
		$killID = intval($row['killInternalID']);
		$solarSystemID = intval($row['solarSystemID']);
		$charID = intval($victim['characterID']);
		
		if(intval($victim['corporationID']) && intval($victim['corporationID']) == $this->_corpID)
		{
			$res = $this->query("SELECT altOf FROM ".$this->_table['snow_alts']." WHERE charID = '{$charID}';");
			if($res->num_rows > 0) {
				$row = $res->fetch_assoc();
				$main = $row['altOf'];
			} 
			else $main = intval($victim['characterID']);
			#echo $main.' vic<br>';
			$this->exec_query("INSERT IGNORE INTO ".$this->_table['snow_kills']." (kill_id,timestamp,pilot_id,corpID,system_id,main_id,loss) VALUES ('{$killID}', '{$time}', '{$charID}', '{$this->_corpID}', '{$solarSystemID}', '{$main}', '1');");
			#echo "<br>INSERT IGNORE INTO ".$this->_table['snow_kills']." (kill_id,timestamp,pilot_id,corpID,system_id,main_id,loss) VALUES ('{$killID}', '{$time}', '{$charID}', '{$this->_corpID}', '{$solarSystemID}', '{$main}', '1');";
		}
		
		return true;
	}
	
	private function processInvolved($inv, $time, $row){
		#print_it($inv);
		if(!strval($inv['characterName'])
			&& !intval($inv['characterID']))
				return false;		
		$time = strtotime($time);
		$killID = intval($row['killInternalID']);
		$solarSystemID = intval($row['solarSystemID']);
		$charID = intval($inv['characterID']);
		
		if(intval($inv['corporationID']) && intval($inv['corporationID']) == $this->_corpID)
		{
			$res = $this->query("SELECT altOf FROM ".$this->_table['snow_alts']." WHERE charID = '{$charID}';");
			if($res->num_rows > 0) {
				$row = $res->fetch_assoc();
				$main = $row['altOf'];
			} 
			else $main = intval($inv['characterID']);
			#echo $main.' inv<br>';
			$this->exec_query("INSERT IGNORE INTO ".$this->_table['snow_kills']." (kill_id,timestamp,pilot_id,corpID,system_id,main_id,loss) VALUES ('{$killID}', '{$time}', '{$charID}', '{$this->_corpID}', '{$solarSystemID}', '{$main}', '0');");
			#echo "<br>INSERT IGNORE INTO ".$this->_table['snow_kills']." (kill_id,timestamp,pilot_id,corpID,system_id,main_id,loss) VALUES ('{$killID}', '{$time}', '{$charID}', '{$this->_corpID}', '{$solarSystemID}', '{$main}', '0');";
		}
		
		return true;
	}
	
	private function killExists($row) {
		if(intval($row['killInternalID']) > 0)
		{
			$res = $this->query("SELECT k.kill_id FROM ".$this->_table['snow_kills']." k WHERE kill_id = '".intval($row['killInternalID'])."' AND corpID = '{$this->_corpID}';");
			if ($res->num_rows > 0) {
				$row = $res->fetch_assoc();
				$id = $row['kill_id'];
				return true;
			}
		}
		return false;
	}
	
	private function request($url) {
		$http = new http_request($url);
		$http->set_useragent("EDK IDFeedfetcher");
		$http->set_timeout(300);
		$this->xml = $http->get_content();
		if($http->get_http_code() != 200)
		{
			#trigger_error("HTTP error ".$http->get_http_code(). " while fetching file.", E_USER_WARNING);
			$this->logerror("HTTP error ".$http->get_http_code(). " while fetching file.\n");
			return false;
		}
		unset($http);
		if($this->xml) return true;
		else return false;
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
			$this->logerror( $e->getMessage() );
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
			$this->logerror( $e->getMessage() );
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
			$this->logerror( $e->getMessage() );
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

}

?>