<?php

class User
{
	public $charID;
	public $keyID;
	public $vCODE;
	
	public function __construct($db = null) {
		if($db !== null) {
			$this->db = $db;
			$this->_table = $this->db->_table;
		}
	}
}

?>