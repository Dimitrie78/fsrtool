<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class eveorderWorld extends world
{
	
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		
		$this->_table['fitting'] = "fsrtool_fitting";
		$this->_table['fitting_module'] = "fsrtool_fitting_module";
		$this->_table['eveorder_user_types'] = "fsrtool_eveorder_user_types";
		$this->_table['eveorder_user_types_systems'] = "fsrtool_eveorder_user_types_systems";
		$this->_table['eveorder_favorits'] = "fsrtool_eveorder_favorits";
		$this->_table['eveorder_skills'] = "fsrtool_skills";
		$this->_table['eveorder_shipvolume'] = "fsrtool_shipvolume";
		$this->_table['eveorder_price'] = "fsrtool_currenttypeprice";
		
		$this->_table['eveorder_shipreplacement'] = "fsrtool_eveorder_shipreplacement";
		$this->_table['eveorder_locations'] = "fsrtool_eveorder_locations";
		$this->_table['eveorder_cachetime'] = "fsrtool_eveorder_cachetime";
		#echo '<pre>'; print_r( $this ); die;
	}
	
	public function eveorder_getOpenOrders($corpid,$status="",$orderby="") {
		
		$corp    = $this->db->escape($corpid);
		$status  = $this->db->escape($status);
		
		switch($orderby){
			case 'username':
				$sort = 'username, timestamp ASC';
			break;
			
			default:
			case 'date':
				$sort = 'timestamp ASC, username';
			break;
			
			case 'typeID':
				$sort = 'typeName, timestamp ASC';
			break;
		}
		$str = "SELECT
				  t.typeName, IFNULL(sv.volume,t.volume) as volume, IFNULL(sv.volume,NULL) as ship, ut.*, u1.username as username, u1.charID AS user, u2.username
				  AS supplierName, u2.charID AS supplierid, price.buy_percentile_price AS price
				FROM
				  {$this->_table['eveorder_user_types']} AS ut LEFT JOIN
				  {$this->_table['fsrtool_user']} AS u1 ON ut.user = u1.charID LEFT JOIN
				  {$this->_table['fsrtool_user']} AS u2 ON ut.supplier = u2.charID INNER JOIN
				  {$this->_table['invtypes']} AS t ON ut.typeID = t.typeID
				LEFT JOIN {$this->_table['eveorder_shipvolume']} AS sv ON t.groupID = sv.groupID
				LEFT JOIN {$this->_table['eveorder_price']} price ON ut.typeID = price.typeID AND region = 30000142
				WHERE
				  u1.corpID = '".$corp."' AND
				  ut.status = '".$status."' AND 
				  ut.deleted = 0
				
				  UNION
				SELECT
				  t.typeName, IFNULL(sv.volume,t.volume) as volume, IFNULL(sv.volume,NULL) as ship, ut.*, u1.charName as username, u1.charID AS user, u2.username
				  AS supplierName, u2.charID AS supplierid, price.buy_percentile_price AS price
				FROM
				  {$this->_table['eveorder_user_types']} AS ut 
				LEFT JOIN {$this->_table['fsrtool_alts']} AS u1 ON ut.user = u1.charID
				LEFT JOIN {$this->_table['fsrtool_user']} AS u2 ON ut.supplier = u2.charID 
				LEFT JOIN {$this->_table['invtypes']} AS t ON ut.typeID = t.typeID
				LEFT JOIN {$this->_table['eveorder_shipvolume']} AS sv ON t.groupID = sv.groupID
				LEFT JOIN {$this->_table['eveorder_price']} price ON ut.typeID = price.typeID AND region = 30000142
				WHERE
				  u1.corpID = '".$corp."' AND
				  ut.status = '".$status."' AND 
				  ut.deleted = 0
				ORDER BY
				  ".$sort.";";
		
		$res = $this->db->query( $str );
		
		if ( $res->num_rows > 0 ) {
			$return = array(); 
			while ( $row = $res->fetch_array() ) {
				$metaLvl = $this->eveorder_getMetaLvl( $row['typeID'] );
				$array['metaLVL']      = $metaLvl;
				$array['typeID']       = $row['typeID'];
				$array['typeName']     = $row['typeName'];
				$array['username']     = $row['username'];
				$array['userid']       = $row['user'];		
				$array['timestamp']    = $row['timestamp'];
				$array['lastchange']   = $row['lastchange'];
				$array['id']       	   = $row['id'];
				$array['comment']      = $row['comment'];
				$array['supplierName'] = $row['supplierName'];
				$array['amount']       = $row['amount'];
				$array['status']       = $row['status'];
				$array['corpid']       = $row['corpid'];
				$array['targetSys']	   = $row['target'];
				$array['volume']	   = $row['volume'];
				$array['ship']	  	   = $row['ship'];
				$array['price']	  	   = $row['price'];
				$return[] = $array;
			}
			$res->close();
			return $return;
		}
	}

	function eveorder_getOpenOrdersUserIds($corpid,$status="") {
		$corp   = $this->db->escape($corpid);
		$status = $this->db->escape($status);
		
		$str = "SELECT orders.user, Count(orders.user) anz 
				FROM {$this->_table['eveorder_user_types']} orders 
				INNER JOIN {$this->_table['fsrtool_user']} u ON orders.user = u.charID 
				WHERE orders.status='".$status."' AND u.corp='".$corp."' 
				GROUP BY orders.user;";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$return = array();
			while ( $row = $res->fetch_array() ) {
				$array['userid'] = $row['user'];
				$array['anz'] = $row['anz'];
				$return[] = $array;
			}
			$res->close();
			return $return;
		}
	}

	public function eveorder_doSearch($string) {
		$string = $this->db->escape($string);
		$str = "SELECT typeID FROM {$this->_table['invtypes']} WHERE typeName LIKE '".$string."%' LIMIT 10;";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$return = "";
			while ( $row = $res->fetch_assoc() ) {
				$return .= $row['typeID'].",";
			}
			$return = substr($return,0,-1);
			$res->close();
			return $return;
		}
	}

	public function eveorder_getFavorits() {
		$str = "SELECT typeID FROM {$this->_table['eveorder_favorits']} WHERE userID=".$this->User->charID." ORDER BY groupID,typeID;";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$return = "";
			while ( $row = $res->fetch_array() ) {
				$return .= $row['typeID'].",";
			}
			$res->close();
			$return = substr($return,0,-1);
			return $return;
		}
		
	}
	
	public function eveorder_getSearchResult($ids) {
		if ($ids != "") {
			$ids = explode(",",$ids);
			$return = array();
			foreach ($ids as $id) {
				$type = new Type( $id, $this );
				$return[] = $type->toArray();
			}
			return $return;
		}
	}

	public function eveorder_getMyOrders( $userID=0 ) {
		if ( $userID == 0 ) 
			$userID = $this->User->id;
		$str = "SELECT utypes.*, u2.username as supplierName, u2.charID as supplierid 
			FROM {$this->_table['eveorder_user_types']} as utypes 
			LEFT JOIN {$this->_table['fsrtool_user']} as u2 ON utypes.supplier = u2.charID 
			WHERE user='".$userID."' AND deleted=0 ORDER BY timestamp;";
		$res = $this->db->query( $str );

		if ( $res->num_rows > 0 ) {
			$return = array();
			while ( $row = $res->fetch_array() ) {
				$type = new Type( $row['typeID'], $this );
				$array = $type->toArray();
				$array['amount']       = $row['amount'];
				$array['lastchange']   = $row['lastchange'];
				$array['status'] 	   = $row['status'];
				$array['timestamp']    = $row['timestamp'];
				$array['id'] 		   = $row['id'];
				$array['comment']      = htmlentities($row['comment']);
				$array['supplierName'] = $row['supplierName'];
				$array['corpID'] 	   = $row['corpid'];
				$array['targetSys']	   = $row['target'];
				$return[] = $array;
			}
			$res->close();
			return $return;
		}
	}
	
	public function eveorder_getCorpOrders() {
		$str = "SELECT utypes.*, u1.username, u2.username as supplierName, u2.id as supplierid 
				FROM {$this->_table['eveorder_user_types']} as utypes
				LEFT JOIN {$this->_table['fsrtool_user']} as u1 ON utypes.user = u1.id 
				LEFT JOIN {$this->_table['fsrtool_user']} as u2 ON utypes.supplier = u2.id 
				WHERE utypes.corpid='".$this->User->corpID."' or utypes.user = '' 
				ORDER BY timestamp DESC;";
		$res = $this->query( $str );
		
		if ( $res->num_rows > 0 ) {
			$return = array();
			while ( $row = $res->fetch_array() ) {
				$type = new Type( $row['typeID'], $this );
				$array = $type->toArray();
				$array['amount']      	= $row['amount'];
				$array['lastchange'] 	= $row['lastchange'];
				$array['status'] 		= $row['status'];
				$array['timestamp'] 	= $row['timestamp'];
				$array['id'] 			= $row['id'];
				$array['comment'] 		= $row['comment'];
				$array['supplierName'] 	= $row['supplierName'];
				$array['username'] 		= $row['username'];
				$return[] = $array;
			}
			$res->close();
			return $return;
		}
	}

	public function eveorder_getMarket($open) {
		$str = "SELECT marketGroupID 
				FROM {$this->_table['invmarketgroups']} 
				WHERE parentGroupID=0 or parentGroupID IS NULL 
				ORDER BY hasTypes, marketGroupName ASC;";
		$res = $this->db->query( $str );
		
		$ebene = 0;
		if ( $res->num_rows > 0 ) {
			$array = array();
			while ( $row = $res->fetch_array() )	{
				$marketGroup = new MarketGroup( $row['marketGroupID'], $this );
				$temp = $marketGroup->toArray();
				$temp['ebene'] = $ebene;
				$newOpen = array();
				$altOpen = array();
				$altOpen[] = 0;
				$altOpen[] = $row['marketGroupID'];
				foreach ( $open as $value ) {
					$newOpen[] = $value;
					if ( $value == $temp['parentGroupID'] )
						break;
				}
				$newOpen[] = $row['marketGroupID'];
				$temp['altopen'] = implode(",",$altOpen);
				$temp['open'] = implode(",",$newOpen);
				$array[] = $temp;
				if ( in_array($row['marketGroupID'], $open) ) {
					$ebene++;
					$temp = $marketGroup->getSubMarketGroups( $open, $ebene );
					$ebene--;
					if ( $temp )
						$array = array_merge( $array, $temp );
				}
			}
			$res->close();
			return $array;
		}
	}

	public function eveorder_getTypesByMarketCategory( $marketGroupID ) {
		$marketGroupID = $this->db->escape( $marketGroupID );
		$str = "SELECT typeID FROM {$this->_table['invtypes']} WHERE marketGroupID='".$marketGroupID."' ORDER BY typeName;";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$return = array();
			$i=0;
			while ( $row = $res->fetch_array() ) {
				$type = new Type( $row['typeID'], $this );
				$return[$i] = $type->toArray();
				if ( $type->fetched < (time()-(60*60*24)) )
					$ids[] = $type->typeID;
				$i++;
			}
			if ( isset($ids) && is_array($ids) ) {
				if ($prices = $this->getCurrentEvecentralPrice(30000142,$ids)) {
					foreach ( $return as $key => $type ) {
						$return[$key]['fetched'] = $prices['date'];
						$return[$key]['price'] = $prices[$type['typeID']];
					}
				}
			}
			#print_r($prices); die;
			return $return;
		}
	}
	
	private function getCurrentEvecentralPrice( $region, array $ids) {
		global $parms;
		unset($parms['main']['host']);
		$ale = AleFactory::getEvECentral($parms);
		
		if ($region == "0")
			$params = array('typeid'=>$ids);			
		else
			$params = array('typeid'=>$ids, 'usesystem'=>$region);	
		try {
			$xml = $ale->marketstat( $params );
			
			$insert = "REPLACE INTO %tab_currentTypePrice% SET 
				typeID='%typeID%', 
				all_volume='%all_volume%', 
				all_avg_price='%all_avg%', 
				all_max_price='%all_max%', 
				all_min_price='%all_min%', 
				all_stddev_price='%all_stddev%', 
				all_median_price='%all_median%', 
				all_percentile_price='%all_percentile%', 
				buy_volume='%buy_volume%', 
				buy_avg_price='%buy_avg%', 
				buy_max_price='%buy_max%', 
				buy_min_price='%buy_min%', 
				buy_stddev_price='%buy_stddev%', 
				buy_median_price='%buy_median%', 
				buy_percentile_price='%buy_percentile%', 
				sell_volume='%sell_volume%', 
				sell_avg_price='%sell_avg%', 
				sell_max_price='%sell_max%', 
				sell_min_price='%sell_min%', 
				sell_stddev_price='%sell_stddev%', 
				sell_median_price='%sell_median%', 
				sell_percentile_price='%sell_percentile%', 
				fetched='%fetched%',
				region='%region%';";
			
			$changed = time();
			$return = array('date' => $changed);
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
				$return[(int)$type->attributes()->id] = (float)$type->buy->percentile;
				$this->db->exec_query( $str );
				#break;
			}
		
			return $return;
		} catch (Exception $e) {
			$this->db->msg->addwarning('eve-central.com failed...');
			return false;
		}
	}
	
	public function eveorder_getCorps($allyID){
		$res = $this->db->query("SELECT * FROM {$this->_table['fsrtool_corps']} WHERE ally={$allyID} ORDER BY name;");
		$corps=array();
		while ( $row = $res->fetch_array() ){
			if ($row) $corps[$row['id']] = $row['name'];
		}
		$res->close();
		return $corps;
	}
	
	private function eveorder_getMetaLvl($typeID) {
		$typeID = $this->db->escape($typeID);
		
		$sqlstring = "SELECT IFNULL(valueInt,valueFloat) as metalvl FROM {$this->_table['dgmtypeattributes']} 
					  WHERE typeID='".$typeID."' AND attributeID=633;";
		$res = $this->db->query( $sqlstring );
		if ( $res->num_rows > 0 ) {
			$row = $res->fetch_assoc();
			return $row['metalvl'];
		} else {
			$sqlstring = "SELECT m.metaGroupID FROM {$this->_table['invmetatypes']} m
						  INNER JOIN {$this->_table['invtypes']} ON {$this->_table['invtypes']}.typeID = m.typeID
						  WHERE m.metaGroupID > 1
						  AND {$this->_table['invtypes']}.typeID = '".$typeID."';";
			$res = $this->db->query( $sqlstring );
			if( $res->num_rows > 0 ) {
				return 6;
			} else {
				$sqlstring = "SELECT {$this->_table['invgroups']}.categoryID 
						  FROM {$this->_table['invgroups']}
						  INNER JOIN {$this->_table['invtypes']} ON {$this->_table['invtypes']}.groupID = {$this->_table['invgroups']}.groupID
						  WHERE {$this->_table['invtypes']}.typeID='".$typeID."';";
				$res = $this->db->query( $sqlstring );
				if( $res->num_rows > 0 ) {
					$cat = $res->fetch_assoc();
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
	
	public function eveorder_stats($order = 'name') {
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
		$query = ("SELECT i.typeID, i.typeName as name, Sum(o.amount) AS quantity, (p.buy_percentile_price * Sum(o.amount)) as price, p.fetched
				FROM {$this->_table['fsrtool_user']} u 
				INNER JOIN {$this->_table['eveorder_user_types']} o ON u.charID = o.user 
				INNER JOIN {$this->_table['invtypes']} i ON o.typeID = i.typeID 
				LEFT JOIN {$this->_table['fsrtool_currentTypePrice']} p ON i.typeID = p.typeID AND p.region = 30000142
				WHERE u.corpID = '".$this->User->corpID."' 
				  /*AND p.region = 10000002*/
				GROUP BY i.typeName
				$orderby;");
		$res = $this->db->query( $query );
		if ( $res->num_rows > 0 ) {
			$return = array();
			while ( $row = $res->fetch_assoc() ) {
				$return[] = $row;
				if ( $row['fetched'] < (time()-(60*60*24)) )
					$ids[] = $row['typeID'];
			}
		}
		if ( isset($ids) && is_array($ids) ) {
			if(count($ids) > 30) {
				$a = ceil(count($ids)/30);
				$new = array_chunk($ids,$a);
				foreach($new as $next)
					$prices = $this->getCurrentEvecentralPrice(30000142,$next);
			} else $prices = $this->getCurrentEvecentralPrice(30000142,$ids);
		}
		$res->close();
		return $return;
	}
	
	public function eveorder_stat() {
		$return = array();
		$strCorp = "SELECT sum(test.price) price, test.datum FROM (
			SELECT
			  Sum(o.amount) AS quantity,
			  (p.buy_percentile_price * Sum(o.amount)) AS price,
			  p.fetched,
			  From_UnixTime(o.timestamp, '%Y-%m') AS datum
			FROM
			  {$this->_table['eveorder_user_types']} o
			  LEFT JOIN {$this->_table['fsrtool_currentTypePrice']} p ON o.typeID = p.typeID AND p.region = 30000142
			WHERE
			  o.corpid = '{$this->User->corpID}'  
			GROUP BY
			  o.typeID) as test
			GROUP BY
			  test.datum;";
		
		$strUser = "SELECT sum(test.price) price, test.datum FROM (
			SELECT
			  Sum(o.amount) AS quantity,
			  (p.buy_percentile_price * Sum(o.amount)) AS price,
			  p.fetched,
			  From_UnixTime(o.timestamp, '%Y-%m') AS datum
			FROM
			  {$this->_table['fsrtool_user']} u 
			  INNER JOIN {$this->_table['eveorder_user_types']} o ON u.charID = o.user 
			  LEFT JOIN {$this->_table['fsrtool_currentTypePrice']} p ON o.typeID = p.typeID AND p.region = 30000142
			WHERE
			  u.corpID = '{$this->User->corpID}' AND o.status = 4 AND o.corpid = ''
			GROUP BY
			  o.typeID) as test
			GROUP BY
			  test.datum;";
		
		$res = $this->db->query( $strUser );
		if ( $res->num_rows > 0 ) {
			while ( $row = $res->fetch_assoc() ) {
				$return[$row['datum']]['user'] = $row;
				$return[$row['datum']]['corp'] = array('datum' => $row['datum'], 'price' => NULL);
			}
		}
		
		$res = $this->db->query( $strCorp );
		if ( $res->num_rows > 0 ) {
			while ( $row = $res->fetch_assoc() ) {
				$return[$row['datum']]['corp'] = $row;
			}
		}
		
		return $return;
	}
	
	public function eveorder_updateOrder($orderid,$status,$supplier,$comment,$check,$target) {
		$status   = $this->db->escape($status);
		$supplier = $this->db->escape($supplier);
		if ( is_array( $orderid ) ){
			foreach ( $orderid as $key => $value ) {
				if ( isset( $check[ $value ] ) and $check[ $value ] == 1 ) {
					$sql = "SELECT status FROM {$this->_table['eveorder_user_types']} WHERE status='".$status."' AND comment='".$comment[ $key ]."' AND id='".$value."' AND target='".$target[ $key ]."';";
					$order = $this->db->query( $sql );
					if ( $order->num_rows < 1 ) {
						$sqlstring = "UPDATE {$this->_table['eveorder_user_types']}
									  SET status     = '".$status."',
									      lastchange = '".time()."',
										  supplier   = '".$supplier."',
										  comment    = '".$comment[$key]."',
										  target	 = '".$target[$key]."'
									  WHERE id = '".$orderid[$key]."';";
						#echo $sqlstring;
						$result = $this->db->exec_query( $sqlstring );
					}
				}
			}
		}
		return $result;
	}
	
	public function eveorder_addToFavorites($typeID,$groupID) {
		global $language;
		$typeID  = $this->db->escape($typeID);
		$groupID = $this->db->escape($groupID);
				
		$str = "SELECT * FROM {$this->_table['eveorder_favorits']} WHERE userID=".$this->User->charID." AND typeID=".$typeID.";";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$this->db->msg->addwarning($language['not_added_to_favorites']);
			return false;
		}
		else {
			$str = "INSERT INTO {$this->_table['eveorder_favorits']} SET userID=".$this->User->charID.", typeID=".$typeID.", groupID=".$groupID.";";
			$res = $this->db->exec_query( $str );
			$this->db->msg->addconfirm($language['added_to_favorites']);
			return $res;
		}
	}
	
	public function eveorder_delFromFavorites($typeID) {
		$typeID = $this->db->escape($typeID);
		
		$str = "DELETE FROM {$this->_table['eveorder_favorits']} WHERE userID=".$this->User->charID." AND typeID=".$typeID.";";
		$res = $this->db->exec_query( $str );
		return $res;
	}
	
	public function eveorder_delallDeliverys($status=4, $userID=0) {
		if ( $userID == 0 ) $userID = $this->User->charID;
		$status = $this->db->escape($status);
		if($status == 0 || $status == '-1')
			$str = "DELETE FROM {$this->_table['eveorder_user_types']} WHERE status=".$status." AND user='".$userID."';";
		else $str = "UPDATE {$this->_table['eveorder_user_types']} SET deleted=1 WHERE status=".$status." AND user='".$userID."';";
		$res = $this->db->exec_query( $str );
		return $res;
	}
	
	public function eveorder_delOrder($orderID) {
		$orderID = $this->db->escape($orderID);
		$str = "DELETE FROM {$this->_table['eveorder_user_types']} WHERE id=".$orderID." AND status IN(0,-1);";
		$res = $this->db->exec_query( $str );
		if(!$res) {
			$str = "UPDATE {$this->_table['eveorder_user_types']} SET deleted=1 WHERE id=".$orderID.";";
			$res = $this->db->exec_query( $str );
		}
		return $res;
	}
	
	public function eveorder_saveOrder($user,$typeID,$amount,$corp=false,$corpid="") {
		//$this->debug = 1;
		if(!$corp && !is_array($corp)) {
			$corp = array();
		}
		foreach($typeID as $key => $value){
			if($amount[$key]!=0 and $amount[$key]!=""){
				if(is_numeric(str_replace('.', '', $amount[$key]))) {
					if( $corp[$value] || ($corp && !is_array($corp)) ) {
						$check = "SELECT * FROM {$this->_table['eveorder_user_types']} 
								  WHERE status=0 AND typeID='".$typeID[$key]."' AND corpID='".$corpid."' AND user='".$user."';";
						$checkresult = $this->db->query( $check );
						if ( $checkresult->num_rows > 0 ) {
							$row = $checkresult->fetch_assoc();
							$str = "UPDATE {$this->_table['eveorder_user_types']}
								  SET amount = '".(str_replace('.', '', $amount[$key]) + $row['amount'])."',
									  lastchange='".time()."'
								  WHERE id = '".$row['id']."';";
							$res = $this->db->exec_query( $str );
						} else {
							$str = "INSERT INTO {$this->_table['eveorder_user_types']}
								  SET user		='".$user."',
									  typeID	='".$typeID[$key]."',
									  amount	='".str_replace('.', '', $amount[$key])."',
									  timestamp	='".time()."',
									  status	='0',
									  lastchange='".time()."',
									  corpid	='".$corpid."';";
							$res = $this->db->exec_query( $str );
						}
					} else {
						$check = "SELECT * FROM {$this->_table['eveorder_user_types']} 
								  WHERE status=0 AND typeID='".$typeID[$key]."' AND corpID='' AND user='".$user."';";
						$checkresult = $this->db->query( $check );
						if ( $checkresult->num_rows > 0 ) {
							$row = $checkresult->fetch_assoc();
							$str = "UPDATE {$this->_table['eveorder_user_types']}
										  SET amount = '".(str_replace('.', '', $amount[$key]) + $row['amount'])."',
											  lastchange='".time()."'
										  WHERE id = '".$row['id']."';";
							$res = $this->db->exec_query( $str );
						} else {
							$str = "INSERT INTO {$this->_table['eveorder_user_types']}
										  SET user		='".$user."',
											  typeID	='".$typeID[$key]."',
											  amount	='".str_replace('.', '', $amount[$key])."',
											  timestamp	='".time()."',
											  status	='0',
											  lastchange='".time()."',
											  corpid	='';";
							$res = $this->db->exec_query( $str );
						}
					}
					$checkresult->close();
				}
			}
		}
		
		if($res) return true;
		else 	 return false;
	}
	
	public function eveorder_ShipReplacement($corpID) {
		#db_tab_ShipReplacement
		switch($_GET['state']) {
			default:
			case '0':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['invgroups']} g ON i.groupID = g.groupID
					WHERE s.corpID='{$corpID}' AND g.categoryID = 6
					ORDER BY i.typeName ASC";
				break;
			
			case '1':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['dgmtypeeffects']} e ON i.typeID = e.typeID
					WHERE s.corpID='{$corpID}' AND e.effectID = 12
					ORDER BY i.typeName ASC";
				break;
			
			case '2':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['dgmtypeeffects']} e ON i.typeID = e.typeID
					WHERE s.corpID='{$corpID}' AND e.effectID = 13
					ORDER BY i.typeName ASC";
				break;
			
			case '3':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['dgmtypeeffects']} e ON i.typeID = e.typeID
					WHERE s.corpID='{$corpID}' AND e.effectID = 11
					ORDER BY i.typeName ASC";
				break;
			
			case '4':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['dgmtypeeffects']} e ON i.typeID = e.typeID
					WHERE s.corpID='{$corpID}' AND e.effectID = 2663
					ORDER BY i.typeName ASC";
				break;
			
			case '5':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['dgmtypeeffects']} e ON i.typeID = e.typeID
					WHERE s.corpID='{$corpID}' AND e.effectID = 3772
					ORDER BY i.typeName ASC";
				break;
			
			case '6':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['invgroups']} g ON i.groupID = g.groupID
					WHERE s.corpID='{$corpID}' AND g.categoryID = 8
					ORDER BY i.typeName ASC";
				break;
				
			case '7':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['invgroups']} g ON i.groupID = g.groupID
					WHERE s.corpID='{$corpID}' AND g.categoryID = 18
					ORDER BY i.typeName ASC";
				break;
			
			case '8':
				$str = "SELECT s.*, i.typeName 
					FROM {$this->_table['eveorder_shipreplacement']} s 
					LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
					LEFT JOIN {$this->_table['invgroups']} g ON i.groupID = g.groupID
					WHERE s.corpID='{$corpID}' AND g.categoryID NOT IN (6,7,8,18,32)
					ORDER BY i.typeName ASC";
				break;
		}
		return $this->db->fetch_all($str);
	}
	
	public function eveorder_AssetsCacheTime() {
		$corpID = $this->User->corpID;
		return $this->db->fetch_one("SELECT cacheTime FROM {$this->_table['eveorder_cachetime']} WHERE corpID='{$corpID}'", 'cacheTime');
	}
	
	public function eveorder_addReplacement() {
		$typeID = $this->db->escape($_POST['typeID']);
		$corpID = $this->User->corpID;
		if ($typeID != 0)
			$this->db->exec_query("INSERT INTO {$this->_table['eveorder_shipreplacement']} (typeID, corpID) VALUES ('{$typeID}', '{$corpID}') ON DUPLICATE KEY UPDATE typeID=typeID");
	}
	
	public function eveorder_addLocation() {
		$loc = $this->db->escape($_POST['loc']);
		$corpID = $this->User->corpID;
		if (is_array($loc)) {
			$this->db->exec_query("UPDATE {$this->_table['eveorder_locations']} SET sel=1 WHERE corpID='{$corpID}' AND locationID IN (".implode(',',$loc).")");
			$this->db->exec_query("UPDATE {$this->_table['eveorder_locations']} SET sel=0 WHERE corpID='{$corpID}' AND locationID NOT IN (".implode(',',$loc).")");
		} else
			$this->db->exec_query("UPDATE {$this->_table['eveorder_locations']} SET sel=0 WHERE corpID='{$corpID}'");
	}
	
	public function eveorder_getLocations($corpID) {
		$str = "SELECT * FROM {$this->_table['eveorder_locations']} WHERE corpID='{$corpID}' ORDER BY locationName ASC";
		return $this->db->fetch_all($str);
	}
	
	public function eveorder_updateAssets() {
		$ale = AleFactory::getEVEOnline();
		#$ale->setConfig('serverError', 'returnParsed');
		$ale->setConfig('parserClass', 'SimpleXMLElement');
		$corpID = $this->User->corpID;
		$key = $this->db->fetch_all("SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE corpID='{$corpID}'");
		$ale->setKey( $key[0]['keyID'], $key[0]['vCODE'], $key[0]['charID'] );
		
		$res = $this->db->query("SELECT locationID FROM {$this->_table['eveorder_locations']} WHERE corpID='{$corpID}' AND sel=1");
		if ($res->num_rows >= 1 ) {
			while($row = $res->fetch_assoc()) {
				$locIDs[] = $row['locationID'];
			}
		} else {
			$res = $this->db->query("SELECT locationID FROM {$this->_table['eveorder_locations']} WHERE corpID='{$corpID}'");
			while($row = $res->fetch_assoc()) {
				$locIDs[] = $row['locationID'];
			}
		}
		$update = array();
		#$res = $this->db->query("SELECT typeID FROM {$this->_table['eveorder_shipreplacement']} WHERE corpID='{$corpID}'");
/*		
		$str = "SELECT sr.typeID, Sum(DISTINCT o.amount) as anz
			FROM {$this->_table['eveorder_shipreplacement']} sr 
			LEFT JOIN {$this->_table['eveorder_user_types']} o ON sr.typeID = o.typeID AND o.corpid = '{$corpID}' AND o.status IN(0,1,2,3)
			GROUP BY sr.typeID;";
*/
		$str = "SELECT sr.typeID, Sum(DISTINCT o.amount) as anz
			FROM {$this->_table['eveorder_shipreplacement']} sr 
			LEFT JOIN 
				 (SELECT uo.typeID, uo.amount, uo.status     
				 FROM {$this->_table['fsrtool_user']} u 
				 INNER JOIN {$this->_table['eveorder_user_types']} uo ON u.charID = uo.user     
				 WHERE u.corpID = '{$corpID}' 
				 AND uo.status IN (0, 1, 2, 3)
				 AND uo.corpid = '{$corpID}') o
			ON sr.typeID = o.typeID 
			WHERE sr.corpID = '{$corpID}'
			GROUP BY sr.typeID";
		$res = $this->db->query($str);
		while($row = $res->fetch_assoc()) {
			$typeIDs[] = $row['typeID'];
			$update[$row['typeID']] = $row['anz'];
		}
		
		$this->db->exec_query("UPDATE {$this->_table['eveorder_shipreplacement']} SET curlvl=0, inorder=0 WHERE corpID='{$corpID}'");
		
		if (is_array($typeIDs) && is_array($locIDs)) {
			#echo '<pre>'; print_r($locIDs); echo '</pre>'; die;
			try {
				$xml = $ale->corp->AssetList();
				if (!$xml->error) {
					$this->db->exec_query("REPLACE INTO {$this->_table['eveorder_cachetime']} SET cacheTime = '".(string)$xml->currentTime."', corpID = '{$corpID}'");
					foreach($xml->result->rowset->row as $row)	{
						if (in_array((string) $row['locationID'], $locIDs)) {
							// here we must go
							$this->assetsChildren($row);
						}
					}
				}
			
			}  catch (Exception $e) {
				return '<pre>'.$e->getMessage().'</pre>';
			}
		}
		
		foreach($update as $typeID => $val) {
			$this->db->exec_query("UPDATE {$this->_table['eveorder_shipreplacement']} SET inorder = '{$val}' WHERE corpID='{$corpID}' AND typeID='{$typeID}'");
		}
		$this->db->exec_query("UPDATE {$this->_table['eveorder_shipreplacement']} SET buy=minlvl-(curlvl+inorder) WHERE corpID='{$corpID}'");
		
		return 'updated';
	}
	
	private function assetsChildren($row) {
		$corpID = $this->User->corpID;
		if (count((array)$row->children()->rowset) >= 1) {
			foreach ($row->children()->rowset->row as $row) {
				if (count((array)$row->children()->rowset) >= 1) {
					$this->assetsChildren($row);
				}
				$this->db->exec_query("UPDATE {$this->_table['eveorder_shipreplacement']} 
					SET curlvl = curlvl+'".(int)$row['quantity']."',
						buy = minlvl-(curlvl+inorder)
					WHERE corpID = '{$corpID}' AND typeID = '".(int)$row['typeID']."'");
			}
		}
	}
	
	public function eveorder_updateLocation() {
		$ale = AleFactory::getEVEOnline();
		#$ale->setConfig('serverError', 'returnParsed');
		$ale->setConfig('parserClass', 'SimpleXMLElement');
		$corpID = $this->User->corpID;
		$key = $this->db->fetch_all("SELECT * FROM {$this->_table['fsrtool_user_fullapi']} WHERE corpID='{$corpID}'");
		$ale->setKey( $key[0]['keyID'], $key[0]['vCODE'], $key[0]['charID'] );
		
		try {
			$xml = $ale->corp->AssetList();
			if (!$xml->error) {
				$this->db->exec_query("REPLACE INTO {$this->_table['eveorder_cachetime']} SET cacheTime = '".(string)$xml->currentTime."', corpID = '{$corpID}'");
				foreach($xml->result->rowset->row as $row)	{
					$loc[(string) $row['locationID']] = $this->db->escape($this->eveLocation($row['locationID']));
				}
			}
		
		}  catch (Exception $e) {
			return '<pre>'.$e->getMessage().'</pre>';
		}
		$locIDs = $this->db->fetch_all("SELECT locationID FROM {$this->_table['eveorder_locations']} WHERE corpID='{$corpID}'", 'locationID');
		
		foreach($loc as $locID => $locName) {
			$locIDsNEW[] = $locID;
			if (!in_array($locID, $locIDs))
				$this->db->exec_query("INSERT INTO {$this->_table['eveorder_locations']} (corpID, locationID, locationName) VALUES ('$corpID','$locID','$locName')");
		}
		
		if($result = implode (',' ,array_diff($locIDs, $locIDsNEW)))
			$this->db->exec_query("DELETE FROM {$this->_table['eveorder_locations']} WHERE corpID='{$corpID}' AND locationID IN ({$result})");
		
		return 'updated';
	}
	
	public function eveorder_updateVal() {
		$id = $this->db->escape($_POST['id']);
		$val = $this->db->escape($_POST['newvalue']);
		$corpID = $this->User->corpID;
		
		if (is_numeric($val)) {
			$this->db->exec_query("UPDATE {$this->_table['eveorder_shipreplacement']} SET minlvl={$val}, buy=minlvl-(curlvl+inorder) WHERE typeID={$id} AND corpID='{$corpID}'");
		}
		$res = $this->db->query("SELECT t.typeID FROM {$this->_table['invtypes']} t INNER JOIN {$this->_table['invgroups']} g ON t.groupID = g.groupID WHERE t.typeID = '{$id}' AND g.categoryID = 6;");
		if($res->num_rows >= 1) $this->sr_calcFitts();
		return true;
	}
	
	private function sr_calcFitts() {
		$corpID = $this->User->corpID;
		$str = "SELECT s.*, i.typeName 
			FROM {$this->_table['eveorder_shipreplacement']} s 
			LEFT JOIN {$this->_table['invtypes']} i ON i.typeID = s.typeID
			LEFT JOIN {$this->_table['invgroups']} g ON i.groupID = g.groupID
			WHERE s.corpID='{$corpID}' AND g.categoryID = 6
			ORDER BY i.typeName ASC";
		$res = $this->db->query($str);
		if ($res->num_rows >= 1) {
			while($row = $res->fetch_assoc()) {
				$ships[] = $this->sr_shipFitts($row['typeID'], $row['minlvl']);
			}
			
			for ($i = 1; $i <= (count($ships)-1); $i++) {
				foreach($ships[$i] as $typeID => $val) {
					if ($ships[0][$typeID]) {
						$ships[0][$typeID] += $val;
					} else {
						$ships[0][$typeID] = $val;
					}
				}
			}
			foreach($ships[0] as $typeID => $val) {
				$this->db->exec_query("UPDATE {$this->_table['eveorder_shipreplacement']} SET minlvl={$val}, buy=minlvl-(curlvl+inorder) WHERE typeID={$typeID} AND corpID='{$corpID}'");
			}
		}
	}
	
	private function sr_shipFitts($id, $val) {
		$corpID = $this->User->corpID;
		$str = "SELECT fm.itemID, Count(fm.itemID) AS anz, fm.drone
			FROM {$this->_table['fitting']} f
			LEFT JOIN {$this->_table['fitting_module']} fm ON f.Id = fm.fitID
			WHERE f.corpID = '{$corpID}'
			AND f.ship = '{$id}'
			GROUP BY fm.itemID;";
		
		$typeIDs = array();
		$res = $this->db->query($str);
		if ($res->num_rows >= 1) {
			while($row = $res->fetch_assoc()) {
				$typeIDs[$row['itemID']] = $row['anz']*$val;
				if($row['drone'] != 0) 
					$typeIDs[$row['itemID']] = ($row['anz']*$row['drone'])*$val;
			}
		}
		return $typeIDs;
	}
	
	public function eveorder_delValue() {
		$id = $this->db->escape($_POST['id']);
		$corpID = $this->User->corpID;
		if (is_numeric($id)) {
			if ($this->db->exec_query("DELETE FROM {$this->_table['eveorder_shipreplacement']} WHERE typeID={$id} AND corpID='{$corpID}'"))
				return 'deleted';
		}
		return false;
	}
	
	public function eveorder_importShipReplaceFittings() {
		$corpID = $this->User->corpID;
		$str1 = "SELECT f.ship, fm.itemID, fm.ammo, fm.drone, count(fm.itemID) as anz
			FROM {$this->_table['fitting']} f
			LEFT JOIN {$this->_table['fitting_module']} fm ON f.Id = fm.fitID
			WHERE f.corpID = '{$corpID}'
			GROUP BY fm.itemID, fm.ammo;";
		
		$str2 = "SELECT sr.typeID 
			FROM {$this->_table['eveorder_shipreplacement']} sr 
			LEFT JOIN {$this->_table['invtypes']} i ON sr.typeID = i.typeID 
			LEFT JOIN {$this->_table['invgroups']} g ON i.groupID = g.groupID
			WHERE g.categoryID IN (6, 7, 8, 18, 32) 
			AND sr.corpID = '{$corpID}';";
		$ids = array();
		$filter = array();
		$res1 = $this->db->query($str1);
		while($row = $res1->fetch_assoc()) {
			if($row['ammo'] != 0) {
				if (!in_array($row['ammo'], $ids)) $ids[] = $row['ammo'];
			}
			if (!in_array($row['ship'], $ids)) $ids[] = $row['ship'];
			if (!in_array($row['itemID'], $ids)) $ids[] = $row['itemID'];
		}
		$res2 = $this->db->query($str2);
		while($row = $res2->fetch_assoc()) {
			$filter[] = $row['typeID'];
		}
		#echo '<pre>'; print_r($ids); echo '</pre>'; die;
		foreach($ids as $typeID) {
			$this->db->exec_query("INSERT IGNORE INTO {$this->_table['eveorder_shipreplacement']} (typeID, corpID) VALUES ('{$typeID}', '{$corpID}')");
		}
		foreach($filter as $typeID) {
			if (!in_array($typeID, $ids))
				$this->db->exec_query("DELETE FROM {$this->_table['eveorder_shipreplacement']} WHERE typeID = '{$typeID}' AND corpID = '{$corpID}'");
		}
	}
	
	private function eveorder_ShipReplaceFittings() {
		$corpID = $this->User->corpID;
		$str = "SELECT f.ship, fm.itemID, fm.ammo, fm.drone 
			FROM {$this->_table['fitting']} f
			LEFT JOIN {$this->_table['fitting_module']} fm ON f.Id = fm.fitID
			WHERE f.corpID = '{$corpID}'
			GROUP BY f.ship, fm.itemID;";
		
		$ids = array();
		$res = $this->db->query($str);
		
		while($row = $res->fetch_assoc()) {
			#$ship[$row['ship']][] = $row['itemID'];
			if($row['ammo'] != 0) {
				#$ship[$row['ship']][] = $row['ammo'];
				if (!in_array($row['ammo'], $ids)) $ids[] = $row['ammo'];
			}
			if (!in_array($row['ship'], $ids)) $ids[] = $row['ship'];
			if (!in_array($row['itemID'], $ids)) $ids[] = $row['itemID'];
		}
		#echo '<pre>'; print_r($ids); echo '</pre>'; die;
		foreach($ids as $typeID) {
			$this->db->exec_query("INSERT IGNORE INTO {$this->_table['eveorder_shipreplacement']} (typeID, corpID) VALUES ('{$typeID}', '{$corpID}')");
		}
	}
	
	public function eveorder_search_item() {
		$item = $this->db->escape($_GET['term']);
		$str = "SELECT typeID, typeName
			FROM {$this->_table['invtypes']}
			WHERE typeName LIKE '%{$item}%' 
			AND	marketGroupID IS NOT NULL 
			AND	published = 1
			AND typeName not LIKE '%Blueprint'
			ORDER BY typeName ASC
			LIMIT 0,30";
	
		$return_array = array();
	
		$res = $this->db->query($str);
		while($row = $res->fetch_assoc()) {
			$row_array['label'] = $row['typeName'];
			$row_array['value'] = $row['typeName'];
			$row_array['id']    = $row['typeID'];
			array_push($return_array, $row_array);
		}
		
		return json_encode($return_array);
	}
	
	private function eveLocation( $locationID ) {
		$ID = (string)$locationID;
		if ( !isset( $this->locationCache[ $ID ] ) ) {
			$locationID = (int)$locationID;
			if ( ($locationID >= 66000000) && ($locationID <= 66014860) ) {
				$locationID -= 6000001;
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['stastations']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( ($locationID >= 66014861) && ($locationID <= 66014929) ) {
				$locationID -= 6000001;
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['fsrtool_api_outposts']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			} 
			else if ( ($locationID >= 66014929) && ($locationID <= 66999999) ) {
				$locationID -= 6000000;
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['stastations']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( ($locationID >= 67000000) && ($locationID <= 67999999) ) {
				$locationID -= 6000000;
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['fsrtool_api_outposts']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( ($locationID >= 60014861) && ($locationID <= 60014928) ) {
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['fsrtool_api_outposts']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( ($locationID >= 60000000) && ($locationID <= 61000000) ) {
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['stastations']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else if ( $locationID >= 61000000 ) {
				$locationID = (string)$locationID;
				$str = "SELECT stationName FROM {$this->_table['fsrtool_api_outposts']} WHERE stationID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'stationName' );
			}
			else {
				$locationID = (string)$locationID; 
				$str = "SELECT itemName FROM {$this->_table['mapdenormalize']} WHERE itemID = '{$locationID}'";
				$locName = $this->db->fetch_one( $str, 'itemName' );
			}
			$this->locationCache[ $ID ] = $locName != '' ? $locName : $locationID;
		}
		
		return $this->locationCache[ $ID ];	
	}
}
?>