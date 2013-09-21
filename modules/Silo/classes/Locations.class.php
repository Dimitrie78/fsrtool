<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class Locations {
	
	private $_corpID = null;
	private $_db = null;
	private $_table = array();
	private $_tableX = null;
	
	private $_table_pos 	= 'fsrtool_pos';
	private $_table_silos 	= 'fsrtool_silos';
	private $_table_silos_reactors = 'fsrtool_silos_reactors';
	
	public function __construct($corpID, World $world) {
		$this->_corpID = $corpID;
		$this->_db = $world->db;
		$this->_table = $world->_table;
		// echo '<pre>'; print_r($this); die;
	}
	
	public function assignLocations() {
		$this->_tableX = $this->_table_pos;
		if ($this->doLocations()) $this->_db->msg->addconfirm( 'Towers assigned' );
		
		$this->_tableX = $this->_table_silos;
		if ($this->doLocations(true)) $this->_db->msg->addconfirm( 'Silo assigned' );
		
		$this->_tableX = $this->_table_silos_reactors;
		if ($this->doLocations(true)) $this->_db->msg->addconfirm( 'Reactors assigned' );
		
		//$this->assignReactions();
	}
	
	private function doLocations($log=false) {
		global $parms;
		$ale = AleFactory::getEVEOnline($parms);
		if ($api = $this->getAPI()) {
			//get ALE object
			try {
				//set api key
				$ale->setKey($api['keyID'], $api['vCODE'], $api['charID']);
				//all errors are handled by exceptions
				//let's check the key first.
				$keyinfo = $ale->account->APIKeyInfo();
				if( !(intval($keyinfo->result->key->accessMask) & 16777216) ) {
					$this->_db->msg->addwarning('You need a Key with access to Locations');
					return false;
				}
				$str = "SELECT * FROM {$this->_tableX} WHERE corpID='{$this->_corpID}'";

				if ($res = $this->_db->query( $str )) {
					while ($row = $res->fetch_assoc()) {
						$ids[] = $row['itemID'];
						$item[$row['itemID']] = $row['locationID'];
					}
				}

				$parmsID = array('ids' => implode(',', $ids)); 
				$Locations = $ale->corp->Locations($parmsID);
				// print_it($Locations->asXML());
				
				foreach($Locations->result->locations as $loc) {
					$str = "UPDATE {$this->_tableX} SET 
						itemName = '".$this->_db->escape((string)$loc->itemName)."', 
						x = '".(string)$loc->x."', 
						y = '".(string)$loc->y."', 
						z = '".(string)$loc->z."' 
						WHERE itemID = '".(string)$loc->itemID."'";
					if (!$this->_db->query($str)) { break; }
				}
				
			} catch (Exception $e) {
				$this->_db->msg->addwarning( $e->getMessage() );
				return false;
			}
			//if($log === true) $this->search_loc($ids);
			if($log === true) $this->search_loc_new();
		}
		return true;
	}
	
	private function search_loc_new() {
        $res = $this->_db->query("SELECT itemID, locationID, x, y, z, moonID FROM ".$this->_table_pos." WHERE corpID = ".$this->_corpID."");
        $res_items =  $this->_db->query("SELECT itemID, locationID, x, y, z FROM ".$this->_tableX." WHERE corpID = ".$this->_corpID." AND pos IS NULL");
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
                    $this->_db->query("UPDATE ".$this->_tableX." SET pos = ".$pos['moonID']." WHERE itemID = ".$item['itemID']."");
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
	
	private function search_loc($ids, $leng=7) {
		if( ! is_array($ids) || !isset($ids[0]) ) return false;
		
		$ids = implode(',', $ids);
		// echo $ids.'<br>';
		if ($res = $this->_db->query("SELECT itemID, moonID, (x+y+z) as s FROM {$this->_table_pos} WHERE corpID='{$this->_corpID}'")) {
			while ($row = $res->fetch_assoc()) {
				$s = number_format( $row['s'],0,'','' );
				if (substr($s, 0, 1) == '-') $s = '-'.substr( substr( $s,1 ),0,$leng );
				else $s = substr( $s,0,$leng );
				$pos[$row['itemID']]['moonID'] = $row['moonID'];
				$pos[$row['itemID']]['s'] = $s;
			}
		}
		if ($res = $this->_db->query("SELECT itemID, locationID, (x+y+z) as s FROM {$this->_tableX} WHERE itemID IN($ids) AND pos IS NULL")) {
			unset($ids);
			$x=0;
			while ($row = $res->fetch_assoc()) {
				$s = number_format( $row['s'],0,'','' );
				if (substr($s, 0, 1) == '-') $s = '-'.substr( substr( $s,1 ),0,$leng );
				else $s = substr( $s,0,$leng );
				$ids[$x] = $row['itemID'];
				foreach($pos as $key => $val) {
					if($val['s'] == $s) {
						$this->_db->query("UPDATE ".$this->_tableX." SET pos = {$val['moonID']} WHERE itemID = {$row['itemID']}");
						unset($ids[$x]);
						// echo $key.' - '.$s.'<br>';
						break;
					}
				}
				$x++;
			}
		}
		sort($ids);
		if( !isset($ids[0]) || (isset($ids) && $leng == 4) ) {
			unset($ids);
			return false;
		} else {
			$leng--;
			$this->search_loc($ids, $leng);
		}
		return true;
	}
	
	private function getAPI() {
		$str = "SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE corpID='{$this->_corpID}';";
		$res = $this->_db->query( $str );
		$api = $res->fetch_assoc();
		$res->close();
		
		if ( $api ) {
			return $api;
		}
		return false;
	}
	
	private function assignReactions() {
		$complex = array(16670,16671,16672,16673,16678,16679,16680,16681,16682,16683,17317);
			
		$res = $this->_db->query("SELECT * FROM {$this->_table_silos_reactors} WHERE corpID = {$this->_corpID} ORDER BY typeID ASC");
		while( $row = $res->fetch_assoc() ) {
			$react = $this->_db->query("SELECT r.typeID, r.input, r.quantity * IFNULL(IFNULL(da.valueInt, da.valueFloat), 1) as qty
				FROM {$this->_table['invtypereactions']} r 
				LEFT JOIN {$this->_table['dgmtypeattributes']} da ON r.typeID = da.typeID AND da.attributeID = 726
				WHERE r.reactionTypeID = {$row['typeIDx']};");
			while( $rea = $react->fetch_assoc() ) {
				$r[$row['pos']][$row['typeID']][$rea['typeID']]['input'] = $rea['input'];
				$r[$row['pos']][$row['typeID']][$rea['typeID']]['qty'] = $rea['qty'];
			}
		}
		
		foreach( $r as $pos => $reaction ) {
			// $silo = $this->_db->fetch_all("SELECT * FROM {$this->_table_silos} WHERE pos = {$pos}");
			foreach( $reaction as $rea => $typ ) {
				foreach( $typ as $key => $val ) {
					if($rea == 20175 && isset($reaction[16869][$key])) {
						$this->_db->query("UPDATE {$this->_table_silos} SET input = 1, turn = 0 WHERE pos = {$pos} AND typeID = {$key};");
					}else{
						if(!in_array($key,$complex)) {
							if($val['input'] == 1) $this->_db->query("UPDATE {$this->_table_silos} SET input = {$val['input']}, turn = 1 WHERE pos = {$pos} AND typeID = {$key};");
							else $this->_db->query("UPDATE {$this->_table_silos} SET input = {$val['input']}, turn = 0 WHERE pos = {$pos} AND typeID = {$key};");
						}
					}
				}
				
			}
		}
	}

}

?>