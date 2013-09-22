<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class test {
	
	private $_corpID = null;
	private $_db = null;
	private $_table = array();
	private $_SovereigntyCache = null;
	private $_towerresurceCache = null;
	
	public function __construct($corpID, World $world) {
		$this->_corpID = $corpID;
		$this->_db = $world->db;
		$this->_table = $world->_table;
		// echo '<pre>'; print_r($this); echo '</pre>';die;
	}
	
	public function test() {
		$r = array();
		$res = $this->_db->query("SELECT * FROM {$this->_table['fsrtool_silos_reactors']} WHERE corpID = {$this->_corpID} ORDER BY typeID ASC");
		if( $res->num_rows >= 1 ) {
			while( $row = $res->fetch_assoc() ) {
				$react = $this->_db->query("SELECT r.typeID, r.input, r.quantity * IFNULL(IFNULL(da.valueInt, da.valueFloat), 1) as qty, i.typeName, p.sell_min_price, p.buy_max_price
					FROM {$this->_table['invtypereactions']} r 
					LEFT JOIN {$this->_table['dgmtypeattributes']} da ON r.typeID = da.typeID AND da.attributeID = 726
					LEFT JOIN {$this->_table['invtypes']} i ON r.typeID = i.typeID
					LEFT JOIN {$this->_table['fsrtool_currentTypePrice']} p ON r.typeID = p.typeID AND p.region = 30000142
					WHERE r.reactionTypeID = {$row['typeIDx']};");
				while( $rea = $react->fetch_assoc() ) {
					
					$price[$rea['typeID']][0] = $rea['sell_min_price'];
					$price[$rea['typeID']][1] = $rea['buy_max_price'];
					
					if ($rea['input'] == 0) {
						if (isset($r['out'][$rea['typeID']])) {
							$r['out'][$rea['typeID']]['quantity'] += $rea['qty'];
						} else {
							$r['out'][$rea['typeID']]['input'] = $rea['input'];
							$r['out'][$rea['typeID']]['quantity'] = $rea['qty'];
							$r['out'][$rea['typeID']]['typeName'] = $rea['typeName'];
						}
					} else {
						if (isset($r['in'][$rea['typeID']])) {
							$r['in'][$rea['typeID']]['quantity'] += $rea['qty'];
						} else {
							$r['in'][$rea['typeID']]['input'] = $rea['input'];
							$r['in'][$rea['typeID']]['quantity'] = $rea['qty'];
							$r['in'][$rea['typeID']]['typeName'] = $rea['typeName'];
						}
					}
				}
			}
			foreach ($r['in'] as $key => $val) {
				if (isset($r['out'][$key])) {
					if ($r['out'][$key]['quantity'] == $val['quantity']) unset($r['out'][$key], $r['in'][$key]);
					elseif ($r['out'][$key]['quantity'] <= $val['quantity']) {
						$r['in'][$key]['quantity'] = $val['quantity'] - $r['out'][$key]['quantity'];
						unset($r['out'][$key]);
					}
					elseif ($r['out'][$key]['quantity'] >= $val['quantity']) {
						$r['out'][$key]['quantity'] = $r['out'][$key]['quantity'] - $val['quantity'];
						unset($r['in'][$key]);
					}
				}
			}
			
			foreach ($r as $key => $val) {
				foreach ($val as $key1 => $val1) {
					if ($key == 'out') {
						$r['plus'] += $val1['quantity'] * $price[$key1][0];
					} else {
						$r['minus'] += $val1['quantity'] * $price[$key1][1];
					}
				}
				
			}
			
			$str = "SELECT r.pos, p.typeID,	p.locationID, res.resourceTypeID, res.quantity,	price.buy_max_price AS price
					FROM {$this->_table['fsrtool_silos_reactors']} r
						INNER JOIN {$this->_table['fsrtool_pos']} p ON r.pos = p.moonID
						INNER JOIN {$this->_table['invcontroltowerresources']} res ON p.typeID = res.controlTowerTypeID
						LEFT JOIN {$this->_table['fsrtool_currentTypePrice']} price ON res.resourceTypeID = price.typeID
					WHERE r.corpID = {$this->_corpID} 
						AND	res.resourceTypeID IN (4051, 4312, 4247, 4246) 
						AND	price.region = 30000142
					GROUP BY r.pos";
			$res = $this->_db->query($str);
			$fuel=0;
			if( $res->num_rows >= 1 ) {
				while( $row = $res->fetch_assoc() ) {
					$fuel += $row['quantity'] * $row['price'];
				}
			}
			//$fuel = (10 * 40 * 12000);
			$r['fuel'] = number_format($fuel,'2',',','.');
			$r['gesammt'] = number_format($r['plus'] - $r['minus'] - $fuel,'2',',','.');
			$r['plus'] = number_format($r['plus'],'2',',','.');
			$r['minus'] = number_format($r['minus'],'2',',','.');
		}
		
		return $r;
		// return number_format($x,'2',',','.');
	}
	
	private function Sovereignty( $solarSystemID, $allyID ) {
		if ($allyID == 0)
			$this->_SovereigntyCache[ $solarSystemID.$allyID ] = false;
		else {
			if ( !isset( $this->_SovereigntyCache[ $solarSystemID.$allyID ] ) ) {
				$str = "SELECT * FROM ".db_tab_sovereignty." WHERE solarSystemID='".$solarSystemID."' AND allianceID='".$allyID."';";
				$result = $this->db->query( $str );
				if( $result->num_rows > 0 ) {
					$this->_SovereigntyCache[ $solarSystemID.$allyID ] = true;
				} else { 
					$this->_SovereigntyCache[ $solarSystemID.$allyID ] = false;
				}
				$result->close();
			}
		}
		return $this->_SovereigntyCache[ $solarSystemID.$allyID ];
	}
	
	private function towerresurce( $id ) {
		if ( !isset( $this->_towerresurceCache[ $id ] ) ) {
			$str = "SELECT * FROM ".db_tab_towerresources." WHERE controlTowerTypeID='{$id}';";
			$this->_towerresurceCache[ $id ] = $this->db->fetch_all( $str );
		}
		return $this->_towerresurceCache[ $id ];
	}



}

?>