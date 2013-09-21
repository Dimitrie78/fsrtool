<?php

function listInactive() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.name, c.lastSeen, c.inactive
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.inactive = 1
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numInactive = $results->num_rows;
	
	//echo "There are $numInactive mains inactive.<br><br>";
	$smarty->assign('numInactive', $numInactive);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listAltNoMain() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.lastSeen, c.name, c.inactive,
		mains.charID AS mainID, mains.name as mainName
		FROM ".db_snow_characters." c
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		LEFT JOIN ".db_snow_characters." AS mains ON a.altOf = mains.charID
		WHERE a.charID IS NOT NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND mains.inCorp = 0";
	$results = $User->db->query( $query );
	$numAltNoMain = $results->num_rows;
	
	//echo "There are $numAltNoMain alts a in corp without mains.<br><br>";
	$smarty->assign('numAltNoMain', $numAltNoMain);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listAFK() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.name, c.lastSeen, c.inactive
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.afk = 1
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numAFK = $results->num_rows;
	
	//echo "There are $numAFK mains AFK.<br><br>";
	$smarty->assign('numAFK', $numAFK);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listNotes() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.name, c.lastSeen, c.inactive
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.notes != ''
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numNotes = $results->num_rows;
	
	//echo "There are $numNotes notes.<br><br>";
	$smarty->assign('numNotes', $numNotes);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listInvestigate() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.name, c.lastSeen, c.inactive
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.investigate = 1
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numInvestigate = $results->num_rows;
	
	//echo "There are $numInvestigate under investigation.<br><br>";
	$smarty->assign('numInvestigate', $numInvestigate);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

function listProbation() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID, c.name, c.lastSeen, c.inactive
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_jobs." j
		ON c.charID = j.charID
		WHERE c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND j.probation = 1
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numProbate = $results->num_rows;
	
	//echo "There are $numProbate member(s) on probation.<br><br>";
	$smarty->assign('numProbate', $numProbate);
		
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}

?>