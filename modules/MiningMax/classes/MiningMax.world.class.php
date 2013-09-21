<?php
defined('fsr_tool') or die;

class MiningMaxWorld extends World
{
	public function __construct( $User ) {
		if ( !$this->db ) parent::__construct( $User );
		#echo '<pre>'; print_r( $this ); die;
	}
	
	function minerals_get_mineralprices()
	{
		$sqlstring = "SELECT * FROM ".db_tab_mineralprices.";";
		$result = $this->db->doQuery($sqlstring,"Database::minerals_get_mineralprices");
		return $result;
	}

	
	function minerals_update_mineralprices($price,$name)
	{
		$price = $this->db->escape($price);
		$name  = $this->db->escape($name);
		$date  = date('Y-m-d');
		
		$sqlstring = "UPDATE ".db_tab_mineralprices." 
					  SET Price = '".$price."', ChangeDate='".$date."' WHERE Name='".$name."';";
		$result = $this->db->doQuery($sqlstring,"Database::minerals_update_mineralprices");
		return $result;
	}
	
	
	function minerals_get_mineralprices_new($num_highest = 1)
	{
	  $typeID_sql = "SELECT `typeID` FROM stsys_eveorder_mineralprices_history GROUP BY `typeID`;";
	  $typeID_res = $this->db->doQuery($typeID_sql,"Database::minerals_get_mineralprices - 1");
	  while($typeID_row = $this->db->fetch_assoc($typeID_res)){
      $lastEntries_sql = "SELECT `typeID`,`timestamp` FROM stsys_eveorder_mineralprices_history  WHERE `typeID` = ".$typeID_row['typeID']." ORDER BY `timestamp` DESC LIMIT ".($num_highest-1).",".($num_highest).";";
  		#$lastEntries_sql = "SELECT `typeID`,max(`timestamp`) as timestamp FROM stsys_eveorder_mineralprices_history GROUP BY `typeID`;";
  		$lastEntries_res = $this->db->doQuery($lastEntries_sql,"Database::minerals_get_mineralprices");
  		while($lastEntries_row = $this->db->fetch_assoc($lastEntries_res)){
  		  $lastPrice_sql = "SELECT it.`typeName`,mh.`price` FROM stsys_eveorder_mineralprices_history as mh LEFT JOIN invtypes as it ON mh.`typeID` = it.`typeID` WHERE mh.`typeID` = ".$lastEntries_row['typeID']." AND mh.`timestamp` = '".$lastEntries_row['timestamp']."' LIMIT 1";
  		  $lastPrice_res = $this->db->doQuery($lastPrice_sql,"Database::minerals_get_mineralprices");
  		  $lastPrice_row = $this->db->fetch_assoc($lastPrice_res);
  		  $lastPrice[$lastEntries_row['typeID']] = array('Name' => $lastPrice_row['typeName'],'Price' => $lastPrice_row['price'],'Date' => $lastEntries_row['timestamp'],'icon' => IDtoIcon($lastEntries_row['typeID']));
      }
    }		
		return $lastPrice;
	}
	
	function minerals_update_mineralprices_new($price,$name)
	{
		$price = $this->db->escape($price);
		$name  = $this->db->escape($name);
		
		$sqlstring = "INSERT stsys_eveorder_mineralprices_history (`typeID`,`price`,`timestamp`) 
                  VALUES (
                    (SELECT `typeID` FROM invtypes WHERE `typeName` = '".$name."' LIMIT 1),
                    ".$price.",
                    UNIX_TIMESTAMP()
                  ) ";
		$result = $this->db->doQuery($sqlstring,"Database::minerals_update_mineralprices");
		return $result;
	}
}
?>