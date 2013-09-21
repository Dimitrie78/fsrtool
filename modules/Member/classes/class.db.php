<?php

class DBManager {
	
	private $db = null;
	
	function DBManager($dbhost, $dbuser, $dbpasswd, $dbname) {
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

}
?>