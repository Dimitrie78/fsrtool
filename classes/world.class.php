<?php
defined('FSR_BASE') or die('Restricted access');

class World
{
	private $config;
	public $_table = array();
	
	public $User;
	public $db;
	
	public function __construct(User $User) {
		$this->db = $User->db;
		$this->User = $User;
		$this->_table = $User->_table;
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
	
	public function userOnline() {
		$this->db->exec_query("INSERT INTO {$this->_table['fsrtool_user_online']} (IP,charID,url,Datum) 
			VALUES ('{$_SERVER['REMOTE_ADDR']}', '{$this->User->charID}', '{$_SERVER['REQUEST_URI']}', NOW())
			ON DUPLICATE KEY UPDATE charID='{$this->User->charID}',url='{$_SERVER['REQUEST_URI']}',Datum=NOW();");
		
		// alte Datensätze löschen
		$this->db->exec_query("DELETE FROM {$this->_table['fsrtool_user_online']} WHERE DATE_SUB(NOW(), INTERVAL 5 MINUTE) > Datum;");
		// Anzahl Ausgeben
		$online = $this->db->fetch_one("SELECT COUNT(*) as Anzahl FROM {$this->_table['fsrtool_user_online']};", 'Anzahl');
		
		if( $this->User->Admin && $online >= 1 ) {
			$res = $this->db->query("SELECT u.username, c.ticker, o.url
				FROM {$this->_table['fsrtool_user_online']} o 
				LEFT JOIN {$this->_table['fsrtool_user']} u ON o.charID = u.charID 
				LEFT JOIN {$this->_table['fsrtool_corps']} c ON u.corpID = c.id
				GROUP BY o.charID
				ORDER BY u.username;");
			if ( $res->num_rows > 0 ) {
				while ($row = $res->fetch_assoc()) {
					if ($this->User->Admin) $users[] = '<a href="http://'.$_SERVER['HTTP_HOST'].$row['url'].'">'.$row['username'].'['.$row['ticker'].']</a>';
					else $users[] = $row['username'] . '[' . $row['ticker'] . ']';
				}
			}
			return $online . ' : ' . implode(', ', $users);
		}
		
		return $online;
	}

	function getModules() {
		$array = array();
		$handle = opendir("./modules/");
		while (false !== ($file = readdir($handle))) {
			if (($file != "..") AND ($file != ".")) {
	    	    if (is_dir("./modules/".$file))	{
					$array[] = $file;
				}
			}
	    }
		closedir($handle);
		return $array;
	}
		
	function getSkilltree()	{		
		#$evedb = new DBManager($this->db->msg, true);
		$sqlquery = "SELECT g.groupID, g.groupName, it.typeID, it.typeName, dt.valueFloat as rank
					FROM {$this->_table['invtypes']} it
					INNER JOIN {$this->_table['invgroups']} g ON g.groupID = it.groupID
					INNER JOIN {$this->_table['dgmtypeattributes']} dt ON dt.typeID = it.typeID
					WHERE g.categoryID=16 AND dt.attributeID=275
					ORDER BY g.groupName, it.typeName;";
		$result = $this->db->query( $sqlquery );

		while ( $row = $result->fetch_assoc() ) {
			if ($row){
				$skilltreeX[$row['typeID']]['groupID']   = $row['groupID'];
				$skilltreeX[$row['typeID']]['groupName'] = $row['groupName'];
				$skilltreeX[$row['typeID']]['typeID']    = $row['typeID'];
				$skilltreeX[$row['typeID']]['typeName']  = $row['typeName'];
				$skilltreeX[$row['typeID']]['rank']      = $row['rank'];
				//$group[] = $row['groupID'];
			}
		}
		$result->close();
		
		return $skilltreeX;
	}
	
	public function getChatUser() {
		$return = array();
		$res = $this->db->fetch_all("SELECT username FROM {$this->_table['fsrtool_user']} WHERE username != '{$this->User->username}' ORDER BY username", 'username');
		$gesucht = array(" ", "'", "&#039;");
		$ersetzt = array("_", "", "");
		foreach($res as $val){
			$out['aname'] = $val;
			$out['bname'] = str_replace($gesucht,$ersetzt,strtolower($val));
			$return[] = $out;
		}
		return $return;
	}
	
	public function getChatUsersOnline() {
		$this->db->exec_query("INSERT INTO {$this->_table['fsrtool_user_online']} (IP,charID,Datum) 
			VALUES ('{$_SERVER['REMOTE_ADDR']}', '{$this->User->charID}', NOW())
			ON DUPLICATE KEY UPDATE charID='{$this->User->charID}',Datum=NOW();");
		
		// alte Datensätze löschen
		$this->db->exec_query("DELETE FROM {$this->_table['fsrtool_user_online']} WHERE DATE_SUB(NOW(), INTERVAL 1 MINUTE) > Datum;");
		// Anzahl Ausgeben
		$online = $this->db->fetch_one("SELECT COUNT(DISTINCT charID) as Anzahl FROM {$this->_table['fsrtool_user_online']};", 'Anzahl');
		$return['online'] = $online-1;
		$return['users'] = array();
		if( $online >= 1 ) {
			$res = $this->db->query("SELECT u.username, c.ticker 
				FROM {$this->_table['fsrtool_user_online']} o 
				LEFT JOIN {$this->_table['fsrtool_user']} u ON o.charID = u.charID 
				LEFT JOIN {$this->_table['fsrtool_corps']} c ON u.corpID = c.id
				WHERE u.charID != '{$this->User->charID}'
				GROUP BY o.charID
				ORDER BY u.username;");
			if ( $res->num_rows > 0 ) {
				$gesucht = array(" ", "'", "&#039;");
				$ersetzt = array("_", "", "");
				while ($row = $res->fetch_assoc()) {
					$out['aname'] = $row['username'] . '[' . $row['ticker'] . ']';
					$out['bname'] = str_replace($gesucht,$ersetzt,strtolower($row['username']));
					$return['users'][] = $out;
				}
			}
			
			return $return;
		}
		
		return false;
	}
	
	public function ajaxSearch() {
		if(isset($_GET['term']) && strlen($_GET['term']) > 2){  // Post Variable Input Feld ?
			$term = $this->db->escape($_GET['term']);
			$sql = "SELECT typeID, typeName
				FROM {$this->_table['invtypes']}
				WHERE
					typeName LIKE '{$term}%' 
				AND	published = 1
				LIMIT 0,30;";
			
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
	
	public function versionCheck($ver) {
		$instver = $this->db->fetch_one("SELECT value FROM {$this->_table['fsrtool_config']} WHERE name='Version'", 'value');
		if($instver == $ver)
			return true;
		else return false;
	}
	
	public function calendarAddEvent() {
		$data = $this->db->escape($_POST);
		$corpID = $this->User->corpID;
		$insert = "INSERT INTO {$this->_table['fsrtool_calendar_events']} (corpID, title, start, end, allDay) VALUES ('%s', '%s', '%s', '%s', %s)";
		$insert = sprintf($insert,
			$corpID,
			$data['data']['title'],
			date('Y-m-d H:i:s', strtotime($data['data']['start'])),
			date('Y-m-d H:i:s', strtotime($data['data']['end'])),
			$data['data']['allDay']);
		
		$res = $this->db->query($insert);
		
		return $this->db->insert_id;
	}
	
	public function calendarDropEvent() {
		$data 	= $this->db->escape($_POST);
		$corpID = $this->User->corpID;
		$id 	= $data['data']['id'];
		$day 	= $data['data']['day'];
		$min 	= $data['data']['min'];
		$allDay = $data['data']['allDay'];
		if($data['data']['allDay'] == 1) {
			$str = "UPDATE {$this->_table['fsrtool_calendar_events']} SET start = DATE_ADD(start, INTERVAL $day DAY), end = DATE_ADD(end, INTERVAL $day DAY), allDay = $allDay WHERE id=$id AND corpID=$corpID";
			$this->db->query($str);
		} else {
			$str = "UPDATE {$this->_table['fsrtool_calendar_events']} SET 
					start = DATE_ADD(start, INTERVAL $day DAY), 
					start = DATE_ADD(start, INTERVAL $min MINUTE), 
					end = DATE_ADD(end, INTERVAL $day DAY), 
					end = DATE_ADD(end, INTERVAL $min MINUTE), 
					allDay = $allDay 
				WHERE id=$id AND corpID=$corpID";
			$this->db->query($str);
		}
		
		return json_encode($data);
	}
	
	public function calendarResizeEvent() {
		$data 	= $this->db->escape($_POST);
		$corpID = $this->User->corpID;
		$id 	= $data['data']['id'];
		$day 	= $data['data']['day'];
		$min 	= $data['data']['min'];
		//$allDay = $data['data']['allDay']);
		
			$str = "UPDATE {$this->_table['fsrtool_calendar_events']} SET 
					end = DATE_ADD(end, INTERVAL $day DAY), 
					end = DATE_ADD(end, INTERVAL $min MINUTE)
				WHERE id=$id AND corpID=$corpID";
			$this->db->query($str);
		
		
		return json_encode($data);
	}
	
	public function calendarDelEvent() {
		$data = $this->db->escape($_POST);
		$corpID = $this->User->corpID;
		$del = "DELETE FROM {$this->_table['fsrtool_calendar_events']} WHERE id = %d AND corpID=$corpID";
		$del = sprintf($del, $data['id']);
		$this->db->query($del);
	}
	
	public function calendarGetEvent() {
		$events=array();
		$corpID = $this->User->corpID;
		$get = "SELECT * FROM {$this->_table['fsrtool_calendar_events']} WHERE corpID=$corpID";
		$res = $this->db->query($get);
		while ($row = $res->fetch_assoc()) {
			$event['id'] 	= $row['id'];
			$event['title'] = $row['title'];
			$event['start'] = $row['start'];
			if($row['end'] != null) $event['end'] = $row['end'];
			if($row['allDay'] == 0) $event['allDay'] = false;
			$event['color'] = 'yellow';
			$event['textColor'] = 'black';
			$events[] = $event;
		}
		return $events;
	}
}

?>