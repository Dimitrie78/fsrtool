<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class ProductionsWorld extends world {
	
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		#echo '<pre>'; print_r( $this ); die;
	}
	
}

?>