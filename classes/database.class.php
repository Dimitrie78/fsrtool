<?php
defined('FSR_BASE') or die('Restricted access');

class Database extends mysqli
{
	public $numQueries = 0;
	public $_table = array();
	
	public $queries = array();
	public $queries_time = 0;
	
	private $debug = 0;
	
	public $msg;
	
	public function __construct( &$Messages=null ) {
		$c = Settings::getInstance(CONFIG);
		parent::__construct($c->dbhost, $c->dbuname, $c->dbpass, $c->dbname);
		
		if($this->connect_error && $Messages !== null) {
			$Messages->showerror("SQL-Error Verbindung zum Server nicht erfolgreich!");
			return false;
		}
		else if($this->connect_error) {
			$this->error = 'Connect Error (' . $this->connect_errno . ') ' . $this->connect_error;
			return false;
		}
		
		parent::query("SET NAMES 'utf8'");
		$this->msg = &$Messages;
		$this->loadTableNames();
		
	}
	
	private function loadTableNames() {
		$this->_table = array(
			'agtagents' 						=> TBL_EVEDB.'agtagents',
			'chrraces' 							=> TBL_EVEDB.'chrraces',
			'dgmtypeattributes' 				=> TBL_EVEDB.'dgmtypeattributes',
			'dgmtypeeffects' 					=> TBL_EVEDB.'dgmtypeeffects',
			'eveicons' 							=> TBL_EVEDB.'eveicons',
			'invblueprinttypes' 				=> TBL_EVEDB.'invblueprinttypes',
			'invcategories' 					=> TBL_EVEDB.'invcategories',
			'invcontroltowerresourcepurposes' 	=> TBL_EVEDB.'invcontroltowerresourcepurposes',
			'invcontroltowerresources' 			=> TBL_EVEDB.'invcontroltowerresources',
			'invflags' 							=> TBL_EVEDB.'invflags',
			'invgroups' 						=> TBL_EVEDB.'invgroups',
			'invmarketgroups' 					=> TBL_EVEDB.'invmarketgroups',
			'invmetatypes' 						=> TBL_EVEDB.'invmetatypes',
			'invtypes' 							=> TBL_EVEDB.'invtypes',
			'invtypereactions'					=> TBL_EVEDB.'invtypereactions',
			'mapconstellations' 				=> TBL_EVEDB.'mapconstellations',
			'mapdenormalize' 					=> TBL_EVEDB.'mapdenormalize',
			'mapregions' 						=> TBL_EVEDB.'mapregions',
			'mapsolarsystems' 					=> TBL_EVEDB.'mapsolarsystems',
			'stastations' 						=> TBL_EVEDB.'stastations',
			'trntranslations' 					=> TBL_EVEDB.'trntranslations',
			
			'fsrtool_user'						=> TBL_PREFIX.'user',
			'fsrtool_user_online'	 			=> TBL_PREFIX.'user_online',
			'fsrtool_user_fullapi'	 			=> TBL_PREFIX.'user_fullapi',
			'fsrtool_user_iplog'	 			=> TBL_PREFIX.'user_iplog',
			'fsrtool_user_notifications'		=> TBL_PREFIX.'user_notifications',
			'fsrtool_alts'			 			=> TBL_PREFIX.'user_alts',
			'fsrtool_user_roles'	 			=> TBL_PREFIX.'user_roles',
			'fsrtool_allys'			 			=> TBL_PREFIX.'allys',
			'fsrtool_corps'			 			=> TBL_PREFIX.'corps',
			'fsrtool_corpchange'	 			=> TBL_PREFIX.'corpchange',
			'fsrtool_currentTypePrice'			=> TBL_PREFIX.'currenttypeprice',
			'fsrtool_logins'		 			=> TBL_PREFIX.'logins',
			'fsrtool_roles'			 			=> TBL_PREFIX.'roles',
			'fsrtool_cron'			 			=> TBL_PREFIX.'cron',
			
			'fsrtool_api_outposts'			 	=> TBL_PREFIX.'api_outposts',
			'fsrtool_api_reftypes'			 	=> TBL_PREFIX.'api_reftypes',
			'fsrtool_api_sovereignty'		 	=> TBL_PREFIX.'api_sovereignty',
			'fsrtool_api_calls'				 	=> TBL_PREFIX.'api_calls',
			'fsrtool_api_callgroups'		 	=> TBL_PREFIX.'api_callgroups',
			'fsrtool_log'		 				=> TBL_PREFIX.'api_log',
			
			'fsrtool_skills'		 			=> TBL_PREFIX.'skills',
			'fsrtool_ship_skills'		 		=> TBL_PREFIX.'ship_skills',
			'fsrtool_ships' 					=> TBL_PREFIX.'ships',
			'fsrtool_ships_player' 				=> TBL_PREFIX.'ships_player',
			'fsrtool_ships_orte' 				=> TBL_PREFIX.'ships_orte',
			'fsrtool_ships_log' 				=> TBL_PREFIX.'ships_log',
			
			'fsrtool_pos' 						=> TBL_PREFIX.'pos',
			'fsrtool_pos_fuel' 					=> TBL_PREFIX.'pos_fuel',
			'fsrtool_pos_corphanger' 			=> TBL_PREFIX.'pos_corphanger',
			'fsrtool_pos_maillist' 				=> TBL_PREFIX.'pos_maillist',
			
			'fsrtool_silos' 					=> TBL_PREFIX.'silos',
			'fsrtool_silos_cachetimes' 			=> TBL_PREFIX.'silos_cachetimes',
			'fsrtool_silos_reactors' 			=> TBL_PREFIX.'silos_reactors',
			'fsrtool_assets' 					=> TBL_PREFIX.'assets',
			'fsrtool_assets_contents' 			=> TBL_PREFIX.'assets_contents',
			
			'snow_afk_time' 					=> TBL_PREFIX.'snow_afk_time',
			'snow_alts' 						=> TBL_PREFIX.'snow_alts',
			'snow_characters' 					=> TBL_PREFIX.'snow_characters',
			'snow_corptax' 						=> TBL_PREFIX.'snow_corptax',
			'snow_evaluation' 					=> TBL_PREFIX.'snow_evaluation',
			'snow_jobs' 						=> TBL_PREFIX.'snow_jobs',
			'snow_kills' 						=> TBL_PREFIX.'snow_kills',
			'snow_news' 						=> TBL_PREFIX.'snow_news',
			'snow_ratkills' 					=> TBL_PREFIX.'snow_ratkills',
			'snow_rats_bountys' 				=> TBL_PREFIX.'snow_rats_bountys',
			'snow_rats_imgs' 					=> TBL_PREFIX.'snow_rats_imgs',
			'snow_run'		 					=> TBL_PREFIX.'snow_run',
			'snow_tempchars' 					=> TBL_PREFIX.'snow_tempchars',
			'snow_time' 						=> TBL_PREFIX.'snow_time',
			'snow_wallet' 						=> TBL_PREFIX.'snow_wallet',
			
		);	
	}
	
	public function query($query, $err=true){
		$start = microtime(true);
		$error_message='';
		if(!$result = parent::query($query)){
			$error_message = $this->error;
			if( $this->error && $err && $this->msg !== null)
				$this->msg->showerror( $this->my_error( sprintf("Errormessage: %s\n", $this->error) ) );
		}
		
		$end = microtime(true);
		
		$this->queries[] = array(
			'number' => count($this->queries),
			'query' => $query,
			'error' => $error_message,
			'time' => $end - $start
		);
		
		$this->queries_time += $end - $start;

		return $result;
	}
	
	public function fetch_one($query, $column=null) {
		$start = microtime(true);
		$error_message='';
		$ret = false;
		if(!$result = parent::query($query)){
			$error_message = $this->error;
			if( $this->error && $this->msg !== null)
				$this->msg->showerror( $this->my_error( sprintf("Errormessage: %s\n", $this->error) ) );
		}
		else {
			if ( $row2 = $result->fetch_assoc() ) {
				if( isset($row2[$column]) ) {
					$ret = $row2[$column];
				} else {
					$ret = $row2;
				}
			}
			$result->close();
		}
		
		$end = microtime(true);
		
		$this->queries[] = array(
			'number' => count($this->queries),
			'query' => $query,
			'error' => $error_message,
			'time' => $end - $start
		);
		
		$this->queries_time += $end - $start;

		return $ret;
	}

	public function exec_query($query) {
		$start = microtime(true);
		$error_message='';
		if(!$result = parent::query($query)){
			$error_message = $this->error;
			if( $this->error && $this->msg !== null)
				$this->msg->showerror( $this->my_error( sprintf("Errormessage: %s\n", $this->error) ) );
		}
		
		$end = microtime(true);
		
		$this->queries[] = array(
			'number' => count($this->queries),
			'query' => $query,
			'error' => $error_message,
			'time' => $end - $start
		);
		
		$this->queries_time += $end - $start;

		return $this->affected_rows;
	}

	public function fetch_all($query, $column=null) {
		$start = microtime(true);
		$error_message='';
		$ret=array();
		if(!$result = parent::query($query)){
			$error_message = $this->error;
			if( $this->error && $this->msg !== null)
				$this->msg->showerror( $this->my_error( sprintf("Errormessage: %s\n", $this->error) ) );
		}
		else {
			while ( $row2 = $result->fetch_assoc() ) {
				if ( isset($row2[ $column ]) )
					$ret[] = $row2[ $column ];
				else $ret[] = $row2;
			}
			$result->close();
		}
		
		$end = microtime(true);
		
		$this->queries[] = array(
			'number' => count($this->queries),
			'query' => $query,
			'error' => $error_message,
			'time' => $end - $start
		);
		
		$this->queries_time += $end - $start;
		
		return $ret;
	}
	
	public function prepare($query){
		$start = microtime(true);
		$error_message = '';
		
		if(!$result = parent::prepare($query)){
			$error_message = $this->error;
			if( $this->error && $this->msg !== null)
				$this->msg->showerror( $this->my_error( sprintf("Errormessage: %s\n", $this->error) ) );
		}
		
		$end = microtime(true);
		
		$this->queries[] = array(
			'number' => count($this->queries),
			'query' => $query,
			'error' => $error_message,
			'time' => $end - $start
		);
		
		$this->queries_time += $end - $start;

		return $result;
	}
	
	public function fetch($result) {   
		$array = array();
		#parent::_querys++;
		if($result instanceof mysqli_stmt) {
			$args = func_get_args();
			#unset($args[0]);
			
			// Feldtypen ermitteln
			$type = '';
			//$values = array_values($data);
			for($i = 1; $i < count($args); $i++) {
				if (is_int($args[$i])) {
					$type .= 'i';
				} else if (is_double($args[$i])) {
					$type .= 'd';
				} else {
					$type .= 's';
				}
			}
			$args[0] = $type;
			
			#print_r($args); die;
			call_user_func_array(array($result, 'bind_param'), $this->refValues($args));
			#$result->bind_param(implode($args, ',')); 
			$result->execute();
			$result->store_result();
			
			$variables = array();
			$data = array();
			$meta = $result->result_metadata();
			
			while($field = $meta->fetch_field())
				$variables[] = &$data[$field->name]; // pass by reference
			
			call_user_func_array(array($result, 'bind_result'), $variables);
			
			$i=0;
			while($result->fetch()) {
				$array[$i] = array();
				foreach($data as $k=>$v)
					$array[$i][$k] = $v;
				$i++;
			}
		}
		elseif($result instanceof mysqli_result) {
			while($row = $result->fetch_assoc())
				$array[] = $row;
		}
		if(is_resource($result)) $result->close();
		
		return $array;
	}
	
	public function refValues($arr){
		if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
		{
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	} 
	
	function escape($string) {
		if ( is_array ( $string ) ) {
			foreach( $string as $key => $value ){
				if ( is_array ( $value ) )
					$new_arr [ $key ] = $this->escape($value);
				else
					$new_arr [ $key ] = get_magic_quotes_gpc()?$this->real_escape_string( stripslashes($value) ):$this->real_escape_string( $value );
			}
			return $new_arr;
		} else {
			$string = get_magic_quotes_gpc()?$this->real_escape_string( stripslashes($string) ):$this->real_escape_string( $string );
			return $string;
		}
	}

	function setDebug($bool) {
		if ($bool) $this->debug = 1;
		else	   $this->debug = 0;
	}
	
	private function add_query() {
		if ( !$this->query( "SET NAMES 'utf8';" ) ) 
			$this->msg->addwarning( "mysql_query SET NAMES 'utf8'". $this->error );
		if ( !$this->query( "SET CHARACTER SET 'utf8';" ) )
			$this->msg->addwarning( "mysql_query SET CHARACTER SET 'utf8'". $this->error );
	}
	
	public function my_error( $message ) {
		$callee = next(debug_backtrace());
		$msg  = $message.'<br />in <strong>'.$callee['file'].'</strong> on line <strong>'.$callee['line'].'</strong><br />';
		//$msg .= $callee['function'].', [class] => '.$callee['class'];
		return $msg;
	}
	
	public function __destruct(){
		parent::close();
    }

}

?>