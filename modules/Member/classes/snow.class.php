<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class Snow extends MemberWorld {
	public $db = null;
	public $User = null;
	
	public $chars = array();
	
	public function __construct($User) {
		if ( !$this->db ) parent::__construct( $User );
		
		$this->eveTime = time()-date('Z');
		$this->getChars();
	}
	
	
	
	public function listNews() {
		$news = array();
		$time30daysago = $this->eveTime - (60*60*24*30);
		
		$query = "SELECT n.dateTime, n.type, c.charID, c.name 
			FROM {$this->_table['snow_news']} n, {$this->_table['snow_characters']} c
			WHERE n.charID = c.charID
			AND n.dateTime > {$time30daysago}
			AND c.corpID = '{$this->User->corpID}'
			ORDER BY dateTime DESC LIMIT 200";
		
		$news = $this->db->fetch_all( $query ); 
		
		return $news;
	}
	
	public function listMembers($smarty) {
		$query = "SELECT c.charID
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
			WHERE a.charID IS NOT NULL
			AND c.inCorp = 1
			AND c.corpID = '{$this->User->corpID}'";
		$results = $this->db->query( $query );
		$numAlt = $results->num_rows;
		
		$query = "SELECT c.charID
			FROM {$this->_table['snow_characters']} c
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
			WHERE a.charID IS NULL
			AND c.inCorp = 1
			AND c.corpID = '{$this->User->corpID}'
			AND c.inactive = 1";
		$results = $this->db->query( $query );
		$numInactive = $results->num_rows;
		
		$time30daysago = (time() - date('z')) - 60*60*24*30;
		$query = "SELECT c.charID
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
			WHERE a.charID IS NULL
			AND c.inCorp = 1
			AND c.corpID = '{$this->User->corpID}'
			AND c.joined > {$time30daysago}
			ORDER BY c.name";
		$results = $this->db->query( $query );
		$numNew = $results->num_rows;
		
		$time3daysago = (time() - date('z')) - 60*60*24*3;
		$query = "SELECT c.charID
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
			WHERE a.charID IS NULL
			AND c.inCorp = 1
			AND c.corpID = '{$this->User->corpID}'
			AND c.lastSeen > {$time3daysago}
			ORDER BY c.name";
		$results = $this->db->query( $query );
		$numRecent = $results->num_rows;

		$query = "SELECT distinct c.charID, c.lastSeen, c.inactive, c.joined
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
			WHERE a.charID IS NULL
			AND c.inCorp = 1
			AND c.corpID = '{$this->User->corpID}'
			ORDER BY c.name";
		$results = $this->db->query( $query );
		$numMain = $results->num_rows;
		
		$smarty->assign('numMain', 		$numMain);
		$smarty->assign('numAlt', 		$numAlt);
		$smarty->assign('numRecent', 	$numRecent);
		$smarty->assign('numNew', 		$numNew);
		$smarty->assign('numInactive',  $numInactive);
		/*
		$return = array();
		while( $char = $results->fetch_array() ) {
			$return[] = $char;
		}
		*/
		$return = $this->chars;
		return $return;
	}
	
	public function listDivision($smarty) {
		
		switch (@$_GET['division']) {
			default:
			case '1': # pvp
				$div = "AND c.division = 1";
				break;
			case '2': # Mining
				$div = "AND c.division = 2";
				break;
			case '3': # Pos
				$div = "AND c.division = 3";
				break;
			case '4': # Support
				$div = "AND c.division = 4";
				break;
			case '5': # HighCommand
				$div = "AND c.division = 5";
				break;
			case '6': # Leaders
				$div = "AND c.division = 6";
				break;
			case '7': # None
				$div = "AND c.division = 0 AND a.charID IS NULL";
				break;
			case '8': # Legend
				$div = "AND c.division = 7";
				break;
		}
		$mainsSql = "SELECT DISTINCT c.*, j.pos, j.exempt, j.legacy, j.probation 
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
			LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID
			WHERE c.inCorp = 1 {$div} AND c.corpID = '{$this->User->corpID}'
			ORDER BY c.name";
		$mains_res = $this->db->query( $mainsSql );
		
		$altsSql = "SELECT a.altOf, a.charID, c.name
			FROM {$this->_table['snow_alts']} a 
			JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
			WHERE c.corpID = '{$this->User->corpID}' 
			AND c.inCorp = 1
			ORDER BY a.altOf, c.name;";
		$alts_res = $this->db->query( $altsSql );
		
		$altss = array();
		while ( $alts = $alts_res->fetch_assoc() ) {
			$a['charID'] = $alts['charID'];
			$a['name'] = $alts['name'];
			$altss[$alts['altOf']][] = $a;
		}
		
		$num = $mains_res->num_rows;
		
		$chars = array();
		while ( $main = $mains_res->fetch_assoc() ) {
			$chars[$main['charID']] = $main;
			if( isset($altss[$main['charID']]) ){
				foreach( $altss[$main['charID']] as $alt )
					$chars[$main['charID']]['alts'][$alt['charID']] = $alt;
			}
		}
		$smarty->assign('numDiv', $num);
		$smarty->assign('list', $chars);
		
		return true;
	}
	
	public function listFlags($smarty) {
		$join = '';
		switch (@$_GET['flag']) {
			default:
			case '1': # listInactive
				$where = "a.charID IS NULL AND c.inCorp = 1 AND c.inactive = 1";
				break;
			case '2': # listAltNoMain
				$join = " LEFT JOIN {$this->_table['snow_characters']} mains ON a.altOf = mains.charID ";
				$where = "a.charID IS NOT NULL AND c.inCorp = 1 AND mains.inCorp = 0";
				break;
			case '3': # listAFK
				$where = "a.charID IS NULL AND c.inCorp = 1 AND c.afk = 1";
				break;
			case '4': # listNotes
				$where = "a.charID IS NULL AND c.inCorp = 1 AND c.notes != ''";
				break;
			case '5': # listInvestigate
				$where = "a.charID IS NULL AND c.inCorp = 1 AND c.investigate = 1";
				break;
			case '6': # listProbation
				$where = "a.charID IS NULL AND c.inCorp = 1 AND j.probation = 1";
				break;
		}
		$mainsSql = "SELECT DISTINCT c.*, j.pos, j.exempt, j.legacy, j.probation 
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID 
			LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID 
			{$join}
			WHERE {$where} AND c.corpID = '{$this->User->corpID}'
			ORDER BY c.name";
		$mains_res = $this->db->query( $mainsSql );
		
		$altsSql = "SELECT a.altOf, a.charID, c.name
			FROM {$this->_table['snow_alts']} a 
			JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
			WHERE c.corpID = '{$this->User->corpID}' 
			AND c.inCorp = 1
			ORDER BY a.altOf, c.name;";
		$alts_res = $this->db->query( $altsSql );
		
		$altss = array();
		while ( $alts = $alts_res->fetch_assoc() ) {
			$a['charID'] = $alts['charID'];
			$a['name'] = $alts['name'];
			$altss[$alts['altOf']][] = $a;
		}
		
		$num = $mains_res->num_rows;
		
		$chars = array();
		while ( $main = $mains_res->fetch_assoc() ) {
			$chars[$main['charID']] = $main;
			if( isset($altss[$main['charID']]) ){
				foreach( $altss[$main['charID']] as $alt )
					$chars[$main['charID']]['alts'][$alt['charID']] = $alt;
			}
		}
		$smarty->assign('numFlag', $num);
		$smarty->assign('list', $chars);
		
		return true;
	}
	
	public function listStats($smarty) {
		
		switch (@$_GET['stat']) {
			default:
			case '1': # listDread
				$div = "AND c.dread > 0";
				break;
			case '2': # listCarrier
				$div = "AND c.carrier > 0";
				break;
			case '3': # listPOSGunners
				$div = "AND c.posgunner = 1";
				break;
			case '4': # listTZEuro a.charID IS NULL
				$div = "AND c.tz = 1";
				break;
			case '5': # listTZAmerican
				$div = "AND c.tz = 2";
				break;
			case '6': # listTZOceanic
				$div = "AND c.tz = 3";
				break;
		}
		$mainsSql = "SELECT DISTINCT c.*, a.charID as aID, j.pos, j.exempt, j.legacy, j.probation 
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
			LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID
			WHERE c.inCorp = 1 {$div} AND c.corpID = '{$this->User->corpID}'
			ORDER BY c.name";
		$mains_res = $this->db->query( $mainsSql );
		
		$altsSql = "SELECT a.altOf, a.charID, c.name
			FROM {$this->_table['snow_alts']} a 
			JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
			WHERE c.corpID = '{$this->User->corpID}' 
			AND c.inCorp = 1
			ORDER BY a.altOf, c.name;";
		$alts_res = $this->db->query( $altsSql );
		
		$altss = array();
		while ( $alts = $alts_res->fetch_assoc() ) {
			$a['charID'] = $alts['charID'];
			$a['name'] = $alts['name'];
			$altss[$alts['altOf']][] = $a;
		}
		
		$num = $mains_res->num_rows;
		
		$chars = array();
		while ( $main = $mains_res->fetch_assoc() ) {
			$chars[$main['charID']] = $main;
			if( isset($altss[$main['charID']]) ){
				foreach( $altss[$main['charID']] as $alt )
					$chars[$main['charID']]['alts'][$alt['charID']] = $alt;
			}
		}
		$smarty->assign('numStats', $num);
		$smarty->assign('list', $chars);
		
		return true;
	}

	public function listEval($smarty) {
		
		switch (@$_GET['eva']) {
			default:
			case '1': # listEvalPvP
				$div = "AND c.division = 1";
				break;
			case '2': # listEvalMining
				$div = "AND c.division = 2";
				break;
			case '3': # listEvalPOS
				$div = "AND c.division = 3";
				break;
			case '4': # listEvalSupport
				$div = "AND c.division = 4";
				break;
			case '5': # listEvalNone
				$div = "AND c.division = 0 AND a.charID IS NULL";
				break;
		}
		$mainsSql = "SELECT DISTINCT c.*, a.charID as aID, j.pos, j.exempt, j.legacy, j.probation 
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
			LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID
			WHERE c.inCorp = 1 {$div} AND c.corpID = '{$this->User->corpID}'
			ORDER BY c.name";
		$mains_res = $this->db->query( $mainsSql );
		
		$altsSql = "SELECT a.altOf, a.charID, c.name
			FROM {$this->_table['snow_alts']} a 
			JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
			WHERE c.corpID = '{$this->User->corpID}' 
			AND c.inCorp = 1
			ORDER BY a.altOf, c.name;";
		$alts_res = $this->db->query( $altsSql );
		
		$evalSql = "SELECT c.charID, e.*
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_evaluation']} e ON c.charID = e.charID
			WHERE c.inCorp = 1 AND c.corpID = '{$this->User->corpID}'
			ORDER BY c.name, e.date DESC;";
		$eval_res = $this->db->query( $evalSql );
		
		$altss = array();
		while ( $alts = $alts_res->fetch_assoc() ) {
			$a['charID'] = $alts['charID'];
			$a['name'] = $alts['name'];
			$altss[$alts['altOf']][] = $a;
		}
		
		#$eval = array(); 
		$i=0;
		while ( $row = $eval_res->fetch_assoc() ) {
			if ($row['charID'] !== null) {
				if (!isset($char)) $char = $row['charID'];
				if ($char != $row['charID']) { $i=0; $char = $row['charID']; }
				if ($i < 6) $eval[$row['charID']][$i] = $row;
				if ($char == $row['charID']) $i++;
			}
		}
		#echo '<pre>'; print_r($eval); echo '</pre>'; die;
		$num = $mains_res->num_rows;
		
		foreach($eval as &$xx) {
			foreach($xx as &$yy) {
				$yy['comment'] = addslashes($yy['comment']);
				$yy['comment'] = preg_replace("/\r\n|\n|\r/", "<br />", $yy['comment']); 
				$yy['comment'] = str_replace('<br />', '\n', $yy['comment']);
			}
		}
		
		$chars = array();
		while ( $main = $mains_res->fetch_assoc() ) {
			$chars[$main['charID']] = $main;
			if( isset($altss[$main['charID']]) ){
				foreach( $altss[$main['charID']] as $alt )
					$chars[$main['charID']]['alts'][$alt['charID']] = $alt;
			}
			if( isset($eval[$main['charID']]) ){
				$chars[$main['charID']]['eval'] = $eval[$main['charID']];
			}
		}
		
		$smarty->assign('numEval', $num);
		$smarty->assign('list', $chars);
		
		return true;
	}	
	
	
	private function getChars() {
		$mainsSql = "SELECT DISTINCT c.*, j.pos, j.exempt, j.legacy, j.probation
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID 
			LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID
			WHERE a.charID IS NULL
			AND c.corpID = '{$this->User->corpID}'
			AND c.inCorp = 1
			ORDER BY c.name;";
		$mains_res = $this->db->query( $mainsSql );
		
		$altsSql = "SELECT a.altOf, a.charID, c.name
			FROM {$this->_table['snow_alts']} a 
			JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
			WHERE c.corpID = '{$this->User->corpID}' 
			AND c.inCorp = 1
			ORDER BY a.altOf, c.name;";
		$alts_res = $this->db->query( $altsSql );
		
		while ( $alts = $alts_res->fetch_assoc() ) {
			$a['charID'] = $alts['charID'];
			$a['name'] = $alts['name'];
			$altss[$alts['altOf']][] = $a;
		}
		
		while ( $main = $mains_res->fetch_assoc() ) {
			$this->chars[$main['charID']] = $main;
			if( isset($altss[$main['charID']]) ){
				foreach( $altss[$main['charID']] as $alt )
					$this->chars[$main['charID']]['alts'][$alt['charID']] = $alt;
			}
		}
	}
	
}

?>