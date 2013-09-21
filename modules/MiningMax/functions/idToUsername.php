<?php

/*
 * This function takes an ID and returns a username.
 */

function idToUsername($id) {
	// Need to access some globals.
	global $DB;

	// $id must be numeric.
	numericCheck($id);

	// Is it -1 ? (Self-added)
	if ($id == "-1") {
		return ("-self-");
	}

	// Ask the oracle.
	$results = $DB->query("select username from users where id='$id' limit 1");

	// Valid user?
	if ($results->num_rows == 0) {
		return ("noone");
		makeNotice("Internal Error: Invalid User at idToUsername", "error");
	}

	// return the username.
	while ($row = $results->fetch_assoc()) {
		return ($row['username']);
	}

}
?>