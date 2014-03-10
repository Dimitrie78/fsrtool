<?php

class cronRatter extends cron
{

	private $refTypeIDList = array(33, 34, 85, 99);
	private $rowCount = 50;
	
	public $output;

	private $_userID = null;
	private $_apiKey = null;
	private $_charID = null;
	
	private $_charName;
	private $_corpID = null;
	private $_corpName = null;
	private $_corpTax = null;
	private $_allyID = null;
	
	private $_numRows = 0;
	
	public function __construct($parms) {
		parent::__construct($parms);
	}
	
	public function run() {
		$count = 0;
		while ( $this->getApis( $count ) < $this->_numRows ) {
			$this->output .= "\n";
			
			try {
				
				if ( $this->getCorpTax() ) {
					$this->output .= $this->_corpName . "\n";
					
					$latestRefID = 0;
					$res = $this->query("SELECT refID FROM {$this->_table['snow_wallet']} WHERE corpid = '{$this->_corpID}' ORDER BY refID DESC LIMIT 1");
					if ( $res->num_rows != 0 ) {
						$row = $res->fetch_row();
						$latestRefID = $row[0];
					}
					if ( !$xml = $this->getApiResponse() ) { 
						$this->output .= "No new entries\n";
						continue; 
					}
					
					$currentRefID = floatval( $xml[0]['refID']);
					
					if ( $currentRefID == $latestRefID )
						$this->output .= "No new entries\n";
					while ( $currentRefID > $latestRefID ) {
						foreach( $xml as $entry ) {
							$currentRefID = floatval($entry['refID']);
							if ( $currentRefID <= $latestRefID )
								break;
							
							// $this->output .= $currentRefID."\n";
							
							$refTypeIDList = $this->refTypeIDList;
							if(!in_array(intval($entry['refTypeID']), $refTypeIDList))
								continue;
							
							$this->output .= $currentRefID."\n";
							
							$data = array();
							foreach ( $entry as $key => $element )
								$data[$key] = $this->real_escape_string($element);
							
							$amount2 = $this->_corpTax == 0 ? floatval( $data['amount'] ) : ( floatval( $data['amount'] ) / $this->_corpTax * 100 );
							// $amount2 = ( floatval( $data['amount'] ) / $this->_corpTax * 100 );
							if ( !empty($entry['reason']) ) {
								$reason = $data['reason'];
								$sql = "INSERT INTO `{$this->_table['snow_wallet']}`
								(`refID`, `char`, `charid`, `system`, `system_id`, `corp`, `corpid`, `reason`, `amount`, `amount2`, `date`) 
								VALUES ('{$data['refID']}', 
								'{$data['ownerName2']}',
								'{$data['ownerID2']}',
								'{$data['argName1']}',
								'{$data['argID1']}',
								'{$this->_corpName}',
								'{$this->_corpID}',
								'{$reason}',
								'{$data['amount']}', 
								'{$amount2}',
								'{$data['date']}')";
							}
							else {
								$reason = $data['reason'].'|refID:'.$data['refTypeID'].'|';
								$sql = "INSERT INTO `{$this->_table['snow_wallet']}`
								(`refID`, `char`, `charid`, `system`, `system_id`, `corp`, `corpid`, `reason`, `agent_name`, `agent_id`, `amount`, `amount2`, `date`) 
								VALUES ('{$data['refID']}', 
								'{$data['ownerName2']}',
								'{$data['ownerID2']}',
								'{$data['argName1']}',
								'{$data['argID1']}',
								'{$this->_corpName}',
								'{$this->_corpID}',
								'{$reason}',
								'{$data['ownerName1']}', 
								'{$data['ownerID1']}',
								'{$data['amount']}', 
								'{$amount2}',
								'{$data['date']}')";
							}
							
							$this->exec_query($sql);
							// $this->output .= ($sql."\n");
							
							if ( !empty($entry['reason']) ) {
								$reason_s = trim($entry['reason'], '.');
								$reason_s = trim($reason_s, ',');
								$reasons = explode(',', $reason_s);
								$values=array();
								foreach ($reasons as $reason) {
									list($rat_id, $rat_amount) = explode(':', $reason);
									$values[] = "('{$this->real_escape_string($data['refID'])}', '{$this->real_escape_string($rat_id)}', '{$this->real_escape_string($rat_amount)}')";
								}
								$sql2 = "INSERT INTO `{$this->_table['snow_ratkills']}` (`refID`, `ratid`, `amount`) VALUES ".implode(',', $values).";";
								$this->exec_query($sql2);
								// $this->output .= ($sql2."\n");
							}
						}
						if(count($xml) < $this->rowCount)
							break;

						if($currentRefID <= $latestRefID)
							break;
						
						$xml = $this->getApiResponse($currentRefID);
					}
				}
			} catch(Exception $e) {
				$this->output .= $e->getCode().' - '.$e->getMessage()."\n";
				$this->errorHandler($e->getMessage(), $e->getCode());
				//return false;
			}
		}
		return parent::format($this->output);
	}
	
	private function getApis(&$count=0) {
		if ( $count == 0 ) {
			$this->_res = $this->query("SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE status=1 GROUP BY corpID;");
			$this->_numRows = $this->_res->num_rows;
		}
		else if ( $count < $this->_numRows ) {
			$this->_res->data_seek( $count );
		}
		$api = $this->_res->fetch_assoc();
		$this->_userID = $api['keyID'];
		$this->_apiKey = $api['vCODE'];
		$this->_charID = $api['charID'];
		$this->_charName = $api['userName'];
		$this->_corpID = $api['corpID'];
		
		return $count++;
	}
	
	private function getCorpTax() {
		try {
			$this->ale->setKey($this->_userID, $this->_apiKey, $this->_charID);
			
			$corpData = $this->ale->corp->CorporationSheet();
			
			$this->_corpID   = (string) $corpData->result->corporationID;
			$this->_corpName = (string) $corpData->result->corporationName;
			$this->_corpTax  = (string) $corpData->result->taxRate;
			$this->_allyID   = (string) $corpData->result->allianceID;
			
		} catch(Exception $e) {
			throw new Exception('Problem in Membertool Carebear Stats -> getCorpTax::'.$e->getMessage(),$e->getCode());
			return false;
		}
		return true;
	}
	
	private function getApiResponse($fromID = null) {
		try {
		/*** Get Wallet Journal ***/
			
			$parms = array();
			
			$parms['rowCount'] = $this->rowCount;
			if( $fromID !== null )
				$parms['fromID'] = $fromID;
				
			$Journal = $this->ale->corp->WalletJournal($parms);
			$Journal = $Journal->result->entries->toArray();
			
			usort($Journal, array($this,'refIDSort')); 
			
		} catch(Exception $e) {
			throw new Exception('Problem in Membertool Carebear Stats -> getApiResponse::'.$e->getMessage(),$e->getCode());
			return false;
		}
		return $Journal;
	}
	
	private function refIDSort($a, $b) {
		return (floatval($a['refID']) > floatval($b['refID'])) ? -1 : 1;
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