<?php

function listPvP() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 1
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query($query);
	$numPvP = $results->num_rows;
	
	$smarty->assign('numPvP', $numPvP);
	//echo "There are $numPvP PvP member.<br><br>";
	
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listMining() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 2
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query($query);
	$numMining = $results->num_rows;
	
	//echo "There are $numMining Mining member.<br><br>";
	$smarty->assign('numMining', $numMining);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listPOS() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 3
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query($query);
	$numPOS = $results->num_rows;
	
	//echo "There are $numPOS POS member.<br><br>";
	$smarty->assign('numPOS', $numPOS);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listSupport() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 4
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query($query);
	$numSupport = $results->num_rows;
	
	//echo "There are $numSupport Support member.<br><br>";
	$smarty->assign('numSupport', $numSupport);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listHC() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 5
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query($query);
	$numHC = $results->num_rows;
	
	//echo "There are $numHC High Command member.<br><br>";
	$smarty->assign('numHC', $numHC);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listLeader() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 6
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query($query);
	$numLeader = $results->num_rows;
	
	//echo "There are $numLeader Leader.<br><br>";
	$smarty->assign('numLeader', $numLeader);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listFSRUhrgestein() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 7
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query($query);
	$numFSRUhr = $results->num_rows;
	
	//echo "There are $numLeader Leader.<br><br>";
	$smarty->assign('numFSRUhr', $numFSRUhr);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listnone() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 0
		AND c.corpID = '{$User->corpID}'
		AND a.charID IS NULL
		ORDER BY c.name";
	$results = $User->db->query($query);
	$numnone = $results->num_rows;
	
	//echo "There are $numnone Mains without an division.<br><br>";
	$smarty->assign('numnone', $numnone);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

?>