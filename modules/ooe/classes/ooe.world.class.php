<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class ooeWorld extends world {
	
	
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
	}
	
	public function assessMask() {
		$res = $this->db->query( "SELECT c.accessMask, c.type, c.name, c.description, g.name AS des, c.groupID
			FROM {$this->_table['fsrtool_api_calls']} c 
			INNER JOIN {$this->_table['fsrtool_api_callgroups']} g ON c.groupID = g.groupID
			WHERE c.type = 'Character'
			ORDER BY g.name, c.accessMask DESC;" );
		$list = array();
		while ( $row = $res->fetch_assoc() ) {
			$list[ $row['groupID'] ]['des'] = $row['des'];
			$list[ $row['groupID'] ][] = $row;
		}
		$res->close();
		return $list;
	}
	
}
?>