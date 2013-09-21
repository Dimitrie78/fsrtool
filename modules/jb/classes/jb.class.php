<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class jb {
	
	private $_corpID = null;
	private $_db = null;
	private $_table = null;
	
	private $_table_api = 'fsrtool_jb_apis';
	private $_table_corp = 'fsrtool_corps';
	
	public function __construct(World $world) {
		$this->_corpID = $world->User->corpID;//$corpID;
		$this->_db = $world->db;
		// echo '<pre>'; print_r($this); echo '</pre>';die;
	}
	
	public function saveApi(array $data) {
		$data = $this->_db->escape($data);
		
		$res = $this->_db->exec_query("INSERT INTO {$this->_table_api} (charID,ownerID,charName,corpID,keyID,vCode,accessMask,status) 
			VALUES ({$data['obj']['charid']},
					{$this->_corpID},
					'{$data['obj']['result']['key']['characters'][$data['obj']['charid']]['characterName']}',
					{$data['obj']['result']['key']['characters'][$data['obj']['charid']]['corporationID']},
					{$data['obj']['keyid']},
					'{$data['obj']['vcode']}',
					{$data['obj']['result']['key']['accessMask']},1)");
		
		
		return array();
	}
	
	public function getApis() {
		return $this->_db->fetch_all("SELECT a.*, c.name AS corpName FROM {$this->_table_api} AS a 
			LEFT JOIN {$this->_table_corp} AS c ON a.corpID = c.id
			ORDER BY a.charName");
	}
}
?>