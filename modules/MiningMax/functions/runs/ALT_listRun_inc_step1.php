<?php

/*
 * Inside:
 * Database business ONLY
 */

// We have to select the most fitting ID. This can be done in three ways.
if (($_GET['id'] >= 0) && (is_numeric($_GET['id']))) {
	// Way Nr. 1: The user specified an ID.
	$ID = $_GET['id'];
} else {
	// Way Nr. 2: The user is in a Mining run, but has not given us an ID. Use the joined MiningOP ID.		
	$ID = userInRun($userID, "check");
	if (!$ID) {
		// Way Nr. 2: The user is not in a run and has not given us an ID. Select the most up to date, not-yet-closed OP.			
		$results = $DB->query("select * from runs where endtime is NULL order by id desc limit 1");
		if (($results->numRows()) == "0") {
			// Total failure: No operations in Database!				
			MakeNotice("There are no mining operations in the database! You have to create an operation prior to join.", "warning", "Not joined");
		}
		$getid = $results->fetchRow();
		$ID = $getid['run'];
	}
}

// Now lets fetch the Dataset.
$results = $DB->query("SELECT * from runs where id = '$ID' limit 1");

// And check that we actually suceeded.
if ($results->numRows() != 1) {
	makeNotice("Internal error: Could not load dataset from Database.", "error", "Internal Error!");
} else {
	$row = $results->fetchRow();
}

// Now that we have the run loaded in RAM, we can load several other things.
$joinlog = $DB->query("select * from joinups where run = '$ID' order by ID DESC");
$activelog = $DB->query("select * from joinups where run = '$ID' and parted is NULL");
if ($row['oreGlue'] <= 0) {
	$values = $DB->query("select * from orevalues order by id desc limit 1");
} else {
	$values = $DB->query("select * from orevalues where id='" . $row['oreGlue'] . "' limit 1");
}

// Load cargo container database.
if (getConfig("cargocontainer")) {
	$CansDS = $DB->query("SELECT id, location, droptime, name, pilot, isFull, miningrun FROM cans WHERE miningrun='$ID' ORDER BY droptime ASC");
}

// note: hauling DB queries have been move into the according step-file
?>