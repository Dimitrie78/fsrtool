<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class Productions {
	
	private $_corpID = null;
	private $_db = null;
	
	private $_table_api = 'fsrtool_jb_apis';
	private $_table_corp = 'fsrtool_corps';
	
	public function __construct(World $world) {
		global $parms;
		
		$this->_corpID = $world->User->corpID;//$corpID;
		$this->_db = $world->db;
		$this->_ale = AleFactory::getEVEOnline($parms);
		// echo '<pre>'; print_r($this); echo '</pre>';die;
	}
	
	public function saveApi(array $data) {
		$data = $this->_db->escape($data);
		
		$res = $this->_db->exec_query("INSERT INTO {$this->_table_api} (charID,ownerID,charName,corpID,keyID,vCode,accessMask,status) 
			VALUES ({$data['obj']['charid']},
					{$this->_corpID},
					'{$data['obj']['result']['key']['characters'][$data['obj']['charid']]['characterName']}',
					{$data['obj']['result']['key']['characters'][$data['obj']['charid']]['corporationID']},
					{$data['obj']['keyid']},
					'{$data['obj']['vcode']}',
					{$data['obj']['result']['key']['accessMask']},1)");
		
		
		return array();
	}
	
	public function getApis() {
		return $this->_db->fetch_all("SELECT a.*, c.name AS corpName FROM {$this->_table_api} AS a 
			LEFT JOIN {$this->_table_corp} AS c ON a.corpID = c.id
			ORDER BY a.charName");
	}
	
	public function getJobs() {
		$userID = '2316674';
		$apiKey = '7qZtSilrBW7ksaVExL3ZmDiDCrF8VdPlW7HBdEnkIZ4pfZRxGdcsrIh4Cmx9YxX9';
		$charID = '483953569';

		try{
		  //$ale = AleFactory::getEvEOnline();
		  $this->_ale->setKey($userID,$apiKey,$charID);
		  $jobs = $this->_ale->corp->IndustryJobs();
		  $cache = strtotime($jobs->cachedUntil->toArray());
		  
		  foreach($jobs->result->jobs as $job){
			if($job->completed == 1)
			  continue;
			$job = $job->toArray();
			$job['installTimeUnix'] = strtotime($job['installTime']);
			$job['installTime'] = date('d.m H:i',$job['installTimeUnix']); 
			$job['beginProductionTime'] = strtotime($job['beginProductionTime']);
			$job['endProductionTimeUnix'] = strtotime($job['endProductionTime']);
			$job['endProductionTime'] = date('d.m H:i',$job['endProductionTimeUnix']);
			$job['pauseProductionTime'] = strtotime($job['pauseProductionTime']); 
			$stat_chars[$job['installerID']]['jobs'][$job['activityID']]++;
			$stat_activity[$job['activityID']]['qty']++;
			$stat_jobs[$job['outputTypeID']]['jobs'][$job['activityID']][] = array('runs'=>$job['runs'],'endProductionTimeUnix'=>$job['endProductionTimeUnix']);
			$stat_jobs[$job['outputTypeID']]['jobs'][$job['activityID']]['all'] += $job['runs'];
			$stat_sys[$job['installedInSolarSystemID']]++;
			if($job['containerID']>=60000000 && $job['containerID']<62000000){
			  $stnlist = $this->_ale->eve->ConquerableStationList(); 
			  foreach($stnlist->result->outposts as $stn){
				if($stn->stationID == $job['containerID']){
				  $stn = $stn->toArray(); 
				  $job['containerName'] = $stn['stationName'];
				  $stat_activity[$job['activityID']]['stn']++; 
				}
			  }         
			}else{
				  $job['containerName'] = 'POS';
				  $stat_activity[$job['activityID']]['pos']++;
			}
			$_jobs[] = $job;
		  }
		  
		  
		  
		  $assets = $this->_ale->corp->AssetList();
		  foreach($assets->result->assets as $asset){
			$this->assets($asset);
		  }
		  
		  # charID -> charName
		 # die(print_r($stat_chars));
		  $charnames = $this->_ale->eve->CharacterName(array('ids'=>implode(array_keys($stat_chars),',')))->result->characters->toArray();
		  foreach($charnames as $charID => $char){
			$stat_chars[$charID]['name'] = $char['name'];
		  }
		  
		  # outputItemTypeID -> TypeName 
		  $qry = 'SELECT i.`typeID`,i.`typeName`,i.`groupID`,ig.`groupName`,ig.`categoryID`,ic.`categoryName` 
				FROM `fsrtool_eve_invtypes` as i 
				LEFT JOIN `fsrtool_eve_invgroups` as ig ON ig.`groupID` = i.`groupID` 
				LEFT JOIN `fsrtool_eve_invcategories` as ic ON ic.`categoryID` = ig.`categoryID` 
				WHERE `typeID` = '.implode(' OR `typeID` = ',array_merge(array_keys($stat_jobs),array_keys($this->_assets)));
		  $res_type = $this->_db->query($qry, false);
		  if (!$res_type) { $json['error'] = 'Ungültige Anfrage: ' . $this->_db->db->error; return $json; }
		  # systemID -> systemName
		  $qry = 'SELECT `solarSystemID`,`solarSystemName` FROM `fsrtool_eve_mapsolarsystems` WHERE `solarSystemID` = '.implode(' OR `solarSystemID` = ',array_keys($stat_sys));
		  $res_sys = $this->_db->query($qry);
		  if (!$res_sys) { $json['error'] = 'Ungültige Anfrage: ' . $this->_db->db->error; return $json; }
		  
		  for($i=0;$i<count($_jobs);$i++){
			$_jobs[$i]['installer'] = $stat_chars[$_jobs[$i]['installerID']]['name'];
			if($res_type){
			  while($row = $res_type->fetch_assoc()){
				if($_jobs[$i]['outputTypeID']==$row['typeID']){
				  $_jobs[$i]['outputTypeName'] = $row['typeName'];
				  $_jobs[$i]['outputGroupName'] = $row['groupName'];
				  $_jobs[$i]['outputGroupID'] = $row['groupID'];
				  $_jobs[$i]['outputCategoryName'] = $row['categoryName'];
				  $_jobs[$i]['outputCategoryID'] = $row['categoryID'];
				}
				if($row['groupID'] != 873){
				  unset($this->_assets[$row['typeID']]);
				}else{
				  if(!isset($this->_assets[$row['typeID']]['qty']))
					$this->_assets[$row['typeID']]['qty'] = 0;
				  $stat_jobs[$row['typeID']]['outputTypeName'] = $row['typeName'];
				  $stat_jobs[$row['typeID']]['groupID'] = $row['groupID']; 
				  $stat_jobs[$row['typeID']]['stockQuantity'] = $this->_assets[$row['typeID']]['qty'];
				}
			  }
			  /* seek to row no. 0 */
			  $res_type->data_seek(0);
			}
			if($res_sys){
			  while($row = $res_sys->fetch_assoc()){
				if($_jobs[$i]['installedInSolarSystemID']==$row['solarSystemID'])
				  $_jobs[$i]['installedInSolarSystemName'] = $row['solarSystemName'];
			  }
			  /* seek to row no. 0 */
			  $res_sys->data_seek(0);
			}    
		  }
		  //mysql_free_result($res_type);
		  
		}
		catch(Exeption $e){
		  $json['error'] = $e->getMessage();
		  return $json;
		}

		#return($stat_jobs);
		#print_r($_jobs);


		// clean up stat_jobs array
		foreach($stat_jobs as $k=>$v){
			if(count($v)==1)
				unset($stat_jobs[$k]);
			if(!isset($v['jobs'][1]))
				unset($stat_jobs[$k]);
		}

		$json['info']['cachedUntil'] = $cache;
		$json['info']['chars'] = $stat_chars;
		$json['info']['activity'] = $stat_activity;
		$json['info']['manjobs'] = $stat_jobs;
		$json['jobs'] = $_jobs;
		
		return $json;
	}
	
	private function assets($node){
		$this->_assets;
		if($node->typeID)
			$this->_assets[$node->typeID]['qty'] += $node->quantity;
		if($node->contents!==null){
			$this->assets($node->contents->row);
		}
	}
}
?>