<?php

function showChar($char) {
	global $world;
	if ( !is_numeric($char) ) {
		$char = $world->db->fetch_one("SELECT charID FROM ".$world->_table['snow_characters']." WHERE name = '".addslashes($char)."';", 'charID');
	}
	$charID = addslashes($char);
	
	$charID = $world->getMainCharID( $charID );
	
	$query = "SELECT name, charID, lastSeen, inactive, joined
		FROM ".$world->_table['snow_characters']." WHERE charID = '".$charID."'";
	$result = $world->db->query( $query );
	$char = $result->fetch_assoc();
	
	$query2 = "SELECT distinct c.charID, c.name, c.lastSeen
		FROM ".$world->_table['snow_characters']." c
		JOIN ".$world->_table['snow_alts']." a ON a.charID = c.charID
		WHERE a.altOf = '{$charID}'
		AND c.inCorp = 1
		ORDER BY c.name";
	
	$return = array();
	$return['char'] = $char;
	
	$altresult = $world->db->query( $query2 );
	while( $alts = $altresult->fetch_assoc() ) {
		$return['alts'][] = $alts;
	}
	
	return $return;
}

function getName() {
	global $world;
	//$charID = 1240402153;
	$charID = addslashes($_GET["id"]);
	if( $name = $world->db->fetch_one("SELECT name FROM ".$world->_table['snow_characters']." WHERE charID = '".$charID."'", 'name') )
		echo( $name );
}

?>
