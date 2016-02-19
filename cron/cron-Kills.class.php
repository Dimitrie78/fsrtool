<?php
date_default_timezone_set('UTC');

class cronKills extends cron
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
		
		while ( $this->getApis( $row ) < $this->numRows ) {
			if ( $this->_corpID == 147849586) $this->getKills();
		}
		
		$this->unlog_cron();
		
		return parent::format($this->output);
	}
	
	private function getKills() {
		
		/*** Get Kills  ***/
		$file = $this->killdir . 'zkb' . $this->_corpID . '.xml';
		//$url  = 'eve-kill.net';
		//$url  = "https://zkillboard.com/api/xml/corporationID/147849586/";
	
		/* get the lastkillid form DB */
		$res = $this->query("SELECT Max(k.kill_id) AS lastkillid FROM ".$this->_table['snow_kills']." k WHERE corpID = '{$this->_corpID}';");
		if ( $res->num_rows > 0 ) {
			$row = $res->fetch_assoc();
		}
		$res->close();
		
		if ($row && $row['lastkillid']) {
			$lastkillID = $row['lastkillid'];
			$url = "https://zkillboard.com/api/xml/corporationID/{$this->_corpID}/afterKillID/{$lastkillID}/orderDirection/asc/";
		} else {
			$url = "https://zkillboard.com/api/xml/corporationID/{$this->_corpID}/startTime/".(date('YmdHi', time()-60*60*24*30))."/";
		}

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
			if ($x > 3000) {
				if ( $this->request($url) ) {
					if(strpos($this->xml,"<?xml") !== false) {
						file_put_contents($file, $this->xml);
						unset ($this->xml);
						$this->prase_mail($file);
					}
				}
			}
		}
		
		//if ( $this->_corpID == 147849586) $this->prase_mail($file);
		//echo $this->_corpID; echo '<br>';
	}
	
	private function prase_mail($file) {
		//echo $file."\n";
	  	$sxe = simplexml_load_file($file);
		//print_it($sxe); die;
		if( isset($sxe->error) )
		{	
			$this->output .= intval($sxe->error['code']) . ' - ' . strval($sxe->error);
			return false;
		}
		if( isset($sxe->result->error) )
		{	
			//$this->output .= strval($sxe->result->error);
			return false;
		}
		if(isset($sxe->result)) foreach($sxe->result->rowset->row as $row) $this->processKill($row);
		return true;
	}
	
	private function processKill($row) {
		//$this->output .= "<a href=\"http://eve-kill.net/?a=kill_detail&kll_id=".$row['killID']."\">".$row['killID']."</a>\n";
		if( !$this->killExists($row) )
		{
			//$this->output .= $row['killID']."\n";
			//echo '<pre>'; print_r($row); die;
			
			$this->processVictim($row, strval($row['killTime']));
			foreach($row->rowset[0]->row as $inv) $this->processInvolved($inv, strval($row['killTime']), $row);
		}
		return true;
	}
	
	private function processVictim($row, $time) {
		$victim = $row->victim;
		//echo $victim['characterName']; die;
		if(!strval($victim['characterName'])
			&& !intval($victim['characterID']))
				return false;
		$time = strtotime($time);
		$killID = intval($row['killID']);
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
		//echo $inv['characterName']; die;
		if(!strval($inv['characterName'])
			&& !intval($inv['characterID']))
				return false;		
		$time = strtotime($time);
		$killID = intval($row['killID']);
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
		if(intval($row['killID']) > 0)
		{
			$res = $this->query("SELECT k.kill_id FROM ".$this->_table['snow_kills']." k WHERE kill_id = '".intval($row['killID'])."' AND corpID = '{$this->_corpID}';");
			if ($res->num_rows > 0) {
				$row = $res->fetch_assoc();
				$id = $row['kill_id'];
				return true;
			}
		}
		return false;
	}
	
	private function request($url) {
		$headers = array(
			'User-Agent: '.$_SERVER['SERVER_NAME'],
			'Maintainer: Dimitrie info@fsrtool.de',
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$this->xml = trim(@curl_exec($ch));

		//chceck for connection errors
		$errno = curl_errno($ch);
		if ($errno > 0) {
			$errstr = curl_error($ch);
			curl_close ($ch);
			$this->output .= $url.' - '.$errstr.' - '.$errno. " while fetching file.\n";
			//echo $url.$errstr.$errno;
		}

		curl_close($ch);
		
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
			$this->output .= $e->getCode().' - '.$e->getMessage()."\n";
			$this->errorHandler($e->getMessage(), $e->getCode());
			return false;
		}
		
		return $MemberTracking->result->members->toArray();
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