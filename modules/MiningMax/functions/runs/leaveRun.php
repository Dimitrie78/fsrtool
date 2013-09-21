<?php

/*
 * This allows the user to leave a run.
 */

function leaveRun() {
	// Access the globals.    
	global $DB;
	global $TIMEMARK;
	global $MySelf;
	$runid = sanitize($_POST['runid']);
	if (empty($runid)) $runid = sanitize($_GET['runid']); // Need for Confirm abfrage
	$userid = $MySelf->getID();

	// Are we actually still in this run?
	if (userInRun($userid, $runid) == "none") {
		makeNotice("Du kannst diesen Run nicht verlassen.", "warning", "Du bist nicht in diesem Run.", MBPATH."&action=show&id=$runid", "[cancel]");
	}

	// Is $runid truly an integer?
	numericCheck($runid);


	// Oh yeah?
	if (runIsLocked($runid)) {
		confirm("Willst Du die OP #$runid wirklich verlassen?<br><br>Vorsicht diese OP ist gelocked. " . runSupervisor($runid, true) . ". Du kannst danach nicht wieder joinen.", $_POST);
	} else {
		confirm("Willst Du die OP #$runid wirklich verlassen?", $_POST);
	}
	

	// Did the run start yet? If not, delete the request.
	$res = $DB->query("SELECT starttime FROM runs WHERE id='$runid' LIMIT 1");
	$runStart = $res->fetch_row();
	$res->close();


	if ($TIMEMARK < $runStart[0]) {
		// Event not started yet. Delete.
		$DB->query("DELETE FROM joinups WHERE run='$runid' AND userid='$userid'");
	} else {
		// Event started, just mark inactive.
		
		$DB->query("update joinups set parted = '$TIMEMARK' where run = '$runid' and userid = '$userid' and parted IS NULL");
	}

	makeNotice("Du hast die OP verlassen.", "notice", "Du hast die OP verlassen.", "?module=MiningMax&action=show&id=$runid", "[OK]");
}
?>