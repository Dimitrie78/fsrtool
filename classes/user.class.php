<?php
defined('FSR_BASE') or die('Restricted access');

class User
{
	public $charID = 0;
	public $corpName = null;
	public $roles = array();
	
	public $db;
	public $evedb;
	public $_table = array();
	
	private $remTime = 2592000; // One month
	private $remCookieName = 'ckSaveFSR';
	private $remCookieDomain = '';
	private $Path = '';
	private $sessionVariable = 'userSessionValue';
	private $absenderMail = 'fsrtool@free-space-ranger.de';
	private $url = URL_DOWORK;
	
	private $userData = array();
	private $displayErrors = true;
	
	public function __construct( $db = null ) {
		$this->absenderMail = EMAIL;
		$this->remCookieDomain = $this->remCookieDomain == '' ? $_SERVER['HTTP_HOST'] : $this->remCookieDomain;
		$this->Path = dirname($_SERVER['PHP_SELF']);
	    $this->db = $db;
		$this->_table = $this->db->_table;
		
	    if( !$this->db ) die('No DB connection');
		if( !isset( $_SESSION ) ) session_start();
	    if( !empty( $_SESSION[$this->sessionVariable]) ){
			$this->loadUser( $_SESSION[$this->sessionVariable] );
	    }
	    //Maybe there is a cookie?
	    if ( isset($_COOKIE[$this->remCookieName]) && !$this->is_loaded() ){
	      //echo 'I know you<br />';
	      $u = unserialize(base64_decode($_COOKIE[$this->remCookieName]));
	      $this->login($u['uname'], $u['password'], true);
	    }
		
	}
  
	/**
  	* Login function
  	* @param string $uname
  	* @param string $password
  	* @param bool $remember
	* @param bool $loadUser
  	* @return bool
	*/
	public function login($uname, $password, $remember = false, $loadUser = true) {
		$uname    = htmlentities( $this->db->escape($uname), ENT_QUOTES, "utf-8" );
    	$password = $originalPassword = $this->db->escape($password);
		$password = md5(PWSALT.sha1($password.PWSALT));
		
		$res = $this->db->query("SELECT u.*, c.name as corpName, a.id as allyID, a.name as allyName, 
					  l.logins, l.lastlogin as lastLogin 
					FROM {$this->_table['fsrtool_user']} u
					LEFT JOIN {$this->_table['fsrtool_corps']} c ON u.corpID = c.id
					LEFT JOIN {$this->_table['fsrtool_allys']} a ON c.ally = a.id
					LEFT JOIN {$this->_table['fsrtool_logins']} l ON u.charID = l.charID 
					WHERE u.username = '$uname' AND u.password = '$password'
					LIMIT 1;");
		if ( $res->num_rows == 0 )
			return false;
		if ( $loadUser )
		{
			$this->userData = $res->fetch_assoc();
			$this->charID = $this->userData['charID'];
			$this->corpName = $this->userData['corpName'];
			$this->getRoles();
			$this->loadRoles( $this->charID );
			$this->loadAtls();
			$this->countUserLogin();
			$this->setIP();
			$_SESSION[$this->sessionVariable] = $this->charID;
			$gesucht = array(" ", "'", "&#039;");
			$ersetzt = array("_", "", "");
			$_SESSION['username'] = str_replace($gesucht, $ersetzt, strtolower($uname));
			if ( $remember ){
			  $cookie = base64_encode(serialize(array('uname'=>$uname,'password'=>$originalPassword)));
			  $a = setcookie($this->remCookieName, $cookie, time()+$this->remTime, $this->Path, $this->remCookieDomain);
			}
		}
		return true;
	}
	
	/**
  	* Logout function
  	* param string $redirectTo
  	* @return bool
    */
	public function logout($redirectTo = '') {
		$a = setcookie($this->remCookieName, '', time()-3600, $this->Path, $this->remCookieDomain);
		unset($_SESSION[$this->sessionVariable]);
		$this->userData = '';
		session_destroy();
		if ( $redirectTo != '' && !headers_sent()){
		   header('Location: '.$redirectTo );
		   exit;//To ensure security
		}
	}
    
	/**
  	* Function to determine if a property is true or false
  	* param string $prop
  	* @return bool
    */
	public function is($prop){
		return $this->get_property($prop)==1?true:false;
	}
	
	/**
	* Getter method
	*
	* @param string $name
	* @return ...
	*/
	public function __get($name) {
        if ( array_key_exists( $name, $this->roles ) 
		&& ( array_key_exists( 'Admin', $this->userData ) || array_key_exists( 'SuperAdmin', $this->userData ) ) )
			return true;
			
		if ( array_key_exists( $name, $this->userData ) )
            return $this->userData[$name];
		
		if ( $name == 'apiX' )
			return ( str_pad( substr( $this->userData['vCODE'],0,6), 60 , "*" ) );
		
		if ( strtolower($name) == 'apiaccess' ) {
			require_once('classes/accessMask.class.php');
			return new accessMask( $this , $name );
		}
		
		return false;
    }
	
    /**
  	* Is the user an active user?
  	* @return bool
    */
	private function is_active() {
		return $this->userData['active'];
	}
  
    /**
    * Is the user loaded?
    * @ return bool
    */
	private function is_loaded() {
		return empty($this->charID) ? false : true;
	}
  
    /**
  	* Activates the user account
  	* @return bool
    */
	public function activate() {
		if (empty($this->charID)) $this->error('No user is loaded', __LINE__);
		if ( $this->is_active()) $this->error('Allready active account', __LINE__);
		$res = $this->db->exec_query("UPDATE {$this->_table['fsrtool_user']} SET active = 1 
		WHERE `charID` = '".$this->db->escape($this->charID)."' LIMIT 1");
		if ( $res == 1 ) {
			$this->userData['active'] = true;
			return true;
		}
		return false;
	}
	
	public function resetPass($data) {
		if (!is_array($data)) return false;
		foreach ($data as $k => $v ) $data[$k] = "'".$this->db->escape($v)."'";	
		$res = $this->db->exec_query("UPDATE {$this->_table['fsrtool_user']} SET password = newPass, newPass = '', code = '' 
		WHERE `charID` = {$data['charID']} and code = {$data['code']} LIMIT 1");
		if ( $res == 1 )
			return true;
		return false;
	}
	
	public function editPass($data) {
		if (!is_array($data)) return false;
		$id = $this->db->escape( $this->charID );
		$oldpass = md5(PWSALT.sha1($this->db->escape( $data['oldpass']).PWSALT));
		$newpass = md5(PWSALT.sha1($this->db->escape( $data['newpass']).PWSALT));
		//$newpass = md5($this->db->escape( $data['newpass']) );
		$check = $this->db->query("SELECT * FROM {$this->_table['fsrtool_user']} WHERE charID = '".$id."' AND password = '".$oldpass."';");
		if ( $check->num_rows > 0 ) {
			$res = $this->db->exec_query("UPDATE {$this->_table['fsrtool_user']} SET password = '".$newpass."' WHERE charID = ".$id.";");
			if ( $res == 1 )
				return true;
		}
		return false;
	}
	
	public function insertUser($data, $stuff){
		if (!is_array($data)) $this->error('Data is not an array', __LINE__);
		$data['username'] = htmlentities($data['username'],ENT_QUOTES,"utf-8");
		$data['password'] = md5(PWSALT.sha1($data['password'].PWSALT));
		
		foreach ($data as $k => $v ) $data[$k] = "'".$this->db->escape($v)."'";	
		
		$check = $this->db->query("SELECT username FROM {$this->_table['fsrtool_user']} WHERE charID = ".$data['charID']." LIMIT 1;");
		if ( $check->num_rows > 0 )
			return false;
		$count = $this->db->fetch_one("SELECT count(charID) as x FROM {$this->_table['fsrtool_user']} WHERE corpID = {$data['corpID']};", 'x');
		$this->db->exec_query("INSERT INTO {$this->_table['fsrtool_user']} (`".implode('`, `', array_keys($data))."`) VALUES (".implode(", ", $data).")");
		if ( $count == 0 ) { 
			$roleID = $this->db->fetch_one("SELECT roleID FROM {$this->_table['fsrtool_roles']} WHERE roleName = 'Manager';", 'roleID');
			$this->db->exec_query("INSERT INTO {$this->_table['fsrtool_user_roles']} (charID, roleID) VALUES ({$data['charID']}, {$roleID});");
		}
		$this->addCorp($stuff);
		$this->addAlly($stuff);
		return true;
	}
	
	public function addAlt( $data, $stuff ) {
		if (!is_array($data)) $this->error('Data is not an array', __LINE__);
		$data['charName'] = htmlentities($data['charName'],ENT_QUOTES,"utf-8");
		foreach ($data as $k => $v ) $data[$k] = "'".$this->db->escape($v)."'";	
		$check = $this->db->query("SELECT * FROM {$this->_table['fsrtool_alts']} WHERE charID = ".$data['charID']." AND mainCharID = '".$this->charID."' LIMIT 1;");
		if ( $check->num_rows > 0 )
			return false;
		$this->db->exec_query("INSERT INTO {$this->_table['fsrtool_alts']} (`".implode('`, `', array_keys($data))."`) VALUES (".implode(", ", $data).")");
		$this->addCorp($stuff);
		$this->addAlly($stuff);
		return true;
	}
	
	public function sendMail($user, $email) {
		#$user  = htmlentities($this->db->escape($user),ENT_QUOTES,"utf-8");
		$user  = $this->db->escape($user); 
		$email = $this->db->escape($email);
		
		$res = $this->db->query("SELECT charID FROM {$this->_table['fsrtool_user']} WHERE username = '".$user."' AND email = '".$email."';");
		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			$res->close();
			$charID = $row['charID'];
		} else return false;
		
		$pass = $this->randomPass();
		$newpass = md5(PWSALT.sha1($pass.PWSALT));
		$code = md5(time());
		$this->url .= '?action=activate';
		$this->url .= '&u='.$charID.'&k='.$code;
		$res = $this->db->exec_query("UPDATE {$this->_table['fsrtool_user']} SET newPass = '".$newpass."', code = '".$code."' WHERE charID = '".$charID."';");
		if( $res == 1 ) {
			// Versende eine HTML Mail:
			//require_once ('mail/phpmailer.inc.php');
			$from = $this->absenderMail;
			$to   = $email;
			$Subject = 'FSR-Tool';
			$Body = 'Hello '.$user.'<br />'
				.'<br />'
				.'You are receiving this notification because you have (or someone pretending<br />'
				.'to be you has) requested a new password be sent for your account on "FSR-Tool".<br />'
				.'If you did not request this notification then please ignore it,<br />'
				.'if you keep receiving it please contact the site administrator.<br />'
				.'<br />'
				.'To use the new password you need to activate it. To do this click the link<br />'
				.'provided below.<br />'
				.'<br />'
				.'<a href="'.$this->url.'">'.$this->url.'</a><br />'
				.'<br />'
				.'If successful you will be able to login using the following password:<br />'
				.'<br />'
				.'<b>Password:</b> '.$pass.'<br />'
				.'<br />'
				.'You can of course change this password yourself via the profile page.<br />'
				.'IF you have any difficulties please contact the site administrator.<br />'
				.'<br />'
				.'-- <br />'
				.'Regards Dimitrie<br />';
			
			$mail = new phpmailer();
			$mail->IsHTML(true);
	
		//	$mail->IsSMTP();
	
			$mail->From      = $from;
			$mail->FromName  = 'Dimitrie';
			$mail->Subject   = $Subject;
			$mail->Body      = $Body;
			$mail->AddAddress($to);   
			
			return $mail->Send();
			
			#$mail = miniMail::send( $email, $this->absenderMail, $Subject, $Body );
			#return $mail;
		}
		return false;
	}
	
	private function addCorp($stuff)	{
		foreach ($stuff as $k => $v ) $stuff[$k] = "'".$this->db->escape($v)."'";
		$now = date("YmdHis");
		$check = $this->db->query("SELECT * FROM {$this->_table['fsrtool_corps']} WHERE id = ".$stuff['corpID']." LIMIT 1;");
		if ( $check->num_rows > 0 )
			return false;
		else {
			$str = "INSERT INTO {$this->_table['fsrtool_corps']} SET id=".$stuff['corpID'].", name=".$stuff['corpName'].", ally=".$stuff['allyID'].", timestamp='".$now."';";
			$res = $this->db->query($str);
			return true;
		}
	}

	private function addAlly($stuff) {
		foreach ($stuff as $k => $v ) $stuff[$k] = "'".$this->db->escape($v)."'";
		$now = date("YmdHis");
		$check = $this->db->query("SELECT * FROM {$this->_table['fsrtool_allys']} WHERE id = ".$stuff['allyID']." LIMIT 1;");
		if ( $check->num_rows > 0 )
			return false;
		else {
			$str = "INSERT INTO {$this->_table['fsrtool_allys']} SET id=".$stuff['allyID'].", name=".$stuff['allyName'].", timestamp='".$now."';";
			$res = $this->db->query($str);
			return true;
		}
	}
    
	private function randomPass($length=10, $chrs = '1234567890qwertyuiopasdfghjklzxcvbnm'){
		for($i = 0; $i < $length; $i++) {
			$pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
		}
		return $pwd;
	}
	
	private function loadUser($charID) {
		$res = $this->db->query("SELECT u.*, c.name as corpName, a.id as allyID, a.name as allyName, 
					  l.logins, l.lastlogin as lastLogin 
					FROM {$this->_table['fsrtool_user']} u
					LEFT JOIN {$this->_table['fsrtool_corps']} c ON u.corpID = c.id
					LEFT JOIN {$this->_table['fsrtool_allys']} a ON c.ally = a.id
					LEFT JOIN {$this->_table['fsrtool_logins']} l ON u.charID = l.charID 
					WHERE u.charID = '".$this->db->escape($charID)."' 
					LIMIT 1;");
		if ( $res->num_rows == 0 )
			return false;
		$this->userData = $res->fetch_assoc();
		$this->charID = $charID;
		$this->corpName = $this->userData['corpName'];
		$this->getRoles();
		$this->loadRoles( $charID );
		$this->loadAtls();
		$_SESSION[$this->sessionVariable] = $this->charID;
		
		return true;
	}
	
	private function loadAtls() {
		$res = $this->db->query("SELECT a.*, c.name AS corpName, c.ally AS allyID, ally.name AS allyName
			FROM {$this->_table['fsrtool_alts']} a 
			LEFT JOIN {$this->_table['fsrtool_corps']} c ON a.corpID = c.id
			LEFT JOIN {$this->_table['fsrtool_allys']} ally ON c.ally = ally.id
			WHERE a.mainCharID = '{$this->charID}' ORDER BY a.charName;");
		if ( $res->num_rows == 0 )
			return false;
		$this->alts = array();
		while( $row = $res->fetch_assoc() ) {
			$this->alts[ $row['charID'] ] = $row;
			if( array_key_exists( 'userAPI', $row ) )
				$this->alts[ $row['charID'] ]['apiX'] = str_pad( substr( $row['userAPI'],0,6), 60 , "*" );
			if( array_key_exists( 'pos', $row ) && $row['pos'] == 1 )
				$this->PosAlt = true;
			if( array_key_exists( 'silo', $row ) && $row['silo'] == 1 )
				$this->SiloAlt = true;
		}
		
		return true;
	}
	
	private function loadRoles($charID) {
		$res = $this->db->query("SELECT r.roleName, ur.roleID FROM {$this->_table['fsrtool_user_roles']} ur 
			INNER JOIN {$this->_table['fsrtool_roles']} r ON r.roleID = ur.roleID WHERE ur.charID = '".$this->db->escape($charID)."' ORDER BY r.roleID;");	
		if ( $res->num_rows == 0 )
			return false;
		while ( $row2 = $res->fetch_assoc() ) {
			$data[] = $row2;
		}
		foreach( $data as $val ) $this->userData[ $val['roleName'] ] = 1;
		return true;
	}
	
	private function getRoles() {
		#echo'<pre>';print_r($this);die;
		$roles = $this->db->fetch_all("SELECT * FROM {$this->_table['fsrtool_roles']} ORDER BY roleID");
		foreach( $roles as $key => $val ) $this->roles[ $val['roleName'] ] = $val['roleID'];
	}
	
	private function setIP() {
		$str = "UPDATE {$this->_table['fsrtool_user']} SET userIP = '".$_SERVER['REMOTE_ADDR']."' WHERE charID='".$this->db->escape($this->charID)."';";
		$this->db->exec_query("INSERT INTO {$this->_table['fsrtool_user_iplog']} (charID, ipv4, userAgent) VALUES ('{$this->charID}', '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['HTTP_USER_AGENT']}');");
		$res = $this->db->exec_query( $str );
		return $res;
	}
	
	private function countUserLogin() {
		$user = $this->db->escape( $this->charID );
		$now  = date("YmdHis");
		$str = "INSERT INTO {$this->_table['fsrtool_logins']} SET charID='".$user."', logins = 1, lastlogin='".$now."'
			ON DUPLICATE KEY UPDATE logins=logins+1, lastlogin='".$now."';";
		$res = $this->db->exec_query( $str );
		return $res;
	}

	private function error($error, $line = '', $die = false) {
		if ( $this->displayErrors )
			$this->db->msg->addwarning( '<b>Error: </b>'.$error.'<br /><b>Line: </b>'.($line==''?'Unknown':$line).'<br />' );
		if ($die) exit;
		return false;
	}
	
	public function delUser($charID) {
		$charID = $this->db->escape( $charID );
		if ( $charID != $this->charID ) {
			$mainstr = ("DELETE FROM {$this->_table['fsrtool_user']} WHERE charID = '{$charID}';");
			$altstr  = ("DELETE FROM {$this->_table['fsrtool_alts']} WHERE mainCharID = '{$charID}';");
			$rolestr = ("DELETE FROM {$this->_table['fsrtool_user_roles']} WHERE charID = '{$charID}';");
			
			$this->db->exec_query( $altstr );
			$this->db->exec_query( $rolestr );
			return $this->db->exec_query( $mainstr );
		}
		else return 0;
	}
	
	public function editUser($charID) {
		if ( is_array( $charID ) ) {
			$data = $this->db->escape( $charID );
			if ( $data['pwd'] == '' ) {
				$str = ("UPDATE {$this->_table['fsrtool_user']} SET email = '{$data['email']}', description = '{$data['des']}' WHERE charID = '{$data['charID']}';");
			}
			else {
				$data['pwd'] = md5(PWSALT.sha1($data['pwd'].PWSALT));
				$str = ("UPDATE {$this->_table['fsrtool_user']} SET email = '{$data['email']}', description = '{$data['des']}', password = '{$data['pwd']}' WHERE charID = '{$data['charID']}';");
			}
			return $this->db->exec_query( $str );
		}
		else {
			$charID = $this->db->escape( $charID );
			$str = ("SELECT charID, username, email, description FROM {$this->_table['fsrtool_user']} WHERE charID = '{$charID}';");
			$res = $this->db->fetch_all( $str );
			return json_encode( $res[0] );	
		}
	}
	
	public function setRole($charID, $roleID, $set=true) {
		$charID = $this->db->escape( $charID );
		$roleID = $this->db->escape( $roleID );
		
		if ( $charID == $this->charID && $roleID == $this->roles['Admin'] && array_key_exists( 'Admin', $this->userData ) ) return false;
		if ( $charID == $this->charID && $roleID == $this->roles['Manager'] && array_key_exists( 'Manager', $this->userData ) ) return false;
		if ( $roleID == $this->roles['Admin'] && !array_key_exists( 'Admin', $this->userData ) ) return 2;
		if ( isset($this->roles['PosManagerAlly']) && $roleID == $this->roles['PosManagerAlly'] && !array_key_exists( 'Admin', $this->userData ) ) return 2;
		if ( isset($this->roles['SiloManagerAlly']) && $roleID == $this->roles['SiloManagerAlly'] && !array_key_exists( 'Admin', $this->userData ) ) return 2;
				
		if ( $set ) {
			$res = $this->db->exec_query("INSERT INTO {$this->_table['fsrtool_user_roles']} (charID, roleID) VALUES ('{$charID}', '{$roleID}');");
		} else {
			$res = $this->db->exec_query("DELETE FROM {$this->_table['fsrtool_user_roles']} WHERE charID = '{$charID}' AND roleID = '{$roleID}';");
		}
		return $res;
	}
	
	public function setRoleAlt($mainCharID, $charID, $role, $set=true) {
		$mainCharID = $this->db->escape( $mainCharID );
		$charID = $this->db->escape( $charID );
		$role = $this->db->escape( $role );
		
		if ( $set ) {
			$res = $this->db->exec_query("UPDATE {$this->_table['fsrtool_alts']} SET {$role} = 1 WHERE mainCharID = '{$mainCharID}' AND charID = '{$charID}';");
		} else {
			$res = $this->db->exec_query("UPDATE {$this->_table['fsrtool_alts']} SET {$role} = 0 WHERE mainCharID = '{$mainCharID}' AND charID = '{$charID}';");
		}
		return $res;
	}
	
	private function loadCallList() {
		$this->mask = array();
		$res = $this->db->query("SELECT c.accessMask, c.name, c.type, c.groupID
			FROM {$this->_table['fsrtool_api_calls']} c
		/*	WHERE c.type = 'Character' 	*/
			ORDER BY c.accessMask;");
		while ( $row = $res->fetch_assoc() ) {
			if ( $row['name'] == 'CharacterInfo' && $row['groupID'] == 3 )
				$this->mask[ $row['type'] ][ $row['accessMask'] ] = 'CharacterInfoPrivate';
			else if ( $row['name'] == 'CharacterInfo' && $row['groupID'] == 4 )
				$this->mask[ $row['type'] ][ $row['accessMask'] ] = 'CharacterInfoPublic';
			else
				$this->mask[ $row['type'] ][ $row['accessMask'] ] = $row['name'];
		}
		$res->close();
	}
	
	public function accessMask($charID) {
		$res = false;
		if ( !$this->mask ) $this->loadCallList();
		$numargs = func_num_args();
		if ( $numargs > 1 ) {
			$args = func_get_args();
			for ($i = 1; $i < $numargs; $i++) {
				if ( $this->charID == $charID ) {
					$mask = array_search( $args[$i], $this->mask['Character'] );
					if ( $mask ) {
						if ( $this->accessMask & $mask )
							$res = true;
						else {
							$res = false;
							break;
						}
					} else {
						$res = false;
						break;
					}
				} else {
					$mask = array_search( $args[$i], $this->mask['Character'] );
					if ( $mask ) {
						if ( $this->alts[$charID]['accessMask'] & $mask )
							$res = true;
						else {
							$res = false;
							break;
						}
					} else {
						$res = false;
						break;
					}
				}
			}
			return $res;
		}
		else return false;
	}
	
	public function setLang($lang='DE') {
		if ($this->charID !== null) {
			$this->userData['lang'] = $lang;
			return $this->db->exec_query("UPDATE {$this->_table['fsrtool_user']} SET lang='{$lang}' WHERE charID='{$this->charID}';");
		}
		return false;
	}
	
}

?>