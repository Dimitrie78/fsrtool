<?php
defined('FSR_BASE') or die('Restricted access');

class Silos {
	
	private $now;
	private $cacheTime;
	private $towerCache;
	private $towerCacheCalc;
	public $towerCacheCalcNEW;
	public $assetTowerCache;
	public $towerCacheAgo;
	public $untouchtAssets = array();
	public $suspect = false;
	private $y;
	private $corpID;
	private $atime = 24; // Stunden
	public  $typ = 1;
	private $thisCacheTime;
	private $siloA = false;
	private $siloB = 0;
	private $siloC = false;
	private $siloD = false;
	
	private $imgCache = array();
	private $simpleIDs = array();
	
	private $_table = array();
	private $_table_pos 			= 'fsrtool_pos';
	private $_table_towerFuel 		= 'fsrtool_pos_fuel';
	private $_table_silos 			= 'fsrtool_silos';
	private $_table_cachetimes 		= 'fsrtool_silos_cachetimes';
	
	public function __construct($corpID, World $world) {
		date_default_timezone_set('UTC');
		$this->corpID = $corpID;
		$this->db 	 = $world->db;
		$this->world = $world;
		$this->_table = $world->_table;
		$cacheTime  = $this->getCacheTime($this->typ);
		$this->y	= date('G', $cacheTime) * 60;
		$cacheTime  = date('Y-m-d H:i:s', $cacheTime);//.':00:00';
		$this->assetCacheTime = $cacheTime;
		$cacheTime  = strtotime($cacheTime);
		$this->now        = (time() / 3600);
		$this->cacheTime  = $cacheTime;
		$this->towerCache = $this->getTowerCycleTimes();
		$this->simpleIDs  = $this->getSimpleReactions();
		$this->assets     = $this->setAssets();
		// echo '<pre>'; print_r($this); die;
	}
	
	private function getSimpleReactions() {
		$simpleIDs = array();
		$str = "SELECT typeID FROM {$this->_table['invtypes']}
			WHERE groupID = 428 
			AND NOT typeName LIKE 'Unrefined%' 
			AND published = 1";
		$res = $this->db->query($str);
		while ($row = $res->fetch_assoc()) {
			if ($row) { 
				$simpleIDs[] = $row['typeID'];						
			}
		}
		// echo '<pre>'; print_r($assets); die;
		$res->close();
		
		return $simpleIDs;
	}
	
	public function getMinTimeLeft() {
		$ret = array();
		if($this->assets) {
			$typ = false;
			$new = false;
			$oldloc = false;
			$numItems = count($this->assets);
			$i = 0;
			foreach($this->assets as $key => $val) {
				$x = $val['typeID'].$val['pos'].$val['stack'];

				if($val['stack'] == 1 && $x != $typ) {
					if($new !== false && $val['locationID'] != $oldloc) {
						$loc[$oldloc][] = $new;
						$new = $val['atime'];
						$oldloc = $val['locationID'];
					} else {
						$new = $val['atime'];#
						$oldloc = $val['locationID'];
					}
				}
				elseif($val['stack'] == 1 && $x == $typ) {
					$new += $val['atime'];#
					$oldloc = $val['locationID'];
				} 
				elseif($val['stack'] == 0 && $new) {
					$loc[$val['locationID']][] = $val['atime'];
					$loc[$oldloc][] = $new;
					$new = false;
					$oldloc = false;
				}
				else {
					$loc[$val['locationID']][] = $val['atime'];
				}
				if(++$i === $numItems && $new) {
					$loc[$oldloc][] = $new;
				}
				$typ = $val['typeID'].$val['pos'].$val['stack'];
			}
			//echo '<pre>';print_r($loc); echo '</pre>';
			
			foreach($loc as $id => $new) {
				$ret[$this->world->moonIDtoName($id)]['days'] = $this->getRemaining(min($new));
				$ret[$this->world->moonIDtoName($id)]['time'] = $this->getRemainingTime(min($new));
				$ret[$this->world->moonIDtoName($id)]['event'] = $this->getCalendarEventTime(min($new));
			}
		}
		
		return $ret;
	}
	
	public function StarbaseFuel() {
		
		$offset = date_offset_get(date_create());
		$now = (time()-$offset)/* /3600 */;
		
		$res = $this->db->query("SELECT * FROM {$this->_table['invcontroltowerresources']}");
		while($row = $res->fetch_assoc()) {
			$towerRes[$row['controlTowerTypeID']][$row['resourceTypeID']] = $row['quantity'];
		}
		// print_it($towerRes);
		
		
		$lowFuel = array();
		
		$corpID = $this->corpID;
		$allyIDres = $this->db->query("SELECT ally FROM {$this->_table['fsrtool_corps']} WHERE id = {$corpID}");
		if($allyIDres->num_rows >= 1) {
			$res = $allyIDres->fetch_assoc();
			$allyID = $res['ally'];
		} else $allyID = 0;
		
		$sovres = $this->db->query("SELECT solarSystemID FROM {$this->_table['fsrtool_api_sovereignty']} WHERE allianceID = '{$allyID}'");
		if($sovres->num_rows >= 1) {
			while($sovrow = $sovres->fetch_assoc()){
				$sov[$row['solarSystemID']] = true; 
			}
		} else $sov = array();
		
		$res = $this->db->query("SELECT p.itemID, p.locationID, p.state, p.typeID as towerTypeID, p.moonID, p.stateTimestamp, f.typeID as fuelTypeID, f.quantity
			FROM {$this->_table_pos} as p
			INNER JOIN {$this->_table_towerFuel} as f ON p.itemID = f.itemID
			WHERE p.corpID = {$corpID} ORDER BY p.itemID, f.quantity DESC;");
		while($row = $res->fetch_assoc()) {
			if($row['fuelTypeID'] != 16275 && $row['state'] == 4) {
				if(isset($sov[$row['locationID']]))
					$row['quantity'] = $row['quantity'] * 0.75;
				if($towerRes[$row['towerTypeID']][$row['fuelTypeID']] >= 0) {
					$hoursago = ceil($now-(strtotime($row['stateTimestamp'])/* /3600 */));
					$time = ($row['quantity'] / $towerRes[$row['towerTypeID']][$row['fuelTypeID']])*3600 - $hoursago;
					
					$lowFuel[$row['moonID']] = $this->getCalendarEventTime($time);
					
				}
			}
		}
		$text = array();
		if(count($lowFuel) >= 1) {
			foreach($lowFuel as $moonID => $time) {
				$text[$this->world->moonIDtoName($moonID)." - low on Fuel"] = $time;
			}
		}
		
		return $text;
	}
	
	public function makeMenue($corpID) { // aus World.class..
		global $smarty;
		
		$query  = "SELECT s.locationID as id, m.solarSystemName as Name, r.regionName as Region
				   FROM ".db_tab_silos." as s 
				   INNER JOIN ".db_tab_mapsolarsystems." as m ON s.locationID = m.solarSystemID
				   INNER JOIN ".db_tab_mapregions." as r ON m.regionID = r.regionID
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
	
	private function setAssets() {
		// global $database;
		
		$corpID = $this->corpID;
		/* $query  = ("SELECT s.*, r.quantity AS stk, it.volume, it.typeName, it2.capacity AS silocapacity,
				  (((SELECT IfNull(d.valueFloat, d.valueInt)
				  FROM ".db_tab_dgmtypeattributes." d INNER JOIN {$this->_table_pos} p ON p.typeID =
					  d.typeID
				  WHERE d.attributeID = 757 AND p.moonID = s.pos) + 100) /
				  100) AS boni
				FROM
				  {$this->_table_silos} s 
				LEFT JOIN {$this->_table_silos_reactions} r ON s.typeID = r.typeID AND s.input = r.input 
				LEFT JOIN ".db_tab_invtypes." it ON s.typeID = it.typeID
				LEFT JOIN ".db_tab_invtypes." it2 ON s.siloTypeID = it2.typeID
				WHERE
				  s.corpID = '$corpID'
				ORDER BY
				  s.pos, s.typeID DESC, s.stack, s.quantity;"); 
		
		$query  = ("SELECT s.*, it.volume, it.typeName, it2.capacity AS silocapacity,
				  (((SELECT IfNull(d.valueFloat, d.valueInt)
				  FROM {$this->_table['dgmtypeattributes']} d INNER JOIN {$this->_table_pos} p ON p.typeID =
					  d.typeID
				  WHERE d.attributeID = 757 AND p.moonID = s.pos) + 100) /
				  100) AS boni,
				  IFNULL((SELECT r.quantity * IFNULL(IFNULL(da.valueInt, da.valueFloat), 1) as qty
						FROM {$this->_table['invtypereactions']} r 
						LEFT JOIN {$this->_table['dgmtypeattributes']} da ON r.typeID = da.typeID AND da.attributeID = 726
						WHERE r.typeID = s.typeID and r.input = s.input
						GROUP BY r.typeID, r.input),100) as stk
				FROM
				  {$this->_table_silos} s 
				LEFT JOIN {$this->_table['invtypes']} it ON s.typeID = it.typeID
				LEFT JOIN {$this->_table['invtypes']} it2 ON s.siloTypeID = it2.typeID
				WHERE
				  s.corpID = '{$corpID}'
				ORDER BY
				  s.pos, s.typeID DESC, s.stack, s.quantity;");
		*/
		$query  = ("SELECT s.*, it.volume, it.typeName, it2.capacity AS silocapacity,
				  (((SELECT IfNull(d.valueFloat, d.valueInt)
				  FROM {$this->_table['dgmtypeattributes']} d INNER JOIN {$this->_table_pos} p ON p.typeID =
					  d.typeID
				  WHERE d.attributeID = 757 AND p.moonID = s.pos) + 100) /
				  100) AS boni,
				  IFNULL((SELECT r.quantity * IFNULL(IFNULL(da.valueInt, da.valueFloat), 1) as qty
						FROM (SELECT sub_ir.reactionTypeID, sub_ir.input, sub_ir.typeID, sub_ir.quantity
							FROM {$this->_table['fsrtool_silos_reactors']} sub_r
							INNER JOIN {$this->_table['invtypereactions']} sub_ir ON sub_r.typeIDx = sub_ir.reactionTypeID
							WHERE sub_r.corpID = {$corpID}) as r
						LEFT JOIN {$this->_table['dgmtypeattributes']} da ON r.typeID = da.typeID AND da.attributeID = 726
						WHERE r.typeID = s.typeID and r.input = s.input
						GROUP BY r.typeID, r.input),100) as stk
				FROM
				  {$this->_table_silos} s 
				LEFT JOIN {$this->_table['invtypes']} it ON s.typeID = it.typeID
				LEFT JOIN {$this->_table['invtypes']} it2 ON s.siloTypeID = it2.typeID
				WHERE
				  s.corpID = '{$corpID}'
				ORDER BY
				  s.pos, s.typeID DESC, s.stack, s.quantity;");

				  
		$res = $this->db->query($query);
		while ($row = $res->fetch_assoc()) {
			if ($row) { 
				$assets[] = $row;						
			}
		}
		// echo '<pre>'; print_r($assets); die;
		$res->close();
		
		if ( isset($assets) && is_array($assets) ) {
			foreach($assets as $asset) {
				$this->untouchtAssets[$asset['itemID']] = $asset;
			}
			$this->buildAssets( $assets );
			
			$alarm = false;
			$typ = $typ2 = $typ3 = false;
			$oldqty = 0;
			$oldKey = null;
			$oldKey2 = null;
			$oldKey3 = null;
			$pos = null;
			$time = time();
			
			foreach ($assets as $key => $item) {
				if ($item['pos'] != $pos) $oldqty = 0;
				// if ($item['pos'] != 40228115) continue;
				// echo $item['alarm'];
				$x=0;
				$volume = $item['quantity'] * $item['volume'];
				if (!empty($item['boni']) && !empty($item['volume'])) $maxCargo = floor(($item['silocapacity'] * $item['boni']) / $item['volume']);
				elseif (!empty($item['volume'])) $maxCargo = floor($item['silocapacity'] / $item['volume']);
				else $maxCargo = $item['silocapacity'];
				// echo $item['typeID'].$item['pos'].$item['input'].' - '.$typ.'<br>';
				$qty = $item['quantity'];
				if ($typ == $item['typeID'].$item['pos'].$item['input'] && $oldqty >= 0) {
					$qty += $oldqty;
				}
				// if ($item['pos'] == 40221003) echo $qty.'<br>';
				if ( $item['turn'] == 0 && ($item['quantity'] + ($item['stk']*$this->atime)) >= $maxCargo ) {
					// silo zu voll in 24h
					//$x = $item['quantity'] / $item['stk'];
					if ( $typ == $item['typeID'].$item['pos'].$item['input'] && $alarm === false ) $alarm = false;
					else $alarm = true;
				} 
				
				elseif ( $item['turn'] == 1 && ($qty - ($item['stk']*$this->atime)) <= 0 ) {
					// silo zu leer in 24h
					//$x = $qty / $item['stk'];
					if ( $typ == $item['typeID'].$item['pos'].$item['input'] && $alarm === false ) $alarm = false;
					else $alarm = true;
				}
	
				else {
					// $typ   = $item['typeID'].$item['pos'].$item['input'];
					$alarm = false;
					if ( $item['alarm'] == 1 ) {
						$this->db->query("UPDATE {$this->_table_silos} SET alarm = 0 WHERE itemID = '{$item['itemID']}';");
						$assets[$key]['alarm'] = 0;
					}
					if ($typ == $item['typeID'].$item['pos'].$item['input'] && $assets[$oldKey]['alarm'] == 1) {
						$this->db->query("UPDATE {$this->_table_silos} SET alarm = 0 WHERE itemID = '{$assets[$oldKey]['itemID']}';");
						$assets[$oldKey]['alarm'] = 0;
					}
					if ($typ2 == $item['typeID'].$item['pos'].$item['input'] && $assets[$oldKey2]['alarm'] == 1) {
						$this->db->query("UPDATE {$this->_table_silos} SET alarm = 0 WHERE itemID = '{$assets[$oldKey2]['itemID']}';");
						$assets[$oldKey2]['alarm'] = 0;
					}
					if ($typ3 == $item['typeID'].$item['pos'].$item['input'] && $assets[$oldKey3]['alarm'] == 1) {
						$this->db->query("UPDATE {$this->_table_silos} SET alarm = 0 WHERE itemID = '{$assets[$oldKey3]['itemID']}';");
						$assets[$oldKey3]['alarm'] = 0;
					}
				}
				/* test */
				if( date('i',$time) < (floor($this->towerCache[$item['pos']]/60)) ){
					$ttc = $this->towerCache[$item['pos']]-date('i',$time)*60-date('s',$time);
				}else{
					$ttc = 60*60-date('i',$time)*60-date('s',$time)+$this->towerCache[$item['pos']];
				}
				if($item['turn'] == 1) $x = ($item['stk'] == 0 ? 0 : ($item['quantity'])/$item['stk']);
				else $x = ($item['stk'] == 0 ? 0 : ($maxCargo-$item['quantity'])/$item['stk']);
				if ($x <= 0) $x=0; else $x = $x*60*60 + $ttc;
				if ( $alarm === true ) {
					$this->db->query("UPDATE {$this->_table_silos} SET alarm = 1 WHERE itemID = '{$item['itemID']}';");
					$assets[$key]['alarm'] = 1;
					$assets[$key]['atime'] = floor($x);
				} else { 
					if($item['alarm']) {
						$this->db->query("UPDATE {$this->_table_silos} SET alarm = 0 WHERE itemID = '{$item['itemID']}';");
						$assets[$key]['alarm'] = 0;
					}
					$assets[$key]['atime'] = $x;
				}
				if($item['suspect']) $this->suspect = true;
				$typ3 = $typ2;
				$typ2 = $typ;
				$typ = $item['typeID'].$item['pos'].$item['input'];
				$oldqty = $qty;
				$pos = $item['pos'];
				$oldKey3 = $oldKey2;
				$oldKey2 = $oldKey;
				$oldKey = $key;
			}
			return $assets;
		} else {
			return false;
		}
	}
	
	public function getSilosByLocation($locationID) {
		$corpID = $this->corpID;
		$query  = ("SELECT s.pos, p.manager
					FROM {$this->_table_silos} s
					INNER JOIN {$this->_table_pos} p ON s.pos = p.moonID
					WHERE
					  s.locationID = '$locationID' AND
					  s.pos != '' AND
					  s.corpID = '$corpID'
					GROUP BY s.pos
					ORDER BY s.sort, s.pos;");
		$res = $this->db->query( $query );
		if ( $res->num_rows > 0 ) {
			$a=0;
			while ( $row = $res->fetch_assoc() ) {
				if ($row) {
					$towers[$a]['pos'] = $row['pos'];
					$towers[$a]['manager'] = $row['manager'];
					$a++;
				}
			}
			
			foreach ($towers as $tower) {
				$return[] = $this->table($this->assets,$tower,$locationID); 
			}
		}
		$res->close();
		
		return $return;	
	}
	
	public function getSilosByManager($manager) {
		$corpID = $this->corpID;
		$query  = ("SELECT s.pos, p.manager
					FROM {$this->_table_silos} s
					INNER JOIN {$this->_table_pos} p ON s.pos = p.moonID
					WHERE
					  p.manager = '$manager' AND
					  s.pos != '' AND
					  s.corpID = '$corpID'
					GROUP BY s.pos
					ORDER BY s.sort, s.pos;");
		$res = $this->db->query( $query );
		if ( $res->num_rows > 0 ) {
			$a=0;
			while ( $row = $res->fetch_assoc() ) {
				if ($row) {
					$towers[$a]['pos'] = $row['pos'];
					$towers[$a]['manager'] = $row['manager'];
					$a++;
				}
			}
			
			foreach ($towers as $tower) {
				$return[] = $this->table($this->assets,$tower,$locationID); 
			}
		}
		$res->close();
		
		return $return;	
	}
	
	public function getSilosByAlarm() {
		$return = array();
		$corpID = $this->corpID;
		$query  = ("SELECT s.pos, s.locationID, p.manager
					FROM {$this->_table_silos} s
					INNER JOIN {$this->_table_pos} p ON s.pos = p.moonID
					WHERE
					  (s.alarm = 1 AND s.pos != '' AND s.corpID = '$corpID') OR (s.suspect = 1 AND s.pos != '' AND s.corpID = '$corpID')
					GROUP BY s.pos
					ORDER BY s.sort, s.pos;");
		$res = $this->db->query( $query );
		if ( $res->num_rows > 0 ) {
			$a=0;
			while ( $row = $res->fetch_assoc() ) {
				if ($row) {
					$towers[$a]['pos']     = $row['pos'];
					$towers[$a]['manager'] = $row['manager'];
					$towers[$a]['locID']   = $row['locationID'];
					$a++;
				}
			}
			
			foreach ($towers as $tower) {
				$return[] = $this->table($this->assets,$tower,$tower['locID']); 
			}
		}
		$res->close();
		
		return $return;	
	}
	
	private function buildAssets(&$assets) {
		
		foreach ($assets as $key => $row) {
			
			$this->thisCacheTime = ($this->cacheTime + $this->towerCacheCalc[$row['pos']]) - 3600;
			//$thisago = floor($this->now - ($this->thisCacheTime / 3600));
			//$this->towerCacheCalcNEW[ $row['pos'] ] = $thisago;
			// $this->db->msg->addwarning( date( 'Y-m-d H:i:s', $this->thisCacheTime ) );
			// $this->db->msg->addconfirm( date( 'Y-m-d H:i:s', $this->xxx[$row['pos']] ) );
			
			// $this->db->msg->addconfirm( floor($this->now - ($this->thisCacheTime / 3600)) );
			
			if ($this->siloA !== false && $assets[$this->siloA]['pos'] != $row['pos']) {
				$this->siloA = false;
				// if ($row['pos'] == 40221003) { echo ' - '.$row['typeID'].'<br>'; }
			}
			
			// if ($row['pos'] == 40221003) { echo $row['quantity'].' - '.$row['typeID'].'<br>'; }
			
			if ($this->siloA === false) {
				$this->siloA = $key;
				$this->siloB = $row['quantity'];
				$assets[$key]['quantity'] = $this->silo_fuel($row); 
				// if ($row['pos'] == 40221003) { echo $row['quantity'].' 1- '.$row['typeID'].'<br>'; }
			}
			
			else if ($this->siloB <= $row['quantity'] &&
				$assets[$this->siloA]['typeID']   == $row['typeID'] &&
				$assets[$this->siloA]['input']    == 0 &&
				$row['input'] == 0 &&
				$assets[$this->siloA]['stack']    == 1 &&
				$row['stack'] == 1
				) 
			{
				// if ($row['pos'] == 40221003) { echo $row['quantity'].' 2- '.$row['typeID'].'<br>'; }
				if ($row['boni'] && $row['volume']) $maxvolume = ($row['silocapacity'] * $row['boni']) / $row['volume']; elseif ($row['volume']) $maxvolume = ($row['silocapacity'] / $row['volume']); else $maxvolume=0;
				if ($row['stk']) $maxvolume = ( floor($maxvolume/$row['stk']) * $row['stk'] );
				$quantity = $this->silo_fuel($row);
				
				if ($quantity >= $maxvolume) {
					$quantityA = $quantity - $maxvolume;
					$quantityB = $maxvolume - $row['quantity'];
					// echo $quantity.'<br>'.$quantityA.'<br>'.$quantityB.'<br>'.$row['volume'];
					$assets[$key]['quantity'] = $row['quantity'] + $quantityB;
					$assets[$this->siloA]['quantity'] = $this->siloB + $quantityA;
					$this->siloA = $key;
					$this->siloB = $row['quantity'] + $quantityB;
				
				} else {
					$assets[$key]['quantity'] = $quantity;
					$assets[$this->siloA]['quantity'] = $this->siloB;
					$this->siloA = $key;
					$this->siloB = $row['quantity'];
				}
			}
			
			else if ($this->siloB <= $row['quantity'] &&
				$assets[$this->siloA]['typeID']   == $row['typeID'] &&
				$assets[$this->siloA]['input']    == 1 &&
				$row['input'] == 1 &&
				$assets[$this->siloA]['stack']    == 1 &&
				$row['stack'] == 1
				) 
			{
				// if ($row['pos'] == 40221003) { echo $row['quantity'].' 2- '.$row['typeID'].'<br>'; }
				if ($row['boni'] && $row['volume']) $maxvolume = ($row['silocapacity'] * $row['boni']) / $row['volume']; elseif ($row['volume']) $maxvolume = ($row['silocapacity'] / $row['volume']); else $maxvolume=0;
				if ($row['stk']) $maxvolume = ( floor($maxvolume/$row['stk']) * $row['stk'] );
				$quantity = $assets[$this->siloA]['quantity'];//$this->silo_fuel($row);
				
				if ($quantity <= 0) {
					$quantityA = $row['quantity'] + $quantity;
					$quantityB = $row['quantity'];
					// echo $quantity.'<br>'.$quantityA.'<br>'.$quantityB.'<br>'.$row['volume'].'<br>';
					$assets[$key]['quantity'] = $quantityA;
					$assets[$this->siloA]['quantity'] = $quantity;
					$this->siloA = $key;
					$this->siloB = $row['quantity'];# + $quantityB;
				
				} else {
					$assets[$key]['quantity'] = $maxvolume;//$quantity;
					$assets[$this->siloA]['quantity'] = $assets[$this->siloA]['quantity'];//$this->siloB;
					$this->siloA = $key;
					$this->siloB = $row['quantity'];
				}
			}
			else { 
				// if ($row['pos'] == 40221003) { echo $row['quantity'].' 3- '.$row['typeID'].'<br>'; }
				$assets[$key]['quantity'] = $this->silo_fuel($row);
				// $assets[$this->siloA]['quantity'] = $this->siloB;
				$this->siloA = $key;// false;
				$this->siloB = $row['quantity'];// 0;
			}
		
			//break;
		}
	}
	
	private function silo_fuel($row) {
		if ($row['emptyTime'] != '0' && $row['emptyTime'] > $this->cacheTime) {
			$newcacheTime = date('Y-m-d H', $row['emptyTime']).':00:00';
			$newcacheTime = strtotime($newcacheTime) - 0;
			$newcacheTime = $newcacheTime; // + $this->towerCache[$row['pos']]; // checken....
			$thisago = floor($this->now - ($newcacheTime / 3600));
			if ($row['input'] == 0) 					 { $fuel = $row['quantity'] + ($row['stk'] * $thisago); }
			if ($row['input'] == 1 && $row['turn'] == 0) { $fuel = $row['quantity'] + ($row['stk'] * $thisago); }
			if ($row['input'] == 1 && $row['turn'] == 1) { $fuel = $row['quantity'] - ($row['stk'] * $thisago); }
		} else {
			//$thisago = floor($this->now - ($this->thisCacheTime / 3600));
			$thisago = $this->towerCacheAgo[ $row['pos'] ];			
			if ($row['input'] == 0) 					 { $fuel = $row['quantity'] + ($row['stk'] * $thisago); }
			if ($row['input'] == 1 && $row['turn'] == 0) { $fuel = $row['quantity'] + ($row['stk'] * $thisago); }
			if ($row['input'] == 1 && $row['turn'] == 1) { $fuel = $row['quantity'] - ($row['stk'] * $thisago); }
		}
		
		return $fuel;
	}

	private function getCacheTime($typ) {
		$corpID = $this->corpID;
		$cacheTime = NULL;
		$query  = ("SELECT cacheTime FROM {$this->_table_cachetimes} WHERE corpID = '$corpID' AND type = '$typ' LIMIT 1;");
		$res = $this->db->query( $query );
		if ( $res->num_rows > 0 ) {
			$cacheTime = $res->fetch_assoc();
			$cacheTime = $cacheTime['cacheTime'];
		}
		$res->close();
		return $cacheTime;
	}

	private function getTowerCycleTimes() {
		$corpID = $this->corpID;
		$times = array();
		$cacheTimeCalc = strtotime(date('Y-m-d H', $this->cacheTime).':00:00');
		$towerCache = unserialize($this->getCacheTime(2));
		//$xxx = strtotime('2013-12-07 01:03:36');  $xxxnow = strtotime('2013-12-07 01:15:38')/3600; echo floor($xxxnow - ($xxx/3600));
		if( is_array($towerCache) && count($towerCache) >= 1 ) {
			foreach( $towerCache as $moonID => $stateTime ) {
				$stateTimestamp = strtotime($stateTime);
				if( $this->cacheTime <= $stateTimestamp ) {
					$cacheTime = $cacheTimeCalc + ((substr($stateTime,14,2) *60) + substr($stateTime,17));
					if( date('i', $this->cacheTime) <= date('i', $stateTimestamp) ) { // +1 stunde
						$this->towerCacheAgo[ $moonID ] = floor($this->now - ($cacheTime/3600))+1;
					} else {
						$this->towerCacheAgo[ $moonID ] = floor($this->now - ($cacheTime/3600));
					}
					$this->towerCacheCalcNEW[ $moonID ] = 'jep';
				} else {
					$cacheTime = $cacheTimeCalc + ((substr($stateTime,14,2) *60) + substr($stateTime,17));
					if( date('i', $this->cacheTime) <= date('i', $stateTimestamp) ) { // +1 stunde
						$this->towerCacheAgo[ $moonID ] = floor($this->now - ($stateTimestamp/3600))+1;
					} else {
						$this->towerCacheAgo[ $moonID ] = floor($this->now - ($stateTimestamp/3600));
					}
					$this->towerCacheCalcNEW[ $moonID ] = 'no';
				}
				
				$times[ $moonID ] = (substr($stateTime,14,2) *60) + substr($stateTime,17); // sekunden
				$x = (substr($stateTime,14,2) *60) + substr($stateTime,17);
				$this->assetTowerCache[ $moonID ] = $stateTime;
				//$this->towerCacheCalcNEW[ $moonID ] = $stateTime;
				// $this->db->msg->addwarning( substr($stateTime,14) );
				if ( $x < $this->y )					
					$this->towerCacheCalc[ $moonID ] = $x + 3600;
				else
					$this->towerCacheCalc[ $moonID ] = $x;
			}
		} else {
			$query  = ("SELECT moonID, stateTimestamp FROM {$this->_table_pos} WHERE corpID = '$corpID';");
			$res = $this->db->query( $query );
			if ( $res->num_rows > 0 ) {
				while ( $row = $res->fetch_assoc() ) {
					if ($row) {
						$stateTimestamp = strtotime($row['stateTimestamp']);
						if( $this->cacheTime <= $stateTimestamp ) {
							$cacheTime = $cacheTimeCalc + ((substr($row['stateTimestamp'],14,2) *60) + substr($row['stateTimestamp'],17));
							if( date('i', $this->cacheTime) <= date('i', $stateTimestamp) ) { // +1 stunde
								$this->towerCacheAgo[ $row['moonID'] ] = floor($this->now - ($cacheTime/3600))+1;
							} else {
								$this->towerCacheAgo[ $row['moonID'] ] = floor($this->now - ($cacheTime/3600));
							}
							$this->towerCacheCalcNEW[ $row['moonID'] ] = 'jep';
						} else {
							$cacheTime = $cacheTimeCalc + ((substr($row['stateTimestamp'],14,2) *60) + substr($row['stateTimestamp'],17));
							if( date('i', $this->cacheTime) <= date('i', $stateTimestamp) ) { // +1 stunde
								$this->towerCacheAgo[ $row['moonID'] ] = floor($this->now - ($stateTimestamp/3600))+1;
							} else {
								$this->towerCacheAgo[ $row['moonID'] ] = floor($this->now - ($stateTimestamp/3600));
							}
							$this->towerCacheCalcNEW[ $row['moonID'] ] = 'no';
						}
						
						
						//$this->thisCacheTime = ($this->cacheTime + $this->towerCacheCalc[$row['pos']]) - 3600;
						//$thisago = floor($this->now - ($this->thisCacheTime / 3600));
						#$this->towerCacheAgo[ $row['moonID'] ] = date('i', $stateTimestamp);
						
						
						$times[ $row['moonID'] ] = (substr($row['stateTimestamp'],14,2) *60) + substr($row['stateTimestamp'],17); // sekunden
						$x = (substr($row['stateTimestamp'],14,2) *60) + substr($row['stateTimestamp'],17);
						$this->assetTowerCache[ $row['moonID'] ] = $row['stateTimestamp'];
						//$this->towerCacheCalcNEW[ $row['moonID'] ] = $row['stateTimestamp'];
						// $this->db->msg->addwarning( substr($row['stateTimestamp'],14) );
						if ( $x < $this->y )					
							$this->towerCacheCalc[ $row['moonID'] ] = $x + 3600;
						else
							$this->towerCacheCalc[ $row['moonID'] ] = $x;
					}
				}
			}
			$res->close();
		}
		return $times;
	}
	
	public function getFillStatus($moonID){ // ajax
		global $language;
		$assets = $this->assets;
		$silos = array();
		
		if( !$assets ) $assets = array();
		
		$time = time();
		if( date('i',$time) < (floor($this->towerCache[$moonID]/60)) ){
			$ttc = $this->towerCache[$moonID]-date('i',$time)*60-date('s',$time);
		}else{
			$ttc = 60*60-date('i',$time)*60-date('s',$time)+$this->towerCache[$moonID];
		}
		$alert = false;
		$suspect = false;
		foreach($assets as $item) {
			if ($item['pos'] == $moonID) {
				if (!empty($item['boni']) && $item['volume']) {
					$menge  = floor(($item['silocapacity'] * $item['boni']) / $item['volume']);
				} elseif ($item['volume']) {
					$menge  = floor($item['silocapacity'] / $item['volume']);
				} else {
					$menge = $item['silocapacity'];
				}
				if ($item['stk']) $meng = ( floor($menge/$item['stk']) * $item['stk'] ); else $meng = $item['silocapacity'];
				if ($item['quantity'] > $meng) $item['quantity'] = $meng;
				if ($item['quantity'] <= 0 && $item['turn'] = 1) $item['quantity'] = 0; // new
				$pro = ceil($item['quantity']/$menge*100); if ($pro <= 0) $pro = 0;
				$endTime = $this->getRemaining(floor($item['atime']));
				$silos[$item['itemID']]['volume'] = number_format($item['quantity'] * $item['volume'],'0',',','.');
				$silos[$item['itemID']]['quantity'] = number_format($item['quantity'],'0',',','.');
				$silos[$item['itemID']]['pro'] = $pro;
				$silos[$item['itemID']]['endTime'] = $endTime;
				$silos[$item['itemID']]['alarm'] = $item['alarm'];
				$silos[$item['itemID']]['suspect'] = $item['suspect'];
			}
			if($item['pos']==$moonID && $item['alarm']){
				$alert = true;
			}
			if($item['pos']==$moonID && $item['suspect']){
				$suspect = true;
			}
			
		}
		return array('ttc'=>$ttc,'silos'=>$silos,'alert'=>$alert,'suspect'=>$suspect,'lang'=>$language);
	}

	private function table($assets, $tower, $locationID) {
		global $language, $world, $igb;
		
		$oldtypeID = false;
		$id = $tower['pos'];
		$manager = $tower['manager'];
		$simpleId = $this->simpleIDs;
		$a=0;
		$time = time();
		$nextcycle = strtotime(date('Y-m-d',$time).(date('H',$time).':00:00 +'.($this->towerCache[$id]+3600).'seconds'));
		if($nextcycle>3600)
		  $nextcycle -= 3600;
		//echo date('Y-m-d H:i:s',$nextcycle);
		$ttc = $nextcycle-time();
		if($ttc<0)
		  $ttc = 3600-($ttc * (-1));
		  
		// Alert
		$alert = '';
		$alertmsg = $suspectmsg = '';
		$suspect = '';
		$typename = array();
		foreach($assets as $item){
			if ($item['typeID'] == 0) $item['typeName'] = 'undefined';
			if($item['pos']==$id && $item['alarm']){
				$alert = ' alert';
				if(!isset($typename[ $item['typeName'] ]))
					$typename[ $item['typeName'] ]=0;
				else $typename[ $item['typeName'] ]++;
			}
			if($item['pos']==$id && $item['suspect']){
				$suspect = ' suspect';
				if(!isset($typenamesus[ $item['typeName'] ]))
					$typenamesus[ $item['typeName'] ]=0;
				else $typenamesus[ $item['typeName'] ]++;
			}
		}
		if($alert != ''){
			$alertmsg = '<tr class="alert"><td colspan="3" style="padding:5px"><b>'.$language['alert_header'].'</b><br/>'.$language['alert_msg'].'<br/> "'.implode('"; "',array_keys($typename)).'"</td></tr>';
		}
		if($suspect != ''){
			$suspectmsg = '<tr class="suspect"><td colspan="3" style="padding:5px"><b>One Silo looks Offline</b><br/> "'.implode('"; "',array_keys($typenamesus)).'"</td></tr>';
		}
		if(!isset($_GET['setti'])) $setti =  ' style="display:none;"';
		
		$assign ='<tr class="assign"'.$setti.'><td colspan="3" style="padding:0 5px;"><a href="'.URL_INDEX .'?module='.ACTIVE_MODULE.'&action=system&id='.$locationID.'&reactors='.$id.'&setti=1#'.$id.'">auto linking</a></td></tr>';
		
		$table = '<table id="'.$id.'" name="'.$id.'" class="silo'.$alert.$suspect.'" cellpadding="0" cellspacing="0">
		  <thead>
        <tr><td align="center" colspan="3" style="padding:5px">
          <div style="float:left;font-weight:bold;">'.$world->moonIDtoName($id).'</div>
          <div style="float:right"><span class="timer">'.$ttc.'</span></div>
        </td></tr>'.$alertmsg.$suspectmsg.$assign.'
      </thead>
		<tbody>';
		
		foreach($assets as $key => $item) {
			if ($item['pos'] == $id) {
				$volume = $item['quantity'] * $item['volume'];
				if (!empty($item['boni']) && $item['volume']) {
					$menge  = floor(($item['silocapacity'] * $item['boni']) / $item['volume']);
				} elseif ($item['volume']) {
					$menge  = floor($item['silocapacity'] / $item['volume']);
				} else {
					$menge = $item['silocapacity'];
				}
				if ($item['stk']) $meng = ( floor($menge/$item['stk']) * $item['stk'] ); else $meng = $item['silocapacity'];
				if ($item['quantity'] > $meng) $item['quantity'] = $meng;
				if ($item['quantity'] <= 0 && $item['turn'] == 1) $item['quantity'] = 0; // new
				if ($item['input'] == 0){
				  $input = 'input';
				  $icon_input = '200.png';
				  $desc_input = $language['silo200'];
				} 
				else {
				  $input = 'output';
				  $icon_input = '100.png';
				  $desc_input = $language['silo100'];
				}
				
				if ($item['turn'] == 0){ 
				  $fill = 'fill';
				  $icon = 'package_fill.png';
				  $desc[1] = $language['silo_filling_up'];
				  $desc[2] = $language['empty_silo'];
				  $desc[3] = 'lorry_right.png';
				} 
				else {
				  $fill = 'empty';
				  $icon = 'package_unfill.png';
				  $desc[1] = $language['silo_empties'];
				  $desc[2] = $language['fill_silo'];
				  $desc[3] = 'lorry_left.png';
				}
				
				if ($item['stack'] == 0) {
					$icon_stack = 'arrow_join_red.png';
					$desc_stack = $language['desc_stack_on'];
					$set_stack = 'set';
				}
				else {
					$icon_stack = 'arrow_join.png';
					$desc_stack = $language['desc_stack_off'];
					$set_stack = 'unset';
				}
				
				//$pro = ceil($item['quantity']/$menge*100); if ($pro <= 0) $pro = 0;
				$pro = $this->getRemaining(floor($item['atime']));
				$alert = '';
				$suspect = '';
				if($item['alarm']){
					$alert = ' class="alert"';
				}
				if($item['suspect']){
					if($alert == '') $suspect = ' class="suspect"';
					else $alert = ' class="alert suspect"';
				}
				if ($item['typeID'] == 0) $item['typeName'] = 'undefined';
				$table .= '<tr'.$alert.$suspect.' id="'.$item['itemID'].'">';
				if ($igb == 1) $table .= '<td width="32"><a href="Javascript:CCPEVE.showMarketDetails('.$item['typeID'].')">'.$this->img($item).'</a></td>';
				else $table .= '<td width="32">'.$this->img($item).'</td>';
				$table .= '<td>
				<div style="margin-top:3px; margin-left:5px;border:solid 2px #000;width:100px;height:10px;float:left;">
				 <div class="pc" style="margin-top:-2px;width:100px;text-align:center">'.$pro.'</div>
				 '.$this->probalken($item['quantity'],$menge,$item['turn']).'</div>
				<div class="qty" style="margin-top:3px;margin-left:120px"><span>'.number_format($item['quantity'],'0',',','.').'</span>/'.number_format($menge,'0',',','.').'</div>
				<div style="clear:both;margin-left:5px">'.$item['typeName'].'</div>
				</td>
				<td align="right" style="padding-right:2px">';
				if($item['suspect'])
					$table .= '<a id="online" href="javascript:online('.$id.','.$item['itemID'].')"><img src="icons/delete.png" alt="online" title="set online"/></a>';
				$table .= '<a id="lorry" href="javascript:empty('.$id.','.$item['itemID'].')"><img src="icons/'.$desc[3].'" alt="empty" title="'.$desc[2].'"/></a>
				</td></tr>';  		
				// <td align="right" style="padding-right:2px"><a class="delete" href="'.URL_INDEX .'?module='.ACTIVE_MODULE.'&action=system&id='.$locationID.'&emptyItemID='.$item['itemID'].'"><img src="icons/'.$desc[3].'" alt="empty" title="'.$desc[2].'"/></a>
				
				$table .='<tr id="setti'.$item['itemID'].'" class="settings"'.$setti.'><td colspan="3" style="padding:0 5px;"><div style="float:right">';
				// arrow_join.png
				#$table .='<a href="'.URL_INDEX .'?module='.ACTIVE_MODULE.'&action=system&id='.$locationID.'&'.$set_stack.'stackID='.$item['itemID'].'&setti=1#'.$id.'" title="'.$desc_stack.'<hr/>'.$language['click_to_convert'].'"><img style="margin:0px 2px 0px 2px" src="icons/'.$icon_stack.'" /></a>';
				
				if(($item['typeID'] != 0 && ($oldtypeID == $item['typeID'] || $wusa = $assets[++$key]['typeID'] == $item['typeID'])) || $item['stack'] == 1)
				  $table .='<a id="stack" href="javascript:setstack('.$id.','.$item['itemID'].',\''.$set_stack.'\')" title="'.$desc_stack.'<hr/>'.$language['click_to_convert'].'"><img style="margin:0px 2px 0px 2px" src="icons/'.$icon_stack.'" /></a>';
				if(in_array($item['typeID'],$simpleId)){
				  #$table .='<a href="'.URL_INDEX .'?module='.ACTIVE_MODULE.'&action=system&id='.$locationID.'&ID'.$input.'='.$item['itemID'].'&setti=1#'.$id.'" title="'.$desc_input.'<hr/>'.$language['click_to_convert'].'"><img style="margin:0px 2px 0px 2px" src="icons/'.$icon_input.'" alt="'.$input.'"/></a>';
				  #$table .='<a href="'.URL_INDEX .'?module='.ACTIVE_MODULE.'&action=system&id='.$locationID.'&ID'.$fill.'='.$item['itemID'].'&setti=1#'.$id.'" title="'.$desc[1].'<hr/>'.$language['click_to_convert'].'"><img style="margin:0px 2px 0px 2px" src="icons/'.$icon.'" alt="'.$fill.'"/></a>';
				  $table .='<a id="inout" href="javascript:setinout('.$id.','.$item['itemID'].',\''.$input.'\')" title="'.$desc_input.'<hr/>'.$language['click_to_convert'].'"><img style="margin:0px 2px 0px 2px" src="icons/'.$icon_input.'" alt="'.$input.'"/></a>';
				  $table .='<a id="fesimple" href="javascript:setfesimple('.$id.','.$item['itemID'].',\''.$fill.'\')" title="'.$desc[1].'<hr/>'.$language['click_to_convert'].'"><img style="margin:0px 2px 0px 2px" src="icons/'.$icon.'" alt="'.$fill.'"/></a>';
				} else {
				  #$table .='<a href="'.URL_INDEX .'?module='.ACTIVE_MODULE.'&action=system&id='.$locationID.'&IDto'.$fill.'='.$item['itemID'].'&setti=1#'.$id.'" title="'.$desc[1].'<hr/>'.$language['click_to_convert'].'"><img style="margin:0px 2px 0px 2px" src="icons/'.$icon.'" alt="'.$fill.'"/></a>';
				  $table .='<a id="fillempty" href="javascript:setfillempty('.$id.','.$item['itemID'].',\''.$fill.'\')" title="'.$desc[1].'<hr/>'.$language['click_to_convert'].'"><img style="margin:0px 2px 0px 2px" src="icons/'.$icon.'" alt="'.$fill.'"/></a>';
				}
				#$table .= '<a class="delete" href="'.URL_INDEX .'?module='.ACTIVE_MODULE.'&action=system&id='.$locationID.'&itemID='.$item['itemID'].'&setti=1#'.$id.'"><img src="icons/delete.png" alt="del" title="'.$language['delete_silo'].'"/></a></div></td></tr>';
				$table .= '<a id="delete" class="delete" href="javascript:delSilo('.$id.','.$item['itemID'].')"><img src="icons/delete.png" alt="del" title="'.$language['delete_silo'].'"/></a></div></td></tr>';
				$oldtypeID = $item['typeID'];
			}
			$a++;
		}
		$mgmt = '';
		if(!empty($manager)){
			$mgmt = '<tfoot><tr><td colspan="3"><div style="float:left">POS '.$language['manager'].':</div>'.$manager.'</td></tr></tfoot>';
		}
		$table .= "</tbody>$mgmt</table>\n";
		
		return $table;
	}
	
	private function img(array $item) {
		if ($item['typeID'] == 0) return false;
		else {
			//$file = MODULE_DIR.ACTIVE_MODULE.'/img/'.$item['typeID'].'_32.png';
			$file = IMG_CACHE . $item['typeID'].'_32.png';
			$url = "https://image.eveonline.com/Type/{$item['typeID']}_32.png";
			if (!file_exists($file)) {
				$ch = curl_init();  
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_URL, $url);  
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
				$content = curl_exec ($ch);  
				curl_close ($ch);

				file_put_contents($file, $content);
			}
			$price = $this->db->fetch_all("SELECT * FROM {$this->_table['fsrtool_currentTypePrice']} WHERE typeID = {$item['typeID']} AND region = 30000142");
			$sell = number_format($price[0]['sell_min_price'],'0',',','.');
			$buy = number_format($price[0]['buy_max_price'],'0',',','.');
			$sumSell = number_format($price[0]['sell_min_price'] * $item['quantity'],'0',',','.');
			$sumBuy = number_format($price[0]['buy_max_price'] * $item['quantity'],'0',',','.');
			return '<img title="<font color=#E54C4C>SELLORDER['.$sell.']</font><br><font size=-10>TOTALVALUE['.$sumSell.']</font><br><hr><font color=#33E500>BUYORDER['.$buy.']</font><br><font size=-10>TOTALVALUE['.$sumBuy.']</font>" src="'.$file.'" width="32" height="32"/>';
		}
		return false;
	}
	
	private function probalken($x,$y,$turn){
	  $fillimg = ''; $unfillimg = '';
		if ($turn == 0) {
		$color = '#777';
		$fillimg = 'background-image:url(\'icons/fill.gif\');';
	  } else {
		$color = '#900';
		$unfillimg = 'background-image:url(\'icons/unfill.gif\');';
	  }
		$pro = ceil(($x/$y*100)); if ($pro <= 0) $pro = 0;
		/* $return = '<div style="padding:1px; margin-top:3px; margin-left:5px; background:'.$color.'; width:100px; height:10px;'.$fillimg.'"> 
				   <div align="left" style="width:'.$pro.'%; height:100%; background:#09f;'.$unfillimg.'"></div>
				   </div>'; */
		$return = '<div class="pcbar_pos" style="margin-top:-11px;float:left;width:'.$pro.'px;height:100%;'.$fillimg.'background-repeat:no-repeat;background-color:#09f">&nbsp;</div>
				   <div class="pcbar_neg" style="margin-top:-11px;float:right;width:'.(100-$pro).'px;height:100%;'.$unfillimg.'background-repeat:no-repeat;background-position:right;background-color:'.$color.'">&nbsp;</div>'; 
		return $return;
	}
	
	private function getRemaining($time){
		if($time <= 0){
			// Time has already elapsed
			return '0h';
		}
		else{
			// Get difference between times
			$days = floor($time/60/60/24);
			$hours = floor($time/60/60)-$days*24;
			if($days <= 0){
				return $hours.'h';
			} else {
				return $days.'d '.$hours.'h';
			}
		}
	}
	
	private function getRemainingTime($time){
		if($time <= 0){
			// Time has already elapsed
			return gmdate('Y-m-d');
		}
		else{
			$next = time() + ($time);
			return gmdate('Y-m-d H:i', $next);
		}
	}
	
	private function getCalendarEventTime($time){
		if($time <= 0){
			// Time has already elapsed
			return gmdate('Y-m-d');
		}
		else{
			$next = time() + ($time);
			return gmdate('Y-m-d H:i:s', $next);
		}
	}
	
	
}
?>