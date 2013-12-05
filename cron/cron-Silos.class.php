<?php

class cronSilos extends cron
{
	
	public function __construct($parms) {
		parent::__construct($parms);
	}
	
	public function run() {
		$out='';
		$out .= $this->getAssets();
		//$out .= $this->StarbaseDetail();
		
		return $out;
	}
	
	private function get_fullApis() {
		$apis = array();
		if ( parent::$corpID )
			$res = $this->query("SELECT api.*, c.name as corpName FROM {$this->_table['fsrtool_user_fullapi']} api LEFT JOIN {$this->_table['fsrtool_corps']} AS c ON api.corpID = c.id WHERE api.status=1 AND api.corpID = '".parent::$corpID."';");
		else
			$res = $this->query("SELECT api.*, c.name as corpName FROM {$this->_table['fsrtool_user_fullapi']} api LEFT JOIN {$this->_table['fsrtool_corps']} AS c ON api.corpID = c.id WHERE api.status=1;");
		while ( $row = $res->fetch_assoc() ) {
			if($row) $apis[] = $row;
		} $res->close();
		
		return $apis;
	}
	
	private function del_fullApi( $charID ) {
		$result = $this->exec_query("UPDATE {$this->_table['fsrtool_user_fullapi']} SET errorcount = if(status = 0, 0, errorcount + 1), status = if(errorcount >= 3, 0, 1) WHERE charID='$charID';");
		
		return $result;
	}
	
	private function get_tower($corpID) {
		$tower = array();
		$res = $this->query("SELECT itemID FROM {$this->_table['fsrtool_pos']} WHERE corpID='$corpID';");
		while ( $row = $res->fetch_assoc() ) {
			if($row) $tower[]=$row['itemID'];
		} $res->close();
		
		return $tower;
	}
	
	private function getAssets() {
		$time_start = microtime(true);
		$out = '';
		$x=0;
		
		$parserClass = $this->ale->setConfig('parserClass', 'SimpleXMLElement');
		
		$insert = "INSERT INTO {$this->_table['fsrtool_assets']} (itemID, corpID, locationID, typeID, quantity, flag, singleton, rawQuantity, contents, lastFetch) 
			VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE 
				locationID = VALUES(locationID),
				typeID = VALUES(typeID),
				quantity = VALUES(quantity),
				flag = VALUES(flag),
				singleton = VALUES(singleton),
				rawQuantity = VALUES(rawQuantity),
				contents = VALUES(contents),
				lastFetch = VALUES(lastFetch)";
		$update = "UPDATE {$this->_table['fsrtool_assets']} SET locationID=?, typeID=?, quantity=?, flag=?, singleton=?, rawQuantity=?, contents=? WHERE itemID=?";
		
		// $fuelarray = array(4051,4246,4247,4312,44,3683,3689,9832,9848,16272,16273,16274,16275,17887,17888,17889,24592,24593,24594,24595,24596,24597);
		// $fuelarray = array(44,3683,3689,9832,9848,16272,16273,16274,16275,17887,17888,17889,24592,24593,24594,24595,24596,24597);
		$fuelarray = array(4051,4246,4247,4312,16275,24592,24593,24594,24595,24596,24597);
		$reactors = array(16869,20175,22634,24684,30656);
		$silos = array(17982,14343,25270,25271,25280,25821,30655);
		$mins = array(34,35,36,37,38,39,40,11399, 29659,29660,29661,29662,29663,29664,32821,32822,32823,32824,32825,32826,32827,32828,32829,
			16633,16634,16635,16636,16637,16638,16639,16640,16641,16642,16643,16644,16646,16647,16648,16649,16650,16651,16652,16653); // RAW mins
		
		$apis = $this->get_fullApis();
		
		foreach( $apis as $apikey ) {
			$Database = new Database();
			$User = new User($Database);
			$worldClass = new SiloWorld($User);
			$silosClass = new Silos($apikey['corpID'], $worldClass);
			$oldAssets = $silosClass->assets;
			// echo '<pre>';print_r($silosClass);die();
			
			
			$this->ale->setKey( $apikey['keyID'], $apikey['vCODE'], $apikey['charID'] );
			$corpID = $apikey['corpID'];
			$allyID = $apikey['allyID'];
			$apiSilos = array();
			$apiReactors = array();
			try {
				$xml = $this->ale->corp->AssetList();
				/* if ( $xml->error ) {
					if ( $this->apiErrorHandler( $xml->error, $data ) ) {
						unset($xml);
						continue;
					}
				} */
				$saveTowerCache = true;
				$res = $this->query("SELECT cacheTime FROM {$this->_table['fsrtool_silos_cachetimes']} WHERE type = '1' AND corpID = '$corpID';");
				$row = $res->fetch_assoc();
				$cachetime = strtotime((string) $xml->cachedUntil) - 21600;
				if ( $row['cacheTime'] == $cachetime ) {
					unset($xml); continue;
					$saveTowerCache = false;
				}
				$towerCache = array();
				$query  = "SELECT moonID, stateTimestamp FROM {$this->_table['fsrtool_pos']} WHERE corpID = '$corpID';";
				$res = $this->query( $query );
				if ( $res->num_rows > 0 ) {
					while ( $row = $res->fetch_assoc() ) {
						if ($row) {
							$towerCache[ $row['moonID'] ] = $row['stateTimestamp'];
						}
					}
				}
				if ($saveTowerCache)
					$this->exec_query("REPLACE INTO {$this->_table['fsrtool_silos_cachetimes']} SET type = '2', corpID = '$corpID', cacheTime = '".serialize($towerCache)."';");
				//$this->exec_query("REPLACE INTO {$this->_table['fsrtool_silos_cachetimes']} SET type = '1', corpID = '$corpID', cacheTime = '".strtotime((string) $xml->currentTime)."';");
				$this->exec_query("REPLACE INTO {$this->_table['fsrtool_silos_cachetimes']} SET type = '1', corpID = '$corpID', cacheTime = '".$cachetime."';");
				$this->exec_query("DELETE FROM {$this->_table['fsrtool_pos_corphanger']} WHERE corpID = '$corpID';");
				//$this->exec_query("TRUNCATE {$this->_table['fsrtool_assets']}");
				//$this->exec_query("TRUNCATE {$this->_table['fsrtool_assets_contents']}");
				
				$res = $this->query("SELECT itemID, quantity, suspect FROM {$this->_table['fsrtool_silos']} WHERE corpID = '$corpID';");
				while ( $row = $res->fetch_assoc() ) {
					if ($row) {
						$silobefore[$row['itemID']]['quantity'] = $row['quantity'];
						$silobefore[$row['itemID']]['suspect'] = $row['suspect'];
					}
				}
				$res->close();
				/* test */
				/* $stmt = $this->prepare($insert);
				$stmt->bind_param("ssiiiiiiii", $itemID, $corpID, $locationID, $typeID, $quantity, $flag, $singleton, $rawQuantity, $contents, $lastFetch); */
				/* test */
				if (isset($xml->result->rowset->row)) {
					foreach ($xml->result->rowset->row as $row)	{
						
						/* test */
						
						/* $itemID 	 = (string)$row['itemID'];
						$locationID	 = (string)$row['locationID'];
						$typeID		 = (string)$row['typeID'];
						$quantity	 = (string)$row['quantity'];
						$flag		 = (string)$row['flag'];
						$singleton	 = (string)$row['singleton'];
						$rawQuantity = (string)$row['rawQuantity'];
						$contents    = 0;
						$lastFetch   = $cachetime;
						
						if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
							$contents = 1;
							$this->assetsContent($row->children()->rowset->row, $itemID, $corpID, $cachetime);
						}
						// Execute the statement 
						$stmt->execute();
						
						if ($stmt->error) {
							$out .= $stmt->error."\n";
						} */
						
						/* test */
						
						/* CorpHanger */
						if ( ((string) $row['typeID'] == '17621' || (string) $row['typeID'] == '27') && (int) $row['singleton'] == 1 ) {
							foreach ($row->attributes() as $key => $val) {
								$output[(string) $key] = (string) $val;					
							}
							if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
							//$out .= count((array)$row->children())."\n";
							//$out .= (string) $row['itemID']."\n";
							//$out .= gettype($row->children()->rowset->row)."\n";
								
								foreach ($row->children()->rowset->row as $row) {
									if ( in_array((string) $row['typeID'],$fuelarray) ) {
										foreach ($row->attributes() as $name => $value) {
											$output['in'][(string) $name] = (string) $value;
										}
										$query = ("REPLACE INTO {$this->_table['fsrtool_pos_corphanger']} SET
											corpID		= '".$corpID."',
											itemID      = '".$output['in']['itemID']."',
											locationID  = '".$output['locationID']."',
											type      	= '".$output['typeID']."',
											typeID   	= '".$output['in']['typeID']."',
											quantity 	= '".$output['in']['quantity']."',
											flag     	= '".$output['in']['flag']."' ;");
										$this->exec_query( $query ); //  'Set Asset -> CorpHanger'
										unset ($output['in']);
									}
								}
							}
							unset ($output);
						}
						
						/* Silos */
						if ( in_array((string) $row['typeID'], $silos) && (int) $row['singleton'] == 1 && (int) $row['flag'] == 0 ) {
							foreach ($row->attributes() as $key => $val) {
								$output[(string) $key] = (string) $val;
							}
							if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
								foreach ($row->children()->rowset->row as $row) {
									foreach ($row->attributes() as $name => $value) {
										$output['silo'][(string) $name] = (string) $value;
									}
								}
							}
							// sort Couplin Arrays out that contain no mineral in groupID 18
							if ($output['typeID'] == 17982 && in_array($output['silo']['typeID'], $mins) || $output['typeID'] != 17982) {
								
								$apiSilos[] = $output['itemID'];
								// print_it ( $output );
								$res = $this->query("SELECT itemID, locationID, typeID FROM {$this->_table['fsrtool_silos']} WHERE itemID = '".$output['itemID']."';");
								if ( $res->num_rows > 0 ) {
									
									$silores = $res->fetch_assoc();
									if( $silores['locationID'] != $output['locationID'] ) 
										$location = "locationID = '".$output['locationID']."', pos = NULL,"; 
									else $location = "";
									if ( isset($output['silo']['typeID']) && $silores['typeID'] == $output['silo']['typeID'] ) {
										$query = "UPDATE {$this->_table['fsrtool_silos']} 
												  SET typeID	 = '".$output['silo']['typeID']."',
													  ".$location."
													  quantity	 = '".$output['silo']['quantity']."',
													  emptyTime	 = '0'
												  WHERE itemID = '".$output['itemID']."';";
									}
									elseif( isset($output['silo']['typeID']) && $silores['typeID'] != $output['silo']['typeID']) {
										$query = "UPDATE {$this->_table['fsrtool_silos']} 
												  SET ".$location."
													  typeID	 = '".$output['silo']['typeID']."',
													  alarm		 = '0',
													  quantity   = '".$output['silo']['quantity']."',
													  emptyTime  = '0'
												  WHERE itemID = '".$output['itemID']."';";
									}
									elseif( isset($output['silo']['typeID']) && $silores['typeID'] != $output['silo']['typeID'] && $output['silo']['typeID'] != 0) {
										$query = "UPDATE {$this->_table['fsrtool_silos']} 
												  SET typeID	 = '".$output['silo']['typeID']."',
													  locationID = '".$output['locationID']."',
													  pos 		 = NULL,
													  turn		 = '0',
													  input		 = '0',
													  stack		 = '0',
													  alarm		 = '0',
													  quantity   = '".$output['silo']['quantity']."',
													  emptyTime  = '0'
												  WHERE itemID = '".$output['itemID']."';";
									}
									else {
									// echo $output['silo']['typeID'].' x<br>';
										$query = "UPDATE {$this->_table['fsrtool_silos']} 
												  SET locationID = '".$output['locationID']."',
													  /*typeID	 = '0',*/
													  quantity   = '0',
													  emptyTime  = '0'
												  WHERE itemID = '".$output['itemID']."';";
									}
									$this->exec_query( $query );
									//print $query. '<br>';
								} else {
									$query = "INSERT INTO {$this->_table['fsrtool_silos']} 
											  SET itemID 	 = '".$output['itemID']."',
												  corpID 	 = '".$corpID."',
												  locationID = '".$output['locationID']."',
												  siloTypeID = '".$output['typeID']."',
												  typeID	 = '".$output['silo']['typeID']."',
												  quantity	 = '".$output['silo']['quantity']."';";
									// echo $query.'<br>';
									$this->exec_query( $query );
								}
								$res->close();
							}
							unset ($output);
						}
						
						/* Reactors */
						if ( in_array((string) $row['typeID'], $reactors) && (int) $row['singleton'] == 1 && (int) $row['flag'] == 0 ) {
							foreach ($row->attributes() as $key => $val) {
								$output[(string) $key] = (string) $val;
							}
							$apiReactors[] = $output['itemID'];
							if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
								foreach ($row->children()->rowset->row as $row) {
									foreach ($row->attributes() as $name => $value) {
										$output['reactor'][(string) $name] = (string) $value;
									}
									$res = $this->query("SELECT itemID FROM {$this->_table['fsrtool_silos_reactors']} WHERE itemID = '".$output['itemID']."';");
									if ( $res->num_rows == 0 ) {
										$query = "INSERT INTO {$this->_table['fsrtool_silos_reactors']} 
												  SET itemID 	 = '".$output['itemID']."',
													  corpID 	 = '".$corpID."',
													  locationID = '".$output['locationID']."',
													  typeID	 = '".$output['typeID']."',
													  typeIDx	 = '".$output['reactor']['typeID']."';";
										// echo $query.'<br>';
										$this->exec_query( $query );
									} else {
										$query = "UPDATE {$this->_table['fsrtool_silos_reactors']} 
												  SET corpID 	 = '".$corpID."',
													  locationID = '".$output['locationID']."',
													  typeID	 = '".$output['typeID']."',
													  typeIDx	 = '".$output['reactor']['typeID']."'
												  WHERE itemID 	 = '".$output['itemID']."';";
										// echo $query.'<br>';
										$this->exec_query( $query );
									}
									$res->close();
								}
								unset ($output);
							}

						}
					}
					/* close statement */
					/* $stmt->close(); */
					
					/* Reactors cleaning */
					$res = $this->query("SELECT itemID FROM {$this->_table['fsrtool_silos_reactors']} WHERE corpID = '$corpID';");
					while ( $row = $res->fetch_assoc() ) {
						if ($row) {
							if (!in_array($row['itemID'], $apiReactors)) {
								$this->exec_query("DELETE FROM {$this->_table['fsrtool_silos_reactors']} WHERE itemID = '{$row['itemID']}';");
							}
						}
					}
					$res->close();
					
					/* Silos cleaning */
					$res = $this->query("SELECT itemID, quantity, suspect FROM {$this->_table['fsrtool_silos']} WHERE corpID = '$corpID';");
					while ( $row = $res->fetch_assoc() ) {
						if ($row) {
							if (!in_array($row['itemID'], $apiSilos)) {
								$this->exec_query("DELETE FROM {$this->_table['fsrtool_silos']} WHERE itemID = '{$row['itemID']}';");
							}
						}
					}
					$res->close();
					
					unset ($apiSilos, $apiReactors, $silobefore, $xml);
					
					/* Assign Locations from API */
					$this->_tableLoc = $this->_table['fsrtool_silos_reactors']; /* set $this->_tableLoc for doLocations() first. */
					$out .= $this->doLocations($apikey, true);
					
					$this->_tableLoc = $this->_table['fsrtool_silos']; /* set $this->_tableLoc for doLocations() first. */
					$out .= $this->doLocations($apikey, true);
					
					/* NEW STUFF TEST */
					$silosClassNew = new Silos($apikey['corpID'], $worldClass);
					$newAssets = $silosClassNew->assets;
					if(is_array($oldAssets)){
						$msg='';
						foreach($oldAssets as $assetItem) {
							foreach($newAssets as $assetItemNew) {
								if($assetItem['itemID'] == $assetItemNew['itemID']){
									if($assetItem['quantity'] != $assetItemNew['quantity']
										&& $assetItem['emptyTime'] == 0
										&& $assetItem['typeID'] != 0)
										//&& $assetItem['quantity'] > $assetItemNew['quantity'])
									{
										$value = $assetItem['quantity'] - $assetItemNew['quantity'];
										if($value <= 400) {
											$this->exec_query("UPDATE {$this->_table['fsrtool_silos']} SET suspect = 1 WHERE itemID = '{$assetItemNew['itemID']}';");
											$posID = $assetItem['pos'];
											$msg .= $apikey['corpName'].'<br/>';
											$msg .= $assetItem['itemID'].'<br/>';
											$msg .= $assetItem['typeName'].'<br/>';
											//$msg .= $assetItem['emptyTime'].'<br/>';
											$msg .= $assetItem['quantity'].'<br/>';
											$msg .=	$assetItemNew['quantity'].'<br/>';
											$msg .=	'old: '.$silosClass->towerCacheAgo[$posID].'<br/>';
											$msg .=	'new: '.$silosClassNew->towerCacheAgo[$posID].'<br/>';
											$msg .=	'old: '.$silosClass->assetTowerCache[$posID].'<br/>';
											$msg .=	'new: '.$silosClassNew->assetTowerCache[$posID].'<br/>';
											$msg .=	'old: '.$silosClass->assetCacheTime.'<br/>';
											$msg .=	'new: '.$silosClassNew->assetCacheTime.'<br/>';
											$msg .=	'old: '.$silosClass->untouchtAssets[$assetItem['itemID']]['quantity'].'<br/>';
											$msg .=	'new: '.$silosClassNew->untouchtAssets[$assetItem['itemID']]['quantity'].'<br/>';
											//$msg .=	print_r($assetItem,true).'<br/>';
											//$msg .=	print_r($assetItemNew,true).'<br/>';
											//echo $assetItem['quantity'] .' - '. $assetItemNew['quantity']. '<br>';
										} else {
											$this->exec_query("UPDATE {$this->_table['fsrtool_silos']} SET suspect = 0 WHERE itemID = '{$assetItemNew['itemID']}';");
										}
									} else {
										$this->exec_query("UPDATE {$this->_table['fsrtool_silos']} SET suspect = 0 WHERE itemID = '{$assetItemNew['itemID']}';");
									}
								}
							}
						}
						if($msg != '') {
							//$msg .= print_r($silosClass->assetTowerCache, true).'<br/>';
							//$msg .= print_r($silosClassNew->assetTowerCache, true).'<br/>';
							$this->sendMail(array('pi@fsrtool.de'), $msg);
						}
					}
					/* echo '<pre>';
					print_r($oldAssets);
					print_r($newAssets);
					echo '</pre>'; */
					//die;
				}
			} catch (Exception $e) {
				$out .= $e->getCode().' - '.$e->getMessage()."\n";
				$this->errorHandler('Problem in getAssets::'.$e->getMessage(), $e->getCode(), $apikey['charID'], $apikey);
			}
			$x++;
		} //  foreach $apis end
		
		$fetchedAccs = $x == 1 ? $x.' account' : $x . '/' . count($apis) . ' account(s)';
		
		$this->ale->setConfig('parserClass', $parserClass);
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$out .= 'AssetList: ' . $fetchedAccs . ' done in ' . round($time, 4) . ' seconds, ' . round((memory_get_usage()/1024), 2) . "kb memory used.\n";
		
		return parent::format( $out );
	}
	
	private function assetsContent(SimpleXMLElement $contents, $fromItemID, $corpID, $lastFetch) {
		//$insert = "INSERT INTO {$this->_table['fsrtool_assets_contents']} (fromItemID, itemID, typeID, quantity, flag, singleton, rawQuantity, contents) VALUES (?,?,?,?,?,?,?,?)";
		$insert = "INSERT INTO {$this->_table['fsrtool_assets_contents']} (fromItemID, itemID, corpID, typeID, quantity, flag, singleton, rawQuantity, contents, lastFetch) 
			VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE 
				typeID = VALUES(typeID),
				quantity = VALUES(quantity),
				flag = VALUES(flag),
				singleton = VALUES(singleton),
				rawQuantity = VALUES(rawQuantity),
				contents = VALUES(contents),
				lastFetch = VALUES(lastFetch)";
		$update = "UPDATE {$this->_table['fsrtool_assets_contents']} SET typeID=?, quantity=?, flag=?, singleton=?, rawQuantity=?, contents=? WHERE itemID=?";
		
		$stmt = $this->prepare($insert);

		$stmt->bind_param("ssiiiiiiii", $fromItemID, $itemID, $corpID, $typeID, $quantity, $flag, $singleton, $rawQuantity, $contents, $lastFetch);
		
		foreach ($contents as $row) {
			$itemID 	 = (string)$row['itemID'];
			$typeID		 = (string)$row['typeID'];
			$quantity	 = (string)$row['quantity'];
			$flag		 = (string)$row['flag'];
			$singleton	 = (string)$row['singleton'];
			$rawQuantity = (string)$row['rawQuantity'];
			$contents    = 0;
			//$lastFetch	 = $lastFetch;
			
			if (count((array)$row->children()) >= 1 && is_object($row->children()->rowset->row)) {
				$contents = 1;
				$this->assetsContent($row->children()->rowset->row, $itemID, $corpID, $lastFetch);
			}
			/* Execute the statement */
			$stmt->execute();
			
			if ($stmt->error) {
				$out .= $stmt->error."\n";
			}
		}
		/* close statement */
		$stmt->close();
	}
	
	private function sendMail(array $emails, $text) {
		$this->mail->to = array();
		
		foreach($emails as $to) {
			$this->mail->AddAddress($to);
		}
		
		$Subject = 'Control Tower alert';
		$Body = 'Hello,<br />'
			.'<br />'
			.$text
			.'<br />'
			.'-- <br />'
			.'Regards Site Admin<br />';
		
		$this->mail->FromName  = 'FSR-Tool';
		$this->mail->Subject   = $Subject;
		$this->mail->Body      = $Body;
		
		return $this->mail->send();
	}
	
	private function errorHandler($message, $code, $charID, $data=array()) {
		switch(substr($code, 0, 1)) {
			case 1:
			break;
			
			case 2:
				$this->del_fullApi($charID);
			break;
			
			case 3:
			break;
			
			case 4:
				$this->del_fullApi($charID);
			break;
			
			case 5:
			break;
			
			default:
			break;
		}
	}
}
?>