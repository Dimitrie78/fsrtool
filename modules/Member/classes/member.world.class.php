<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class MemberWorld extends world 
{
	public $_table = array();
	
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		#echo '<pre>'; print_r( $this ); die;
		
		$this->_table['snow_afk_time'] 		= 'fsrtool_snow_afk_time';
		$this->_table['snow_alts'] 			= 'fsrtool_snow_alts';
		$this->_table['snow_characters'] 	= 'fsrtool_snow_characters';
		$this->_table['snow_evaluation'] 	= 'fsrtool_snow_evaluation';
		$this->_table['snow_jobs'] 			= 'fsrtool_snow_jobs';
		$this->_table['snow_kills'] 		= 'fsrtool_snow_kills';
		$this->_table['snow_news'] 			= 'fsrtool_snow_news';
		$this->_table['snow_tempchars'] 	= 'fsrtool_snow_tempchars';
		$this->_table['snow_time'] 			= 'fsrtool_snow_time';
		$this->_table['snow_wallet'] 		= 'fsrtool_snow_wallet';
		$this->_table['snow_ratkills'] 		= 'fsrtool_snow_ratkills';
		$this->_table['snow_rats_bountys']	= 'fsrtool_snow_rats_bountys';
		$this->_table['snow_rats_imgs']		= 'fsrtool_snow_rats_imgs';
	}
	
	public function get_fullApi_corps($corpID) {
		if( !$corpID )
			return false;
		
		$sql = "SELECT c.ally FROM {$this->_table['fsrtool_corps']} c WHERE id = {$corpID}";
		$allyID = $this->db->fetch_one( $sql, 'ally' );
		$where = ($allyID == 'None' ? "WHERE api.corpID = {$corpID}" : "WHERE api.allyID = {$allyID}");
		$sql = "SELECT c.ticker, api.corpID
			FROM {$this->_table['fsrtool_user_fullapi']} api 
			INNER JOIN {$this->_table['fsrtool_corps']} c ON api.corpID = c.id
			".$where."
			ORDER BY c.ticker;";
		$res = $this->db->Query( $sql );
		$out = array();
		while( $row = $res->fetch_assoc() ) {
			if( $row )
				$out[$row['corpID']] = $row['ticker'];
		}
		$res->close();
		
		return $out;
	}
	
	public function getApiStatus() {
		$str = "SELECT fullapi.*, corps.name as corpName 
			  FROM {$this->_table['fsrtool_user_fullapi']} as fullapi 
			  INNER JOIN {$this->_table['fsrtool_corps']} as corps ON corps.id = fullapi.corpID
			  WHERE fullapi.corpID='".$this->User->corpID."';";
		$api = $this->db->fetch_one( $str );
		
		if($api['status'] == 1) {
			return false;
		} else {
			return true;
		}
	}
	
	public function isHighCommand()	{
		$str = "SELECT * FROM {$this->_table['snow_characters']} WHERE charID='".$this->User->charID."' AND corpID='".$this->User->corpID."' AND division=5;";
		$res = $this->db->query( $str );
		if($res->num_rows > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function isLeader() {
		$str = "SELECT * FROM {$this->_table['snow_characters']} WHERE charID='".$this->User->charID."' AND corpID='".$this->User->corpID."' AND division=6;";
		$res = $this->db->query( $str );
		if($res->num_rows > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_mainchar_m($charName) {
		$str = "SELECT charID FROM {$this->_table['snow_characters']} WHERE name='".$this->db->escape($charName)."' AND corpID='".$this->User->corpID."';";
		$res = $this->db->query( $str );
		$rowt = $res->fetch_row();
		$charID = $rowt[0];
		$str = "SELECT altOF FROM {$this->_table['snow_alts']} WHERE charID='".$charID."';";
		$res = $this->db->query( $str );
		if($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$str = "SELECT * FROM {$this->_table['snow_characters']} WHERE charID='".$main."';";
		$res = $this->db->query( $str );
		$row = $res->fetch_assoc();
		
		return $row;
	}
	
	public function get_atls_m($charName) {
		$str = "SELECT charID FROM {$this->_table['snow_characters']} WHERE name='".$this->db->escape($charName)."' AND corpID='".$this->User->corpID."';";
		$res = $this->db->query( $str );
		$rowt = $res->fetch_assoc();
		$charID = $rowt['charID'];
		$str = "SELECT altOF FROM {$this->_table['snow_alts']} WHERE charID='".$charID."';";
		$res = $this->db->query( $str );
		if($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$str = "SELECT charID FROM {$this->_table['snow_alts']} WHERE altOf='".$main."';";
		$res = $this->db->query( $str );
		while ($row = $res->fetch_assoc()) 
		{
			$alt_name = $row['charID'];
			$sql_statement = "SELECT name FROM {$this->_table['snow_characters']} WHERE charID='".$alt_name."' AND inCorp=1;";
			$get_alt_name = $this->db->query( $sql_statement );
			$name = $get_alt_name->fetch_assoc();
			if(!empty($name['name'])){
				$alt.= $name['name'].", ";
			}
		}
		$alt=substr($alt,0,-2);
		
		return $alt;
	}
	
	public function get_mainchar($charID) {
		$str = "SELECT altOF FROM {$this->_table['snow_alts']} WHERE charID='".$this->db->escape($charID)."';";
		$res = $this->db->query( $str );
		if($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$str = "SELECT * FROM {$this->_table['snow_characters']} WHERE charID='".$main."';";
		$res = $this->db->query( $str );
		$row = $res->fetch_assoc();
		
		return $row;
	}
	
	public function getMainCharID($charID) {
		$str = "SELECT altOF FROM {$this->_table['snow_alts']} WHERE charID='".$this->db->escape($charID)."';";
		$res = $this->db->query( $str );
		if($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
			
		return $main;
	}
	
	public function get_atls($charID) {
		$str = "SELECT altOF FROM {$this->_table['snow_alts']} WHERE charID='".$this->db->escape($charID)."';";
		$res = $this->db->query( $str );
		if($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$str = "SELECT charID FROM {$this->_table['snow_alts']} WHERE altOf='".$main."';";
		$res = $this->db->query( $str );
		$alts_array=array();
		while ($row = $res->fetch_assoc())  
		{
			$alt_id = $row['charID'];
			$sql_statement = "SELECT * FROM {$this->_table['snow_characters']} WHERE charID='".$alt_id."' AND inCorp=1;";
			$get_alt = $this->db->query( $sql_statement );
			if($get_alt->num_rows == 1) {
				$alts = $get_alt->fetch_assoc();
				$alts_array[] = $alts;
			}
		}
		
		return $alts_array;
	}
	
	public function set_values($main,$carrier,$dread,$posgunner,$afk="0",$afkText="",$tz="0") {
		$str = "SELECT afk FROM {$this->_table['snow_characters']} WHERE charID='".$this->db->escape($main)."';";
		$res = $this->db->query( $str );
		if($res->num_rows == 1) {
			$row = $res->fetch_assoc();
		}
		
		if($afk == 1 and $afk != $row['afk']) {
			$this->go_afk($main,$afkText);//set time go afk
		}
		elseif($afk == 0 and $afk != $row['afk']) {
			$this->back_afk($main);//set time come from afk
		}
		
		$sql="UPDATE {$this->_table['snow_characters']} SET carrier   = '".$this->db->escape($carrier)."',
									dread     = '".$this->db->escape($dread)."',
									afk       = '".$this->db->escape($afk)."',
									afkText   = '".$this->db->escape($afkText)."',
									tz        = '".$this->db->escape($tz)."',
									posgunner = '".$this->db->escape($posgunner)."'
									WHERE charID = '".$this->db->escape($main)."';";
		$result = $this->db->exec_query( $sql );
		return $result;
	}
	
	private function go_afk($charID,$afkText) {
		$sqlstring = "INSERT INTO {$this->_table['snow_afk_time']} SET charID   = '".$this->db->escape($charID)."',
											   date_go  = UNIX_TIMESTAMP(), 
											   afk_text = '".$this->db->escape($afkText)."';";
		$result = $this->db->exec_query( $sqlstring );
	}
	
	private function back_afk($charID) {
		$str = "SELECT date_go FROM {$this->_table['snow_afk_time']} WHERE charID='".$this->db->escape($charID)."' and date_back='0';";
		$res = $this->db->query( $str );
		if($res->num_rows == 1) {
			$row = $res->fetch_assoc();
		}
		if ($row) {
			$sqlstring = "UPDATE {$this->_table['snow_afk_time']} SET date_back=UNIX_TIMESTAMP() WHERE charID='".$this->db->escape($charID)."' AND date_go='".$row['date_go']."'";
			$result = $this->db->exec_query( $sqlstring );
		}
		return $result;
	}
	
	public function get_Evaluation($charID) {
		$main = $this->get_mainchar($charID);
		$str = "SELECT * FROM {$this->_table['snow_evaluation']} WHERE charID='".$main['charID']."' ORDER BY date DESC LIMIT 3;";
		$res = $this->db->query( $str );
		while($row = $res->fetch_assoc()) {
			if($row) $eval[] = $row;
		}
		return $eval;
	}
	
	public function get_afk() { 
		$sqlstring = "SELECT a.*, c.name as charName 
					  FROM {$this->_table['snow_afk_time']} as a
					  INNER JOIN {$this->_table['snow_characters']} as c ON c.charID = a.charID
					  WHERE c.inCorp=1
					  AND c.corpID='".$this->User->corpID."'
					  ORDER BY c.name, a.date_go;";
		$res = $this->db->query( $sqlstring );
		while($row = $res->fetch_assoc()) {
			if($row) {
				$afkchar[] = $row;				
			}
		}
		
		return $afkchar;
	}
	
	public function get_updateTime() {
		$res = $this->db->query("SELECT updateTime FROM {$this->_table['snow_time']} WHERE updateTime > 0");	
		$row = $res->fetch_array();
		
		return $row[0];
	}
}
?>