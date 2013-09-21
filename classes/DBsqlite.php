<?php

class DBSqlite {
	public static $numQueries = 0;
	private static $conn = null;
	
	private static $msg;
	public static $error;
	
	private static $_qry;
	
	public function __construct() {
		if (!self::$conn) {
			self::init();
		}
	}
	
	public static function init() {
		if( ! self::$msg ) self::$msg = new Messages();
		if (!self::$conn) {
			if(!self::connect()) {
				self::$msg->showerror("SQL-Error Verbindung zum Server nicht erfolgreich! <br/>" . self::$error);
			}
			
		}
	}
	
	public static function query( $query ) {
		if( self::$_qry = self::$conn->query( $query ) ) {
			self::$numQueries ++;
			return true;
		} else {
			if (is_object(self::$msg))
				self::$msg->showerror(print_r(self::$conn->errorInfo(), true));
			self::$error = print_r(self::$conn->errorInfo(), true);
		}
		return false;
	}
	
	public static function fetch() {
		if (is_object(self::$_qry))
			return self::$_qry->fetch(PDO::FETCH_ASSOC);
		return false;
	}
	
	public static function fetch_all() {
		if (is_object(self::$_qry))
			return self::$_qry->fetchAll(PDO::FETCH_ASSOC);
		return false;
	}
	
	public static function fetch_one( $column=null ) {
		if( is_object(self::$_qry) ) {
			$res = self::$_qry->fetch(PDO::FETCH_ASSOC);
			if( $res[$column] )
				return $res[$column];
			else return $res;
		}
		return false;
	}
	
	private static function connect() {
		try{
			self::$conn = new PDO("sqlite:evedb/cru110-sqlite3-v1.db");
		} catch (PDOException $e) {
			self::$error = "Error!: " . $e->getMessage();
			return false;
		}
		return true;
	}
	
	public function __destruct() {
		if (self::$conn)
			self::$conn = null;
	}
	
}

?>