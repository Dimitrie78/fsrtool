<?php
defined('fsr_tool') or die;

class EveorderDatabase extends Database
{
	function EveorderDatabase()
	{
		$this->loadSQLStrings();
		$this->sqlstrings['eveorder'][0] = "INSERT INTO %tab_currentTypePrice% SET typeID='%typeID%', all_volume='%all_volume%', all_avg_price='%all_avg%', all_max_price='%all_max%', all_min_price='%all_min%', all_stddev_price='%all_stddev%', all_median_price='%all_median%', buy_volume='%buy_volume%', buy_avg_price='%buy_avg%', buy_max_price='%buy_max%', buy_min_price='%buy_min%', buy_stddev_price='%buy_stddev%', buy_median_price='%buy_median%', sell_volume='%sell_volume%', sell_avg_price='%sell_avg%', sell_max_price='%sell_max%', sell_min_price='%sell_min%', sell_stddev_price='%sell_stddev%', sell_median_price='%sell_median%', fetched='%fetched%',region='%region%';";
		$this->sqlstrings['eveorder'][1] = "UPDATE %tab_currentTypePrice% SET all_volume='%all_volume%', all_avg_price='%all_avg%', all_max_price='%all_max%', all_min_price='%all_min%', all_stddev_price='%all_stddev%', all_median_price='%all_median%', buy_volume='%buy_volume%', buy_avg_price='%buy_avg%', buy_max_price='%buy_max%', buy_min_price='%buy_min%', buy_stddev_price='%buy_stddev%', buy_median_price='%buy_median%', sell_volume='%sell_volume%', sell_avg_price='%sell_avg%', sell_max_price='%sell_max%', sell_min_price='%sell_min%', sell_stddev_price='%sell_stddev%', sell_median_price='%sell_median%', fetched='%fetched%' WHERE typeID='%typeID%' AND region='%region%';";
	}

	function eveorder_saveCurrentPrice($data)
	{
		$this->debug = 0;
		
		$changed = date("YmdHis");
		$sqlstring = "SELECT * FROM ".db_tab_currentTypePrice." WHERE typeID = '".$data[0]['typeID']."' AND region='".$data[0]['region']."';";
		$result = $this->doQuery($sqlstring,"Database::saveCorpChange");
		if ($this->get_num_rows($result) > 0)
			$sqlstring = $this->sqlstrings['eveorder'][1];
		else
			$sqlstring = $this->sqlstrings['eveorder'][0];
		$sqlstring = ereg_replace("%tab_currentTypePrice%", db_tab_currentTypePrice, $sqlstring);
		foreach ($data[0] as $key=>$value)
		{
			if(!is_array($value))
			{
				$sqlstring = ereg_replace("%".$key."%", $value, $sqlstring);
			}
			if(is_array($value))
			{
				foreach($value as $key1=>$value1)
				{
					$sqlstring = ereg_replace("%".$key."_".$key1."%", $value1, $sqlstring);
				}
			}
		}
		$sqlstring = ereg_replace("%fetched%", $changed, $sqlstring);
		$result = $this->doQuery($sqlstring,"Database::saveCorpChange");
		return $result;
	}

	function eveorder_getProductTypeForTypeID($typeID)
	{
		$typeID = $this->escape($typeID);
		$sqlstring = "SELECT productTypeID FROM ".db_tab_invblueprinttypes." WHERE blueprintTypeID = '".$typeID."';";
		$result = $this->doQuery($sqlstring,"Database::getProducedTypeForItemID");
		return $result;
	}

	function eveorder_getLanguage($typeID)
	{
		$typeID = $this->escape($typeID);
		$sqlstring = "SELECT lang.text 
					FROM ".db_tab_trntranslations." as lang 
					WHERE lang.keyID = '".$typeID."' 
					AND lang.tcID = 33 
					AND lang.languageID = 'DE';";
		$result = $this->doQuery($sqlstring,"Database::getTypeByID");
		return $result;
	}
	
	function eveorder_getTypeNameByID($typeID)
	{
		$typeID = $this->escape($typeID);
		$sqlstring = "SELECT typeID, typeName FROM ".db_tab_invtypes." WHERE typeID='".$typeID."';";
		$result = $this->doQuery($sqlstring,"Database::getTypeByID");
		return $result;		
	}
	
	function eveorder_getTypeByID($typeID,$region)
	{
		$typeID = $this->escape($typeID);
		$region = $this->escape($region);
		
		$sqlstring = "SELECT it.*, eg.icon, ig.categoryID AS categoryID, bp.techLevel,
		(SELECT p.fetched FROM ".db_tab_currentTypePrice." as p 
		 WHERE p.typeID = '".$typeID."' AND p.region = ".$region.") as fetched,  
		(SELECT p1.sell_median_price FROM ".db_tab_currentTypePrice." as p1
		 WHERE p1.typeID = '".$typeID."' AND p1.region = ".$region.") as price
					  FROM ".db_tab_invtypes." AS it 
					  LEFT JOIN ".db_tab_evegraphics." AS eg ON it.graphicID = eg.graphicID 
					  LEFT JOIN ".db_tab_invgroups." AS ig ON it.groupID = ig.groupID 
					  LEFT JOIN ".db_tab_invblueprinttypes." AS bp ON it.typeID = bp.productTypeID
					  WHERE it.typeID = '".$typeID."';";
		$result = $this->doQuery($sqlstring,"Database::getTypeByID");
		return $result;
	}

	function eveorder_getTopLevelMarketGroups()
	{
		$sqlstring = "SELECT marketGroupID 
					FROM ".db_tab_invmarketgroups." 
					WHERE parentGroupID=0 or parentGroupID IS NULL 
					ORDER BY hasTypes, marketGroupName ASC;";
		$result = $this->doQuery($sqlstring,"Database::getTopLevelMarketGroups");
		return $result;
	}

	function eveorder_getMarketGroup($marketGroupID)
	{
		$marketGroupID = $this->escape($marketGroupID);
		$sqlstring = "SELECT mg.*, eg.icon as icon 
					FROM ".db_tab_invmarketgroups." mg
					LEFT JOIN ".db_tab_evegraphics." eg ON mg.graphicID = eg.graphicID 
					WHERE mg.marketGroupID = '".$marketGroupID."';";
		$result = $this->doQuery($sqlstring,"Database::getMarketGroup");
		return $result;
	}

	function eveorder_getSubMarketGroupIDs($parentGroupID)
	{
		$parentGroupID = $this->escape($parentGroupID);
		$sqlstring = "SELECT marketGroupID 
					FROM ".db_tab_invmarketgroups." 
					WHERE parentGroupID='".$parentGroupID."' 
					ORDER BY hasTypes, marketGroupName ASC;";
		$result = $this->doQuery($sqlstring,"Database::getSubMarketGroupIDs");
		return $result;
	}

	function eveorder_getIconForMarketGroup($marketGroupID)
	{
		$marketGroupID = $this->escape($marketGroupID);
		$sqlstring = "SELECT icon 
					FROM ".db_tab_evegraphics." as eg
					LEFT JOIN ".db_tab_invgroups." ig ON ig.graphicID = eg.graphicID 
					LEFT JOIN ".db_tab_invtypes." it ON ig.groupID = it.groupID 
					WHERE it.marketGroupID='".$marketGroupID."';";
		$result = $this->doQuery($sqlstring,"Database::getIconForMarketGroup");
		if ($this->get_num_rows($result) > 0)
		{
			$row = $this->fetch_array($result);
			return $row[0];
		}
		else return false;
	}

	function eveorder_getTypeIDsByMarketGroup($marketGroupID)
	{
		$marketGroupID = $this->escape($marketGroupID);
		$sqlstring = "SELECT typeID FROM ".db_tab_invtypes." WHERE marketGroupID='".$marketGroupID."' ORDER BY typeName;";
		$result = $this->doQuery($sqlstring,"Database::getTypeIDByMarketGroup");
		return $result;
	}
	
	function eveorder_saveOrder($user,$typeID,$amount,$corp=false,$corpid="")
	{
		//$this->debug = 1;
		if(!$corp && !is_array($corp)) {
			$corp = array();
		}
		foreach($typeID as $key => $value){
			if($amount[$key]!=0 and $amount[$key]!=""){
				if(is_numeric(str_replace('.', '', $amount[$key]))) {
					if( $corp[$value] || ($corp && !is_array($corp)) ) {
						$check = "SELECT * FROM ".db_tab_user_types." 
								  WHERE status=0 AND typeID='".$typeID[$key]."' AND corpID='".$corpid."' AND user='".$user."';";
						$checkresult = $this->doQuery($check,"Database::eveorder_saveOrder");
						if($this->get_num_rows($checkresult) > 0) {
							$row = $this->fetch_assoc($checkresult);
							$sqlstring = "UPDATE ".db_tab_user_types."
										  SET amount = '".(str_replace('.', '', $amount[$key]) + $row['amount'])."',
											  lastchange='".time()."'
										  WHERE id = '".$row['id']."';";
							$result = $this->doQuery($sqlstring,"Database::eveorder_saveOrder");
						} else {
							$sqlstring = "INSERT INTO ".db_tab_user_types."
										  SET user		='".$user."',
											  typeID	='".$typeID[$key]."',
											  amount	='".str_replace('.', '', $amount[$key])."',
											  timestamp	='".time()."',
											  status	='0',
											  lastchange='".time()."',
											  corpid	='".$corpid."';";
							$result = $this->doQuery($sqlstring,"Database::eveorder_saveOrder");
						}
					} else {
						$check = "SELECT * FROM ".db_tab_user_types." 
								  WHERE status=0 AND typeID='".$typeID[$key]."' AND corpID='' AND user='".$user."';";
						$checkresult = $this->doQuery($check,"Database::eveorder_saveOrder");
						if($this->get_num_rows($checkresult) > 0) {
							$row = $this->fetch_assoc($checkresult);
							$sqlstring = "UPDATE ".db_tab_user_types."
										  SET amount = '".(str_replace('.', '', $amount[$key]) + $row['amount'])."',
											  lastchange='".time()."'
										  WHERE id = '".$row['id']."';";
							$result = $this->doQuery($sqlstring,"Database::eveorder_saveOrder");
						} else {
							$sqlstring = "INSERT INTO ".db_tab_user_types."
										  SET user		='".$user."',
											  typeID	='".$typeID[$key]."',
											  amount	='".str_replace('.', '', $amount[$key])."',
											  timestamp	='".time()."',
											  status	='0',
											  lastchange='".time()."',
											  corpid	='';";
							$result = $this->doQuery($sqlstring,"Database::eveorder_saveOrder");
						}
						$this->free_result($checkresult);
					}
				}
			}
		}
		
		if($result) return true;
		else 		return false;
	}

	function eveorder_delOrder($orderID)
	{
		$orderID = $this->escape($orderID);
		$sqlstring = "DELETE FROM ".db_tab_user_types." WHERE id=".$orderID.";";
		$result = $this->doQuery($sqlstring,"Database::eveorder_delOrder");
		return $result;
	}

	function eveorder_getOrdersFromUser($user)
	{
		$user = $this->escape($user);
		$sqlstring = "SELECT utypes.*, u2.username as supplierName, u2.id as supplierid 
		FROM ".db_tab_user_types." as utypes 
		LEFT JOIN ".db_tab_user." as u2 ON utypes.supplier = u2.id 
		WHERE user='".$user."' ORDER BY timestamp;";
		$result = $this->doQuery($sqlstring,"Database::getOrdersFromUser");
		return $result;
	}
	
	function eveorder_getOrdersFromCorp($corpID)
	{
		$corpID = $this->escape($corpID);
		
		$sqlstring = "SELECT utypes.*, u1.username, u2.username as supplierName, u2.id as supplierid 
						FROM ".db_tab_user_types." as utypes
						LEFT JOIN ".db_tab_user." as u1 ON utypes.user = u1.id 
						LEFT JOIN ".db_tab_user." as u2 ON utypes.supplier = u2.id 
						WHERE utypes.corpid='".$corpID."' or utypes.user = '' 
						ORDER BY timestamp DESC;";
		$result = $this->doQuery($sqlstring,"Database::getOrdersFromUser");
		return $result;
	}

	function eveorder_doSearch($string)
	{
		$string = $this->escape($string);
		
		$sqlstring = "SELECT typeID FROM ".db_tab_invtypes." WHERE typeName LIKE '".$string."%' AND marketGroupID != '0' LIMIT 10;";
		$result = $this->doQuery($sqlstring,"Database::eveorder_doSearch");
//		$this->debug = 0;
		return $result;
	}
	
	function eveorder_getMetaLvl($typeID) {
		$typeID = $this->escape($typeID);
		
		$sqlstring = "SELECT IFNULL(valueInt,valueFloat) as metalvl FROM ".db_tab_dgmtypeattributes." 
					  WHERE typeID='".$typeID."' AND attributeID=633;";
		$result = $this->doQuery($sqlstring,"Database::eveorder_getMetaLvl");
		if($this->get_num_rows($result) > 0) {
			$row = $this->fetch_assoc($result);
			return $row['metalvl'];
		} else {
			$sqlstring = "SELECT ".db_tab_invmetatypes.".metaGroupID FROM ".db_tab_invmetatypes." 
						  INNER JOIN ".db_tab_invtypes." ON ".db_tab_invtypes.".typeID = ".db_tab_invmetatypes.".typeID
						  WHERE ".db_tab_invmetatypes.".metaGroupID > 1
						  AND ".db_tab_invtypes.".typeID = '".$typeID."';";
			$result = $this->doQuery($sqlstring,"Database::eveorder_getMetaLvl");
			if($this->get_num_rows($result) > 0) {
				return 6;
			} else {
				$sqlstring = "SELECT ".db_tab_invgroups.".categoryID 
						  FROM ".db_tab_invgroups."
						  INNER JOIN ".db_tab_invtypes." ON ".db_tab_invtypes.".groupID = ".db_tab_invgroups.".groupID
						  WHERE ".db_tab_invtypes.".typeID='".$typeID."';";
				$result = $this->doQuery($sqlstring,"Database::eveorder_getMetaLvl");
				if($this->get_num_rows($result) > 0) {
					$cat = $this->fetch_assoc($result);
					if($cat['categoryID'] == 16) { // Skills
						return -1;
					} elseif($cat['categoryID'] == 4) { // Minerals
						return 'mins';
					} elseif($cat['categoryID'] == 20) { // Imps
						return 6;
					} else {
						return 0;
					}
				}
			}
		}
	}
	
	function eveorder_getOpenOrders($corp,$status=0,$orderby)
	{
		$corp    = $this->escape($corp);
		$status  = $this->escape($status);
		
		switch($orderby)
		{
			case 'username':
				$sort = 'u1.username, ut.timestamp ASC';
			break;
			
			default:
			case 'date':
				$sort = 'ut.timestamp ASC, u1.username';
			break;
			
			case 'typeID':
				$sort = 't.typeName, ut.timestamp ASC';
			break;
		}
		
		$sqlstring = "SELECT
					  t.typeName, ut.*, u1.username, u1.id AS user, u2.username
					  AS supplierName, u2.id AS supplierid
					FROM
					  ".db_tab_user_types." AS ut LEFT JOIN
					  ".db_tab_user." AS u1 ON ut.user = u1.id LEFT JOIN
					  ".db_tab_user." AS u2 ON ut.supplier = u2.id INNER JOIN
					  ".db_tab_invtypes." AS t ON ut.typeID = t.typeID
					WHERE
					  u1.corp = '".$corp."' AND
					  ut.status = '".$status."'
					ORDER BY
					  ".$sort.";";
		$result = $this->doQuery($sqlstring,"Database::eveorder_getOpenOrders");
		return $result;
	}
	
	function eveorder_getOpenOrdersUserIds($corp,$status)
	{
		$corp   = $this->escape($corp);
		$status = $this->escape($status);
		
		$sqlstring = "SELECT orders.user, Count(orders.user) anz 
						FROM ".db_tab_user_types." orders 
						INNER JOIN ".db_tab_user." u ON orders.user = u.id 
						WHERE orders.status='".$status."' AND u.corp='".$corp."' 
						GROUP BY orders.user;";
		$result = $this->doQuery($sqlstring,"Database::getOrdersFromUser");
		return $result;
	}

	function eveorder_updateOrder($orderid,$status,$supplier,$comment,$check)
	{
		$status   = $this->escape($status);
		$supplier = $this->escape($supplier);
		
		if(is_array($orderid)){
			foreach($orderid as $key => $value){
				if(isset($check[$value]) and $check[$value]==1) {
					$sql="SELECT status FROM ".db_tab_user_types." WHERE status='".$status."' AND comment='".$comment[$key]."' AND id='".$value."' ;";
					$order=$this->doQuery($sql,"Database::eveorder_updateOrder");
					if($this->get_num_rows($order) < 1){
						$sqlstring = "UPDATE ".db_tab_user_types."
									  SET status     = '".$status."',
									      lastchange = '".time()."',
										  supplier   = '".$supplier."',
										  comment    = '".$comment[$key]."' 
									  WHERE id = '".$orderid[$key]."';";
						$result = $this->doQuery($sqlstring,"Database::eveorder_updateOrder");
					}
				}
			}
		}
		return $result;
	}
	
	function eveorder_addToFavorites($typeID,$groupID)
	{
		global $User,$Messages,$language;
		$typeID  = $this->escape($typeID);
		$groupID = $this->escape($groupID);
				
		$sqlstring = "SELECT * FROM ".db_tab_favorits." WHERE userID=".$User->id." AND typeID=".$typeID.";";
		$result = $this->doQuery($sqlstring,"Database::eveorder_addToFavorites");
		if ($this->get_num_rows($result) > 0)
		{
			$Messages->addwarning($language['not_added_to_favorites']);
			return false;
		}
		else
		{
			$sqlstring = "INSERT INTO ".db_tab_favorits." SET userID=".$User->id.", typeID=".$typeID.", groupID=".$groupID.";";
			$result = $this->doQuery($sqlstring,"Database::eveorder_addToFavorites");
			$Messages->addconfirm($language['added_to_favorites']);
			return $result;
		}
	}
	
	function eveorder_getFavorites()
	{
		global $User;
		$sqlstring = "SELECT typeID FROM ".db_tab_favorits." WHERE userID=".$User->id." ORDER BY groupID,typeID;";
		$result = $this->doQuery($sqlstring,"Database::eveorder_getFavorites");
		return $result;
	}
	
	function eveorder_delFromFavorites($typeID)
	{
		global $User;
		$typeID = $this->escape($typeID);
		
		$sqlstring = "DELETE FROM ".db_tab_favorits." WHERE userID=".$User->id." AND typeID=".$typeID.";";
		$result = $this->doQuery($sqlstring,"Database::eveorder_delFromFavorites");
		return $result;
	}
	
	function eveorder_delallDeliverys($status=4)
	{
		global $User;
		$status = $this->escape($status);
		$sqlstring = "DELETE FROM ".db_tab_user_types." WHERE status=".$status." AND user='".$User->id."';";
		$result = $this->doQuery($sqlstring,"Database::eveorder_delallDeliverys");
		return $result;
	}
	
	function eveorder_stats($order = 'name') {
		global $User;
		$order = $this->escape($order);
		
		switch ($order) {
			case '1':
			default:
				$orderby = 'ORDER BY name ASC'; 
			break;
			case '2':
				$orderby = 'ORDER BY name DESC'; 
			break;
			case '3':
				$orderby = 'ORDER BY quantity ASC'; 
			break;
			case '4':
				$orderby = 'ORDER BY quantity DESC'; 
			break;
		}
		$query = ("SELECT i.typeName as name, Sum(o.amount) AS quantity, (p.sell_min_price * Sum(o.amount)) as price
				FROM stsys_eveorder_user u 
				INNER JOIN stsys_eveorder_user_types o ON u.id = o.user 
				INNER JOIN invtypes i ON o.typeID = i.typeID 
				INNER JOIN stsys_eveorder_currenttypeprice p ON i.typeID = p.typeID
				WHERE u.corp = '".$User->corp."' 
				  AND p.region = 1
				GROUP BY i.typeName
				$orderby;");
		$result = $this->doQuery($query,"Database::eveorder_stats");
		if ($this->get_num_rows($result) > 0) {
			$return = array();
			while ($row = $this->fetch_assoc($result)) {
				if ($row) $return[] = $row;
			}
		}
		$this->free_result($result);
		return $return;
	}
}

?>