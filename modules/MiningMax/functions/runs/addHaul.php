<?php

/*
 * This adds a haul to the database.
 */

function addHaul() {
	// Globals.
	global $DB;
	global $DBORE;
	global $ORENAME_STR;
	global $TIMEMARK;
	global $MySelf;
	
	// Some more settings we need
	$userID = $MySelf->getID();

	// is the POST[id] truly a number?
	numericCheck($_POST[runid]);
	$ID = sanitize($_POST[runid]);

	// Are we allowed to haul?
	if (!$MySelf->canAddHaul()) {
		makeNotice("You are not allowed to haul to runs!", "error", "forbidden");
	}

	// Is the run still open?
	if (!miningRunOpen($ID)) {
		makeNotice("This mining operation has been closed!", "warning", "Can not join");
	}

	// Is the user in the run?
	if (userInRun($MySelf->getUsername(), "$ID") == "none") {
		makeNotice("You need to join that run before you can haul to it!", "error", "Need to join", "{$index}?module=MiningMax&action=show&id=$ID");
	}
	
	// Mr. Proper
	$location = strtolower(sanitize($_POST[location]));
	$location2 = strtolower(sanitize($_POST[location2]));
	
	// Use manual input, if given.
	if ($location2) {
		$location = $location2;
	}
	
	// We dont accept empty locations.
	if ($location == "") {
		makeNotice("You need to supply a target location!", "error", "Commit haul denied.", "index.php?action=addhaul", "[Cancel]");
	}

	// Get the current ore amount for the selected run.
	$results = $DB->query("select * from runs where id='$ID' limit 1");

	/* Even tho its only one row (result) we are going to loop
	* through it. Just to be on the safe side. While we are at it,
	* we add the submited ore amount to the already stored amount.
	*
	* Note: I explicitly *allow* negative amounts to be "added", in
	*       case the hauler got destroyed on his way back.
	*/
	
	while ($row = $results->fetch_assoc()) {
		foreach ($DBORE as $ORE) {
			$newcount = $row[$ORE] + $_POST[$ORE]; 			
			$DB->query("update runs set $ORE = '" . $newcount . "' where id = '$ID'");
		}
	}

	/*
	* But wait! There is more!
	* Someone hauled our ore, lets record that in the
	* hauled database, along with a timestamp and whatever
	* he hauled.
	*/


	// Lets create the raw entry fist.
	$stmt = $DB->prepare("insert into hauled (miningrun, hauler, time, location) values (?,?,?,?)");
	
	$stmt->bind_param('iiis',$ID, $userID, $TIMEMARK, $location);
		
	/* execute prepared statement */
	$stmt->execute(); 
	
	// Now loop through all the ore-types.
	foreach ($DBORE as $ORE) {

		// Check the input, and insert it!
		if ((isset ($_POST[$ORE])) && (!empty ($_POST[$ORE])) && is_numeric($_POST[$ORE])) {

			// Is that ore-type actually enabled?
			if (getOreSettings($ORE) == 0) {
				makeNotice("Your corporation has globally disabled the mining and hauling of $ORE. Please ask your CEO to re-enable $ORE globally.", "error", "$ORE disabled!", "index.php?action=show&id=$ID", "[back]");
			}

			// No insert the database.
			$DB->query("UPDATE hauled SET $ORE = '$_POST[$ORE]' WHERE time ='$TIMEMARK'");
			$changed = $changed + $DB->affected_rows;
		}
	}

	// Delete the haul again if nothing (useful) was entered.
	if ($changed < 1) {
		$DB->query("DELETE FROM hauled WHERE time = '$TIMEMARK' LIMIT 1");
		makeNotice("No valid Ore information found in your query, aborted.", "warning", "Haul not accepted", "index.php?module=MiningMax&action=show&id=$ID", "[cancel]");
	}
	else
	{

	/*
	 * All done.
	 */
	makeNotice("Dieser Haulvorgang wurde eingetragen.", "notice", "Danke, Dave.", "{$index}?module=MiningMax&action=show&id=$ID");
  }
}
?>