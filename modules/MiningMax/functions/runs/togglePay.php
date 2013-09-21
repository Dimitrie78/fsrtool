<?php
// isAccountant ist im alten MB unter Usermanager: edit other users credits.
function togglePay() {
	global $MySelf;
	// Check the runID for validity.
	if (!numericCheck($_POST[runid])) {
		makeNotice("That run ID is invalid.", "error", "Invalid RUN");
	} else {
		$ID = $_POST[runid];
		$ID=sanitize($ID);
	}
	if ($Myself[isAccountant]) {
		makeNotice("Only the supervisor of a run can lock and unlock his/her run.", "warning", "Unable to comply", "index.php?module=MiningMax&action=show&id=$_POST[runid]", "[Cancel]");
	}
	confirm("M&ouml;chtest Du die OP #$ID auszahlen?",$_POST);
	$bool = "1";
	// Update the database!
	global $DB;
    #echo "UPDATE runs SET isLocked='$bool' WHERE id='$ID' LIMIT 1";
	$DB->query("UPDATE runs SET isLocked='$bool' WHERE id='$ID' LIMIT 1");
	$good = $DB->affected_rows;
	// Success?
	if ($good == 1) {
		header("Location: ".URL_INDEX .'?module='.ACTIVE_MODULE);
	} else {
		makeNotice("Unable to set the new locked status in the database. Be sure to run the correct sql schema!", "warning", "Cannot write to database.");
	}

}
?>
