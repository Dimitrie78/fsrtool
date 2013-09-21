<?php

/*
 * This function checks wether the user currently takes part in the
 * mining run. Required: Username and RunID.
 */

function userInRun($username, $run = "check") {
// echo "Userinrun".$username;

	// Get / Set important variables.
	global $DB;

	// If username is given, convert to ID.
	if (!is_numeric($username)) {
		$userID = usernameToID($username, "userInRun");
	} else {
		$userID = $username;
	}

	// Is $run truly an integer?
	if ($run != "check") {
		// We want to know wether user is in run X.
		numericCheck($run);
	} else {
		// We want to know if user is in any run, and if so, in which one.
		$res = $DB->query("select run from joinups where userid = '$userID' and parted is NULL limit 1");
		$results = $res->fetch_row();
		$res->close();

		// Return false if in no run, else ID of runNr.
		if ($results[0] == "") {
			return (false);
		} else {
			return ($results[0]);
		}
	}

	// Query the database and return wether he is in run X or not.
	$results = $DB->query("select joined from joinups where userid = '$userID' and run = '$run' and parted is NULL limit 1");

	if ($results->num_rows == 0) {
		return ("none");
	} else {
		while ($row = $results->fetch_assoc()) {
			return ($row[joined]);
		}
	}
}
?>