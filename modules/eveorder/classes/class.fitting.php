<?php
class fitting
{
	private $ships = array();
	
	public function __construct( $world, $fitID=false, $id=0 ) {
		global $smarty;
		
		$this->db = $world->db;
		$this->_table = $world->_table;
		$this->_fitID = $fitID;
		$this->_id = $id;
		$this->_charID = $_SESSION['order_user'];
		$this->getFittings();
		if ($fitID !== false) {
			$this->attrib = $this->getSlots( $fitID );
			$this->attrib['module'] = $this->getModule( $fitID );
			$this->attrib['imgShip'] = IMG_URL.'/Renders/'.$this->ships[ $fitID ]['ship'].'.png';
			$this->attrib['corpOrder'] = $world->User->CorpOrder;
			$this->skill();
			$imgShip = IMG_URL.'/Types/'.$this->ships[ $fitID ].'_256.png';
			$smarty->assign('ShipImgBig', $imgShip);
			$smarty->assign('showship', 1);
			$smarty->assign('showFitting', 1);
			
		}
	}
	
	private function skill() {
		$qry = "SELECT G.categoryID, G.groupName, G.groupID, T.typeName, T.typeID, T2.typeName AS skillName, 
					T2.typeID AS skillID, IFNULL(TAL.valueInt, TAL.valueFloat) AS skillLevel
			FROM {$this->_table['invgroups']} G
			INNER JOIN {$this->_table['invtypes']} T ON T.groupID = G.groupID
			INNER JOIN {$this->_table['dgmtypeattributes']} TA ON TA.typeID = T.typeID AND 
                                         TA.attributeID IN (182, 183, 184, 1285, 1289, 1290)
			INNER JOIN {$this->_table['dgmtypeattributes']} TAL ON TAL.typeID = TA.typeID AND -- I hate this join
                                              ((TAL.attributeID = 277 AND TA.attributeID = 182) OR
                                              (TAL.attributeID = 278 AND TA.attributeID = 183) OR
                                              (TAL.attributeID = 279 AND TA.attributeID = 184) OR
                                              (TAL.attributeID = 1286 AND TA.attributeID = 1285) OR
                                              (TAL.attributeID = 1287 AND TA.attributeID = 1289) OR
                                              (TAL.attributeID = 1288 AND TA.attributeID = 1290))
			INNER JOIN {$this->_table['invtypes']} T2 ON T2.typeID = IFNULL(TA.valueInt, TA.valueFloat)
			WHERE T.typeID NOT IN (19430, 9955)";
		
		$res = $this->db->query($qry);
		while($row = $res->fetch_assoc()) {
			$this->allSkills[$row['typeID']][$row['skillID']] = $row;
		}
		
		/* $fitres = $this->db->query("SELECT f.ship, fm.itemID
			FROM stsys_eveorder_fitting f
			INNER JOIN stsys_eveorder_fitting_module fm ON f.Id = fm.fitID
			WHERE f.Id = $fitID
			GROUP BY f.ship, fm.itemID;"); */
		
		$this->skill = array();
		//while($row = $fitres->fetch_assoc()) {
		foreach($this->attrib['module']['order'] as $slot => $item) {
			foreach($item as $itemID => $items) {
				foreach($this->allSkills[$itemID] as $id => $val) {
					if(is_array($this->allSkills[$id])) {
						$this->wusa($id, $itemID);
					}
					if(is_array($this->skill[$itemID][$id])) {
						if($val['skillLevel'] >= $this->skill[$itemID][$id]['skillLevel']) {
							$this->skill[$itemID][$id]['skillLevel'] = $val['skillLevel'];
						}
					} else {
					//$this->skill[$itemID]['typeName'] = $val['typeName'];
						$this->skill[$itemID][$id]['skillName'] = $val['skillName'];
						$this->skill[$itemID][$id]['skillLevel'] = $val['skillLevel'];
					}
				}
			}
		}
		$itemID = $this->ships[$this->_fitID]['ship'];
		foreach($this->allSkills[$itemID] as $id => $val) {
			if(is_array($this->allSkills[$id])) {
				$this->wusa($id, $itemID);
			}
			if(is_array($this->skill[$itemID][$id])) {
				if($val['skillLevel'] >= $this->skill[$itemID][$id]['skillLevel']) {
					$this->skill[$itemID][$id]['skillLevel'] = $val['skillLevel'];
				}
			} else {
			//$this->skill[$itemID]['typeName'] = $val['typeName'];
				$this->skill[$itemID][$id]['skillName'] = $val['skillName'];
				$this->skill[$itemID][$id]['skillLevel'] = $val['skillLevel'];
			}
		}
		
		$char = $this->db->query("SELECT * FROM {$this->_table['eveorder_skills']} WHERE charID = $this->_charID;");
		while($row = $char->fetch_assoc()) {
			$charSkills[$row['skillID']] = $row['quantity'];
		}
		
		foreach($this->skill as $id => $val) {
			foreach($val as $skillID => $skillVal) {
				if(isset($charSkills[$skillID])) {
					if($charSkills[$skillID] >= $skillVal['skillLevel']) {
						unset($this->skill[$id][$skillID]);
					}
				}
			}
			if(count($this->skill[$id]) == 0) unset($this->skill[$id]);
		}
		
		foreach($this->attrib['module']['order'] as $slot => $item) {
			foreach($item as $itemID => $items) {
				if(isset($this->skill[$itemID])) {
					$this->attrib['module']['order'][$slot][$itemID]['skillOK'] = 'cross.png';
				} else {
					$this->attrib['module']['order'][$slot][$itemID]['skillOK'] = 'tick.png';
				}
			}
		}
		if(isset($this->skill[$this->ships[$this->_fitID]['ship']])) {
			$this->attrib['module']['ship']['stuf']['skillOK'] = 'cross.png';
		} else {
			$this->attrib['module']['ship']['stuf']['skillOK'] = 'tick.png';
		}
		
		//print_r($this->attrib['module']['ship']['stuf']);
		//print_r($this->attrib['module']['order']);
		#print_r($this->skill);
		
	}
	
	private function wusa($id, $itemID) {
		foreach($this->allSkills[$id] as $id2 => $val2) {
			if(is_array($this->allSkills[$id2])) {
				$this->wusa($id2, $itemID);
			}
			if(is_array($this->skill[$itemID][$id2])) {
				if($val2['skillLevel'] > $this->skill[$itemID][$id2]['skillLevel']) {
					$this->skill[$itemID][$id2]['skillLevel'] = $val2['skillLevel'];
				}
			} else {
			//$this->skill[$itemID]['typeName'] = $val2['typeName'];
				$this->skill[$itemID][$id2]['skillName'] = $val2['skillName'];
				$this->skill[$itemID][$id2]['skillLevel'] = $val2['skillLevel'];
			}
		}
	}
	
	public function fetchFits($id) {
		$this->_id = $id;
		$this->getFittings();
	}
	
	function getFittings() {
		global $smarty;
		$this->ships = array();
		$query = ("SELECT r.raceName, t.raceID, t.typeName, t.groupID, g.groupName, fitt.*
			FROM {$this->_table['fitting']} fitt 
			INNER JOIN {$this->_table['invtypes']} t ON fitt.ship = t.typeID 
			INNER JOIN {$this->_table['invgroups']} g ON t.groupID = g.groupID 
			LEFT JOIN {$this->_table['chrraces']} r ON t.raceID = r.raceID
			WHERE fitt.corpID = '{$this->_id}'
			ORDER BY g.groupName, t.typeName, fitt.name;");
			
		$res = $this->db->query( $query );
		while ( $row = $res->fetch_assoc() ){
			//$this->ships[$row['Id']] = $row['ship'];
			
			$arr['fittID'] = $row['Id'];
			$arr['ship']   = $row['ship'];
			$arr['name']   = $row['name'];
			$arr['poster'] = $row['poster'];
			#$arr['module'] = $this->getModule($row['Id']);
			$this->ships[ $row['Id'] ] = $arr;
			$ret[ $row['groupName'] ][ $row['typeName'] ][] = $arr;
		}
		$res->close();
		$this->jsonFits = json_encode($ret);
		$smarty->assign('fitts', $ret);
	}
	
	function getModule($fitID) {
		$arr['fitting'] = array();
		$query = ("SELECT e.effectID, m.*
			FROM {$this->_table['fitting_module']} m 
			LEFT JOIN {$this->_table['dgmtypeeffects']} e ON m.itemID = e.typeID
			WHERE e.effectID IN (11, 12, 13, 2663, 3772) AND m.fitID = '{$fitID}'
			UNION ALL
			SELECT null, m.* FROM {$this->_table['fitting_module']} m 
			WHERE m.drone!=0 AND m.fitID = '{$fitID}';");
		$res = $this->db->query( $query );
		$arr['order']['high']= array();
		$arr['order']['mid']= array();
		$arr['order']['low']= array();
		
		while ( $row = $res->fetch_assoc() ) {
			$item = new Item($this, $row['itemID']);
			if ($row['ammo'] != 0) {
				$itemAmmo = new Item($this, $row['ammo']);
				$art['ammo'] = $itemAmmo->getIconJson();
			}else{
				$art['ammo'] = '';
			}
			$art['itemID'] = $row['itemID'];
			$art['icon'] = $item->getIconJson();
			if ($row['effectID'] == 12){
				$arr['fitting']['high'][] = $art;
				$arr['order']['high'][$row['itemID']]['anzahl']+=1;
				$arr['order']['high'][$row['itemID']]['icon']=$art['icon'];
				$arr['order']['high'][$row['itemID']]['itemID']=$row['itemID'];
			}else	if ($row['effectID'] == 13){
				$arr['fitting']['mid'][] = $art;
				$arr['order']['mid'][$row['itemID']]['anzahl']+=1;
				$arr['order']['mid'][$row['itemID']]['icon']=$art['icon'];
				$arr['order']['mid'][$row['itemID']]['itemID']=$row['itemID'];
			}else	if ($row['effectID'] == 11){
				$arr['fitting']['low'][] = $art;
				$arr['order']['low'][$row['itemID']]['anzahl']+=1;
				$arr['order']['low'][$row['itemID']]['icon']=$art['icon'];
				$arr['order']['low'][$row['itemID']]['itemID']=$row['itemID'];
			}else	if ($row['effectID'] == 2663){
				$arr['fitting']['rig'][] = $art;
				$arr['order']['rig'][$row['itemID']]['anzahl']+=1;
				$arr['order']['rig'][$row['itemID']]['icon']=$art['icon'];
				$arr['order']['rig'][$row['itemID']]['itemID']=$row['itemID'];
			}else	if ($row['effectID'] == 3772){
				$arr['fitting']['sub'][] = $art;
				$arr['order']['sub'][$row['itemID']]['anzahl']+=1;
				$arr['order']['sub'][$row['itemID']]['icon']=$art['icon'];
				$arr['order']['sub'][$row['itemID']]['itemID']=$row['itemID'];
			}else	if ($row['drone'] != 0){
				$arr['order']['drone'][$row['itemID']]['anzahl'] = $row['drone'];
				$arr['order']['drone'][$row['itemID']]['icon'] = $art['icon'];
				$arr['order']['drone'][$row['itemID']]['itemID']=$row['itemID'];
			}
		}
		$res->close();
		$item = new Item( $this, $this->ships[$fitID]['ship'] );
		$arr['ship']['stuf']=$this->ships[$fitID];
		$arr['ship']['icon']=$item->getIconJson();
		
		return $arr;
	}

	function getSlots($fitID) {
		$typeID = $this->ships[$fitID]['ship'];
		$subres = $this->db->query("SELECT GROUP_CONCAT(fm.itemID SEPARATOR ',') subs 
				FROM {$this->_table['fitting_module']} fm 
				INNER JOIN {$this->_table['invtypes']} t ON fm.itemID = t.typeID 
				INNER JOIN {$this->_table['invgroups']} g ON t.groupID = g.groupID 
				WHERE fm.fitID = {$fitID} AND g.categoryID = 32;");
		if ( $subres->num_rows == 1 ) {
			$row = $subres->fetch_assoc();
			$subs = $row['subs'];
		}
		$subres->close();
		if ( $subs != '' ){
			$query = ("SELECT sum(IfNull(valueInt, valueFloat)) slots, attributeID
			FROM {$this->_table['dgmtypeattributes']}
			WHERE attributeID IN (12, 13, 14, 1137, 1367, 1374, 1375, 1376) AND typeID IN ({$typeID},{$subs}) GROUP BY attributeID;");
		}else{
			$query = ("SELECT sum(IfNull(valueInt, valueFloat)) slots, attributeID
			FROM {$this->_table['dgmtypeattributes']}
			WHERE attributeID IN (12, 13, 14, 1137, 1367, 1374, 1375, 1376) AND typeID IN ({$typeID}) GROUP BY attributeID;");
		}
		$res = $this->db->query($query);
		while ( $row = $res->fetch_assoc() ) {
			if ($row['attributeID'] == 14) $arr['hiSlots'] = $row['slots'];
			if ($row['attributeID'] == 13) $arr['medSlots'] = $row['slots'];
			if ($row['attributeID'] == 12) $arr['lowSlots'] = $row['slots'];
			if ($row['attributeID'] == 1374) $arr['hiSlots'] = $row['slots'];
			if ($row['attributeID'] == 1375) $arr['medSlots'] = $row['slots'];
			if ($row['attributeID'] == 1376) $arr['lowSlots'] = $row['slots'];
			if ($row['attributeID'] == 1137) $arr['rigSlots'] = $row['slots'];
			if ($row['attributeID'] == 1367) $arr['subSlots'] = $row['slots'];
			
			#echo $row['attributeID'].'<br>';
		}
		$res->close();
		return $arr;
	}

	function getSlot($typeID) {
		$query = ("SELECT eff.effectID FROM {$this->_table['dgmtypeeffects']} eff
			WHERE eff.effectID IN (11, 12, 13, 2663 ,3772) AND eff.typeID = '{$typeID}';");
		$row = $this->db->fetch_one( $query, 'effectID' );
		
		return $row;
		
	}
	
	public function delFit($fiID) {
		$this->db->exec_query("DELETE FROM {$this->_table['fitting']} WHERE Id = '{$fiID}';");
		$this->db->exec_query("DELETE FROM {$this->_table['fitting_module']} WHERE fitID = '{$fiID}';");
	}
	
	function addFit($fitarray,$id=0){
		global $User;
		$fitarray = stripslashes($fitarray);
		#echo '<pre>'; print_r($fitarray); echo '</pre>';
		$fitarray = preg_replace('`[\r\n]+`',"\n", $fitarray);
		$fitarray = strip_tags($fitarray, '\n');
		$fitarray = explode("\n", $fitarray);
		
	//	print_r($fitarray);
		$now  = time();
		$time = date( 'Y-m-d H:i:s', $now );
		
		// Ship and fitting name
		$bracks   = array("[" => "", "]" => "");
		$details  = strtr($fitarray[0], $bracks);
		$shipInfo = explode(", ", $details);
		
		$item = new Item($this);
		$item->lookup($shipInfo[0]);
		$shipID  = $item->getID();
		$fitName = addslashes($shipInfo[1]);
		
		if ($shipID == NULL) return false;
	
		$columns = "corpID, name, poster, ship, time";
		$values  = "'{$id}', '{$fitName}', '{$User->username}', '{$shipID}', '{$time}'";
		$table   = $this->_table['fitting'];
		$this->db->exec_query("INSERT INTO $table ($columns) VALUES ($values);");
		
		
		$fitID = $this->db->insert_id;
		// The following code will print all the modules
		$num_elements=count($fitarray);
		$i = 1;
		while ($i < $num_elements)
		{
			if($fitarray[$i] != NULL)
			{
				$module = explode(",", $fitarray[$i]);
				$ammoID = "";
				$items  = preg_split("/(.*) x([0-9]*)/",$module[0],-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
				#echo $items[0].'<br>';
				$modName = trim($items[0]);
				#echo $modName.'<br>';
	
				if($modName != "[empty high slot]" && $modName != "[empty med slot]" && $modName != "[empty low slot]" && $modName != "[empty rig slot]" && $modName != "[empty subsystem slot]")
				{
				
					if(isset($module[1]))
					{
						$ammoID = "";
						$module[1] = trim($module[1]);			
						
						$item = new Item($this);
						$item->lookup($module[1]);
						$ammoID = $item->getID();
					}
					if(isset($items[1]))
					{
						$drones = trim($items[1]);
					}
					$item = new Item($this);
					$item->lookup($modName);
					$modID = $item->getID();
					#echo $modName.'<br>';
					#echo $modID.'<br>';
					$columns = "fitID, itemID, ammo, drone";
					$values  = "'{$fitID}', '{$modID}', '" . ($ammoID == NULL ? "0" : $ammoID) . "', '" . ($drones == NULL ? "0" : $drones) . "'";
					$table   = $this->_table['fitting_module'];
					$this->db->exec_query("INSERT INTO $table ($columns) VALUES ($values);");
				}
				
			}
			$i = ++$i;
		}
	}

}
?>