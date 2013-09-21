<?php
	class Kill extends MemberWorld {
		public $db = null;
		private $corp = null;
		
		public $months = array();
		public $chars = array();
		
		private $joinDates = array();
		private $times = array();
		private $kill = array();
		private $loss = array();
		
		public function __construct( $User ) {
			if ( !$this->db ) parent::__construct( $User );
			
			$this->corp = $this->User->corpID;
			$this->joinDate();
			$this->listKills($_GET['state']);
			$this->getKills();
			$this->getPlayer();
		}
		
		function getPlayer() {
			foreach ($this->chars as $key => $char) {
				#$char['charID'];
				for($i = 0; $i < 6; $i++) {
					if ($this->times[$i] < $char['joined']) {
						$this->chars[$key]['kill'][$i]['kills'] = '*';
						$this->chars[$key]['kill'][$i]['loss'] = '*';
					} else {
						$this->chars[$key]['kill'][$i]['kills'] = $this->kill[$i][$char['charID']] ? $this->kill[$i][$char['charID']] : 0;
						$this->chars[$key]['kill'][$i]['loss'] = $this->loss[$i][$char['charID']] ? $this->loss[$i][$char['charID']] : 0;
					}
				}
			}
			
			#$chars[$i]['kill'] = getKills($row['charID']);
			if($time2 < $joined) $player[$i]['kills'] = '*';
			else $player[$i]['kills'] += $row['total'];	
		}
		
		function joinDate() {
			$join = array();
			$result = $this->db->query("SELECT charID, joined FROM {$this->_table['snow_characters']} WHERE corpID = {$this->corp};");
			while( $row = $result->fetch_assoc() ) {
				if( $row ) $join[$row['charID']] = $row['joined'];	
			}
			
			$this->joinDates = $join;
		}
		
		function listKills($state = 0) {
			global $smarty;
			
			switch (@$state) {
				default:
				case '0': # Probation
					$div = "AND j.probation = 1";
					break;
				case '1': # PVP
					$div = "AND c.division = 1";
					break;
				case '2': # Mining
					$div = "AND c.division = 2";
					break;
				case '3': # POS
					$div = "AND c.division = 3";
					break;
				case '4': # Support
					$div = "AND c.division = 4";
					break;
				case '5': # high command
					$div = "AND c.division = 5";
					break;
				case '6': # leader
					$div = "AND c.division = 6";
					break;
				case '7': # leagend
					$div = "AND c.division = 7";
					break;
				case '8': # all
					$div = "AND a.charID IS NULL";
					break;
			}
			$mainsSql = "SELECT DISTINCT c.*, a.charID as aID, j.pos, j.exempt, j.legacy, j.probation 
				FROM {$this->_table['snow_characters']} c 
				LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID
				LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID
				WHERE c.inCorp = 1 {$div} AND c.corpID = '{$this->corp}'
				ORDER BY c.name";
			$mains_res = $this->db->query( $mainsSql );
			
			$altsSql = "SELECT a.altOf, a.charID, c.name
				FROM {$this->_table['snow_alts']} a 
				JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
				WHERE c.corpID = '{$this->corp}' 
				AND c.inCorp = 1
				ORDER BY a.altOf, c.name;";
			$alts_res = $this->db->query( $altsSql );
			
			$altss = array();
			while ( $alts = $alts_res->fetch_assoc() ) {
				$a['charID'] = $alts['charID'];
				$a['name'] = $alts['name'];
				$altss[$alts['altOf']][] = $a;
			}
			
			$numMain = $mains_res->num_rows;
			
			$this->chars = array();
			while ( $main = $mains_res->fetch_assoc() ) {
				$this->chars[$main['charID']] = $main;
				if( isset($altss[$main['charID']]) ){
					foreach( $altss[$main['charID']] as $alt )
						$this->chars[$main['charID']]['alts'][$alt['charID']] = $alt;
				}
			}

			$month = date('n');
			$year  = date('Y');
			
			$months = array();
			for($i = 0; $i < 6; $i++) {
				$months[] = date('F',gmmktime(0,0,0,$month)); if($month > 1) $month--; else $month = 12;
			}
			$this->months = $months;
		}
		
		function getKills() {
						
			$year  = date('Y');
			$month = date('n');
				
			for($i = 0; $i < 6; $i++) {
				if($month == 12) $mo = 1; else $mo = 0;
				
				//getting the span of the current month
				$time1 = gmmktime(0,0,0, $month, 1, $year);
				$time1 += (9*60*60);
				#echo " ".date('Y.m.d H:i',$time1)." ";
				if($month < 12) $month++;
				else $month -= 11;
				
				if($mo == 1) $year++;
				
				$time2 = gmmktime(0,0,0, $month, 0, $year);
				$time2 += (9*60*60);
				#echo ' -- '.date('Y.m.d H:i',$time2)."<br />";
				if($mo == 1) $year--;
				
				if ($month > 2)	$month -= 2;
				else if ($month == 2) $month = 12;
				else $month = 11;
				if ($month == 12) $year--;
				
				$this->times[$i] = $time2;
				
				//getting number of kills grouping by pilot id
				$res = $this->db->query("SELECT Count(main_id) AS total, main_id FROM {$this->_table['snow_kills']} WHERE (timestamp >= {$time1} AND timestamp <= {$time2}) AND loss = 0 GROUP BY main_id;");
				while( $row = $res->fetch_assoc() ) {
					$this->kill[$i][$row['main_id']] = $row['total'];
				}
				
				//getting number of losses grouping by pilot id
				$res = $this->db->query("SELECT Count(main_id) AS total, main_id FROM {$this->_table['snow_kills']} WHERE (timestamp >= {$time1} AND timestamp <= {$time2}) AND loss = 1 GROUP BY main_id;");
				while( $row = $res->fetch_assoc() ) {
					$this->loss[$i][$row['main_id']] = $row['total'];
				}
			}
		}
		
	}
?>