<?php
function listNews() {
	global $User;
	
	$return = array();
	$time30daysago = eveTime() - (60*60*24*30);
	
	$query = "SELECT n.dateTime, n.type, c.charID, c.name 
		FROM ".db_snow_news." n, ".db_snow_characters." c
		WHERE n.charID = c.charID
		AND n.dateTime > {$time30daysago}
		AND c.corpID = '{$User->corpID}'
		ORDER BY dateTime DESC";
	
	$news = $User->db->fetch_all( $query ); 
	
	return $news;
}
?>