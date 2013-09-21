<?php
defined('fsr_tool') or die;

class MemberDB
{
	private $conn = false;
	private $sqlstrings = array();
	private $debug = 0;
	private $Messages; 
	
	public function __construct() {
		$this->Messages = $_SESSION["messages"];
		if (!($this->conn))	{
			if(!($this->conn = mysql_connect(db_host_fsrclan_member, db_user_fsrclan_member, db_pass_fsrclan_member))) {
				$this->Messages->showerror("SQL-Error Verbindung zum Server nicht erfolgreich!");
				exit();
			}

			if(!(mysql_select_db(db_name_fsrclan_member,$this->conn))) {
				$this->Messages->showerror("SQL-Error Datenbank konnte nicht gefunden werden!");
				exit();
			}
		}
	}
	
	public function doQuery($sqlstring,$from='') {
		if (!($this->conn))	{
			if(!($this->conn = mysql_connect(db_host_fsrclan_member, db_user_fsrclan_member, db_pass_fsrclan_member))) {
				$this->Messages->showerror("SQL-Error Verbindung zum Server nicht erfolgreich!");
				exit();
			}

			if(!(mysql_select_db(db_name_fsrclan_member,$this->conn))) {
				$this->Messages->showerror("SQL-Error Datenbank konnte nicht gefunden werden!");
				exit();
			}
		}
		mysql_query("SET NAMES 'utf8'") OR $Messages->addwarning("mysql_query SET NAMES 'utf8'". mysql_error());
		mysql_query("SET CHARACTER SET 'utf8'") OR $Messages->addwarning("mysql_query SET CHARACTER SET 'utf8'". mysql_error());
		$result = mysql_query($sqlstring, $this->conn)
			or $this->Messages->addwarning("Schwerer Fehler [database/doQuery: $from]<BR>\nDB: ". mysql_error()."<BR>\n".$sqlstring);
		if ($this->debug == "1") $this->Messages->addwarning($sqlstring);
		return $result;
	}
	
	public function isHighCommand()	{
		global $User;
		$sqlstring = "SELECT * FROM ".db_snow_characters." WHERE charID='".$User->id."' AND division=6;";
		$result=$this->doQuery($sqlstring,"Database::isHighCommand");
		if(mysql_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function isLeader() {
		global $User;
		$sqlstring = "SELECT * FROM ".db_snow_characters." WHERE charID='".$User->id."' AND division=5;";
		$result=$this->doQuery($sqlstring,"Database::isLeader");
		if(mysql_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_mainchar_m($charName) {
		$sql="SELECT charID FROM ".db_snow_characters." WHERE name='".mysql_real_escape_string($charName)."';";
		$result=$this->doQuery($sql,"Database::get_mainchar_m");
		$rowt=mysql_fetch_row($result);
		mysql_free_result($result);
		$charID = $rowt[0];
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".$charID."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysql_num_rows($result) == 1) {
			$row=mysql_fetch_assoc($result);
			mysql_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$sql="SELECT * FROM ".db_snow_characters." WHERE charID='".$main."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		$row=mysql_fetch_assoc($result);
		mysql_free_result($result);
		
		return $row;
	}
	
	public function get_atls_m($charName) {
		$sql="SELECT charID FROM ".db_snow_characters." WHERE name='".mysql_real_escape_string($charName)."';";
		$result=$this->doQuery($sql,"Database::get_mainchar_m");
		$rowt=mysql_fetch_assoc($result);
		mysql_free_result($result);
		$charID=$rowt['charID'];
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".$charID."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysql_num_rows($result) == 1) {
			$row=mysql_fetch_assoc($result);
			mysql_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$sql="SELECT charID FROM ".db_snow_alts." WHERE altOf='".$main."';";
		$result=$this->doQuery($sql,"Database::get_atls");
		while ($row = mysql_fetch_assoc($result)) 
		{
			$alt_name = $row['charID'];
			$sql_statement="SELECT name FROM ".db_snow_characters." WHERE charID='".$alt_name."' AND inCorp=1;";
			$get_alt_name=$this->doQuery($sql_statement,"Database::get_atls");
			$name = mysql_fetch_assoc($get_alt_name);
			mysql_free_result($get_alt_name);
			if(!empty($name['name'])){
				$alt.= $name['name'].", ";
			}
		}
		$alt=substr($alt,0,-2);
		mysql_free_result($result);
		return $alt;
	}
	
	public function get_mainchar($charID) {
		global $User;
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".mysql_real_escape_string($charID)."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysql_num_rows($result) == 1) {
			$row=mysql_fetch_assoc($result);
			mysql_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$sql="SELECT * FROM ".db_snow_characters." WHERE charID='".$main."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		$row=mysql_fetch_assoc($result);
		mysql_free_result($result);
		
		return $row;
	}
	
	public function get_atls($charID) {
		global $User;
		$sql="SELECT altOF FROM ".db_snow_alts." WHERE charID='".mysql_real_escape_string($charID)."';";
		$result=$this->doQuery($sql,"Database::get_mainchar");
		if (mysql_num_rows($result) == 1) {
			$row=mysql_fetch_assoc($result);
			mysql_free_result($result);
			$main = $row['altOF'];
		} else {
			$main = $charID;
		}
		$sql="SELECT charID FROM ".db_snow_alts." WHERE altOf='".$main."';";
		$result=$this->doQuery($sql,"Database::get_atls");
		$alts_array=array();
		while ($row = mysql_fetch_assoc($result)) 
		{
			$alt_id = $row['charID'];
			$sql_statement="SELECT * FROM ".db_snow_characters." WHERE charID='".$alt_id."' AND inCorp=1;";
			$get_alt=$this->doQuery($sql_statement,"Database::get_atls");
			if(mysql_num_rows($get_alt) == 1) {
				$alts = mysql_fetch_assoc($get_alt);
				mysql_free_result($get_alt);
				$alts_array[] = $alts;
			}
		}
		//$alt=substr($alt,0,-2);
		mysql_free_result($result);
		return $alts_array;
	}
	
	public function set_values($main,$carrier,$dread,$posgunner,$afk="0",$afkText="",$tz="0") {
		global $User;
		$sqlstring = "SELECT afk FROM ".db_snow_characters." WHERE charID='".mysql_real_escape_string($main)."';";
		$result=$this->doQuery($sqlstring,"Database::set_values");
		if (mysql_num_rows($result) == 1) {
			$row=mysql_fetch_assoc($result);
			mysql_free_result($result);
		}
		
		if($afk == 1 and $afk != $row['afk']) {
			$this->go_afk($main,$afkText);//set time go afk
		}
		elseif($afk == 0 and $afk != $row['afk']) {
			$this->back_afk($main);//set time come from afk
		}
		
		$sql="UPDATE ".db_snow_characters." SET carrier   = '".mysql_real_escape_string($carrier)."',
									dread     = '".mysql_real_escape_string($dread)."',
									afk       = '".mysql_real_escape_string($afk)."',
									afkText   = '".mysql_real_escape_string($afkText)."',
									tz        = '".mysql_real_escape_string($tz)."',
									posgunner = '".mysql_real_escape_string($posgunner)."'
									WHERE charID = '".mysql_real_escape_string($main)."';";
		$result=$this->doQuery($sql,"Database::set_values");
		return $result;
	}
	
	private function go_afk($charID,$afkText) {
		$sqlstring = "INSERT INTO ".db_snow_afk_time." SET charID   = '".mysql_real_escape_string($charID)."',
											   date_go  = UNIX_TIMESTAMP(), 
											   afk_text = '".mysql_real_escape_string($afkText)."';";
		$result=$this->doQuery($sqlstring,"Database::go_afk");
	}
	
	private function back_afk($charID) {
		$sqlstring = "SELECT date_go FROM ".db_snow_afk_time." WHERE charID='".mysql_real_escape_string($charID)."' and date_back='0';";
		$result=$this->doQuery($sqlstring,"Database::back_afk");
		if (mysql_num_rows($result) == 1) {
			$row=mysql_fetch_assoc($result);
			mysql_free_result($result);
		}
		if ($row) {
			$sqlstring = "UPDATE ".db_snow_afk_time." SET date_back=UNIX_TIMESTAMP() WHERE charID='".mysql_real_escape_string($charID)."' AND date_go='".$row['date_go']."'";
			$result=$this->doQuery($sqlstring,"Database::back_afk");
		}
		return $result;
	}
	
	public function get_Evaluation($charID) {
		global $User;
		$main = $this->get_mainchar($charID);
		$sqlstring="SELECT * FROM ".db_snow_evaluation." WHERE charID='".$main['charID']."' ORDER BY date DESC LIMIT 3;";
		$result=$this->doQuery($sqlstring,"Database::get_Evaluation");
		while($row = mysql_fetch_assoc($result)) {
			if($row) $eval[] = $row;
		}
		mysql_free_result($result);
		return $eval;
	}
	
	public function get_afk() { 
		global $User;
		$sqlstring = "SELECT a.*, c.name as charName 
					  FROM ".db_snow_afk_time." as a
					  INNER JOIN ".db_snow_characters." as c ON c.charID = a.charID
					  WHERE c.inCorp=1
					  ORDER BY c.name, a.date_go;";
		$result=$this->doQuery($sqlstring,"Database::get_afk");
		while($row = mysql_fetch_assoc($result)) {
			if($row) {
				$afkchar[] = $row;				
			}
		}
		mysql_free_result($result);
		return $afkchar;
	}
	
	public function get_updateTime() {
		$result = $this->doQuery("SELECT updateTime FROM time WHERE updateTime > 0");	
		$row = mysql_fetch_array($result);
		
		return $row[0];
	}
}
?>
