<?php
defined('FSR_BASE') or die('Restricted access');

class DBManager {
	public $numQueries = 0;
	public $conn = false;
	public $error = false;
	
	private $msg;
	
	
	public function DBManager($dbhost, $dbuname, $dbpass, $dbname, &$Messages = null, $eve = false) {
		if (!$this->conn) {
			$db = new mysqli( $dbhost, $dbuname, $dbpass, $dbname );
		
			if ($db->connect_error && $eve === false) {
				$Messages->showerror("SQL-Error Verbindung zum Server nicht erfolgreich!");
			}
			else if ($db->connect_error) {
				$this->error = 'Connect Error (' . $db->connect_errno . ') ' . $db->connect_error;
				return false;
			}
			$this->msg = &$Messages;
			$this->conn = $db;
			$this->conn->query("SET NAMES 'utf8'");
		}
		return true;
	}
	
	public function query($str) {
		$this->error = false;
		if ($res = $this->conn->query($str))
			return $res;
		
		$this->error = $this->conn->error;
		
		return false;
	}
	
	
}

?>