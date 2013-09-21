<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class PosWorld extends world {
	
	var $querys = 0;
	
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
	
	public $PosIDsCache = array();
	public $PosFuelCache = array();
	public $SystemSecurityCache = array();
	public $SovereigntyCache = array();
	public $towerresurceCache = array();
	
	public $locationCache = array();
	
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		// echo '<pre>'; print_r( $this ); die;
	}
	
	function pos_get_possen($status,$corp,$sortby="time") {
		// global $database;
		if	  ($sortby=='time') 	  $this->sort="online";
		elseif($sortby=='regionName') $this->sort="region";
		elseif($sortby=='Moon')	  	  $this->sort="moon";
		elseif($sortby=='typeID')	  $this->sort="tower";
		elseif($sortby=='manager')	  $this->sort="manager";
		
		$itemIDs = $this->getPosID( $status, $corp );
		
		$pos = array();
		
		foreach ( $itemIDs as $itemID ) {
			// $s = microtime(true);
			$thispos = new Pos( $itemID, $this );
			$pos[] = $thispos->toArray();
			// echo round(microtime(true) - $s, 4).' - ';
			// echo '<pre>'; print_r($pos); die;
		}
		
		usort($pos, array($this, "sort"));
		
		return $pos;
	}
	
	private function sort($a, $b) {
		if ($a[ $this->sort ] == $b[ $this->sort ])
			return 0;
		return ($a[ $this->sort ] < $b[ $this->sort ]) ? -1 : 1;
	}
	
	public function SystemSecurity( $systemID ) {
		if ( !isset( $this->SystemSecurityCache[ $systemID ] ) ) {
			$str = "SELECT security FROM {$this->_table['mapsolarsystems']} WHERE solarSystemID='{$systemID}';";
			$res = $this->db->fetch_one( $str, 'security' );
			$this->SystemSecurityCache[ $systemID ] = $res;
			// $this->querys ++;
		}
        return $this->SystemSecurityCache[ $systemID ];
    }
	
	public function Sovereignty( $solarSystemID, $allyID ) {
		if ($allyID == 0)
			$this->SovereigntyCache[ $solarSystemID.$allyID ] = false;
		else {
			if ( !isset( $this->SovereigntyCache[ $solarSystemID.$allyID ] ) ) {
				$sqlstring = "SELECT * FROM {$this->_table['fsrtool_api_sovereignty']} WHERE solarSystemID='".$solarSystemID."' AND allianceID='".$allyID."';";
				$result = $this->db->query( $sqlstring );
				if( $result->num_rows > 0 ) {
					$this->SovereigntyCache[ $solarSystemID.$allyID ] = true;
				} else { 
					$this->SovereigntyCache[ $solarSystemID.$allyID ] = false;
				}
				$result->close();
				// $this->querys ++;
			}
		}
		return $this->SovereigntyCache[ $solarSystemID.$allyID ];
	}
	
	public function towerresurce( $id ) {
		if ( !isset( $this->towerresurceCache[ $id ] ) ) {
			$sqlstring = "SELECT * FROM {$this->_table['invcontroltowerresources']} WHERE controlTowerTypeID='{$id}';";
			$this->towerresurceCache[ $id ] = $this->db->fetch_all( $sqlstring );
			// $this->querys ++;
		}
		return $this->towerresurceCache[ $id ];
	}
	
	public function moonType( $moonID ) {
		
		if ( !isset( $this->moonTypeCache[ $moonID ] ) ) {
			$str = ("SELECT md.itemID,
						md.itemName AS moonName,
						ms.solarSystemName,
						const.constellationName,
						mr.regionName
					FROM {$this->_table['mapdenormalize']} md
					LEFT JOIN {$this->_table['mapsolarsystems']} ms ON md.solarSystemID = ms.solarSystemID 
					LEFT JOIN {$this->_table['mapregions']} mr ON md.regionID = mr.regionID 
					LEFT JOIN {$this->_table['mapconstellations']} const ON md.constellationID = const.constellationID
					WHERE md.solarSystemID = (SELECT md.solarSystemID FROM {$this->_table['mapdenormalize']} md WHERE md.itemID = '{$moonID}') and md.typeID = 14;");
			$res = $this->db->query( $str );
			while ( $row = $res->fetch_assoc() ) {
				$this->moonTypeCache[ $row['itemID'] ] = $row;
			}
		}
		
		return $this->moonTypeCache[ $moonID ];
	}
	
	public function pos_get_GlobalPossen( $status, $corp, $sortby="time" ) {
	
		if($sortby=='regionName')     $this->sort="region";
		elseif($sortby=='Moon')	  	  $this->sort="moon";
		elseif($sortby=='typeID')	  $this->sort="tower";
		elseif($sortby=='manager')	  $this->sort="manager";
		
		$pos = array();
		
		$itemIDs = $this->getPosID( $status, $corp, true );
		foreach ( $itemIDs as $itemID ) {
			$thispos = new Pos( $itemID, $this );
			$pos[] = $thispos->toArray();
		}
		/*
		if($sortby=='time' && $return) {
			foreach($return as $key => $value) {
				if($value['online']) $online[] = $value['online'];
				else $online[] = 0;
			}
			array_multisort($online, SORT_ASC, $return);
		}*/
		if ( is_array( $pos ) )
			usort($pos, array($this, "sort"));
		
		return $pos;
	}
	
	public function pos_getTower( $status, $corp, $sort="regionName" ) {
		$itemIDs = $this->getPosID( $status, $corp );
		
		foreach ( $itemIDs as $id ) {
			$moon = $this->moonType( $this->PosIDsCache[ $id ]['moonID'] );
			$return[ $id ] = $moon['regionName'] . ' - ' . $moon['moonName'];
		}
		return $return;
	}
	
	public function pos_getUserAPI() {
		$corpID = $this->db->escape( $_SESSION['corpID'] );
		$str = "SELECT fullapi.*, corps.name as corpName 
				FROM {$this->_table['fsrtool_user_fullapi']} as fullapi 
				LEFT JOIN {$this->_table['fsrtool_corps']} as corps ON corps.id = fullapi.corpID
				WHERE fullapi.corpID='{$corpID}';";
		$res = $this->db->query( $str );
		$api = $res->fetch_assoc();
		$res->close();
		
		if ( $api ) {
			$return = array('UserName' 	=> $api['userName'],
							'keyID'		=> $api['keyID'],
							'CharID'	=> $api['charID'],
							'CorpID'	=> $api['corpID'],
							'CorpName'	=> $api['corpName'],
							'status'	=> $api['status'],
							'vCODE'		=> $api['vCODE'],
							'vCODEx'	=> str_pad(substr($api['vCODE'],0,6), 60 , "*"),
							'lowftime'  => $api['lowfueltime'],
						);
			return $return;
		} else {
			return false;
		}
		
	}
	
	public function pos_getApiStatus() {
		$api = $this->getAPI();
		if($api['status'] == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function pos_getcorps() {
		if ( $this->User->Admin ) { // && $this->User->charID == '285591396' ) {
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
		if ( $this->User->allyID != 0 && $this->User->PosManagerAlly ) {
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
					if ( $alt['pos'] == 1 )
						$corps[ $alt['corpID'] ] = $alt['corpName'];
				}
			}
		} else if ( $this->User->PosManager || $this->User->Admin) {
			$corps[ $this->User->corpID ] = $this->User->corpName;
			if ( $this->User->alts ) {
				foreach ( $this->User->alts as $alt ) {
					if ( $alt['pos'] == 1 )
						$corps[ $alt['corpID'] ] = $alt['corpName'];
				}
			}
		} else {
			if ( $this->User->alts ) {
				foreach( $this->User->alts as $alt ) {
					if ( $alt['pos'] == 1 )
						$corps[ $alt['corpID'] ] = $alt['corpName'];
				}
				unset( $corps[ $this->User->corpID ] );
			}
		}
		return $corps;
	}
	
	public function pos_getSolarSystems( $corpID ) {
		$corpID = $this->db->escape( $corpID );
		
		$str = "SELECT locationID FROM {$this->_table_pos} WHERE corpID = '{$corpID}' GROUP BY locationID;";
		$locationIDs = $this->db->fetch_all( $str, 'locationID' );
		if ( isset($locationIDs[0]) ) {
			$str = "SELECT solarSystemID, solarSystemName FROM {$this->_table['mapsolarsystems']} WHERE solarSystemID IN( ".implode(",", $locationIDs)." )
				GROUP BY solarSystemID
				ORDER BY solarSystemName";
			
			$res = $this->db->query( $str );
			$systems = array('All Systems');
			
			while ( $row = $res->fetch_assoc() ) {
				$systems[ $row['solarSystemID'] ] = $row['solarSystemName'];
			}
			$res->close();
			return $systems;
		} else
			return array('All Systems');
	}
	
	public function pos_getConstellations( $corpID ) {
		$corpID = $this->db->escape( $corpID );
		
		$str = "SELECT locationID FROM {$this->_table_pos} WHERE corpID = '{$corpID}' GROUP BY locationID;";
		$locationIDs = $this->db->fetch_all( $str, 'locationID' );
		if ( isset($locationIDs[0]) ) {
			$str = "SELECT cons.constellationID, cons.constellationName FROM {$this->_table['mapconstellations']} cons
				INNER JOIN {$this->_table['mapsolarsystems']} sys ON sys.constellationID = cons.constellationID
				WHERE sys.solarSystemID IN( ".implode(",", $locationIDs)." )
				GROUP BY constellationID
				ORDER BY constellationName";
			
			$res = $this->db->query( $str );
			$constellations = array('All Constellations');
			
			while ( $row = $res->fetch_assoc() ) {
				$constellations[ $row['constellationID'] ] = $row['constellationName'];
			}
			$res->close();
			return $constellations;
		} else 
			return array('All Constellations');
	}
	
	public function pos_getRegions($corpID) {
		$corpID = $this->db->escape( $corpID );
		
		$str = "SELECT locationID FROM {$this->_table_pos} WHERE corpID = '{$corpID}' GROUP BY locationID;";
		$locationIDs = $this->db->fetch_all( $str, 'locationID' );
		if ( isset($locationIDs[0]) ) {
			$str = "SELECT regions.regionID, regions.regionName FROM {$this->_table['mapregions']} regions
				INNER JOIN {$this->_table['mapsolarsystems']} sys ON sys.regionID = regions.regionID
				WHERE sys.solarSystemID IN( ".implode(",", $locationIDs)." )
				GROUP BY regionID
				ORDER BY regionName";
			
			$res = $this->db->query( $str );
			$regions = array('All Regions');
			
			while ( $row = $res->fetch_assoc() ) {
				$regions[ $row['regionID'] ] = $row['regionName'];
			}
			$res->close();
			return $regions;
		} else 
			return array('All Regions');
		
	}
	
	private function pos_getTowers($corpID,$regionID,$systemID,$constellationID,$pos_ids) {
		$corpID   = $this->db->escape($corpID);
		$regionID = $this->db->escape($regionID);
		$systemID = $this->db->escape($systemID);
		$constellationID = $this->db->escape($constellationID);
		if ( $regionID != 0 ) {
			$str = "SELECT solarSystemID FROM {$this->_table['mapsolarsystems']} WHERE regionID = {$regionID};";
			$res = $this->db->fetch_all( $str, 'solarSystemID' );
		}
		if ( $constellationID != 0 ) {
			$str = "SELECT solarSystemID FROM {$this->_table['mapsolarsystems']} WHERE constellationID = {$constellationID};";
			$res = $this->db->fetch_all( $str, 'solarSystemID' );
		}
		if ( isset($res) )	  $varpos = 'AND p.locationID IN('.implode(",", $res).')';
		if ( $systemID != 0 ) $varpos = 'AND p.locationID = '.$systemID;
		if ( $pos_ids ) 	  $varpos = 'AND p.itemID IN('.implode(",", $pos_ids).')';
		
		if(!isset($varpos)) $varpos = '';
	// 	$str = "SELECT p.itemID FROM {$this->_table_pos} p WHERE p.corpID = '{$corpID}' AND p.state = 4 {$varpos} ORDER BY p.moonID;";
		
		$str = "SELECT p.itemID
			FROM {$this->_table_pos} p 
			INNER JOIN {$this->_table['mapsolarsystems']} ms ON p.locationID = ms.solarSystemID
			WHERE p.corpID = '{$corpID}' AND p.state = 4 {$varpos}
			ORDER BY ms.regionID DESC, p.moonID;";
		
		$itemIDs = $this->db->fetch_all( $str, 'itemID' );
		
		return $itemIDs;
	}
	
	private function pos_CorpHangers($corpID) {
		$corpID = $this->db->escape( $corpID );
		$hangers = array();
		
		$query = ("SELECT case
					  when a.locationID BETWEEN 66000000 AND 66014860 then
						(SELECT s.stationName FROM {$this->_table['stastations']} AS s
						  WHERE s.stationID=a.locationID-6000001)
					  when a.locationID BETWEEN 66014861 AND 66014929 then
						(SELECT c.stationName FROM {$this->_table['fsrtool_api_outposts']} AS c
						  WHERE c.stationID=a.locationID-6000001)
					  when a.locationID BETWEEN 66014929 AND 66999999 then
						(SELECT s.stationName FROM {$this->_table['stastations']} AS s
						  WHERE s.stationID=a.locationID-6000000)
					  when a.locationID BETWEEN 67000000 AND 67999999 then
						(SELECT c.stationName FROM {$this->_table['fsrtool_api_outposts']} AS c
						  WHERE c.stationID=a.locationID-6000000)
					  when a.locationID BETWEEN 60014861 AND 60014928 then
						(SELECT c.stationName FROM {$this->_table['fsrtool_api_outposts']} AS c
						  WHERE c.stationID=a.locationID)
					  when a.locationID BETWEEN 60000000 AND 61000000 then
						(SELECT s.stationName FROM {$this->_table['stastations']} AS s
						  WHERE s.stationID=a.locationID)
					  when a.locationID>=61000000 then
						(SELECT c.stationName FROM {$this->_table['fsrtool_api_outposts']} AS c
						  WHERE c.stationID=a.locationID)
					  else (SELECT m.itemName FROM {$this->_table['mapdenormalize']} AS m
						WHERE m.itemID=a.locationID) end
					  AS location, a.locationID AS locID, a.type, a.typeID, i.typeName,
					  SUM(a.quantity) as quantity, a.flag
					FROM {$this->_table_pos_corphanger} AS a
					LEFT JOIN {$this->_table['invtypes']} as i ON a.typeID = i.typeID
					WHERE a.corpID = '$corpID'
					GROUP BY a.locationID, a.typeID, a.flag
					ORDER BY location, a.flag, a.typeID;");
		$res = $this->db->query( $query );
		while ( $row = $res->fetch_assoc() ) {
			if ($row) {
				$hangers[$row['locID']]['location'] = $row['location'] != '' ? $row['location'] : $row['locID'];
				$hangers[$row['locID']][$row['flag']][$row['typeID']] = $row['quantity'];
			}
		}
		
		/*
		$str = "SELECT a.locationID AS locID, a.type, a.typeID, SUM(a.quantity) as quantity, a.flag 
			FROM stsys_eveorder_corphanger a
			WHERE a.corpID = '{$corpID}'
			GROUP BY a.locationID, a.typeID, a.flag 
			ORDER BY a.locationID, a.flag, a.typeID";
		$res = $this->db->query( $str );
		
		while ( $row = $res->fetch_assoc() ) {
			$hangers[ $row['locID'] ]['location'] = $this->eveLocation( $row['locID'] );
			$hangers[ $row['locID'] ][ $row['flag'] ][ $row['typeID'] ] = $row['quantity'];
		}
		*/
		$res->close();
		
		return $hangers;
		
	}
	
	public function pos_setFuelFilter($corpID,$fName,$filter) {
		$corpID = $this->db->escape($corpID);
		$fName  = $this->db->escape($fName);
		
		$query = ("SELECT filter_name FROM {$this->_table_pos_filter} 
			WHERE corpID='{$corpID}' 
			AND charID='{$this->User->id}'
			AND filter_name='{$fName}';");
		$res = $this->db->query($query);
		
		if ($res->num_rows == 0) {
			$query = ("INSERT INTO {$this->_table_pos_filter}
				SET charID = '{$this->User->id}',
					corpID = '{$corpID}',
					filter_name = '{$fName}',
					filter = '{$filter}';");
			$res = $this->db->query($query);
		} else $res = false;
		
		return $res;
	}
	
	public function pos_getFuelFilter($corpID) {
		$corpID = $this->db->escape($corpID);
		
		//$query = ("SELECT filter_name, filter FROM {$this->_table_pos_filter} WHERE corpID='{$corpID}' AND charID='{$User->id}';");
		$query = ("SELECT filter_name, filter FROM {$this->_table_pos_filter} WHERE corpID='{$corpID}';");
		$result = $this->db->query($query);
		
		$filter['name'][0] = 'No Filter';
		$filter['ids'][0] = '';
		$i=1;
		while ($row = $result->fetch_assoc()) {
			if ($row) {
				$filter['name'][$i] = $row['filter_name'];
				$filter['ids'][$i] = unserialize($row['filter']);
				$i++;
			}
		}
		return $filter;
	}
	
	public function pos_delFuelFilter($corpID, $fName) {
		$corpID = $this->db->escape($corpID);
		$fName  = $this->db->escape($fName);
		
		$query = ("DELETE FROM {$this->_table_pos_filter} 
			WHERE corpID='{$corpID}' 
			/*AND charID='{$this->User->charID}'*/
			AND filter_name='{$fName}';");
		$res = $this->db->exec_query($query);
		
		return $res;
	}
	
	private function getCurrentEvecentralPrice( $region, array $ids) {
		global $parms;
		unset($parms['main']['host']);
		$ale = AleFactory::getEvECentral($parms);
		if ($region == "0")
			$params = array('typeid'=>$ids);			
		else
			$params = array('typeid'=>$ids, 'regionlimit'=>$region);	
		try {
			$xml = $ale->marketstat( $params );
			// echo '<pre>';print_r($xml);echo '</pre>'; die;
			
			$insert = "REPLACE INTO %tab_currentTypePrice% SET typeID='%typeID%', all_volume='%all_volume%', all_avg_price='%all_avg%', all_max_price='%all_max%', all_min_price='%all_min%', all_stddev_price='%all_stddev%', all_median_price='%all_median%', buy_volume='%buy_volume%', buy_avg_price='%buy_avg%', buy_max_price='%buy_max%', buy_min_price='%buy_min%', buy_stddev_price='%buy_stddev%', buy_median_price='%buy_median%', sell_volume='%sell_volume%', sell_avg_price='%sell_avg%', sell_max_price='%sell_max%', sell_min_price='%sell_min%', sell_stddev_price='%sell_stddev%', sell_median_price='%sell_median%', fetched='%fetched%',region='%region%';";
			$update = "UPDATE %tab_currentTypePrice% SET all_volume='%all_volume%', all_avg_price='%all_avg%', all_max_price='%all_max%', all_min_price='%all_min%', all_stddev_price='%all_stddev%', all_median_price='%all_median%', buy_volume='%buy_volume%', buy_avg_price='%buy_avg%', buy_max_price='%buy_max%', buy_min_price='%buy_min%', buy_stddev_price='%buy_stddev%', buy_median_price='%buy_median%', sell_volume='%sell_volume%', sell_avg_price='%sell_avg%', sell_max_price='%sell_max%', sell_min_price='%sell_min%', sell_stddev_price='%sell_stddev%', sell_median_price='%sell_median%', fetched='%fetched%' WHERE typeID='%typeID%' AND region='%region%';";
			$changed = time();
			$return = array();
			$insert = str_replace("%tab_currentTypePrice%", $this->_table['fsrtool_currentTypePrice'], $insert);
			$insert = str_replace("%fetched%", $changed, $insert);
			$insert = str_replace("%region%", $region, $insert);
			foreach ( $xml->marketstat->type as $type ) {
				$str = str_replace("%typeID%", (int)$type->attributes()->id, $insert);
				foreach ( $type as $typ => $v ) {
					foreach( $v as $key => $val ) { 
						$str = str_replace("%".$typ."_".$key."%", $val, $str);
					}
				}
				$return[(int)$type->attributes()->id] = (float)$type->buy->max;
				$this->db->exec_query( $str );
				// break;
			}
			
			return $return;
		} catch (Exception $e) {
			$this->db->msg->addwarning('eve-central.com failed...');
			return false;
		}
	}
	
	public function pos_fuelBill($args, $corpSAG = false) {
		global $database,$smarty;
		
		$daystorefuel    = $args['days_to_refuel']*24;
		$usecurlvl 	     = $args['use_current_level'] == 1 ? true : false;
		$optimal_fuel	 = $args['optimal'] == 1 ? true : false;
		$negative_fuel	 = $args['negative_fuel'] == 1 ? true : false;
		$corpID			 = $args['corpID'];
		$regionID	     = $args['regionID'];
		$systemID	     = $args['systemID'];
		$constellationID = $args['consteID'];
		$pos_ids 		 = is_array($args['pos_ids']) ? $args['pos_ids'] : false;
		
		
		$itemIDs = $this->pos_getTowers($corpID,$regionID,$systemID,$constellationID,$pos_ids);
		foreach ( $itemIDs as $itemID ) {
			$thispos = new Pos( $itemID, $this );
			$towers[] = $thispos->toArray();
		}
		
		$res = $this->db->query("SELECT typeID, buy_max_price, fetched
		FROM {$this->_table['fsrtool_currentTypePrice']}
		WHERE typeID IN (4247, 4312, 4051, 4246, 16275) 
		AND region = 10000002;");
		if ( $res->num_rows > 0 ) {
			$price = array();
			while ( $row = $res->fetch_assoc() ) {
				if ( $row['fetched'] < (time()-(60*60*24)) ) {
					$ids[] = $row['typeID'];
				} else {
					$price[$row['typeID']] = $row['buy_max_price'];
				}
			}
			if ( isset($ids) && is_array($ids) ) {
				if ($xxx = $this->getCurrentEvecentralPrice(10000002,$ids))
					$price = $price + $this->getCurrentEvecentralPrice(10000002,$ids);
				
			}
		} else {
			$price = $this->getCurrentEvecentralPrice(10000002, array(4247, 4312, 4051, 4246, 16275));
		}
		$res->close();
		
		//echo'<pre>';print_r($towers);echo'</pre>'; 
		// Clear total values		
		$fuel_blocks   = 0;
		$fuel_Amarr    = 0;
		$fuel_Caldari  = 0;
		$fuel_Gallente = 0;
		$fuel_Minmatar = 0;
		$fuel_charters = 0;
		$default_days  = 30;
		$disp_towers   = array();
		$fuel_Amarrs = $fuel_Caldaris = $fuel_Gallentes = $fuel_Minmatars = $fuel_charters = 0;
		
		if( isset($towers) && is_array($towers)) {
			foreach ($towers as $key => $tower) {
				$required_Amarr  = 0;
				$required_Caldari  = 0;
				$required_Gallente  = 0;
				$required_Minmatar = 0;
				$required_charters = 0;
				$current_Amarr   = 0;
				$current_Minmatar	 = 0;
				$current_Caldari	 = 0;
				$current_Gallente	 = 0;
				$current_charters = 0;
				

				$system                   = $tower['region'];
				$needed_blocks            = $tower['resource']['Blocks'];
				$needed_charters          = $tower['charters_need']?1:0;
				$needed_stront            = $tower['resource']['StrontiumClathrates'];
				//current in tower
				$current_blocks          = $tower['towerfuel']['Blocks'];
				$current_charters        = $tower['towerfuel']['Charters'];
				$current_strontium       = $tower['towerfuel']['Stront'];
				
				$pos_capacity             = $tower['towercar'];
				$pos_id                   = $tower['posID'];
				$race_iso                 = $tower['raseBlocks'];
				$locationName             = $tower['moon'];
				
				//Calculate Optimal cycles
				$volume_per_cycle  = 0;
				$volume_per_cycle += ($needed_blocks * 5);
				$volume_per_cycle += ($needed_charters * 0.1);
				$optimum_cycles    = floor($pos_capacity/$volume_per_cycle);
				
				if ($optimal_fuel) $daystorefuel = $optimum_cycles;
				
				switch($race_iso) {
					case 'Amarr Blocks':    $required_Amarr  = $tower['resource']['Blocks']; break;				
					case 'Minmatar Blocks':  $required_Minmatar = $tower['resource']['Blocks']; break;
					case 'Caldari Blocks':  $required_Caldari  = $tower['resource']['Blocks']; break;
					case 'Gallente Blocks':    $required_Gallente  = $tower['resource']['Blocks']; break;
				}		
				$fuel_Amarrs    = $fuel_Amarrs    + ($required_Amarr * $daystorefuel);
				$fuel_Caldaris  = $fuel_Caldaris  + ($required_Caldari * $daystorefuel);
				$fuel_Gallentes = $fuel_Gallentes + ($required_Gallente * $daystorefuel);
				$fuel_Minmatars = $fuel_Minmatars + ($required_Minmatar * $daystorefuel);
				$fuel_charters  = $fuel_charters  + ($needed_charters * $daystorefuel);
				
				
				if ($usecurlvl) {
					switch($race_iso) {
						case 'Amarr Blocks':    $current_Amarr  = $current_blocks; break;				
						case 'Minmatar Blocks':  $current_Minmatar = $current_blocks; break;
						case 'Caldari Blocks':  $current_Caldari  = $current_blocks; break;
						case 'Gallente Blocks':    $current_Gallente  = $current_blocks; break;
					}
					
					$fuel_Amarrs    = $fuel_Amarrs    - $current_Amarr;
					$fuel_Caldaris  = $fuel_Caldaris  - $current_Caldari;
					$fuel_Gallentes = $fuel_Gallentes - $current_Gallente;
					$fuel_Minmatars = $fuel_Minmatars - $current_Minmatar;
					$fuel_charters  = $fuel_charters  - $current_charters;
				}
				if (!$usecurlvl) {
					$tower['required_Amarr']  		= $required_Amarr >= 0 ? ($required_Amarr  * $daystorefuel) : 0;
					$tower['required_Minmatar'] 		= $required_Minmatar>= 0 ? ($required_Minmatar  * $daystorefuel) : 0;
					$tower['required_Caldari']  		= $required_Caldari >= 0 ? ($required_Caldari  * $daystorefuel) : 0;
					$tower['required_Gallente']  		= $required_Gallente >= 0 ? ($required_Gallente * $daystorefuel) : 0;
					$tower['required_charters']            = ($needed_charters 			* $daystorefuel);
				} else {
					$tower['required_Amarr']  		= $required_Amarr >= 0 ? ($required_Amarr  * $daystorefuel) - $current_Amarr : 0;
					$tower['required_Minmatar'] 		= $required_Minmatar>= 0 ? ($required_Minmatar  * $daystorefuel) - $current_Minmatar : 0;
					$tower['required_Caldari']  		= $required_Caldari >= 0 ? ($required_Caldari  * $daystorefuel) - $current_Caldari : 0;
					$tower['required_Gallente']  		= $required_Gallente >= 0 ? ($required_Gallente * $daystorefuel) - $current_Gallente : 0;
					$tower['required_charters']            = ($needed_charters 			* $daystorefuel) - $current_charters;
				}
				$disp_towers[$key] = $tower;
			}
		}
		
		$corpHangers = $this->pos_CorpHangers($corpID);
		$smarty->assign('CorpHangers', $corpHangers);
		$selCorpSAG=array();
		if ($corpSAG) {
			foreach ($corpSAG as $key => $val) {
				$flags = array_keys($val);
				foreach ($flags as $flag) {
					$fuel_Amarrs        = $fuel_Amarrs        - $corpHangers[$key][$flag][4247];
					$fuel_Caldaris        = $fuel_Caldaris        - $corpHangers[$key][$flag][4051];
					$fuel_Gallentes        = $fuel_Gallentes        - $corpHangers[$key][$flag][4312];
					$fuel_Minmatars       = $fuel_Minmatars       - $corpHangers[$key][$flag][4246];
					$fuel_charters             = $fuel_charters;//              - $corpHangers[$key][$flag][16273];
					
					$selCorpSAG[$key][$flag] = $flag; 
				}
			}
		}
		if( !$negative_fuel ) {
			$fuel_Amarrs        = $fuel_Amarrs        >= 0 ? $fuel_Amarrs 		: 0;
			$fuel_Caldaris        = $fuel_Caldaris        >= 0 ? $fuel_Caldaris 		: 0;
			$fuel_Gallentes        = $fuel_Gallentes 		  >= 0 ? $fuel_Gallentes 		: 0;
			$fuel_Minmatars       = $fuel_Minmatars       >= 0 ? $fuel_Minmatars 		: 0;
			$fuel_charters             = $fuel_charters             >= 0 ? $fuel_charters 			: 0;
		}
		(integer) $fuel_Amarrs_size       = round($fuel_Amarrs        * 5);
		(integer) $fuel_Caldaris_size       = round($fuel_Caldaris        * 5);
		(integer) $fuel_Gallentes_size       = round($fuel_Gallentes        * 5);
		(integer) $fuel_Minmatars_size      = round($fuel_Minmatars       * 5);
		(integer) $fuel_charters_size            = round($fuel_charters             * 0.1);
		//(integer) $fuel_strontium_size        = round($current_strontium * 3) ;
		$total_size = $fuel_Amarrs_size + $fuel_Caldaris_size + $fuel_Gallentes_size + $fuel_Minmatars_size + $fuel_charters_size;

		$smarty->assign('selCorpSAG', $selCorpSAG);
		
		$smarty->assign('fuel_Amarr_size',        $fuel_Amarrs_size);
		$smarty->assign('fuel_Caldari_size',        $fuel_Caldaris_size);
		$smarty->assign('fuel_Gallente_size',        $fuel_Gallentes_size);
		$smarty->assign('fuel_Caldari_size',        $fuel_Caldaris_size);
		$smarty->assign('fuel_Minmatar_size',       $fuel_Minmatars_size);
		$smarty->assign('fuel_charters_size',             $fuel_charters_size);
		
		$smarty->assign('fuel_Amarr',             $fuel_Amarrs);
		$smarty->assign('fuel_Caldari',             $fuel_Caldaris);
		$smarty->assign('fuel_Gallente',             $fuel_Gallentes);
		$smarty->assign('fuel_Minmatar',            $fuel_Minmatars);
		$smarty->assign('fuel_charters',                  $fuel_charters);
		$smarty->assign('total_size',                  $total_size);
		$smarty->assign('towers',                      $disp_towers);
		
		$smarty->assign('price_Amarr',            $fuel_Amarrs  * $price['4247']);
		$smarty->assign('price_Caldari',            $fuel_Caldaris  * $price['4051']);
		$smarty->assign('price_Gallente',            $fuel_Gallentes  * $price['4312']);
		$smarty->assign('price_Minmatar',           $fuel_Minmatars * $price['4246']);
		$smarty->assign('price_charters',                 $fuel_charters * 0);
		$price_total =  array_sum(array(
							($fuel_Amarrs    * $price['4247']),
							($fuel_Caldaris  * $price['4051']),
							($fuel_Gallentes * $price['4312']),
							($fuel_Minmatars * $price['4246']),
							($fuel_charters  * 0),
						));
		$smarty->assign('price_total', $price_total);
	
	}

	/*** Aus DB class ***/
	
	private function getPosID( $status, $corp=false, $global=false ) {
		// global $User;
		if (!$corp) $corp = $this->User->corpID;
		if ( $global ) {
			$str = "SELECT p.*, c.ally AS allyID 
				FROM {$this->_table_pos} AS p
				INNER JOIN {$this->_table['fsrtool_corps']} c ON c.id = p.corpID 
				WHERE (p.state = '{$status}' AND p.corpID = '{$corp}' AND p.global = 1) OR (p.state = 3 AND p.corpID = '{$corp}' AND p.global = 1)
				ORDER BY p.moonID;";
		} else {
			$str = "SELECT p.*, c.ally AS allyID 
				FROM {$this->_table_pos} AS p
				INNER JOIN {$this->_table['fsrtool_corps']} c ON c.id = p.corpID 
				WHERE (p.state = '{$status}' AND p.corpID = '{$corp}') OR (p.state = 3 AND p.corpID = '{$corp}')
				ORDER BY p.moonID;";
		}
		$res = $this->db->query( $str );
		while ( $row = $res->fetch_assoc() ) {
			$this->PosIDsCache[ $row['itemID'] ] = $row;
		}
		$itemIDs = $this->db->fetch_all( $str, 'itemID');
		
		$fuel = "SELECT fuel.*, invt.typeName, invt.volume
			FROM {$this->_table_pos_fuel} fuel 
			INNER JOIN {$this->_table_pos} tower ON fuel.itemID = tower.itemID
			LEFT JOIN {$this->_table['invtypes']} invt ON fuel.typeID = invt.typeID
			WHERE tower.corpID = '{$corp}';";
		$resfuel = $this->db->query( $fuel );
		while ( $row = $resfuel->fetch_assoc() ) {
			$this->PosFuelCache[ $row['itemID'] ]['fuel'][ $row['typeID'] ] = $row['quantity'];
			$this->PosFuelCache[ $row['itemID'] ]['Fname'][ $row['typeID'] ] = $row['typeName'];
			$this->PosFuelCache[ $row['itemID'] ]['Fvol'][ $row['typeID'] ] = $row['volume'];
		}
		// echo '<pre>'; print_r($this->PosFuelCache); die;
		return $itemIDs;
	}
	
	private function getAPI() {
		$corpID = $this->db->escape($_SESSION['corpID']);
		$sqlstring = "SELECT fullapi.*, corps.name as corpName 
					  FROM {$this->_table['fsrtool_user_fullapi']} as fullapi 
					  INNER JOIN {$this->_table['fsrtool_corps']} as corps ON corps.id = fullapi.corpID
					  WHERE fullapi.corpID='".$corpID."';";
		$result = $this->db->query($sqlstring);
		$row = $result->fetch_assoc();
		
		return $row;
	}
	
	public function getPosByID( $id ) {
		$id = $this->db->escape( $id );
		
		if ( !isset( $this->PosIDsCache[ $id ] ) ) {
			$str = "SELECT p.*, c.ally AS allyID 
				FROM {$this->_table_pos} p 
				INNER JOIN {$this->_table['fsrtool_corps']} c ON c.id = p.corpID 
				WHERE p.itemID = '{$id}';";
			$this->PosIDsCache[ $id ] = $this->db->fetch_one( $str );
		}
		$res1 = $this->PosIDsCache[ $id ];
		
		if ( !isset( $this->PosFuelCache[ $id ] ) ) {
			$fuel = "SELECT fuel.*, invt.typeName, invt.volume
				FROM {$this->_table_pos_fuel} fuel 
				INNER JOIN {$this->_table_pos} tower ON fuel.itemID = tower.itemID
				LEFT JOIN {$this->_table['invtypes']} invt ON fuel.typeID = invt.typeID
				WHERE tower.itemID = '{$id}';";
			$resfuel = $this->db->query( $fuel );
			while ( $row = $resfuel->fetch_assoc() ) {
				$this->PosFuelCache[ $row['itemID'] ]['fuel'][ $row['typeID'] ] = $row['quantity'];
				$this->PosFuelCache[ $row['itemID'] ]['Fname'][ $row['typeID'] ] = $row['typeName'];
				$this->PosFuelCache[ $row['itemID'] ]['Fvol'][ $row['typeID'] ] = $row['volume'];
			}
		}
		
		if ( !isset( $this->towerTypeCache[ $res1['typeID'] ] ) ) {
			$str3 = ("SELECT
					  it.typeName, 
					  it.capacity AS towercapacity,
					  cargo_tab.valueFloat AS towerstrontbay, 
					  cpu_tab.valueInt AS towercpu, 
					  pg_tab.valueInt AS towerpg
					FROM {$this->_table['invtypes']} it  
					LEFT JOIN {$this->_table['dgmtypeattributes']} cargo_tab ON it.typeID = cargo_tab.typeID
					LEFT JOIN {$this->_table['dgmtypeattributes']} cpu_tab ON cargo_tab.typeID = cpu_tab.typeID
					LEFT JOIN {$this->_table['dgmtypeattributes']} pg_tab ON cargo_tab.typeID = pg_tab.typeID
					WHERE
					  cargo_tab.attributeID = 1233 AND
					  cpu_tab.attributeID = 48 AND
					  pg_tab.attributeID = 11 AND
					  it.typeID = '{$res1['typeID']}';");
			$this->towerTypeCache[ $res1['typeID'] ] = $this->db->fetch_one( $str3 );
			// $this->querys ++;
		}
		$res2 = $this->moonType( $res1['moonID'] );
		
		$res3 = $this->towerTypeCache[ $res1['typeID'] ];
		
		$res4 = $this->PosFuelCache[ $id ];
		if (!is_array($res4)) $res4 = array();
		
		return array_merge( $res2, $res3, $res1, $res4 );
	}
	
	public function get_logs() {
		$rows = array();
		$corpID = $this->db->escape( $_SESSION['corpID'] );
		$sqlstring = "SELECT * FROM {$this->_table['fsrtool_log']} WHERE (corpID='".$corpID."' OR corpID=0) AND typ=1 ORDER BY date DESC LIMIT 3;";
		$result = $this->db->query($sqlstring);
		while ($row = $result->fetch_assoc()) {
			if($row) $rows[] = $row;
		}
		$result->close();
		
		$sqlstring = "SELECT * FROM {$this->_table['fsrtool_log']} WHERE (corpID='".$corpID."' OR corpID=0) AND typ=2 ORDER BY date DESC LIMIT 10;";
		$result = $this->db->query($sqlstring);
		while ($row = $result->fetch_assoc()) {
			if($row) $rows[] = $row;
		}
		$result->close();
		
		$sqlstring = "SELECT * FROM {$this->_table['fsrtool_log']} WHERE (corpID='".$corpID."' OR corpID=0) AND typ=3 ORDER BY date DESC LIMIT 3;";
		$result = $this->db->query($sqlstring);
		while ($row = $result->fetch_assoc()) {
			if($row) $rows[] = $row;
		}
		$result->close();
		return $rows;
	}
	
	private function eveLocation( $locationID ) {
		$ID = (string)$locationID;
		// $locidsuche = 60014944;
		if ( !isset( $this->locationCache[ $ID ] ) ) {
			$locationID = (int)$locationID;
			if ( ($locationID >= 66000000) && ($locationID <= 66014860) ) {
				$locationID -= 6000001;
				// if ( $locationID == $locidsuche ) die('1');
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['stastations']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( ($locationID >= 66014861) && ($locationID <= 66014929) ) {
				$locationID -= 6000001;
				// if ( $locationID == $locidsuche ) die('2');
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['fsrtool_api_outposts']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			} 
			else if ( ($locationID >= 66014929) && ($locationID <= 66999999) ) {
				$locationID -= 6000001;
				// if ( $locationID == $locidsuche ) die('3');
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['stastations']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( ($locationID >= 67000000) && ($locationID <= 67999999) ) {
				$locationID -= 6000000;
				// if ( $locationID == $locidsuche ) die('4');
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['fsrtool_api_outposts']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( ($locationID >= 60014861) && ($locationID <= 60014928) ) {
				// if ( $locationID == $locidsuche ) die('5');
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['fsrtool_api_outposts']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( ($locationID >= 60000000) && ($locationID <= 61000000) ) {
				// if ( $locationID == $locidsuche ) die('6');
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['stastations']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( $locationID >= 61000000 ) {
				// if ( $locationID == $locidsuche ) die('7');
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['fsrtool_api_outposts']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else {
				// if ( $locationID == $locidsuche ) die('8');
				$locationID = (string)$locationID; 
				$str = "SELECT itemName FROM {$this->_table['mapdenormalize']} WHERE itemID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'itemName' );
			}
			// if ( $locationID == $locidsuche ) die('9');
			$this->locationCache[ $ID ] = $locName != '' ? $locName : $locationID;
			// $this->querys ++;
		}
		
		return $this->locationCache[ $ID ];
		
	}
	
	public function pos_update_tower( $pos ) {
		$id      = $this->db->escape($pos['id']);
		$manager = $this->db->escape($pos['manager']);
		$cpu     = $this->db->escape($pos['cpu']);
		$pg      = $this->db->escape($pos['pg']);
		$sma     = $pos['sma'] ? 1 : 0;
		$cha     = $pos['cha'] ? 1 : 0;
		$jb      = $pos['jb'] ? 1 : 0;
		$cj      = $pos['cj'] ? 1 : 0;
		$global  = $pos['global'] ? 1 : 0;
									  
		$sqlstring = "UPDATE {$this->_table_pos} SET 
						manager = '".$manager."',
						cpu     = '".$cpu."', 
						pg      = '".$pg."', 
						sma     = '".$sma."',
						cha     = '".$cha."',
						jb      = '".$jb."',
						cj      = '".$cj."',
						global  = '".$global."'
					WHERE itemID = '".$id."';";
		$result = $this->db->exec_query( $sqlstring );
		return $result;
	}
	
	public function saveApi(array $data) {
		$data = $this->db->escape($data);
		
		$res = $this->db->query("SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE charID={$data['obj']['charid']};");
		if ( $res->num_rows > 0 ) {
			$str = "UPDATE {$this->_table['fsrtool_user_fullapi']} SET 
							userName   = '{$data['obj']['result']['key']['characters'][$data['obj']['charid']]['characterName']}',
							keyID 	   = {$data['obj']['keyid']},
							vCODE	   = '{$data['obj']['vcode']}',
							corpID	   = {$data['obj']['result']['key']['characters'][$data['obj']['charid']]['corporationID']},
							allyID 	   = {$data['obj']['allyid']},
							accessMask = {$data['obj']['result']['key']['accessMask']},
							status     = 1,
							errorcount = 0 WHERE charID = {$data['obj']['charid']};";
		} else {
			$str = "INSERT INTO {$this->_table['fsrtool_user_fullapi']} (charID,userName,keyID,vCODE,corpID,allyID,accessMask,status) 
			VALUES ({$data['obj']['charid']},
					'{$data['obj']['result']['key']['characters'][$data['obj']['charid']]['characterName']}',
					{$data['obj']['keyid']},
					'{$data['obj']['vcode']}',
					{$data['obj']['result']['key']['characters'][$data['obj']['charid']]['corporationID']},
					{$data['obj']['allyid']},
					{$data['obj']['result']['key']['accessMask']},1)";
		}
		$this->db->exec_query( $str );
		
		return array();
	}
	
	public function saveLowFuelTime(array $data) {
		$data = $this->db->escape($data);
		
		$str = "UPDATE {$this->_table_fullApi} SET lowfueltime = '{$data['time']}' WHERE charID = {$data['charid']}";
		$res = $this->db->exec_query( $str );
		
		return $res;
	}
	
	public function savePosMailList(array $data) {
		$data = $this->db->escape($data);
		
		$str = "INSERT INTO {$this->_table_pos_maillist} (corpID, email) VALUES ({$data['corpid']}, '{$data['email']}') ON DUPLICATE KEY UPDATE email = VALUES(email)";
		$res = $this->db->exec_query( $str );
		
		return $res;
	}
	
	public function delPosMailList(array $data) {
		$data = $this->db->escape($data);
		
		$str = "DELETE FROM {$this->_table_pos_maillist} WHERE corpID = {$data['corpid']} AND email = '{$data['email']}'";
		$res = $this->db->exec_query( $str );
		
		return $res;
	}
	
	public function getPosEmails() {
		$res = $this->db->query("SELECT * FROM {$this->_table_pos_maillist} WHERE corpID = {$_SESSION['corpID']}");
		
		$mails = array();
		
		while( $row = $res->fetch_assoc() ) {
			$mails[] = $row;
		}
		
		return $mails;
	}
	
	public function delAllStuff(array $data) {
		$data = $this->db->escape($data);
		
		$str = array();
		
		$res = $this->db->fetch_one("SELECT group_concat(itemID) as ids FROM {$this->_table_pos} WHERE corpID = {$data['corpid']}");
		
		$str[] = "DELETE FROM {$this->_table_fullApi} WHERE corpID = {$data['corpid']}";
		$str[] = "DELETE FROM {$this->_table_pos} WHERE corpID = {$data['corpid']}";
		$str[] = "DELETE FROM {$this->_table_pos_corphanger} WHERE corpID = {$data['corpid']}";
		$str[] = "DELETE FROM {$this->_table_pos_filter} WHERE corpID = {$data['corpid']}";
		$str[] = "DELETE FROM {$this->_table_pos_fuel} WHERE itemID IN ({$res['ids']})";
		$str[] = "DELETE FROM {$this->_table_pos_maillist} WHERE corpID = {$data['corpid']}";
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
	
	public function pos_setUserApi($charID,$userName,$userID,$userAPI,$corpid,$allyid,$accessMask) {
		$charID   = $this->db->escape($charID);
		$userName = $this->db->escape($userName);
		$userID   = $this->db->escape($userID);
		$userAPI  = $this->db->escape($userAPI);
		$corpid   = $this->db->escape($corpid);
		$allyid   = $this->db->escape($allyid);
		$accessMask = $this->db->escape($accessMask);
		
		// $sqlstring = "DELETE FROM {$this->_table['fsrtool_user_fullapi']} WHERE corpID='".$corpid."';";
		// $this->doQuery($sqlstring,"Database::pos_setUserApi");
		
		$str = "SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE charID='".$charID."';";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$sqlstring = "UPDATE {$this->_table['fsrtool_user_fullapi']} SET 
							userName   = '".$userName."',
							keyID 	   = '".$userID."',
							vCODE	   = '".$userAPI."',
							corpID	   = '".$corpid."',
							allyID 	   = '".$allyid."',
							accessMask = '".$accessMask."',
							status     = 1 WHERE charID = '".$charID."';";
		} 
		else {
			$this->db->exec_query("DELETE FROM {$this->_table['fsrtool_user_fullapi']} WHERE corpID='".$corpid."';");
			$sqlstring = "INSERT INTO {$this->_table['fsrtool_user_fullapi']} SET 
							charID 	   = '".$charID."',
							userName   = '".$userName."',
							keyID 	   = '".$userID."',
							vCODE	   = '".$userAPI."',
							corpID	   = '".$corpid."',
							allyID 	   = '".$allyid."',
							accessMask = '".$accessMask."',
							status     = 1;";
		}
		// echo $sqlstring;
		$res->close();
		$update = $this->db->exec_query( $sqlstring );

		return $update;
	}
}

?>