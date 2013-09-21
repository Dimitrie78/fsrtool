<?php


/*
* getTotalHaulRuns($run)
* Returns an int with the total number of hauling runs for
* the specified mining operation.
*/
function getTotalHaulRuns($run) {
	global $DB;

	// Is $run truly an integer?
	numericCheck($run);

	// Query the oracle.
	$result = $DB->query("select * from hauled where miningrun = '$run'");

	// Now return the results.
	return ($result->num_rows);
}
?>