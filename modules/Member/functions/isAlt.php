<?php

function addAlt($charID, $charName) {
	global $world;
	$charName = $world->db->escape($charName);
	if( $altOf = $world->db->fetch_one("SELECT charID FROM ".$world->_table['snow_characters']." WHERE name = '{$charName}'", 'charID') ) {
		$query = "INSERT INTO ".$world->_table['snow_alts']." (charID, altOf) VALUES ($charID, $altOf) ON DUPLICATE KEY UPDATE altOf = $altOf";
		$result = $world->db->exec_query( $query );
		return $result;
	} else
		return false;
}

function delAlt($charID, $altOf) {
	global $world;
	$query = "DELETE FROM ".$world->_table['snow_alts']." WHERE charID = '{$charID}' AND altOf = '{$altOf}'";
	$result = $world->db->exec_query( $query );
	
	return ( $result );
}

?>
