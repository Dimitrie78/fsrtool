<?php
defined('fsr_tool') or die;

class MemberDB
{
	public $db = false;
	private $sqlstrings = array();
	private $debug = 0;
	private $Messages; 
	
	public function MemberDB($dbhost, $dbuser, $dbpasswd, $dbname, $dbport=null) {
		$this->Messages = &$_SESSION["messages"];
		if (!$this->db) {
			$db = new mysqli( $dbhost, $dbuser, $dbpasswd, $dbname, ( !$dbport ? null : $dbport ) );
		
			if ($db->connect_error) {
				$_SESSION['messages']->showerror("SQL-Error Verbindung zum Server nicht erfolgreich!");
				#error_log( "ratter db error, msg: ".$db->connect_error.", code: ".$db->connect_errno );
				#die("database error, check logs for more!");
			}
			$this->db = $db;
		}
		
	}
	
	public function doQuery( $sql, $from=null ) {
		if ($this->debug == "1") $this->Messages->addwarning($sql);
		if (!$this->db->query("SET NAMES 'utf8';")) 
			$this->Messages->addwarning("mysqli_query SET NAMES 'utf8'". $this->db->error);
		if (!$this->db->query("SET CHARACTER SET 'utf8';"))
			$this->Messages->addwarning("mysqli_query SET CHARACTER SET 'utf8'". $this->db->error);
		
		if ($res = $this->db->query($sql))
			return $res;
		else {
			$this->Messages->showerror("Schwerer Fehler [database/doQuery: $from]<br />\nDB: ". $this->db->error ."<br />\n" . $sql);
			return false;
		}
	}
	
	public function fetch_assoc($result) {
		if ( $row = $result->fetch_assoc() )
			return $row;	
	}
	
	public function query($sql) {
		try {
			$result = $this->db->query($sql);
			return ( $result );
		} catch (Exception $e) {
			return 0;
		}
	}
	
	public function fetch_one($sql,$column=null) {
		try {
			$ret = false;
			if( $result = $this->db->query($sql) ) {
				if ( $row2 = $result->fetch_assoc() ) {
					if( isset($row2[$column]) ) {
						$ret = $row2[$column];
					} else {
						$ret = $row2;
					}
				}
				$result->close();
			}
			return $ret;
		} catch (Exception $e) {
			print ("db-error: ". $e->getMessage());
			return null;
		}
	}

	public function exec_query($sql) {
		try {
			$this->db->query($sql);
			return ( $this->db->affected_rows );
		} catch (Exception $e) {
			return 0;
		}
	}


	public function fetch_all($sql,$ret=array()) {
		try {
			if( $result = $this->db->query($sql) ) {
				while ( $row2 = $result->fetch_assoc() ) {
					$ret[] = $row2;
				}
				$result->close();
			} 
			return $ret;
		} catch (Exception $e) {
			print ("db-error: ". $e->getMessage());
			return array();
		}
	}
	
	public function close() {
		$this->db->close();	
	}
	
	public function isHighCommand()	{
		global $User;
		$sqlstring = "SELECT * FROM ".db_snow_characters." WHERE charID='".$User->charID."' AND corpID='".$User->corpID."' AND division=5;";
		$result=$this->doQuery($sqlstring,"Database::isHighCommand");
		if(mysqli_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function isLeader() {
		global $User;
		$sqlstring = "SELECT * FROM ".db_snow_characters." WHERE charID='".$User->charID."' AND corpID='".$User->corpID."' AND division=6;";
		$result=$this->doQuery($sqlstring,"Database::isLeader");
		if(mysqli_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_mainchar_m($charName) {
		global $User;
		$sql="SELECT charID FROM ".db_snow_characters." WHERE name='".$this->db->real_escape_string($charName)."' AND corpID='".$User->corpID."';";
		$result=$this->doQuery($sql,"Database::get_mainchar_m");
		$rowt=mysqli_fetch_row($result);
		mysqli_free_result($result);
		$charID = $rowt[0];
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".$charID."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysqli_num_rows($result) == 1) {
			$row=mysqli_fetch_assoc($result);
			mysqli_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$sql="SELECT * FROM ".db_snow_characters." WHERE charID='".$main."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		$row=mysqli_fetch_assoc($result);
		mysqli_free_result($result);
		
		return $row;
	}
	
	public function get_atls_m($charName) {
		global $User;
		$sql="SELECT charID FROM ".db_snow_characters." WHERE name='".$this->db->real_escape_string($charName)."' AND corpID='".$User->corpID."';";
		$result=$this->doQuery($sql,"Database::get_mainchar_m");
		$rowt=mysqli_fetch_assoc($result);
		mysqli_free_result($result);
		$charID=$rowt['charID'];
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".$charID."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysqli_num_rows($result) == 1) {
			$row=mysqli_fetch_assoc($result);
			mysqli_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$sql="SELECT charID FROM ".db_snow_alts." WHERE altOf='".$main."';";
		$result=$this->doQuery($sql,"Database::get_atls");
		while ($row = mysqli_fetch_assoc($result)) 
		{
			$alt_name = $row['charID'];
			$sql_statement="SELECT name FROM ".db_snow_characters." WHERE charID='".$alt_name."' AND inCorp=1;";
			$get_alt_name=$this->doQuery($sql_statement,"Database::get_atls");
			$name = mysqli_fetch_assoc($get_alt_name);
			mysqli_free_result($get_alt_name);
			if(!empty($name['name'])){
				$alt.= $name['name'].", ";
			}
		}
		$alt=substr($alt,0,-2);
		mysqli_free_result($result);
		return $alt;
	}
	
	public function get_mainchar($charID) {
		global $User;
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".$this->db->real_escape_string($charID)."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysqli_num_rows($result) == 1) {
			$row=mysqli_fetch_assoc($result);
			mysqli_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$sql="SELECT * FROM ".db_snow_characters." WHERE charID='".$main."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		$row=mysqli_fetch_assoc($result);
		mysqli_free_result($result);
		
		return $row;
	}
	
	public function getMainCharID($charID) {
		global $User;
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".$this->db->real_escape_string($charID)."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysqli_num_rows($result) == 1) {
			$row=mysqli_fetch_assoc($result);
			mysqli_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
			
		return $main;
	}
	
	public function get_atls($charID) {
		global $User;
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".$this->db->real_escape_string($charID)."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysqli_num_rows($result) == 1) {
			$row=mysqli_fetch_assoc($result);
			mysqli_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$sql="SELECT charID FROM ".db_snow_alts." WHERE altOf='".$main."';";
		$result=$this->doQuery($sql,"Database::get_atls");
		$alts_array=array();
		while ($row = mysqli_fetch_assoc($result)) 
		{
			$alt_id = $row['charID'];
			$sql_statement="SELECT * FROM ".db_snow_characters." WHERE charID='".$alt_id."' AND inCorp=1;";
			$get_alt=$this->doQuery($sql_statement,"Database::get_atls");
			if(mysqli_num_rows($get_alt) == 1) {
				$alts = mysqli_fetch_assoc($get_alt);
				mysqli_free_result($get_alt);
				$alts_array[] = $alts;
			}
		}
		//$alt=substr($alt,0,-2);
		mysqli_free_result($result);
		return $alts_array;
	}
	
	public function set_values($main,$carrier,$dread,$posgunner,$afk="0",$afkText="",$tz="0") {
		global $User;
		$sqlstring = "SELECT afk FROM ".db_snow_characters." WHERE charID='".$this->db->real_escape_string($main)."';";
		$result=$this->doQuery($sqlstring,"Database::set_values");
		if (mysqli_num_rows($result) == 1) {
			$row=mysqli_fetch_assoc($result);
			mysqli_free_result($result);
		}
		
		if($afk == 1 and $afk != $row['afk']) {
			$this->go_afk($main,$afkText);//set time go afk
		}
		elseif($afk == 0 and $afk != $row['afk']) {
			$this->back_afk($main);//set time come from afk
		}
		
		$sql="UPDATE ".db_snow_characters." SET carrier   = '".$this->db->real_escape_string($carrier)."',
									dread     = '".$this->db->real_escape_string($dread)."',
									afk       = '".$this->db->real_escape_string($afk)."',
									afkText   = '".$this->db->real_escape_string($afkText)."',
									tz        = '".$this->db->real_escape_string($tz)."',
									posgunner = '".$this->db->real_escape_string($posgunner)."'
									WHERE charID = '".$this->db->real_escape_string($main)."';";
		$result=$this->doQuery($sql,"Database::set_values");
		return $result;
	}
	
	private function go_afk($charID,$afkText) {
		$sqlstring = "INSERT INTO ".db_snow_afk_time." SET charID   = '".$this->db->real_escape_string($charID)."',
											   date_go  = UNIX_TIMESTAMP(), 
											   afk_text = '".$this->db->real_escape_string($afkText)."';";
		$result=$this->doQuery($sqlstring,"Database::go_afk");
	}
	
	private function back_afk($charID) {
		$sqlstring = "SELECT date_go FROM ".db_snow_afk_time." WHERE charID='".$this->db->real_escape_string($charID)."' and date_back='0';";
		$result=$this->doQuery($sqlstring,"Database::back_afk");
		if (mysqli_num_rows($result) == 1) {
			$row=mysqli_fetch_assoc($result);
			mysqli_free_result($result);
		}
		if ($row) {
			$sqlstring = "UPDATE ".db_snow_afk_time." SET date_back=UNIX_TIMESTAMP() WHERE charID='".$this->db->real_escape_string($charID)."' AND date_go='".$row['date_go']."'";
			$result=$this->doQuery($sqlstring,"Database::back_afk");
		}
		return $result;
	}
	
	public function get_Evaluation($charID) {
		global $User;
		$main = $this->get_mainchar($charID);
		$sqlstring="SELECT * FROM ".db_snow_evaluation." WHERE charID='".$main['charID']."' ORDER BY date DESC LIMIT 3;";
		$result=$this->doQuery($sqlstring,"Database::get_Evaluation");
		while($row = mysqli_fetch_assoc($result)) {
			if($row) $eval[] = $row;
		}
		mysqli_free_result($result);
		return $eval;
	}
	
	public function get_afk() { 
		global $User;
		$sqlstring = "SELECT a.*, c.name as charName 
					  FROM ".db_snow_afk_time." as a
					  INNER JOIN ".db_snow_characters." as c ON c.charID = a.charID
					  WHERE c.inCorp=1
					  AND c.corpID='".$User->corpID."'
					  ORDER BY c.name, a.date_go;";
		$result=$this->doQuery($sqlstring,"Database::get_afk");
		while($row = mysqli_fetch_assoc($result)) {
			if($row) {
				$afkchar[] = $row;				
			}
		}
		mysqli_free_result($result);
		return $afkchar;
	}
	
	public function get_updateTime() {
		$result = $this->doQuery("SELECT updateTime FROM time WHERE updateTime > 0");	
		$row = mysqli_fetch_array($result);
		
		return $row[0];
	}
}
?>
