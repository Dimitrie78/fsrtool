<?php

defined('FSR_BASE') or die('Restricted access');


class cronException extends RuntimeException {
	private $_charID;

	function __construct($message, $code, $charID) {
		parent::__construct($message, $code);
		$this->_charID = $charID;
	}

	function getCharID() {
		return $this->_charID;
	}

}

?>