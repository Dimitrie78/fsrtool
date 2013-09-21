<?php



function getLocationOfRun($id) {

	// We need the database access.
	global $DB;

	// Is the ID a number and greater (or euqal) zero?
	numericCheck($id, 0);
//	if (!numericCheck($id, 0)) {
//		makeNotice("Internal Error: getLocationOfRun called with negative ID.", "error", "Internal Error");
//	}

	// Compact: Query, sort and return.
	$res = $DB->query("SELECT location FROM runs WHERE id = '$id'");
	$loc = $res->fetch_row();
	$res->close();
	return ($loc[0]);

}
?>