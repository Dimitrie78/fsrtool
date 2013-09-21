<?php
function listMembers() {
	global $smarty;
	global $User;
	
	$query = "SELECT c.charID
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE a.charID IS NOT NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'";
	$results = $User->db->query( $query );
	$numAlt = $results->num_rows;
	
	$query = "SELECT c.charID
		FROM ".db_snow_characters." c
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.inactive = 1";
	$results = $User->db->query( $query );
	$numInactive = $results->num_rows;
	
	$time30daysago = (time() - date('z')) - 60*60*24*30;
	$query = "SELECT c.charID
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.joined > {$time30daysago}
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numNew = $results->num_rows;
	
	$time3daysago = (time() - date('z')) - 60*60*24*3;
	$query = "SELECT c.charID
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		AND c.lastSeen > {$time3daysago}
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numRecent = $results->num_rows;

	$query = "SELECT distinct c.charID, c.lastSeen, c.inactive, c.joined
		FROM ".db_snow_characters." c 
		LEFT JOIN ".db_snow_alts." a ON c.charID = a.charID
		WHERE a.charID IS NULL
		AND c.inCorp = 1
		AND c.corpID = '{$User->corpID}'
		ORDER BY c.name";
	$results = $User->db->query( $query );
	$numMain = $results->num_rows;
	
	$smarty->assign('numMain', 		$numMain);
	$smarty->assign('numAlt', 		$numAlt);
	$smarty->assign('numRecent', 	$numRecent);
	$smarty->assign('numNew', 		$numNew);
	$smarty->assign('numInactive',  $numInactive);
	
	$return = array();
	while( $char = $results->fetch_array() ) {
		$return[] = $char;
	}
	
	return $return;
}
?>