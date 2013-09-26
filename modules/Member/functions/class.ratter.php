<?php

class Ratter extends MemberWorld {
	
	private $show_rats_bounty_bigger = 2500000;
	private $show_max_fancy_kills = 20;
	
	public $db = null;
	private $period_filter = '';
	
	private $corp_id = null;
	private $corpName = null;
	private $charid = null;
	
	private $chars = null;
	
	public $date_filter_res = array( 'maxdate' => 'none', 'mindate' => 'none' );
	public $total_ratting_selected_filter = null;
	public $total_ratting_selected_filter_corptax = null;
	
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		
		$this->stime = microtime(true);
		$this->corp_id = $this->User->corpID;
		
		$this->period_filter();
		$this->npcs();
		$this->corpName = $this->db->fetch_one( "SELECT corp FROM {$this->_table['snow_wallet']} WHERE corpid = ".$this->corp_id , 'corp' );
		$this->stuff();
	}
	
	public function get_content() {
		switch( @$_GET['ratting'] ) {
			default:
			case 'player':
				if( isset( $_GET['char'] ) ){
					$_GET['ratting'] = 'system';
					return $this->ratting_by_system();
					break;
				} else {
					$_GET['ratting'] = 'player';
					return $this->listCarebears();
					break;
				}
			
			case 'system':
				return $this->ratting_by_system();
				break;
			
			case 'region':
				return $this->ratting_by_region();
				break;
			
			case 'highsec':
				return $this->highseconly();
				break;
				
			case 'fancy':
				return $this->domi_kills();
				break;
				
			case 'missionplayer':
				return $this->mission_all();
				break;
				
			case 'missionagents':
				return $this->mission_agents();
				break;
				
			case 'npc':
				return $this->ratting_by_rattype();
				break;
			
			case 'day':
				return $this->player_ratting_by_day();
				break;
		}
	}
	
	private function stuff() {
		$sql = "SELECT DATE_FORMAT( MAX( w.date ), '%W %D %M %Y' ) AS maxdate, DATE_FORMAT( MIN( w.date ), '%W %D %M %Y' ) AS mindate
			FROM {$this->_table['snow_wallet']} w
			WHERE 1 {$this->period_filter} AND corpid = '{$this->corp_id}' ";
		
		$this->date_filter_res = $this->db->fetch_one($sql);
		
		$str = "select SUM( w.amount ) AS corptax, SUM( w.amount2 ) AS ratBountys FROM {$this->_table['snow_wallet']} w
			WHERE 1 {$this->period_filter} AND w.corpid = '{$this->corp_id}' ";
		
		$row = $this->db->fetch_one( $str );
		
		$this->total_ratting_selected_filter = $row['ratBountys'];
		$this->total_ratting_selected_filter_corptax = $row['corptax'];
	}
	
	private function npcs() {
		$this->gutrats = array(
			'Domination%',
			'Dread Guristas%',
			'Shadow Serpentis%',
			'Dark Blood%',
			'True Sansha%',
		//	"Jorun \'Red Legs\' Greaves"
		);

		$this->officers = array(
			'Gotan Kreiss', 'Hakim Stormare', 'Mizuro Cybon', 'Tobias Kruzhor', // angel
			'Ahremen Arkah', 'Draclira Merlonne', 'Raysere Giant', 	'Tairei Namazoth', // blood raiders
			'Estamel Tharchon', 'Kaikka Peunato', 'Thon Eney', 	'Vepas Minimala', // guristas
			'Brokara Ryver', 'Chelm Soran', 'Selynne Mardakar', 'Vizan Ankonin',  // sansha
			'Brynn Jerdola', 'Cormack Vaaja', 'Setele Schellan', 'Tuvan Orth', // serpentis
		#	'Angel Malakim',
		#	'Domination War General',
		);
	}
	
	private function period_filter() {
	
		switch( @$_GET['period'] ) {
			case 'last24h':
				$this->period_filter = ' AND w.date > DATE_SUB(NOW(),INTERVAL 24 HOUR) ';
				break;
				
			case 'last30days':
				$this->period_filter = ' AND w.date > DATE_SUB(NOW(),INTERVAL 30 DAY) ';
				break;
				
			case 'alltime':
				$_GET['period'] = 'alltime';
				
				break;
				
			default:
			case 'last7days':
				$_GET['period'] = 'last7days';
				$this->period_filter = ' AND w.date > DATE_SUB(NOW(),INTERVAL 7 DAY) ';
				break;
		}
		
		#$this->get_chars();
		
		if( !empty($_GET['char']) ) {
			$str = "SELECT w.charid FROM {$this->_table['snow_wallet']} w WHERE w.char LIKE '".addslashes($_GET['char'])."' LIMIT 1 ";
			#print $str; die;
			$this->charid = (int)$this->db->fetch_one( $str , 'charid' );
			if( !$this->charid ) {
				$this->charid = 1;
			}	
			$this->period_filter .= " AND w.charid = '".$this->charid."' ";	
		}
	}
	
	private function get_chars() {
		$str = "SELECT w.char, w.charid, w.corp FROM {$this->_table['snow_wallet']} w
			WHERE 1 {$this->period_filter} AND corpid = '{$this->corp_id}'
			GROUP BY w.charid
			ORDER BY w.char ASC";
		$this->chars = $this->db->fetch_all( $str );
	}
	
	private function listCarebears() {
		
		$mainsSql = "SELECT DISTINCT c.*, j.pos, j.exempt, j.legacy, j.probation
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID 
			LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID
			WHERE a.charID IS NULL
			AND c.corpID = '{$this->corp_id}'
			AND c.inCorp = 1
			ORDER BY c.name;";
		$mains_res = $this->db->query( $mainsSql );
		#$_SESSION['messages']->addwarning(round((microtime(true) - $this->stime),3));
		$altsSql = "SELECT a.altOf, a.charID, c.name
			FROM {$this->_table['snow_alts']} a 
			INNER JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
			WHERE c.corpID = '{$this->corp_id}' 
			AND c.inCorp = 1
			ORDER BY a.altOf, c.name;";
		$alts_res = $this->db->query( $altsSql );
		#$_SESSION['messages']->addwarning(round((microtime(true) - $this->stime),3));
		while ( $alts = $alts_res->fetch_assoc() ) {
			$a['charID'] = $alts['charID'];
			$a['name'] = $alts['name'];
			$this->alts[$alts['altOf']][] = $a;
		}
		
		$str = "SELECT w.char AS charName, w.charid as charID, SUM( w.amount2 ) AS amount
			FROM {$this->_table['snow_wallet']} w
			WHERE 1 {$this->period_filter} AND w.corpid = '{$this->corp_id}'
			GROUP BY w.charid
			ORDER BY w.charid DESC;";
		$res = $this->db->query( $str );
		while ($out = $res->fetch_assoc()) {
			$isk[$out['charID']] = $out['amount'];
		}
		
		$a = array();
		while ( $main = $mains_res->fetch_assoc() ) {
			$this->char[$main['charID']] = $main;
			$this->char[$main['charID']]['isk'] = isset($isk[$main['charID']]) ? $isk[$main['charID']] : 0;
			#$this->char[$main['charID']]['alts'][] = $main['charID'];
			$a[$main['charID']]['isk'][] = isset($isk[$main['charID']]) ? $isk[$main['charID']] : 0; 
			if( isset($this->alts[$main['charID']]) ){
				foreach( $this->alts[$main['charID']] as $alts ) {
					$this->char[$main['charID']]['alts'][$alts['charID']] = $alts;
					$this->char[$main['charID']]['alts'][$alts['charID']]['isk'] = isset($isk[$alts['charID']]) ? $isk[$alts['charID']] : 0;
					$a[$main['charID']]['isk'][] = isset($isk[$alts['charID']]) ? $isk[$alts['charID']] : 0;
				}
			}
			$this->char[$main['charID']]['ratBountys'] = array_sum($a[$main['charID']]['isk']);
		}
		
		unset($a);
		$head = array('Name', 'Bountys');
		return array( 'sort' => 1, 'head' => $head, 'body' => $this->char);
	
	}
	
	private function ratting_all() {
	
		$str = "SELECT w.char AS charName, w.charid as _charid, SUM( w.amount2 ) AS ratBountys
				FROM {$this->_table['snow_wallet']} w
				WHERE 1 {$this->period_filter} AND w.corpid = '{$this->corp_id}'
				GROUP BY w.charid
				ORDER BY SUM( w.amount2 ) DESC
				";
		
		return $this->db->fetch_all( $str );
		
	}
	
	private function mission_all() {
	
		$str = "SELECT w.char AS charName, w.charid as charID, SUM( w.amount2 ) AS amount
				FROM {$this->_table['snow_wallet']} w
				WHERE w.agent_name IS NOT NULL 
				{$this->period_filter} AND w.corpid = '{$this->corp_id}'
				GROUP BY w.charid
				ORDER BY SUM( w.amount2 ) DESC";
		
		$mainsSql = "SELECT DISTINCT c.*, j.pos, j.exempt, j.legacy, j.probation
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID 
			LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID
			WHERE a.charID IS NULL
			AND c.corpID = '{$this->corp_id}'
			AND c.inCorp = 1
			ORDER BY c.name;";
		$mains_res = $this->db->query( $mainsSql );
		
		$altsSql = "SELECT a.altOf, a.charID, c.name
			FROM {$this->_table['snow_alts']} a 
			INNER JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
			WHERE c.corpID = '{$this->corp_id}' 
			AND c.inCorp = 1
			ORDER BY a.altOf, c.name;";
		$alts_res = $this->db->query( $altsSql );
		
		while ( $alts = $alts_res->fetch_assoc() ) {
			$a['charID'] = $alts['charID'];
			$a['name'] = $alts['name'];
			$this->alts[$alts['altOf']][] = $a;
		}
		
		$res = $this->db->query( $str );
		while ($out = $res->fetch_assoc()) {
			$isk[$out['charID']] = $out['amount'];
		}
		
		$a = array();
		while ( $main = $mains_res->fetch_assoc() ) {
			$this->char[$main['charID']] = $main;
			$this->char[$main['charID']]['isk'] = isset($isk[$main['charID']]) ? $isk[$main['charID']] : 0;
			#$this->char[$main['charID']]['alts'][] = $main['charID'];
			$a[$main['charID']]['isk'][] = isset($isk[$main['charID']]) ? $isk[$main['charID']] : 0; 
			if( isset($this->alts[$main['charID']]) ){
				foreach( $this->alts[$main['charID']] as $alts ) {
					$this->char[$main['charID']]['alts'][$alts['charID']] = $alts;
					$this->char[$main['charID']]['alts'][$alts['charID']]['isk'] = isset($isk[$alts['charID']]) ? $isk[$alts['charID']] : 0;
					$a[$main['charID']]['isk'][] = isset($isk[$alts['charID']]) ? $isk[$alts['charID']] : 0;
				}
			}
			$this->char[$main['charID']]['ratBountys'] = array_sum($a[$main['charID']]['isk']);
		}
		
		unset($a);
		$head = array('Name', 'Bountys');
		return array( 'sort' => 1, 'head' => $head, 'body' => $this->char);
	}
	
	private function mission_agents() {
	
		$str_1 = "SELECT w.agent_id, COUNT(w.refID) as missions 
				FROM {$this->_table['snow_wallet']} w
				WHERE w.agent_name IS NOT NULL 
				{$this->period_filter} AND w.corpid = '{$this->corp_id}'
				AND w.reason LIKE '%|refID:33|%' 
				GROUP BY w.agent_id ";
			
		$str_2 = "SELECT w.agent_id, w.charid as char_id, COUNT(w.refID) as missions 
				FROM {$this->_table['snow_wallet']} w
				WHERE w.agent_name IS NOT NULL 
				{$this->period_filter} AND w.corpid = '{$this->corp_id}'
				AND w.reason LIKE '%|refID:33|%' 
				GROUP BY w.agent_id, w.charid ";

		#print $str_2; die; 
				
		$str = "SELECT w.agent_name, w.agent_id as _agent_id
				,ag.level, ag.quality, ag.isLocator as locate
			/*	, ag.factionName as faction	,ag.systemName, ag.systemSecurity as truesec, ag.regionName */
				, ms.missions
				, SUM( w.amount2 ) AS mission_rewards 
				FROM {$this->_table['snow_wallet']} w
				
				LEFT JOIN {$this->_table['agtagents']} ag ON ag.agentID = w.agent_id
				LEFT JOIN ({$str_1}) AS ms ON ms.agent_id = w.agent_id
		/*		
				INNER JOIN {$this->_table['mapsolarsystems']} es ON es.solarSystemID = w.system_id
				INNER JOIN {$this->_table['mapconstellations']} ec ON es.constellationID = ec.constellationID
				INNER JOIN {$this->_table['mapregions']} evr ON ec.regionID = evr.regionID
		*/	
				WHERE w.agent_name IS NOT NULL 
				{$this->period_filter} AND w.corpid = '{$this->corp_id}'
				GROUP BY w.agent_id
				ORDER BY SUM( w.amount2 ) DESC
				LIMIT 50;";
		#print $str; #die;
		#$mission_agents = fetch_all( $str );

		$mission_agents_list_unq = $mission_agent_whomake = array();
		foreach( $this->db->fetch_all( $str ) as $agent ) {
			$mission_agents_list_unq[$agent['_agent_id']] = $agent['_agent_id'];
			$mission_agent_whomake[$agent['_agent_id']] = array();
			
			$agent['_whomade'] = array();
			$mission_agents[$agent['_agent_id']] = $agent;
		}
		if( count($mission_agents_list_unq) ) {

			$str = "SELECT w.char, w.charid as _charid, ms.missions, SUM( w.amount2 ) AS mission_rewards, /*w.agent_name,*/ w.agent_id AS _agent_id
			FROM {$this->_table['snow_wallet']} w
			LEFT JOIN {$this->_table['agtagents']} ag ON ag.agentID = w.agent_id
			LEFT JOIN ({$str_2}) AS ms ON ms.agent_id = w.agent_id AND ms.char_id = w.charid
			WHERE ms.agent_id IN ( '".join("', '",$mission_agents_list_unq)."' )
			AND w.agent_name IS NOT NULL
			{$this->period_filter} AND w.corpid = '{$this->corp_id}'
			GROUP BY w.charid , w.agent_id
			ORDER BY SUM( w.amount2 ) DESC
			LIMIT 10000 "; // just to make sure we don't get some killing all dataset
	
	
			foreach( $this->db->fetch_all( $str ) as $row ) {
				$mission_agent_whomake[$row['_agent_id']][] = $row;
			#	foreach( $mission_agents as &$_row ) {
				
				if( isset($mission_agents[$row['_agent_id']]) ) {
					$mission_agents[$row['_agent_id']]['_whomade'][$row['_charid']] = $row;
				}
	
			}

		}
		$head = array( 'Agent', 'level', 'quality', 'locate', 'missions', 'mission_rewards' );
		return array( 'sort' => 9, 'head' => $head, 'body' => $mission_agents );
		#return $mission_agents;
		#print_r2(  $mission_agents , true );
		#print_r2(  $mission_agents_list_unq , true );
		#print_r2(  $mission_agent_whomake , true );
	
	}
	
	private function highseconly() {
		$str = "SELECT w.char AS charName, w.charid as charID, SUM( w.amount2 ) AS amount
			FROM {$this->_table['snow_wallet']} w
			INNER JOIN {$this->_table['mapsolarsystems']} es ON es.solarSystemID = w.system_id
			WHERE 1 {$this->period_filter} AND w.corpid = '{$this->corp_id}'
			AND es.security > 0.41
			GROUP BY w.charid
			ORDER BY SUM( w.amount2 ) DESC";
		
		$mainsSql = "SELECT DISTINCT c.*, j.pos, j.exempt, j.legacy, j.probation
			FROM {$this->_table['snow_characters']} c 
			LEFT JOIN {$this->_table['snow_alts']} a ON c.charID = a.charID 
			LEFT JOIN {$this->_table['snow_jobs']} j ON c.charID = j.charID
			WHERE a.charID IS NULL
			AND c.corpID = '{$this->corp_id}'
			AND c.inCorp = 1
			ORDER BY c.name;";
		$mains_res = $this->db->query( $mainsSql );
		
		$altsSql = "SELECT a.altOf, a.charID, c.name
			FROM {$this->_table['snow_alts']} a 
			INNER JOIN {$this->_table['snow_characters']} c ON a.charID = c.charID
			WHERE c.corpID = '{$this->corp_id}' 
			AND c.inCorp = 1
			ORDER BY a.altOf, c.name;";
		$alts_res = $this->db->query( $altsSql );
		
		while ( $alts = $alts_res->fetch_assoc() ) {
			$a['charID'] = $alts['charID'];
			$a['name'] = $alts['name'];
			$this->alts[$alts['altOf']][] = $a;
		}
		
		$res = $this->db->query( $str );
		while ($out = $res->fetch_assoc()) {
			$isk[$out['charID']] = $out['amount'];
		}
		
		$a = array();
		while ( $main = $mains_res->fetch_assoc() ) {
			$this->char[$main['charID']] = $main;
			$this->char[$main['charID']]['isk'] = isset($isk[$main['charID']]) ? $isk[$main['charID']] : 0;
			#$this->char[$main['charID']]['alts'][] = $main['charID'];
			$a[$main['charID']]['isk'][] = isset($isk[$main['charID']]) ? $isk[$main['charID']] : 0; 
			if( isset($this->alts[$main['charID']]) ){
				foreach( $this->alts[$main['charID']] as $alts ) {
					$this->char[$main['charID']]['alts'][$alts['charID']] = $alts;
					$this->char[$main['charID']]['alts'][$alts['charID']]['isk'] = isset($isk[$alts['charID']]) ? $isk[$alts['charID']] : 0;
					$a[$main['charID']]['isk'][] = isset($isk[$alts['charID']]) ? $isk[$alts['charID']] : 0;
				}
			}
			$this->char[$main['charID']]['ratBountys'] = array_sum($a[$main['charID']]['isk']);
		}
		
		unset($a);
		$head = array('Name', 'Bountys');
		return array( 'sort' => 1, 'head' => $head, 'body' => $this->char);
		
	}
	
	private function ratting_by_system() {
		$str = "SELECT w.system AS systemName, evr.regionName as regionName, es.security as truesec, SUM( w.amount2 ) AS ratBountys
				FROM {$this->_table['snow_wallet']} w
				INNER JOIN {$this->_table['mapsolarsystems']} es ON es.solarSystemID = w.system_id
				INNER JOIN {$this->_table['mapconstellations']} ec ON es.constellationID = ec.constellationID
				INNER JOIN {$this->_table['mapregions']} evr ON ec.regionID = evr.regionID
				WHERE 1 {$this->period_filter} AND corpid = '{$this->corp_id}'
				GROUP BY system_id
				ORDER BY SUM( amount2 ) DESC
				LIMIT 100 ";
		# print $str;  die;
		$head = array('systemName', 'regionName', 'truesec', 'ratBountys');
		return array( 'sort' => 3, 'head' => $head, 'body' => $this->db->fetch_all( $str )); 
	}
	
	private function ratting_by_region() {
		$str = "SELECT evr.regionName as regionName, avg(es.security) as avg_truesec, COUNT(distinct w.system_id) AS dif_sys, SUM( w.amount2 ) AS ratBountys
				FROM {$this->_table['snow_wallet']} w
				INNER JOIN {$this->_table['mapsolarsystems']} es ON es.solarSystemID = w.system_id
				INNER JOIN {$this->_table['mapconstellations']} ec ON es.constellationID = ec.constellationID
				INNER JOIN {$this->_table['mapregions']} evr ON ec.regionID = evr.regionID
				WHERE 1 {$this->period_filter} AND corpid = '{$this->corp_id}'
				GROUP BY evr.regionID
				ORDER BY SUM( amount2 ) DESC
				LIMIT 100 ";
		$head = array('regionName', 'avg_truesec', 'dif_sys', 'ratBountys');
		return array( 'sort' => 3, 'head' => $head, 'body' => $this->db->fetch_all( $str ));
	}
	
	private function domi_kills() {
		$domi_where = ''; 
		$domi_where_arr = array();
		foreach($this->gutrats as $ratf ) {
			$domi_where_arr [] = " (ei.typeName LIKE '{$ratf}') ";
		}
		foreach($this->officers as $ratf ) {
			$domi_where_arr [] = " (ei.typeName LIKE '{$ratf}') ";
		}

		if( count($domi_where_arr) ) {
			$domi_where = " (".join(' OR ',$domi_where_arr).") ";
		}
		#print $domi_where;

		$str = "SELECT w.char AS charName, w.charid as _charid, ei.typeName AS ratName, /* w.corp AS corpName,*/ 
			IFNULL(rb.valueInt,0) as ratBounty, /* IFNULL(ri.graphicId,0) as _ratImgId, ei.description as _description,*/
			w.system AS systemName, evr.regionName as regionName, es.security as truesec, w.date, er.ratid as _ratid, 
			unix_timestamp(w.date) as _date_unixtime
			FROM {$this->_table['snow_wallet']} w
			INNER JOIN {$this->_table['snow_ratkills']} er ON er.refID = w.refID
			INNER JOIN {$this->_table['invtypes']} ei ON ei.typeID = er.ratid
			INNER JOIN {$this->_table['mapsolarsystems']} es ON es.solarSystemID = w.system_id
			INNER JOIN {$this->_table['mapconstellations']} ec ON es.constellationID = ec.constellationID
			INNER JOIN {$this->_table['mapregions']} evr ON ec.regionID = evr.regionID
			LEFT JOIN {$this->_table['snow_rats_bountys']} rb ON rb.typeID = er.ratid 
			/*LEFT JOIN {$this->_table['snow_rats_imgs']} ri ON ri.typeID = er.ratid */
			WHERE ( {$domi_where} OR {$this->show_rats_bounty_bigger} < IFNULL(rb.valueInt,0) )
			{$this->period_filter} AND corpid = '{$this->corp_id}'
			ORDER BY w.date desc
			LIMIT ".(string)($this->show_max_fancy_kills*3)." ";
			
		$domi_kills2 = $this->db->fetch_all( $str );

		$index_old = $index_new = 0;
		$domi_kills = array();

		$time_dif_as_same = 25*60; // 25min

		#print_r2( $domi_kills2 );


		while( isset($domi_kills2[$index_old]) ) {	

			$current = $domi_kills2[$index_old];
			if( isset( $current['dublicate'] ) ) {
				$index_old++;
				continue;
			}
			
			$_chars = array( 
					array( 
							'charName'	=>	$current['charName'] ,
							'_charid'	=>	$current['_charid'],
						) 
				);
			
			
			$next_index_old = $index_old+1;
			while( isset($domi_kills2[$next_index_old]) && true ) {
				$_current = $domi_kills2[$next_index_old];
				
				if( isset( $_current['dublicate'] ) ) {
					$next_index_old++;
					continue;
				}
				
				if( $current['_ratid']==$_current['_ratid'] && $current['systemName']==$_current['systemName']
						&& $current['_date_unixtime']+$time_dif_as_same>$_current['_date_unixtime']
						&& $current['_date_unixtime']-$time_dif_as_same<$_current['_date_unixtime']
					) {
					$_chars[] = array( 
									'charName'	=>	$_current['charName'] ,
									'_charid'	=>	$_current['_charid'],
								);
					$domi_kills2[$next_index_old]['dublicate'] = true;
				} else {
					break;
				}
				$next_index_old++;
			}
			
			if( count($_chars) > 1 ) {
				$current['_chars'] = $_chars;
				$current['charName'] = '_char';
			}
			
			$domi_kills[$index_new] = $current;
			$index_new++;
			
			$index_old++;
			if( $index_new>= $this->show_max_fancy_kills ) {
				break;
			}
		}
		
		$head = array('Name', 'ratType', 'ratBounty', 'system', 'region', 'truesec', 'Date');
		return array( 'sort' => 2, 'head' => $head, 'body' => $domi_kills); 
	}
	
	private function bounty_list() {
		/*
		$str = "SELECT w.char AS charName, w.charid as _charid, ei.typeName AS ratName, 
			IFNULL(rb.valueInt,0) as ratBounty, 
			w.system AS systemName, evr.regionName as regionName, es.security as truesec, w.date, er.ratid as _ratid
			FROM eve_online_wallet w
			INNER JOIN eve_online_ratkills er ON er.refID = w.refID
			INNER JOIN evedump_invtypes ei ON ei.typeID = er.ratid
			INNER JOIN evedump_systems es ON es.solarSystemID = w.system_id
			INNER JOIN evedump_constellations ec ON es.constellationID = ec.constellationID
			INNER JOIN evedump_regions evr ON ec.regionID = evr.regionID
			LEFT JOIN evedump_rats_bountys rb ON rb.typeID = er.ratid 
			WHERE 1 {$period_filter} AND corpid = '{$corp_id}'
			ORDER BY IFNULL(rb.valueInt,0) desc
			LIMIT 20 ";
		$bounty_list = fetch_all( $str );
		print_r2( $bounty_list );
		*/
	}
	
	private function ratting_by_rattype() {
		$orders_ratting_by_rattype = array('max_ratBountys','ratBounty','totalRatKills');
		$order_valid = in_array( @$_GET['order'] , $orders_ratting_by_rattype );
		if( !$order_valid ) {
			$_GET['order'] = 'ratBounty';
		} 
		$str = "SELECT ei.typeName AS ratName,      
			ei.description as _description, er.ratid as _ratid,
			SUM(er.amount) as totalRatKills, IFNULL(rb.valueInt,0) as ratBounty, SUM( IFNULL(rb.valueInt,0) * er.amount ) AS max_ratBountys,
			IFNULL(ri.graphicId,0) as _ratImgId
			FROM {$this->_table['snow_wallet']} w
			INNER JOIN {$this->_table['snow_ratkills']} er ON er.refID = w.refID
			INNER JOIN {$this->_table['invtypes']} ei ON ei.typeID = er.ratid
			LEFT JOIN {$this->_table['snow_rats_bountys']} rb ON rb.typeID = er.ratid 
			LEFT JOIN {$this->_table['snow_rats_imgs']} ri ON ri.typeID = er.ratid 
			WHERE 1 {$this->period_filter} AND corpid = '{$this->corp_id}'
			GROUP BY er.ratid
			ORDER BY ".$_GET['order']." DESC, ei.typeName ASC
			LIMIT 50;";
		
		$head = array('ratName', 'totalRatKills', 'ratBounty', 'max_ratBountys');
		return array( 'sort' => 23, 'head' => $head, 'body' => $this->db->fetch_all( $str ));
		
	}
	
	private function player_ratting_by_day() {
		$player_ratting_by_day = array();
		if( $this->charid ) {
			$str = "SELECT SUM( w.amount2 ) AS _ratBountys, YEAR( date ) AS _year , MONTH( date ) as _month , DAY( date ) as _day, date as _date
				FROM {$this->_table['snow_wallet']} w
				WHERE 1 {$this->period_filter} AND corpid = '{$this->corp_id}'
				GROUP BY YEAR( date ) , MONTH( date ) , DAY( date )
				ORDER BY date DESC 
				LIMIT 100 ";
				
			$player_ratting_by_day = $this->db->fetch_all( $str );
			foreach( $player_ratting_by_day as &$day ) {
				$unixtime = strtotime( $day['_date'] );
				$day['date'] = date( 'd. M Y, l ' , $unixtime );
				$day['_date'] = sprintf('%s-%s-%s',$day['_year'],$this->add_zero($day['_month']),$this->add_zero($day['_day']));
				
				$day['ratBountys'] = $day['_ratBountys'];
			}
		}
		$head = array('date', 'ratBounty');
		return array( 'sort' => 1, 'head' => $head, 'body' => $player_ratting_by_day );
	}
	
	private function player_ratting_by_day_all() {
		$player_ratting_by_day_all = null;
		if( !( $this->charid || ($_GET['period']=='last24h') ) ):
				$str = "SELECT SUM( w.amount2 ) AS _ratBountys, YEAR( date ) AS _year , MONTH( date ) as _month , DAY( date ) as _day, date as _date, w.char as charName, w.charid as _charid
			/*	,e.kills_30days as _kb_kills_30days */
				FROM {$this->_table['snow_wallet']} w
			/*	
				LEFT JOIN (
					SELECT ex.api_characterID , xxx.kills_all_time , xxx.kills_30days , xxx.kills_7days
					FROM eve_online ex
					LEFT JOIN (
						SELECT sum( kills_all_time ) AS kills_all_time , sum( kills_7days ) kills_7days , sum( kills_30days ) kills_30days , forum_user_id
						FROM eve_online
						GROUP BY forum_user_id
					) AS xxx ON xxx.forum_user_id = ex.forum_user_id
				)e ON e.api_characterID = w.charid 
			*/
				WHERE 1 {$this->period_filter} AND corpid = '{$this->corp_id}'
				GROUP BY YEAR( date ) , MONTH( date ) , DAY( date ), w.charid
				ORDER BY SUM( w.amount2 ) DESC 
				LIMIT 25 ";
				
			$player_ratting_by_day_all = $this->db->fetch_all( $str );
			foreach( $player_ratting_by_day_all as &$day ) {
				$unixtime = strtotime( $day['_date'] );
				$day['date'] = date( 'd.M Y, l ' , $unixtime );
				$day['_date'] = sprintf('%s-%s-%s',$day['_year'],add_zero($day['_month']),add_zero($day['_day']));
				
				$day['ratBountys'] = $day['_ratBountys'];
			}
		#print_r2( $player_ratting_by_day );
		endif;
		return $player_ratting_by_day_all;
	}
	
	public function charts($args) {
		#$_SESSION['messages']->addwarning(round((microtime(true) - $this->stime),3));
		$return = array();
		$a=0;
		foreach( $args as $corpID => $name ) {
			#$ti = microtime(true);
			$res = array();
			$result = $this->db->query("SELECT Sum(amount2) as amount 
					FROM {$this->_table['snow_wallet']} 
					WHERE corpid = '{$corpID}'
					AND date between DATE_SUB(NOW(),INTERVAL 8 day) AND DATE_SUB(NOW(),INTERVAL 7 day)

					UNION all
					
					SELECT Sum(amount2) as amount 
					FROM {$this->_table['snow_wallet']} 
					WHERE corpid = '{$corpID}'
					AND date between DATE_SUB(NOW(),INTERVAL 7 day) AND DATE_SUB(NOW(),INTERVAL 6 day)

					UNION all

					SELECT Sum(amount2) as amount 
					FROM {$this->_table['snow_wallet']} 
					WHERE corpid = '{$corpID}'
					AND date between DATE_SUB(NOW(),INTERVAL 6 day) AND DATE_SUB(NOW(),INTERVAL 5 day)

					UNION all

					SELECT Sum(amount2) as amount 
					FROM {$this->_table['snow_wallet']} 
					WHERE corpid = '{$corpID}'
					AND date between DATE_SUB(NOW(),INTERVAL 5 day) AND DATE_SUB(NOW(),INTERVAL 4 day)

					UNION all

					SELECT Sum(amount2) as amount 
					FROM {$this->_table['snow_wallet']} 
					WHERE corpid = '{$corpID}'
					AND date between DATE_SUB(NOW(),INTERVAL 4 day) AND DATE_SUB(NOW(),INTERVAL 3 day)

					UNION all

					SELECT Sum(amount2) as amount 
					FROM {$this->_table['snow_wallet']} 
					WHERE corpid = '{$corpID}'
					AND date between DATE_SUB(NOW(),INTERVAL 3 day) AND DATE_SUB(NOW(),INTERVAL 2 day)

					UNION all

					SELECT Sum(amount2) as amount 
					FROM {$this->_table['snow_wallet']} 
					WHERE corpid = '{$corpID}'
					AND date between DATE_SUB(NOW(),INTERVAL 2 day) AND DATE_SUB(NOW(),INTERVAL 1 day)

					UNION all

					SELECT Sum(amount2) as amount 
					FROM {$this->_table['snow_wallet']} 
					WHERE corpid = '{$corpID}'
					AND date between DATE_SUB(NOW(),INTERVAL 1 day) AND NOW();");
			while( $row = $result->fetch_assoc() ){
				if( $row ) 
					$res[] = round($row['amount']);
			}
			
			$return[$a]['name'] = $name;
			$return[$a]['data'] = $res;
			$a++;
			#$_SESSION['messages']->addwarning(round((microtime(true) - $ti),3));
		} 
		#echo json_encode($return);
		#$smarty->assign('charts', json_encode($return));
		return ( json_encode($return) );
	}
	
	private function show_xml() {
		if( @$_GET['show_xml'] == 'true' ) {
			include 'class.domdoc.php';
			
			$xmlBuilder = new domdoc();
			$feed_body = $xmlBuilder->createElement('iskfarmers');
			$feed_body->setAttribute('period', $_GET['period'] );
			
			foreach( $ratting_all as $row ) {
				$row['char_name'] = $row['charName'];
				$row['eve_char_id'] = $row['_charid'];
				$row['rat_bountys'] = $row['ratBountys'];
				$build_cons = array(
					'useonly' 	 => array( 'char_name' , 'eve_char_id' , 'rat_bountys' ),
				);
				$feed_body->appendChild( $xmlBuilder->createblock( 'farmer', $row , $build_cons ) );
			}
			$xmlBuilder->appendChild($feed_body);
			
			$xmlBuilder->setHeader();
			$xmlBuilder->formatoutput();
			exit(0);
		}
	}
	
	private function add_zero($nr) {
		if( $nr <= 9 )
			return '0'.$nr;
		return $nr;
	}
	
	
}
?>