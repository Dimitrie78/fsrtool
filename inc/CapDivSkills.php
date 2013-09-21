<?php
class CapSkills extends world{
	
	public $db;
	public $User;
	public $_table = array();
	
	public function __construct( User $User ) {
		/* $this->db = $User->db;
		$this->User = $User;
		$this->_table = $User->_table; */
		if ( !$this->db ) parent::__construct( $User );
		
		$this->_table['eveorder_skills'] 		= TBL_PREFIX."skills";
		$this->_table['fsrtool_ships_player'] 	= TBL_PREFIX."ships_player";
		$this->_table['fsrtool_ships_orte'] 	= TBL_PREFIX."ships_orte";
		$this->_table['fsrtool_ships_log'] 		= TBL_PREFIX."ships_log";
	}
	// Cap DIV Skill abfrage
	public function CapDivSkills($charIDs = array()) {
		include( 'inc/CapDivSkillsX.php' );
		
		$commaseperatet_charIDs = implode(',', $charIDs);
		$sqlstring = "SELECT * FROM {$this->_table['eveorder_skills']} WHERE charID IN (".$commaseperatet_charIDs.");";
		$result    = $this->db->query($sqlstring);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()){
				if ($row){
					$skills[$row['charID']]['charID']        = $row['charID'];
					//$skills[$row['charID']]['charName']      = $charName[$row['charID']];
					$skills[$row['charID']][$row['skillID']] = $row['quantity'];
				}
			}
		} else {
			$skills = false;
		}
		
		foreach ($charIDs as $charID) {
			$SkillTree[$charID]['charID']   = $skills[$charID]['charID'];
			$SkillTree[$charID]['charName'] = $this->charIDtoName($charID);
			$SkillTree[$charID]['fly']		= $this->getFlyDread($charID);
			foreach ($CapSkillsX as $key => $skill) {
				foreach ($skill as $id => $name) {	
					
					if ($skills[$charID][$id]) {
						$SkillTree[$charID][$key][] = array('name'  => $name, 
															'level' => $skills[$charID][$id]); 
					} else {
						$SkillTree[$charID][$key][] = array('name'  => $name, 
															'level' => '-'); 
					}
					
				}
			}
		}
		//echo '<pre>';print_r($SkillTree);echo '</pre>';exit;
		
		return $SkillTree;
	}

	public function getFlyDread($charID) {
		$sqlstring = "SELECT Moros, Naglfar, Revelation, Phoenix FROM {$this->_table['fsrtool_ships_player']} WHERE user_id = '".$charID."';";
		$result = $this->db->query($sqlstring);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
		}
		
		return $row;
	}

	public function charIDtoName($charID) {
		$charID = $this->db->escape($charID);
		$query1 = "SELECT username as name FROM {$this->_table['fsrtool_user']} WHERE charID='$charID' LIMIT 1;";
		$query2 = "SELECT charName as name FROM {$this->_table['fsrtool_alts']} WHERE charID='$charID' LIMIT 1;";
		$result = $this->db->query($query1);
		if ($result->num_rows == 1) {
			$row = $result->fetch_assoc();
		} else {
			$res = $this->db->query($query2);
			$row = $res->fetch_assoc();
		}
		
		
		return ($row['name']);
	}
}
?>