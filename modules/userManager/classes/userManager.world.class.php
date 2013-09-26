<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class userManagerWorld extends world {
	
	public $corps = array();
	
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		if ( $this->User->Admin ) $this->getUserCorps();
	}
	
	public function getUsers($corpID, $roles=false, $alts=false)	{
		
		if ( !$this->User->Admin ) unset( $this->User->roles['Admin'], $this->User->roles['PosManagerAlly'], $this->User->roles['SiloManagerAlly'] );
		switch( $corpID )
		{
			default:  $where = " u.corpID = '{$corpID}' "; break;
			case "1": $where = " 1 "; break;
		}
		if( !$roles && !$alts ) {
			$this->getAlts($where);
			$this->getMains($where);
			
			$head = array('#', 'Name', 'Titel', 'Corp', 'Email', 'logins', 'lastLogin', 'create date', '&nbsp;', '&nbsp;');
			return array( 'head' => $head, 'body' => $this->mains );
		}
		if ( $roles && !$alts ) {
			$head = array_merge(array('#', 'Name', 'Corp'), array_keys($this->User->roles));
			return array( 'head' => $head, 'body' => $this->getRoles($where) );
		}
		if ( $alts ) {
			$head = array('#', 'Main', 'Alt', 'Corp', 'Pos', 'EditPos', 'Silo');
			
			return array( 'head' => $head, 'body' => $this->getRolesAlts($where) );
		}
	}
	
	private function getMains($where) {
		$this->mains = array();
		$str = ("SELECT
			  u.corpID as _corpID, a.id AS _allyID, a.name AS
			  _allyName, u.charID as _charID, u.username AS uname, u.description, c.name AS corpName,
			  u.email, u.active as _act, l.logins, UNIX_TIMESTAMP(l.lastlogin) AS lastlogin, UNIX_TIMESTAMP(u.timestamp) AS created,
			  GROUP_CONCAT(r.roleID) AS _roles
			FROM
			  {$this->_table['fsrtool_user']} u LEFT JOIN
			  {$this->_table['fsrtool_corps']} c ON u.corpID = c.id LEFT JOIN
			  {$this->_table['fsrtool_allys']} a ON c.ally = a.id LEFT JOIN
			  {$this->_table['fsrtool_logins']} l ON u.charID = l.charID LEFT JOIN
			  {$this->_table['fsrtool_user_roles']} r ON u.charID = r.charID
			WHERE
			  $where
			GROUP BY
			  u.charID
			ORDER BY
			  u.username;");
		$res = $this->db->query( $str );
		while( $row = $res->fetch_assoc() ) {
			$this->mains[ $row['_charID'] ] = $row;
			$this->mains[ $row['_charID'] ]['_alts'] = $this->alts[ $row['_charID'] ];
		}
	}
	
	private function getAlts($where) {
		$this->alts = array();
		$str = ("SELECT alt.mainCharID AS _mainCharID, alt.charID AS _charID, alt.charName, alt.corpID AS _corpID,
			corp.name AS corpName, ally.id AS _allyID, ally.name AS _allyName
			FROM {$this->_table['fsrtool_alts']} alt 
			LEFT JOIN {$this->_table['fsrtool_user']} u ON alt.mainCharID = u.charID
			LEFT JOIN {$this->_table['fsrtool_corps']} corp ON alt.corpID = corp.id 
			LEFT JOIN {$this->_table['fsrtool_allys']} ally ON corp.ally = ally.id
			WHERE $where 
			ORDER BY alt.charName;");
		$res = $this->db->query( $str );
		while( $row = $res->fetch_assoc() ) {
			$this->alts[ $row['_mainCharID'] ][] = $row;
		}
	}
	
	private function getUserCorps() {
		$res = $this->db->query("SELECT u.corpID, c.name, c.ticker FROM {$this->_table['fsrtool_user']} u INNER JOIN {$this->_table['fsrtool_corps']} c ON u.corpID = c.id GROUP BY u.corpID ORDER BY c.name;");
		$this->corps[1] = 'all Corps';
		while ( $row = $res->fetch_assoc() ){
			$this->corps[$row['corpID']] = $row['name'];
		}
	}
	
	private function getRoles($where) {
		$roles = array();
		$str = ("SELECT u.charID AS _charID, u.username, u.active as _act, c.name AS _corpName, GROUP_CONCAT(r.roleID) AS _roles
			FROM {$this->_table['fsrtool_user']} u 
			LEFT JOIN {$this->_table['fsrtool_corps']} c ON u.corpID = c.id
			LEFT JOIN {$this->_table['fsrtool_user_roles']} r ON u.charID = r.charID
			WHERE $where
			GROUP BY u.charID
			ORDER BY u.username;");
		
		$res = $this->db->query( $str );
		while( $row = $res->fetch_assoc() ) {
			$x = array();
			$x['uname'] = $row['username'];
			$x['_act']  = $row['_act'];
			$x['corp']  = $row['_corpName'];
			
			$y = explode(",", $row['_roles']);
			
			foreach ( $this->User->roles as $name => $role )
				$x[ $role ] = in_array($role, $y) ? 1 : 0;
			
			$roles[ $row['_charID'] ] = $x;
		}
		return $roles;
	}
	
	private function getRolesAlts( $where ) {
		$roles = array();
		$str = ("SELECT u.active AS _act, a.mainCharID as _mainCharID, u.username as _mname, uc.name as _mainCorp, 
				a.charID as _charID, a.charName, ac.name as altCorp, a.pos, a.pos_edit, a.silo
			FROM {$this->_table['fsrtool_alts']} a 
			LEFT JOIN {$this->_table['fsrtool_user']} u ON a.mainCharID = u.charID
			LEFT JOIN {$this->_table['fsrtool_corps']} ac ON a.corpID = ac.id 
			LEFT JOIN {$this->_table['fsrtool_corps']} uc ON u.corpID = uc.id
			WHERE $where
			ORDER BY u.username, a.charName;");
		$res = $this->db->query( $str );
		while( $row = $res->fetch_assoc() ) {
			$roles[ $row['_mainCharID'] ]['uname'] = $row['_mname'];
			$roles[ $row['_mainCharID'] ]['corp'] = $row['_mainCorp'];
			$roles[ $row['_mainCharID'] ]['_act'] = $row['_act'];
			$roles[ $row['_mainCharID'] ]['_alts'][] = $row;
		}
		return $roles;
	}
	
	public function getCronJobs() {
		$jobs = array();
		$str = "SELECT * FROM {$this->_table['fsrtool_cron']}";
		$res = $this->db->query( $str );
		while ($row = $res->fetch_assoc()) {
			$jobs[$row['id']] = $row;
		}
		return $jobs;
	}
}

?>