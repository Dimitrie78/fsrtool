<?php
defined('FSR_BASE') or die('Restricted access');

class DBManager {
	public $numQueries = 0;
	public $conn = false;
	
	private $msg;
	
	
	public function DBManager($dbhost, $dbuname, $dbpass, $dbname, $Messages = null, $eve = false) {
		if (!$this->conn) {
			$db = new mysqli( $dbhost, $dbuname, $dbpass, $dbname );
			if ($db->connect_error && $eve === false) {
				$Messages->showerror("SQL-Error Verbindung zum Server nicht erfolgreich!");
				return false;
			}
			else if ($db->connect_error) {
				$this->error = 'Connect Error (' . $db->connect_errno . ') ' . $db->connect_error;
				return false;
			}
			$this->conn = $db;
			$this->conn->query("SET NAMES 'utf8'");
		}
		return true;
	}

}

?>