<?php

function IDtoIcon($id) {
	global $database;
	
	$sql = "SELECT g.iconFile as icon
			FROM ".db_tab_invtypes." i
			INNER JOIN ".db_tab_eveicons." g ON i.iconID = g.iconID
			WHERE i.typeID = '$id' ";
	$result = $database->doQuery($sql, 'functions::IDtoIcon');
	$row = $database->fetch_object($result);
	$database->free_result($result);
	
	return $row->icon;
}
?>