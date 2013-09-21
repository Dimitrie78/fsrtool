<?php

/*
 * This function takes an ID and returns a username.
 */

function usernameToID($username) {
	global $DB;
	global $MySelf;
	$username = sanitize($username);

	// Just return the self-id.
	if ($username == $MySelf->getUsername()) {
		return ($MySelf->GetID());
	}

	// Ask the oracle.
	$results = $DB->query("select id from users where username='$username' limit 1");

	// Valid user?
	if ($results->num_rows == 0) {
		// Special case: User got wiped from the database while logged in.
		//if ($caller == "authKeyIsValid") {
			return "-1";
		//}
		makeNotice("Internal Error: Invalid User at usernameToID<br>(called by $caller)", "error");
	}

	// return the username.
	while ($row = $results->fetch_assoc()) {
		return ($row['id']);
	}

}
?>