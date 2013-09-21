<?php


/*
* miningRunOpen($run)
* Returns true is the mining run is still open, else false.
*/
function miningRunOpen($run) {
	global $DB;

	// Is $run truly an integer?
	numericCheck($run);

	// Query the oracle.
	$result = $DB->query("select id from runs where endtime is NULL and id = '$run' limit 1");

	if ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}
?>