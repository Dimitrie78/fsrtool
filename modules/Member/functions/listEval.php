<?php

function listEvalPvP() {
	global $smarty;
	global $User;
	
	$query = "SELECT a.charID as aID, c.charID, c.name, c.lastSeen, c.inactive, c.dread
		FROM ".db_snow_characters." c LEFT JOIN ".db_snow_alts." a
		ON c.charID = a.charID
		WHERE c.inCorp = 1
		AND c.division = 1
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
		//WHERE a.charID IS NULL
	$results = $User->db->query( $query );
	$numPvP = $results->num_rows;
	
	$smarty->assign('numPvP', $numPvP);
	$i=0;
	$return = array();
	while( $char = $results->fetch_assoc() ) {
		$return[$i]['char'] = $char;
		$eva = $User->db->query("SELECT evaluation, comment, date FROM ".db_snow_evaluation." WHERE charID = '".$char['charID']."' ORDER BY date DESC LIMIT 6;");
		while( $row = $eva->fetch_assoc() ) {
			$return[$i]['eval'][] = $row;
		}
		$i++;
	}
	
	return $return;
}

function listEvalMining() {
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
	$results = $User->db->query( $query );
	$numMining = $results->num_rows;
	
	$smarty->assign('numMining', $numMining);
	$i=0;
	$return = array();
	while( $char = $results->fetch_assoc() ) {
		$return[$i]['char'] = $char;
		$eva = $User->db->query("SELECT evaluation, comment, date FROM ".db_snow_evaluation." WHERE charID = '".$char['charID']."' ORDER BY date DESC LIMIT 6;");
		while( $row = $eva->fetch_assoc() ) {
			$return[$i]['eval'][] = $row;
		}
		$i++;
	}
	
	return $return;
}

function listEvalPOS() {
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
	$results = $User->db->query( $query );
	$numPOS = $results->num_rows;
	
	$smarty->assign('numPOS', $numPOS);
	$i=0;
	$return = array();
	while( $char = $results->fetch_assoc() ) {
		$return[$i]['char'] = $char;
		$eva = $User->db->query("SELECT evaluation, comment, date FROM ".db_snow_evaluation." WHERE charID = '".$char['charID']."' ORDER BY date DESC LIMIT 6;");
		while( $row = $eva->fetch_assoc() ) {
			$return[$i]['eval'][] = $row;
		}
		$i++;
	}
	
	return $return;
}

function listEvalSupport() {
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
	$results = $User->db->query( $query );
	$numSupport = $results->num_rows;
	
	$smarty->assign('numSupport', $numSupport);
	$i=0;
	$return = array();
	while( $char = $results->fetch_assoc() ) {
		$return[$i]['char'] = $char;
		$eva = $User->db->query("SELECT evaluation, comment, date FROM ".db_snow_evaluation." WHERE charID = '".$char['charID']."' ORDER BY date DESC LIMIT 6;");
		while( $row = $eva->fetch_assoc() ) {
			$return[$i]['eval'][] = $row;
		}
		$i++;
	}
	
	return $return;
}

function listEvalNone() {
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
	$results = $User->db->query( $query );
	$numnone = $results->num_rows;
	
	$smarty->assign('numnone', $numnone);
	$i=0;
	$return = array();
	while( $char = $results->fetch_assoc() ) {
		$return[$i]['char'] = $char;
		$eva = $User->db->query("SELECT evaluation, comment, date FROM ".db_snow_evaluation." WHERE charID = '".$char['charID']."' ORDER BY date DESC LIMIT 6;");
		while( $row = $eva->fetch_assoc() ) {
			$return[$i]['eval'][] = $row;
		}
		$i++;
	}
	
	return $return;
}

?>