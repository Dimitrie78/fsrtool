<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class DreadWorld extends world
{
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		#echo '<pre>'; print_r( $this ); die;
	}
	
	public function dread_check_versicherung() {
		$str = "UPDATE {$this->_table['fsrtool_ships']}
				SET versichert_bis = NULL,
					 versichert	   = 'Nein'
				WHERE versichert_bis < UNIX_TIMESTAMP();";
		$res = $this->db->exec_query( $str );
		return $res;
	}
	
	public function dread_get_standorte() {
		$str = "SELECT * FROM {$this->_table['fsrtool_ships_orte']} WHERE corpID='".$this->User->corpID."';";
		$res = $this->db->query( $str );
		$standorte = array();
		while ( $row = $res->fetch_assoc() ) {
			$array = array($row['standort'] => $row['standort']);
			$standorte = array_merge($standorte,$array);
		}
		
		return $standorte;
	}
	
	function dread_get_ships($sort=false) {
		if(!$sort) $sort = "id";
		else 	   $sort = $this->db->escape($sort);
		$str = "SELECT * FROM {$this->_table['fsrtool_ships']} WHERE status!='verstorben' AND corpID='".$this->User->corpID."' ORDER BY ".$sort.";";
		$res = $this->db->query( $str );
		while ( $row = $res->fetch_assoc() ) {
			if($row) $rows[] = $row;
		}
		$res->close();
		#foreach ( $rows as &$row ) $row['name'] = htmlentities($row['name']);
		return $rows;
	}
	
	function dread_get_deadDreads($sort=false) {
		if(!$sort) $sort = "id";
		else 	   $sort = $this->db->escape($sort);
		$str = "SELECT * FROM {$this->_table['fsrtool_ships']} WHERE status='verstorben' AND corpID='".$this->User->corpID."' ORDER BY ".$sort.";";
		$res = $this->db->query( $str );
		while ( $row = $res->fetch_assoc() ) {
			if($row) $rows[] = $row;
		}
		$res->close();
		return $rows;
	}
	
	function dread_get_dreadTyp($typ,$sort=false) {
		if(!$sort) $sort = "versichert,status,name";
		else 	   $sort = $this->db->escape($sort);
		$str = "SELECT * FROM {$this->_table['fsrtool_ships']} WHERE typ = '".$typ."' AND tot = 0 AND corpID='".$this->User->corpID."' ORDER BY ".$sort.";";
		$res = $this->db->query( $str );
		while ( $row = $res->fetch_assoc() ) {
			if($row) $rows[] = $row;
		}
		$res->close();
		return $rows;
	}
	
	function dread_canFly($typ) {
		$typ = $this->db->escape($typ);
		
		if ( $User->corp == 147849586 ) {
			$output=array();
		
			$str = "SELECT shipsp.user_id, users.username as username
					FROM {$this->_table['fsrtool_ships_player']} as shipsp 
					INNER JOIN {$this->_table['fsrtool_user']} as users ON shipsp.user_id = users.charID
					WHERE shipsp.".$typ." = 1 
						AND shipsp.hattdread='0' 
						AND users.corpID IN (".$this->User->corpID.",1731679944)
					union
					SELECT shipsp.user_id, alts.charName as username
					FROM {$this->_table['fsrtool_ships_player']} as shipsp 
					INNER JOIN {$this->_table['fsrtool_alts']} as alts ON shipsp.user_id = alts.charID
					WHERE shipsp.".$typ." = 1 
						AND shipsp.hattdread='0' 
						AND alts.corpID IN (".$this->User->corpID.",1731679944) 
					ORDER BY username;";
			$res = $this->db->query( $str );
			while ( $row = $res->fetch_assoc() ) {
				$array = array($row['user_id'] => $row['username']);
				$out= $output += $array;
			}
			$res->close();
			return $out;
		} else {
			$output=array();
			
			$str = "SELECT shipsp.user_id, users.username as username
					FROM {$this->_table['fsrtool_ships_player']} as shipsp 
					INNER JOIN {$this->_table['fsrtool_user']} as users ON shipsp.user_id = users.charID
					WHERE shipsp.".$typ." = 1 
						AND shipsp.hattdread='0' 
						AND users.corpID='".$this->User->corpID."' 
					union
					SELECT shipsp.user_id, alts.charName as username
					FROM {$this->_table['fsrtool_ships_player']} as shipsp 
					INNER JOIN {$this->_table['fsrtool_alts']} as alts ON shipsp.user_id = alts.charID
					WHERE shipsp.".$typ." = 1 
						AND shipsp.hattdread='0' 
						AND alts.corpID='".$this->User->corpID."' 
					ORDER BY username;";
			$res = $this->db->query( $str );
			while ( $row = $res->fetch_assoc() ) {
				$array = array($row['user_id'] => $row['username']);
				$out= $output += $array;
			}
			$res->close();
			return $out;
		}
	}
	
	function dread_vorhanden() {
		$str = "SELECT
				  Count(Mor.typ) AS Moros, Nag.Naglfar, Rev.Revelation,
				  Pho.Phoenix
				FROM
				  {$this->_table['fsrtool_ships']} Mor,
					(SELECT Count({$this->_table['fsrtool_ships']}.typ) AS Naglfar
					FROM {$this->_table['fsrtool_ships']}
					WHERE {$this->_table['fsrtool_ships']}.typ = 'Naglfar' AND
					  {$this->_table['fsrtool_ships']}.tot = 0 AND
					  {$this->_table['fsrtool_ships']}.status != 'not_ready' AND
					  {$this->_table['fsrtool_ships']}.corpID = '".$this->User->corpID."') Nag,
					(SELECT Count({$this->_table['fsrtool_ships']}.typ) AS Revelation
					FROM {$this->_table['fsrtool_ships']}
					WHERE {$this->_table['fsrtool_ships']}.typ = 'Revelation' AND
					  {$this->_table['fsrtool_ships']}.tot = 0 AND
					  {$this->_table['fsrtool_ships']}.status != 'not_ready' AND
					  {$this->_table['fsrtool_ships']}.corpID = '".$this->User->corpID."') Rev,
					(SELECT Count({$this->_table['fsrtool_ships']}.typ) AS Phoenix
					FROM {$this->_table['fsrtool_ships']}
					WHERE {$this->_table['fsrtool_ships']}.typ = 'Phoenix' AND
					  {$this->_table['fsrtool_ships']}.tot = 0 AND
					  {$this->_table['fsrtool_ships']}.status != 'not_ready' AND
					  {$this->_table['fsrtool_ships']}.corpID = '".$this->User->corpID."') Pho
				WHERE
				  Mor.typ = 'Moros' AND
				  Mor.tot = 0 AND
				  Mor.status != 'not_ready' AND
				  Mor.corpID = '".$this->User->corpID."'
				GROUP BY
				  Nag.Naglfar, Rev.Revelation, Pho.Phoenix;";
		$res = $this->db->query( $str );
		$row = $res->fetch_assoc();
		$res->close();
		return $row;
	}
	
	function dread_ausgabe($player, $id) {
		$player = $this->db->escape($player);
		$id     = $this->db->escape($id);
		
		$str = "SELECT username FROM {$this->_table['fsrtool_user']} WHERE charID='".$player."'
				UNION
				SELECT charName as username FROM {$this->_table['fsrtool_alts']} WHERE charID='".$player."';";
		$username = $this->db->fetch_one( $str, 'username' );
		
		
		$str = "UPDATE {$this->_table['fsrtool_ships']} SET status='verliehen',
					player='".$username."',
					time='".time()."',
					timeback=null							
				WHERE Id='".$id."' AND corpID='".$this->User->corpID."';";
		$res = $this->db->exec_query( $str );
		
		$str = "UPDATE {$this->_table['fsrtool_ships_player']} SET hattdread='1' WHERE user_id='".$player."';";
		$res = $this->db->exec_query( $str );
		
		return true;
	}
	
	function dread_back($id) {
		$id = $this->db->escape($id);
		$str = "SELECT ships.time, ships.player, users.charID AS userID
			    FROM {$this->_table['fsrtool_ships']} as ships 
			    INNER JOIN {$this->_table['fsrtool_user']} as users ON ships.player = users.username
			    WHERE ships.Id='".$id."' AND ships.corpID='".$this->User->corpID."'
			    UNION
			    SELECT ships.time, ships.player, alts.charID AS userID
			    FROM {$this->_table['fsrtool_ships']} as ships 
			    INNER JOIN {$this->_table['fsrtool_alts']} as alts ON ships.player = alts.charName
			    WHERE ships.Id='".$id."' AND ships.corpID='".$this->User->corpID."';";
		$res = $this->db->query( $str );
		$row = $res->fetch_assoc();
		$res->close();
		
		$str = "INSERT INTO {$this->_table['fsrtool_ships_log']}
				SET corpID	 = '".$this->User->corpID."',
					shipID   = '".$id."',
					charName = '".$row['player']."',
					time     = '".$row['time']."',
					timeback = '".time()."';";
		$res = $this->db->exec_query( $str );
		
		$str = "UPDATE {$this->_table['fsrtool_ships']} SET status='einsatzbereit',	timeback='".time()."'
				WHERE Id='".$id."' AND corpID='".$this->User->corpID."';";
		$res = $this->db->exec_query( $str );
		
		$str = "UPDATE {$this->_table['fsrtool_ships_player']} SET hattdread='0' WHERE user_id='".$row['userID']."';";
		$res = $this->db->exec_query( $str );
		
		return true;
	}
	
	function dread_skill_add($typ,$name,$level) {
		#$typ   = $this->db->escape($typ);
		$name  = $this->db->escape($name);
		$level = $this->db->escape($level);
		
		$typeID = $this->dread_NametoID($name);
		
		if (!is_array($typ) || count($typ)==4) {
			$typ = array($typ);
		}
		
		foreach($typ as $value) {
			$str = "SELECT * FROM {$this->_table['fsrtool_ship_skills']} WHERE ship_id='".$value."' AND skill_id='".$typeID."' AND corpID='".$this->User->corpID."';";
			$res = $this->db->query( $str );
			if ( $res->num_rows > 0 ) {
				$str = "UPDATE {$this->_table['fsrtool_ship_skills']} 
						SET quantity='".$level."' 
						WHERE ship_id='".$value."' AND skill_id='".$typeID."' AND corpID='".$this->User->corpID."';";
				$row = $this->db->exec_query( $str );
			} else {
				$str = "INSERT INTO {$this->_table['fsrtool_ship_skills']} 
						SET ship_id	 ='".$value."',
							skill_id ='".$this->dread_NametoID($name)."',
							quantity ='".$level."',
							corpID	 ='".$this->User->corpID."';";
				$row = $this->db->exec_query( $str );
			}
			$res->close();
		}
	}
	
	function dread_skill_del($shipID,$skillID) {
		$shipID  = $this->db->escape($shipID);
		$skillID = $this->db->escape($skillID);
		
		$str = "SELECT * FROM {$this->_table['fsrtool_ship_skills']} WHERE ship_id='".$shipID."' AND skill_id='".$skillID."' AND corpID='".$this->User->corpID."';";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$str = "DELETE FROM {$this->_table['fsrtool_ship_skills']} WHERE ship_id='".$shipID."' AND skill_id='".$skillID."' AND corpID='".$this->User->corpID."';";
			$row = $this->db->exec_query( $str );
		}
		$res->close();
		
	}
	
	function dread_get_skills() {
		$str = "SELECT s.*, i.typeName
				FROM {$this->_table['invtypes']} i 
				INNER JOIN {$this->_table['fsrtool_ship_skills']} s ON i.typeID = s.skill_id
				WHERE s.corpID='".$this->User->corpID."'
				ORDER BY s.ship_id, s.skill_id;";
		
		$rows = $this->db->fetch_all( $str );
			
		return $rows;
	}
	
	function dread_NametoID($name) {
		$str = "SELECT typeID FROM {$this->_table['invtypes']} WHERE typeName = '".$this->db->escape($name)."';";
		return $this->db->fetch_one( $str, 'typeID' );
	}
	
	function dread_add_dread($dread) {
		$typ = $this->db->escape($dread['typ']);
		$name= $this->db->escape($dread['name']);
		$ort = $this->db->escape($dread['ort']);
		$text= $this->db->escape($dread['text']);
		
		$query = "SELECT count(Id) as id FROM {$this->_table['fsrtool_ships']} WHERE corpID = '".$this->User->corpID."';";
		$id = $this->db->fetch_one( $query, 'id' );
		$id = $id+1;
				
		$str = "INSERT INTO {$this->_table['fsrtool_ships']}
			    SET Id	   = '".$id."',
				  corpID   = '".$this->User->corpID."',
				  typ	   = '".$typ."',
				  name	   = '".$name."',
				  standort = '".$ort."',
				  bemerkung= '".$text."';";
		$res = $this->db->doQuery( $str );
		return $res;
	}
	
	function dread_getDreadByID($id) {
		$id = $this->db->escape($id);
		$str = "SELECT * FROM {$this->_table['fsrtool_ships']} WHERE corpID='".$this->User->corpID."' AND id='".$id."';";
		$res = $this->db->query( $str );
		if ( $res->num_rows > 0 ) {
			$row = $res->fetch_assoc();
		}
		$res->close();
		return $row;
	}
	
	function dread_editDread($dread) {
		$id  	= $this->db->escape($dread['id']);
		$name	= $this->db->escape($dread['name']);
		$ort 	= $this->db->escape($dread['ort']);
		$status = $this->db->escape($dread['stat']);
		$text	= $this->db->escape($dread['text']);
		$time	= empty($dread['time']) ? 'NULL' : (is_int(strtotime($dread['time'])) ? strtotime($dread['time']) : 'NULL');
		$vers	= empty($dread['time']) ? 'Nein' : (is_int(strtotime($dread['time'])) ? 'Ja' : 'Nein');
		if ($status == 'verstorben') $tot = 1; else $tot = 0;
		
		$str = "UPDATE {$this->_table['fsrtool_ships']}
			    SET name	     = '".$name."',
				  standort   	 = '".$ort."',
				  status     	 = '".$status."',
				  versichert 	 = '".$vers."',
				  versichert_bis = '".$time."',
				  bemerkung  	 = '".$text."',
				  tot			 = '".$tot."'
			    WHERE Id = '".$id."' AND corpID='".$this->User->corpID."';";
		$res = $this->db->doQuery( $str );
		if ($tot == 1) {
			$str = "SELECT ships.time, ships.player, users.charID AS userID
				    FROM {$this->_table['fsrtool_ships']} as ships 
				    INNER JOIN {$this->_table['fsrtool_user']} as users ON ships.player = users.username
				    WHERE ships.Id='".$id."' AND ships.corpID='".$this->User->corpID."'
				    UNION
				    SELECT ships.time, ships.player, alts.charID AS userID
				    FROM {$this->_table['fsrtool_ships']} as ships 
				    INNER JOIN {$this->_table['fsrtool_alts']} as alts ON ships.player = alts.charName
				    WHERE ships.Id='".$id."' AND ships.corpID='".$this->User->corpID."';";
			$res = $this->db->query( $str );
			if ( $res->num_rows > 0 ) {
				$row = $res->fetch_assoc();
			}
			$res->close();
			
			$str = "UPDATE {$this->_table['fsrtool_ships_player']} SET hattdread='0' WHERE user_id='".$row['userID']."';";
			$res = $this->db->exec_query( $str );
		}
		return $res;
	}
	
	function dread_editDeadDread($dread) {
		$id  	= $this->db->escape($dread['id']);
		$text	= $this->db->escape($dread['text']);
		$kblink = $this->db->escape($dread['kblink']);
		
		$str = "UPDATE {$this->_table['fsrtool_ships']}
				SET bemerkung = '".$text."',
					kblink	  = '".$kblink."'
				WHERE Id = '".$id."' AND corpID='".$this->User->corpID."';";
		$res = $this->db->doQuery( $str );
		
		return $res;
	}
	
	function dread_addStandort( $ort ) {	
		$str = "REPLACE INTO {$this->_table['fsrtool_ships_orte']} SET corpID='".$this->User->corpID."', standort='".$this->db->escape($ort)."';";
		$res = $this->db->exec_query( $str );
		
		return $res;
	}
	
	function dread_delStandort($ort) {		
		$str = "DELETE FROM {$this->_table['fsrtool_ships_orte']} WHERE corpID='".$this->User->corpID."' AND standort='".$this->db->escape($ort)."';";
		$res = $this->db->exec_query( $str );
		
		return $res;
	}
	
	function ajaxSearch() {
		if(isset($_GET['term']) && strlen($_GET['term']) > 2){  // Post Variable Input Feld ?
			$term = $this->db->escape($_GET['term']);
			$sql = "SELECT
				i.typeID, i.typeName
			FROM
				{$this->_table['invmarketgroups']} m 
			INNER JOIN
				{$this->_table['invtypes']} i ON m.marketGroupID = i.marketGroupID
			WHERE
				i.typeName LIKE '{$term}%' 
			AND m.parentGroupID = 150;";

			$return_array = array();

			$res=$this->db->query($sql);
			while($row=$res->fetch_assoc()){
				$row_array['label'] = $row['typeName'];
				$row_array['value'] = $row['typeName'];
				array_push($return_array,$row_array);
			}
			$res->close();

			return json_encode($return_array);
		}
	}
}
?>