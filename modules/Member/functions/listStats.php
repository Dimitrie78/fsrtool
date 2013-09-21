<?php

function listDread() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.dread > 0
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query( $query );
	$numDreads = $results->num_rows;
	
	//echo "There are $numDreads dreadnoughts.<br><br>";
	$smarty->assign('numDreads', $numDreads);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listCarrier() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.carrier
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.carrier > 0
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query( $query );
	$numCarriers = $results->num_rows;
	
	//echo "There are $numCarriers carriers.<br><br>";
	$smarty->assign('numCarriers', $numCarriers);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listPOSGunners() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.posgunner
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.posgunner = 1
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query( $query );
	$numPOS = $results->num_rows;
	
	//echo "There are $numPOS POS gunners.<br><br>";
	$smarty->assign('numPOS', $numPOS);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listTZEuro() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.name, c.lastSeen, c.inactive, c.carrier
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.tz = 1
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numTZEuro = $results->num_rows;
	
	//echo "There are $numTZEuro European TZ players.<br><br>";
	$smarty->assign('numTZEuro', $numTZEuro);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listTZAmerican() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.name, c.lastSeen, c.inactive, c.carrier
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.tz = 2
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numTZAmericas = $results->num_rows;
	
	//echo "There are $numTZAmericas American TZ players.<br><br>";
	$smarty->assign('numTZAmericas', $numTZAmericas);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listTZOceanic() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.name, c.lastSeen, c.inactive, c.carrier
		FROM ".db_snow_characters." c
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.tz = 3
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numTZOceanic = $results->num_rows;
	
	//echo "There are $numTZOceanic Oceanic TZ players.<br><br>";
	$smarty->assign('numTZOceanic', $numTZOceanic);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

?>