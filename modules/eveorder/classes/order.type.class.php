<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class Type
{
	var $typeID;
	var $groupID;
	var $typeName;
	var $description;
	var $graphicID;
	var $radius;
	var $mass;
	var $volume;
	var $capacity;
	var $portionSize;
	var $raceID;
	var $basePrice;
	var $published;
	var $marketGroupID;
	var $chanceOfDuplicating;
	var $icon;
	var $iconOGB;
	var $price;
	var $fetched;
	var $categoryID;
	var $produces;
	var $producesCategory;
	var $techLevel = 1;
	//var $metaLVL;
	
	public function Type( $id=null, $world=null, $region='10000002' ) {
		$this->db = $world->db;
		$this->_table = $world->_table;
		if ( $id != null ) {
			#$res = $database->eveorder_getTypeByID($id,$region);
			$typeID = $this->db->escape( $id );
			$region = $this->db->escape( $region );
			
			$str = "SELECT it.*, ei.iconFile AS icon, ig.categoryID AS categoryID, bp.techLevel, IfNull(dmg.valueInt, dmg.valueFloat) itm_techlevel,
			(SELECT p.fetched FROM {$this->_table['fsrtool_currentTypePrice']} as p 
			 WHERE p.typeID = '".$typeID."' AND p.region = ".$region.") as fetched,  
			(SELECT p1.sell_median_price FROM {$this->_table['fsrtool_currentTypePrice']} as p1
			 WHERE p1.typeID = '".$typeID."' AND p1.region = ".$region.") as price
				  FROM {$this->_table['invtypes']} AS it 
				  LEFT JOIN {$this->_table['eveicons']} AS ei ON it.iconID = ei.iconID
				  LEFT JOIN {$this->_table['invgroups']} AS ig ON it.groupID = ig.groupID 
				  LEFT JOIN {$this->_table['invblueprinttypes']} AS bp ON it.typeID = bp.productTypeID
				  LEFT JOIN {$this->_table['dgmtypeattributes']} AS dmg ON it.typeID = dmg.typeID AND dmg.attributeID IN (633)
				  WHERE it.typeID = '".$typeID."';";
			$res = $this->db->query( $str );

			if ( $res->num_rows > 0 ) {
				$row = $res->fetch_array();
				$this->typeID = $id;
				$this->set( $row, $region ); 
				$res->close();
			}
			else {
				$this->typeID = "-1";
			}
		}
	}

	private function set( $row, $region ) {
		global $smarty;
		
		$this->groupID  = $row['groupID'];
		$this->typeName = $row['typeName'];
		$suchtext = array('<font size="14">', '</font>');
		if ($row['categoryID']=="9" or $_SESSION["chosenLanguage"]=="EN") {
			$txt = $row['description'];
			$txt = str_replace($suchtext, "", $txt);
			#$txt = htmlentities( str_replace(array('<br>','<br />'),"\n",$txt ) );
			#$txt = str_replace('&gt;', '>', $txt);
			#$txt = str_replace('&lt;', '<', $txt);
			#$txt = str_replace('’', "&rsquo;", $txt);
			$this->description = nl2br( $txt ) . '<br />';
			#$this->description = nl2br( htmlentities( $row['description'] ) ) . '<br />';
		}
		else {
			$str = "SELECT lang.text 
					FROM {$this->_table['trntranslations']} as lang 
					WHERE lang.keyID = '".$this->typeID."' 
					AND lang.tcID = 33 
					AND lang.languageID = 'DE';";
			$res = $this->db->query( $str );
			if ( $res->num_rows > 0 ) {
				$rowLang = $res->fetch_array();
				$txt = $rowLang['text'];
				$txt = str_replace($suchtext, "", $txt);
				#$txt = htmlentities( str_replace(array('<br>','<br />'),"\n",$txt) );
				#$txt = str_replace('&gt;', '>', $txt);
				#$txt = str_replace('&lt;', '<', $txt);
				#$txt = str_replace('’', "&rsquo;", $txt);
				$this->description = nl2br( $txt ) . '<br />';
				#$this->description = nl2br( $rowLang['text'] ) . '<br />';
				$res->close();
			}
			else {
				$txt = $row['description'];
				$txt = str_replace($suchtext, "", $txt);
				#$txt = htmlentities( str_replace(array('<br>','<br />'),"\n",$txt ) );
				#$txt = str_replace('&gt;', '>', $txt);
				#$txt = str_replace('&lt;', '<', $txt);
				#$txt = str_replace('’', "&rsquo;", $txt);
				$this->description = nl2br( $txt ) . '<br />';
			}
		}
		//$this->metaLVL = $row['metalvl'];
		$this->graphicID = $row['graphicID'];
		$this->radius = $row['radius'];
		$this->mass = $row['mass'];
		$this->volume = $row['volume'];
		$this->capacity = $row['capacity'];
		$this->portionSize = $row['portionSize'];
		$this->raceID = $row['raceID'];
		$this->basePrice = $row['basePrice'];
		$this->published = $row['published'];
		$this->marketGroupID = $row['marketGroupID'];
		$this->chanceOfDuplicating = $row['chanceOfDuplicating'];
		$this->techLevel = $row['techLevel'];
		$this->icon = $row['icon'];
		
		$size = 64;
		$dir = 'icons/Types/' . $this->typeID . '_' . $size . '.png';
		
		if ( is_file( $dir ) ) 
			$img = $dir;
		else {
			$img = 'icons/Icons/items/'.$row['icon'].'.png';
		}
		
		$it_name = $this->typeName;
		
		$smarty->assign('img', $img);
        //$smarty->assign('icon', $icon);
        $smarty->assign('name', $it_name);
        //$this->iconOGB = $smarty->fetch(TPL_DIR .'icon_64.tpl');
        $this->iconOGB = $smarty->fetch('file:['.ACTIVE_MODULE.']icon_64.tpl');
		
		$this->fetched = $row['fetched'];
		$this->price = $row['price'];
		$this->categoryID = $row['categoryID'];
		if ($this->categoryID == "9")
		{
			$this->produces = $this->getProducedType();
			$temp = new Type( $this->produces, $this );
			$this->producesCategory = $temp->categoryID;
			unset($temp);
		}
	}

	public function toArray() {
		$array = get_object_vars($this);
		foreach ( $array as $key => $val )
			if ( is_object($val) ) unset ( $array[ $key ] );
		return $array;
	}

	
	function getCurrentEvecentralPrice($region)
	{
		global $world,$database;
		$api = new Api();
		$api->setDebug(true);
		$api->setUseCache(false); // that's the default, done for testing purposes
		$api->setTimeTolerance(5); // also the default value
		
		$typeid[] = $this->typeID;  // multiples added by numberical sub array 
		$regionlimit[] = $region;

		if ($region == "0")
			$dataxml = $api->getMarketStat($typeid,null);			
		else
			$dataxml = $api->getMarketStat($typeid,null,$regionlimit);
		$data = MarketStat::getMarketStat($dataxml);
		$data[0]['region'] = $region;
		$result = $database->eveorder_saveCurrentPrice($data);
		unset($dataxml);
		return $data[0]['sell'];
		unset($data);
	}

	private function getProducedType() {
		$str = "SELECT productTypeID FROM {$this->_table['invblueprinttypes']} WHERE blueprintTypeID = '".$this->typeID."';";
		if ( $productTypeID = $this->db->fetch_one( $str, 'productTypeID' ) )
			return $productTypeID;
	}
}

?>