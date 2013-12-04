<?php
defined('FSR_BASE') or die('Restricted access');

class SiloWorld extends World {
	
	private $MoonIDsFromTowersCache = array();
	private $moonIDtoNameCache = array();
	private $dotlanLinkCache = array();
	
	private $_table_fullApi 		= 'fsrtool_user_fullapi';
	private $_table_pos 			= 'fsrtool_pos';
	private $_table_pos_corphanger 	= 'fsrtool_pos_corphanger';
	private $_table_pos_filter 		= 'fsrtool_pos_filter';
	private $_table_pos_fuel 		= 'fsrtool_pos_fuel';
	private $_table_pos_maillist 	= 'fsrtool_pos_maillist';
	private $_table_silo 			= 'fsrtool_silos';
	private $_table_silo_cachetimes = 'fsrtool_silos_cachetimes';
	private $_table_silo_reactors 	= 'fsrtool_silos_reactors';
	private $_table_assets 			= 'fsrtool_assets';
	private $_table_assets_contents = 'fsrtool_assets_contents';
	
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		// echo '<pre>'; print_r( $this ); die;
	}
	
	public function makeMenue($corpID) {
		global $smarty;
		$menue = array();
		$query  = "SELECT s.locationID as id, m.solarSystemName as Name, r.regionName as Region
				   FROM {$this->_table_silo} as s 
				   INNER JOIN {$this->_table['mapsolarsystems']} as m ON s.locationID = m.solarSystemID
				   INNER JOIN {$this->_table['mapregions']} as r ON m.regionID = r.regionID
				   WHERE corpID = '$corpID'
				   GROUP BY s.locationID 
				   ORDER BY Name ASC;";
		$res = $this->db->query($query);
		while ($row = $res->fetch_assoc()) {
			if ($row) {
				// $menue[] = $row;
				$menue[$row['Region']][] = $row;
			}
		}
		
		$res->close();
		$smarty->assign('Menue', $menue);
	}
	
	public function pos_getApiStatus() {
		$corpID = $this->db->escape( $_SESSION['corpID'] );
		$str = "SELECT fullapi.*, corps.name as corpName 
			  FROM {$this->_table['fsrtool_user_fullapi']} as fullapi 
			  INNER JOIN {$this->_table['fsrtool_corps']} as corps ON corps.id = fullapi.corpID
			  WHERE fullapi.corpID='".$corpID."';";
		$api = $this->db->fetch_one( $str );
		
		if($api['status'] == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function assignSilo($moonIDs, $itemIDs) {		
		foreach ($moonIDs as $key => $moonID) {
			if ( $moonID != 0 ) {
				$this->db->exec_query("UPDATE {$this->_table_silo} SET pos = '".$moonID."' 
					WHERE itemID = '".$itemIDs[ $key ]."';");
			}
		}
		
		return true;
	}
	
	public function unAssignSilo($itemID) {		
		$query  = "UPDATE {$this->_table_silo} SET pos = NULL, turn=0,input=0,stack=0,alarm=0,suspect=0 WHERE itemID = '$itemID';";
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function emptySilo($itemID) {
		$now    = time();
		$query  = ("UPDATE {$this->_table_silo} 
				SET quantity = if (turn = 0, 0, 20000 * IFNULL((((SELECT IfNull(d.valueFloat, d.valueInt)
					  FROM {$this->_table['dgmtypeattributes']} d 
					  INNER JOIN {$this->_table_pos} p ON p.typeID = d.typeID
					  WHERE d.attributeID = 757 AND p.moonID = {$this->_table_silo}.pos) + 100) / 100),1)
					  / (SELECT volume FROM {$this->_table['invtypes']} WHERE {$this->_table_silo}.typeID = {$this->_table['invtypes']}.typeID)),
				emptyTime = '$now' WHERE itemID = '$itemID'");
		//$query  = "UPDATE {$this->_table_silo} SET quantity = 0, emptyTime = '$now' WHERE itemID = '$itemID';";
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function onlineSilo($itemID=NULL, $corpID=NULL) {		
		if($itemID!==NULL) $query  = "UPDATE {$this->_table_silo} SET suspect=0 WHERE itemID = '$itemID';";
		if($corpID!==NULL) $query  = "UPDATE {$this->_table_silo} SET suspect=0 WHERE corpID = '$corpID';";
		
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function setSiloInput($itemID) {
		$query  = "UPDATE {$this->_table_silo} SET input = 1 WHERE itemID = '$itemID';";
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function setSiloOutput($itemID) {
		$query  = "UPDATE {$this->_table_silo} SET input = 0 WHERE itemID = '$itemID';";
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function setSiloFill($itemID) {
		$query  = "UPDATE {$this->_table_silo} SET turn = 1 WHERE itemID = '$itemID';";
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function setSiloEmpty($itemID) {
		$query  = "UPDATE {$this->_table_silo} SET turn = 0 WHERE itemID = '$itemID';";
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function setSiloStacked($itemID) {
		$query  = "UPDATE {$this->_table_silo} SET stack = 1 WHERE itemID = '$itemID';";
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function setSiloUnStack($itemID) {
		$query  = "UPDATE {$this->_table_silo} SET stack = 0 WHERE itemID = '$itemID';";
		$res = $this->db->exec_query( $query );
		
		return $res;
	}
	
	public function getcorps() {
		if ( $this->User->Admin ) { //&& $this->User->charID == '285591396' ) {
			$corps[ $this->User->corpID ] = $this->User->corpName;
			if ( $this->User->alts ) {
				foreach ( $this->User->alts as $alt ) {
					if ( $alt['pos'] == 1 )
						$corps[ $alt['corpID'] ] = $alt['corpName'];
				}
			}
			$str = "SELECT p.corpID, c.name
					  FROM {$this->_table['fsrtool_user_fullapi']} p
					  LEFT JOIN {$this->_table['fsrtool_corps']} c ON p.corpID = c.id 
					  GROUP BY p.corpID ORDER BY c.name;";
			$res = $this->db->query( $str );
			while ( $row = $res->fetch_assoc() ) {
				if ( $row ) {
					$corps[ $row['corpID'] ] = $row['name']; 
				}
			}
		} else
		if ( $this->User->allyID != 0 && $this->User->SiloManagerAlly ) {
			$corps[ $this->User->corpID ] = $this->User->corpName;
			$str = "SELECT p.corpID, c.name
					  FROM {$this->_table['fsrtool_user_fullapi']} p
					  INNER JOIN {$this->_table['fsrtool_corps']} c ON p.corpID = c.id
					  WHERE c.ally='".$this->User->allyID."' 
					  GROUP BY p.corpID ORDER BY c.name;";
			$res = $this->db->query( $str );
			while ( $row = $res->fetch_assoc() ) {
				if ( $row ) {
					$corps[ $row['corpID'] ] = $row['name']; 
				}
			}
			if ( $this->User->alts ) {
				foreach ( $this->User->alts as $alt ) {
					if ( $alt['silo'] == 1 )
						$corps[ $alt['corpID'] ] = $alt['corpName'];
				}
			}
		} else if ( $this->User->SiloManager || $this->User->Admin) {
			$corps[ $this->User->corpID ] = $this->User->corpName;
			if ( $this->User->alts ) {
				foreach ( $this->User->alts as $alt ) {
					if ( $alt['silo'] == 1 )
						$corps[ $alt['corpID'] ] = $alt['corpName'];
				}
			}
		} else {
			if ( $this->User->alts ) {
				foreach( $this->User->alts as $alt ) {
					if ( $alt['silo'] == 1 )
						$corps[ $alt['corpID'] ] = $alt['corpName'];
				}
				unset( $corps[ $this->User->corpID ] );
			}
		}
		return $corps;
	}
	
	public function getUnassignSilos($corpID) {
		$silos = array();
		$allIds = array(0,16654,16655,16656,16657,16658,16659,16660,16661,16662,16663,16664,16665,16666,16667,16668,16669,17769,17959,17960,29659,29660,29661,29662,29663,29664,16670,16671,16672,16673,16678,16679,16680,16681,16682,16683,17317,16633,16634,16635,16636,16637,16638,16639,16640,16641,16642,16643,16644,16646,16647,16648,16649,16650,16651,16652,16653);
		$sid = implode(',', $allIds);
		$res = $this->db->query("SELECT silo.itemID, silo.locationID, i.typeName, i.typeID, m.itemName AS sysName, silo.quantity
									  FROM {$this->_table_silo} silo 
									  INNER JOIN {$this->_table['invtypes']} i ON silo.typeID = i.typeID 
									  INNER JOIN {$this->_table['mapdenormalize']} m ON silo.locationID = m.itemID
									  WHERE silo.pos IS NULL AND silo.corpID = '$corpID' /*AND silo.typeID IN ({$sid})*/
									  ORDER BY sysName, silo.typeID;");
		while ( $row = $res->fetch_assoc() ) {
			if ($row) {
				$silo['itemID']   = $row['itemID'];
				$silo['location'] = $row['sysName'];
				$silo['typeID']   = $row['typeID'];
				if($row['typeID'] != 0){
					$silo['type']     = $row['typeName'];
					$silo['typePic']  = '<img src="'.MODULE_DIR . ACTIVE_MODULE.'/img/'.$row['typeID'].'_32.png" width="32" height="32" />';
					// $silo['typePic']  = '<img src="./icons/Types/'.$row['typeID'].'_32.png" width="32" height="32" />';
				}else{
					$silo['type']     = 'undefined';
				}
				$silo['quantity'] = number_format($row['quantity'],'0',',','.');
				$silo['moonIDs']  = $this->getMoonIDsFromTowers($row['locationID'], $corpID);
				$silos[] 		  = $silo;
				
			}
		}
		$res->close();
		
		return $silos;
	}

	private function getMoonIDsFromTowers($id, $corpID) {
		if ( !isset( $this->MoonIDsFromTowersCache[ $id ] ) ) {
			$towers = array(' ');
			$id     = $this->db->escape($id);
			$res = $this->db->query("SELECT moonID FROM {$this->_table_pos} 
				WHERE locationID = '$id' AND corpID = '$corpID' ORDER BY moonID;");
			while ( $row = $res->fetch_assoc() ) {
				if ($row) {
					$towers[$row['moonID']] = $this->moonIDtoName($row['moonID']);
				}
			}
			$res->close();
			$this->MoonIDsFromTowersCache[ $id ] = $towers;
		}
		return $this->MoonIDsFromTowersCache[ $id ];
	}
	
	public function moonIDtoName($ID) {
		if ( !isset( $this->moonIDtoNameCache[ $ID ] ) ) {
			$this->moonIDtoNameCache[ $ID ] = $this->db->fetch_one("SELECT itemName FROM {$this->_table['mapdenormalize']} 
				WHERE itemID = '$ID';", 'itemName');
		}
		return $this->moonIDtoNameCache[ $ID ];
	}
	
	public function dotlanLink($locationID) {
		if ( !isset( $this->dotlanLinkCache[ $locationID ] ) ) {
			$name = $this->db->fetch_one("SELECT itemName FROM {$this->_table['mapdenormalize']} WHERE itemID = '$locationID';", 'itemName');
			$link = '<a target="_blank" href="http://evemaps.dotlan.net/system/'.$name.'">'.$name.'</a>'; // http://evemaps.dotlan.net/system/5KG-PY/VI-Moon-7
			$this->dotlanLinkCache[ $locationID ] = $link;
		}
		return $this->dotlanLinkCache[ $locationID ];
	}
	
	public function getAssetsCacheTime($corpID) {
		$cacheTime = $this->db->fetch_one("SELECT cacheTime FROM {$this->_table_silo_cachetimes} WHERE type = '1' AND corpID = '$corpID';", 'cacheTime');
		
		return date( 'd.m.Y H:i', ($cacheTime - date('Z')) );
	}
	
	public function updatePrice($corpID) {
		$id = $this->db->fetch_all("SELECT typeID FROM {$this->_table_silo} WHERE corpID = {$corpID} AND typeID != 0 GROUP BY typeID", 'typeID');
		$ids = array_merge(array(4051,4246,4247,4312), $id);
		if (!isset($ids[0])) return false;
		return $this->getCurrentEvecentralPrice(30000142, $ids);
	}
	
	private function getCurrentEvecentralPrice( $systemID=0, array $ids) {
		global $parms;
		unset($parms['main']['host']);
		$ale = AleFactory::getEvECentral($parms);
		if ($systemID == "0")
			$params = array('typeid'=>$ids);			
		else
			$params = array('typeid'=>$ids, 'usesystem'=>$systemID);	
		try {
			$xml = $ale->marketstat( $params );
			#echo '<pre>';print_r($xml);echo '</pre>'; die;
			
			$insert = "REPLACE INTO %tab_currentTypePrice% SET typeID='%typeID%', all_volume='%all_volume%', all_avg_price='%all_avg%', all_max_price='%all_max%', all_min_price='%all_min%', all_stddev_price='%all_stddev%', all_median_price='%all_median%', buy_volume='%buy_volume%', buy_avg_price='%buy_avg%', buy_max_price='%buy_max%', buy_min_price='%buy_min%', buy_stddev_price='%buy_stddev%', buy_median_price='%buy_median%', sell_volume='%sell_volume%', sell_avg_price='%sell_avg%', sell_max_price='%sell_max%', sell_min_price='%sell_min%', sell_stddev_price='%sell_stddev%', sell_median_price='%sell_median%', fetched='%fetched%',region='%region%';";
			$update = "UPDATE %tab_currentTypePrice% SET all_volume='%all_volume%', all_avg_price='%all_avg%', all_max_price='%all_max%', all_min_price='%all_min%', all_stddev_price='%all_stddev%', all_median_price='%all_median%', buy_volume='%buy_volume%', buy_avg_price='%buy_avg%', buy_max_price='%buy_max%', buy_min_price='%buy_min%', buy_stddev_price='%buy_stddev%', buy_median_price='%buy_median%', sell_volume='%sell_volume%', sell_avg_price='%sell_avg%', sell_max_price='%sell_max%', sell_min_price='%sell_min%', sell_stddev_price='%sell_stddev%', sell_median_price='%sell_median%', fetched='%fetched%' WHERE typeID='%typeID%' AND region='%region%';";
			$changed = time();
			$return = array();
			$insert = str_replace("%tab_currentTypePrice%", $this->_table['fsrtool_currentTypePrice'], $insert);
			$insert = str_replace("%fetched%", $changed, $insert);
			$insert = str_replace("%region%", $systemID, $insert);
			foreach ( $xml->marketstat->type as $type ) {
				$str = str_replace("%typeID%", (int)$type->attributes()->id, $insert);
				foreach ( $type as $typ => $v ) {
					foreach( $v as $key => $val ) { 
						$str = str_replace("%".$typ."_".$key."%", $val, $str);
					}
				}
				$return[(int)$type->attributes()->id] = (float)$type->buy->max;
				$this->db->exec_query( $str );
				#break;
			}
			
			return 'done';
		} catch (Exception $e) {
			$this->db->msg->addwarning('eve-central.com failed...');
			return 'eve-central.com failed...';
		}
	}
	
	public function delAllStuff(array $data) {
		$data = $this->db->escape($data);
		
		$str = array();
				
		$str[] = "DELETE FROM {$this->_table_silo} WHERE corpID = {$data['corpid']}";
		$str[] = "DELETE FROM {$this->_table_silo_cachetimes} WHERE corpID = {$data['corpid']}";
		$str[] = "DELETE FROM {$this->_table_silo_reactors} WHERE corpID = {$data['corpid']}";
		$str[] = "DELETE FROM {$this->_table_assets} WHERE corpID = {$data['corpid']}";
		$str[] = "DELETE FROM {$this->_table_assets_contents} WHERE corpID = {$data['corpid']}";
		
		foreach($str as $query) {
			$res = $this->db->exec_query( $query );
		}
		
		return 'Bye Bye...';
	}
	
	public function getManager($corpID) {
		$str = "SELECT manager FROM {$this->_table['fsrtool_pos']} WHERE corpID = '{$corpID}' AND manager != '' GROUP BY manager";
		$res = $this->db->fetch_all($str, 'manager');
		
		return $res;
	}
}

?>