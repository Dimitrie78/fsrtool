<?php

class eveAssets {
	public $db;
	private $ale;
	private $cacheDir = 'cache/assetsCache/';
	private $cacheTime;
	
	public $assets = array();
	
	public function __construct( $User, $ale ) {
		$this->db = new eveDB( $User );
		$this->ale = $ale;
		$this->ale->setConfig('serverError', 'returnParsed');
		$this->ale->setConfig('parserClass', 'SimpleXMLElement');
		$this->cacheTime = 60*60*7;
		if ( isset($_POST['cid']) && !empty($_POST['cid']) && $_POST['cid'] != $User->charID ){
			$id = $_POST['cid'];
			$this->charID = $User->alts[$id]['charID'];
			$this->ale->setKey( $User->alts[$id]['userID'], 
										$User->alts[$id]['userAPI'], 
										$User->alts[$id]['charID'] 
										);
		} else {
			$this->charID = $User->charID;
			$this->ale->setKey( $User->keyID, $User->vCODE, $User->charID );
		}
		#$this->loadAssets();
	}
	
	private function loadAssets() {
		try {
			$assetList = $this->ale->char->AssetList();
			if ( !$assetList->error ) {
				foreach ($assetList->result->rowset->row as $asset)
					$this->assets[] = new Assets($this->db, $asset, $this);
			}
		} catch (Exception $e) {
			#echo $e->getMessage();
		}		
	}
	
	private function cacheHandler() {
		$file = $this->cacheDir . $this->charID . '.txt';
		if ( file_exists($file) ) {
			$x = time() - filemtime($file);
			if ( $x > $this->cacheTime ) {
				$this->loadAssets();
				file_put_contents( $file, base64_encode(gzcompress(serialize($this->assets))) );
			}
			else $this->assets = unserialize(gzuncompress(base64_decode( file_get_contents($file) )));
		}
		else {
			$this->loadAssets();
			file_put_contents( $file, base64_encode(gzcompress(serialize($this->assets))) );
		}
	}
	
	private function mySerialize( $obj ) {
		file_put_contents( 'xmlcache/assetsCache/asset.txt', base64_encode(gzcompress(serialize($obj))) );
	}

	private function myUnserialize() {
		return unserialize(gzuncompress(base64_decode( file_get_contents('xmlcache/assetsCache/asset.txt') )));
	}
	
	public function getContent() {
		if (!isset($_GET['p']))
			$_GET['p'] = 0;
	#	$start = microtime(true);
	#	$this->loadAssets();
		$this->cacheHandler();
		
	#	$_SESSION['messages']->addWarning( round(microtime(true)-$start,4) );
		
		$fullAssetList = $this->assets;

		if (isset($_GET['type']) && ($_GET['type'] == 'find')) {
			$_GET['item'] = trim($_GET['item']);

			$assets = $this->searchAsset($fullAssetList, $_GET['item']);
			usort($assets, array($this, 'assetNameSort'));

			$assetList = objectToArray($assets, array('eveDB'));
			
			return $this->render('find', array('assets' => $assetList, 'search' => $_GET['item'], 'corp' => isset($_GET['corp'])));
		} else if (isset($_GET['type']) && ($_GET['type'] == 'ships')) {
			$this->name .= ': My Ships';
			$ships = $this->searchAssetCategory($fullAssetList, 6);
			usort($ships, array($this, 'assetNameSort'));
			for ($i = 0; $i < count($ships); $i++)
				if ($ships[$i]->contents)
					usort($ships[$i]->contents, array($this, 'assetSlotSort'));


			if (count($ships) > 10) {
				$ships = array_chunk($ships, 10);

				$pageCount = count($ships);
				$pageNum = max((int)$_GET['p'], 0);
				$nextPage = min($pageNum + 1, $pageCount);
				$prevPage = max($pageNum - 1, 0);

				$ships = $ships[$pageNum];
			} else {
				$pageCount = 0;
				$pageNum = 0;
				$nextPage = 0;
				$prevPage = 0;
			}

			$shipList = objectToArray($ships, array('eveDB'));
			
			return $this->render('ships', array('ships' => $shipList, 'pageCount' => $pageCount, 
									'pageNum' => $pageNum, 'nextPage' => $nextPage, 'prevPage' => $prevPage, 'corp' => isset($_GET['corp'])));
		} else {
			$assets = array();

			foreach ($fullAssetList as $asset) {
				if (!empty($asset->locationID)) {
					if (!isset($assets[(string)$asset->locationID])) {
						$assets[(string)$asset->locationID] = array();
						$assets[(string)$asset->locationID]['location'] = $asset->location;
						$assets[(string)$asset->locationID]['locationId'] = $asset->locationID;
						$assets[(string)$asset->locationID]['locationName'] = $asset->locationName;
						$assets[(string)$asset->locationID]['assets'] = array();
					}
					if ($asset->contents)
						usort($asset->contents, array($this, 'assetSlotSort'));
					$assets[(string)$asset->locationID]['assets'][] = $asset;

					usort($assets[(string)$asset->locationID]['assets'], array($this, 'assetSlotSort'));
				}
			}
			usort($assets, array($this, 'assetStationSort'));

			
			if (count($assets) > 15) {
				$assets = array_chunk($assets, 15);

				$pageCount = count($assets);
				$pageNum = max((int)$_GET['p'], 0);
				$nextPage = min($pageNum + 1, $pageCount);
				$prevPage = max($pageNum - 1, 0);

				$assets = $assets[$pageNum];
			} else {
				$pageCount = 0;
				$pageNum = 0;
				$nextPage = 0;
				$prevPage = 0;
			}

			$assetList = objectToArray($assets, array('eveDB'));
			
			return $this->render('assets', array('assets' => $assetList, 'pageCount' => $pageCount, 
									'pageNum' => $pageNum, 'nextPage' => $nextPage, 'prevPage' => $prevPage, 'corp' => isset($_GET['corp'])));
		}
	}

	function searchAsset($ass, $search) {
		$result = array();

		for ($i = 0; $i < count($ass); $i++) {
			if ($ass[$i]->contents)
				$result = array_merge($result, $this->searchAsset($ass[$i]->contents, $search));
			if ((stripos($ass[$i]->item->typename, $search) !== false) ||(stripos($ass[$i]->locationName, $search) !== false))
				array_push($result, $ass[$i]);
		}
		return $result;
	}

	function searchAssetCategory($ass, $search) {
		$result = array();

		for ($i = 0; $i < count($ass); $i++) {
			#$ass[$i]->item->getGroup();
			if (($ass[$i]->item->group) && ($ass[$i]->item->group->category) && ($ass[$i]->item->group->category->categoryid == $search))
				if (($search <> 6) || (($search == 6) && ($ass[$i]->item->groupid <> 31)))      // nasty way to filter shuttles from the ships list
					$result[] = $ass[$i];
			if ($ass[$i]->contents)
				$result = array_merge($result, $this->searchAssetCategory($ass[$i]->contents, $search));
		}

		return $result;
	}

    function assetStationSort($a, $b) {
        if ($a['locationName'] == $b['locationName'])
            return 0;
        return ($a['locationName'] < $b['locationName']) ? -1 : 1;
    }

    function assetSlotSort($a, $b) {
        if ($a->flagText == $b->flagText)
            return 0;
        return ($a->flagText < $b->flagText) ? -1 : 1;
    }

    function assetNameSort($a, $b) {
        if ($a->item->typename == $b->item->typename)
            return 0;
        return ($a->item->typename < $b->item->typename) ? -1 : 1;
    }
	
	function render($template, $vars) {
		$tpl = new Smarty();

		$tpl->registerPlugin('modifier', 'eveNum', 'eveNum');
		$tpl->registerPlugin('modifier', 'eveNumInt', 'eveNumInt');
		$tpl->registerPlugin('modifier', 'eveRoman', 'eveRoman');
		$tpl->registerPlugin('modifier', 'formatTime', 'formatTime');
		$tpl->registerPlugin('modifier', 'yesNo', 'yesNo');

		$tpl->template_dir = 'modules/ooe/templates/';
		$tpl->compile_dir = smarty_compile;
		
		foreach ($vars as $var => $value)
			$tpl->assign($var, $value);

		$tpl->assign('index', URL_INDEX .'?module=ooe');
		$tpl->assign('url_params', $_GET);

		return $tpl->fetch($template . '.html');
	}
}




class Assets {
	var $typeID = 0;
	var $itemID = 0;
	var $flag = 0;
	var $qty = 0;
	var $locationID = 0;
	var $locationName = '';

	var $location = null;
	var $item = null;

	var $flagText = '';

	var $contents = false;

	// internal use id. seems the api duplicates the same ID multiple items within a /single/ result set.
	var $_ooe_id = 0;

	function Assets($db, $asset, $parentLocation = null) {
		$this->typeID = (int)$asset['typeID'];
		$this->itemID = (int)$asset['itemID'];
		$this->flag = (int)$asset['flag'];
		if (isset($asset['locationID'])) {
			$this->locationID = (int)$asset['locationID'];
			$this->location = $db->eveStation($this->locationID);
			$this->locationName = $this->location->stationname;
			if ($this->location->stationid == 0) {
				$this->location = $db->eveSolarSystem($this->locationID);
				$this->locationName = $this->location->solarsystemname;
			}
		} else if (isset($parentLocation)) {
			$this->location = $parentLocation;
			if (isset($parentLocation->stationid))
				$this->locationID = $parentLocation->stationid;
			else
				$this->locationID = $parentLocation->solarsystemid;
		}
		$this->item = $db->eveItem($this->typeID);
		$this->item->getGroup();
		$this->qty = (int)$asset['quantity'];

		$this->flagText = $db->flagText($this->flag);

		if (isset($asset->rowset) && ($asset->rowset['name'] == 'contents')) {
			$this->contents = array();
			foreach ($asset->rowset->row as $subAsset)
				$this->contents[] = new Assets($db, $subAsset, $this->location);
		}
		
		$this->_ooe_id = $this->itemID . '_' . $this->typeID . '_' . mt_rand();
	}
}
?>