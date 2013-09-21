<?php

/*
 * Returns the name of the supervisor for the given run(id).
 */

function runSupervisor($id, $capped = false) {

	// ID valid?
	numericCheck($id, 0);
//	if (!numericCheck($id, 0)) {
//		makeNotice("Internal Error: Invalid RUN selected for runSupervisor.");
//	}

	// Query the database.
	global $DB;
	$res = $DB->query("SELECT supervisor FROM runs WHERE id='$id'");
	$DS = $res->fetch_row();
	$res->close();

	// Return the supervisor.
	if ($capped) {
		return (ucfirst(idToUsername($DS[0])));
	} else {
		return (idToUsername($DS[0]));
	}

}
?>