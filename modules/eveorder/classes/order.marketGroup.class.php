<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class MarketGroup
{
	var $marketGroupID;
	var $parentGroupID;
	var $marketGroupName;
	var $description;
	var $graphicID;
	var $hasTypes;
	var $icon;

	public function MarketGroup( $id=null, $world=null ) {
		$this->db = $world->db;
		$this->_table = $world->_table;
		if ( $id != null ) {
			$marketGroupID = $this->db->escape( $id );
			$str = "SELECT mg.*, ei.iconFile as icon 
					FROM {$this->_table['invmarketgroups']} mg
					LEFT JOIN {$this->_table['eveicons']} ei ON mg.iconID = ei.iconID 
					WHERE mg.marketGroupID = '".$marketGroupID."';";
			$res = $this->db->query( $str );
			if ( $res->num_rows > 0 ) {
				$row = $res->fetch_array();
				$this->marketGroupID = $id;
				$this->set($row);
			}
			$res->close();
		}
	}

	private function set( $row ) {
		$this->parentGroupID = $row['parentGroupID'];
		$this->marketGroupName = $row['marketGroupName'];
		$this->description = $row['description'];
		$this->graphicID = $row['iconID'];
		$this->hasTypes = $row['hasTypes'];
		$this->icon = '';
		if($row['icon']) {
			$pos = strpos($row['icon'], 'res:');
			if ($pos === false) {
				$size = 64;
				$imageFile = explode('_', $row['icon']);
				$imageFile[0] = preg_replace('/^0/', '', $imageFile[0]);
				$imageFile[1] = preg_replace('/^0/', '', $imageFile[1]);
				$imageFileName = $imageFile[0].'_'.$size.'_'.$imageFile[1].'.png';
				
				$this->icon = 'icons/Icons/items/' . $imageFileName;
			}
		} 
		if ( !is_file($this->icon) ) 
			$this->icon = 'icons/Icons/items/38_16_173.png';
		
	}

	public function toArray() {
		$array = get_object_vars($this);
		foreach ( $array as $key => $val )
			if ( is_object($val) ) unset ( $array[ $key ] );
		return $array;
	}

	public function getSubMarketGroups($open,$ebene) {
		$parentGroupID = $this->marketGroupID;
		$str = "SELECT marketGroupID 
				FROM {$this->_table['invmarketgroups']} 
				WHERE parentGroupID='".$parentGroupID."' 
				ORDER BY hasTypes, marketGroupName ASC;";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$array = array();
			while ( $row = $res->fetch_array() ) {
				$marketGroup = new MarketGroup( $row['marketGroupID'], $this );
				$temp = $marketGroup->toArray();
				$temp['ebene'] = $ebene;
				$newOpen = array();
				foreach ($open as $value) {
					$newOpen[] = $value;
					if ( $value == $temp['parentGroupID'] )
						break;
				}
				$newOpen[] = $row['marketGroupID'];
				$temp['open'] = implode(",",$newOpen);
				$array[] = $temp;
				if ( in_array($row['marketGroupID'],$open) ) {
					$ebene++;
					$temp = $marketGroup->getSubMarketGroups( $open, $ebene );
					$ebene--;
					if ($temp)
						$array = array_merge( $array, $temp );
				}
			}
			return $array;
		}
		else
			return false;
	}
}

?>