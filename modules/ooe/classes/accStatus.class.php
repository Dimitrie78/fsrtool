<?php
class accStatus extends ooeWorld {
	
	private $ale;
	
	public function __construct( $User, $ale ) {
		if ( !$this->db ) parent::__construct( $User );
		
		$this->ale = $ale;
		$this->ale->setConfig('serverError', 'returnParsed');
		$this->ale->setConfig('parserClass', 'AleParserXMLElement');
		
		if ( isset($_POST['cid']) && !empty($_POST['cid']) && $_POST['cid'] != $this->User->charID ){
			$id = $_POST['cid'];
			$this->ale->setKey( $this->User->alts[$id]['userID'], 
										$this->User->alts[$id]['userAPI'], 
										$this->User->alts[$id]['charID'] 
										);
		} else {
			$this->ale->setKey( $this->User->keyID, $this->User->vCODE, $this->User->charID );
		}
		
	}
	
	public function getStatus() {
		$status = array();
		try {
			$accStatus = $this->ale->account->AccountStatus();
			if ( !$accStatus->error ) {
				foreach ($accStatus->result->toArray() as $var => $val) {
					if (!is_array($val) && !is_object($val))
						if ($var == 'paidUntil' || $var == 'createDate')
							$status[$var] = strtotime($val);
						else $status[$var] = trim($val);
				}
			}
		} catch (Exception $e) {
			return $e->getMessage();
		}
		return $status;
	}

}
?>