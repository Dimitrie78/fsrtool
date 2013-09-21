<?php

/*
 * This kicks / bans / removes a user from a run.
 */

function kick() {
/*	
	echo "<pre>";
	print_r($_POST);
	echo "</pre>";
*/	
	// Set some vars.
	$joinID = $_POST[joinid];
	$state = $_POST[state];
	numericCheck($joinID, 0);
	numericCheck($state, 0, 3);
	global $DB;
	global $MySelf;
	global $TIMEMARK;

	// Get the RunID.
	$res = $DB->query("SELECT run, joined FROM joinups WHERE id='$joinID' LIMIT 1");
	$runID = $res->fetch_row();
	$res->close();
	$runID = $runID[0];

	// Are we allowed to kick/ Ban?
	if ((runSupervisor($runID) != $MySelf->getUsername()) && !$MySelf->isOfficial()) {
		makeNotice("Du hat keine Rechte jemanden zu entfernen.", "warning", "Es tut mir leid Dave.");
		return;
	}

	// get the userid (to be kicked)
	$res = $DB->query("SELECT userid FROM joinups WHERE id='$joinID' LIMIT 1");
	$kicked = $res->fetch_row();
	$res->close();
	$kicked = $kicked[0];

	// We cant kick ourselves.
	if ($kicked == $MySelf->getID()) {
		makeNotice("Du kannst Dich nicht selbst entfernen.", "notice", "Es tut mir leid Dave.","{$index}?module=MiningMax&action=show&id=$runID");
		return;
	}

	// get confirmations.
	switch ($state) {
		case ("1") :
			confirm("Bist Du sicher das Du " . ucfirst(idToUsername($kicked)) . " entfernen willst?<br>" .
			"Die erworbenen ISK bleiben dem Teilnehmer erhalten.",$_POST);
			break;
		case ("2") :
			confirm("Are you sure you want to kick " . ucfirst(idToUsername($kicked)) . "?<br>" .
			"By kicking the user he or she loses all shares of his ISK and is dishonorably discharged from this operation.");
			break;
		case ("3") :
			confirm("Are you sure you want to ban " . ucfirst(idToUsername($kicked)) . "?<br>" .
			"By banning the user he or she loses all shares of his ISK and is dishonorably discharged from this operation and additionally the user can never rejoin his operation.");
			break;
	}
	
	/* 
	 * Logic bomb work-around
	 * If a user joins an op before it starts, and the leaves during the operation
	 * he will receive huge bonuses while all the others will get negative amounts.
	 * So we have to...
	 * 
	 * 1. Check if the op has started yet (current time < operation start)
	 *  If "no" then we are not affected by the logic bomb.
	 *  
	 *  If "yes" then we need to set the kicktime AND the jointime to the current time.
	 *  Why? If we just set the kicktime to the jointime then the "kicked at" time will
	 *  always show the time of the op launch, never the real kick time. Also, the
	 *  duration is always zero seconds, so the user will never receive any share from
	 *  this run.
	 */
	 if ($TIMEMARK < $kicked[joined]) {
	 	$partedTime = $kicked[joined];
	 } else {
	 	$partedTime = $TIMEMARK;
	 }
	 
	// Now lets handle kicks, bans and removals.
/* DEBUG
	echo "DB schreiben erreicht";
	echo "<br>update joinups set remover = '" . $MySelf->getID() . "' where run = '$runID' and userid = '$kicked' and parted IS NULL";
	echo "<br>update joinups set status = '$state' where run = '$runID' and userid = '$kicked' and parted IS NULL";
	echo "<br>update joinups set parted = '$partedTime' where run = '$runID' and userid = '$kicked' and parted IS NULL";
	exit;
*/	
	$DB->query("update joinups set remover = '" . $MySelf->getID() . "' where run = '$runID' and userid = '$kicked' and parted IS NULL");
	$DB->query("update joinups set status = '$state' where run = '$runID' and userid = '$kicked' and parted IS NULL");
	$DB->query("update joinups set parted = '$partedTime' where run = '$runID' and userid = '$kicked' and parted IS NULL");

	// Thats it, for now.	
	header("Location: index.php?module=MiningMax&action=show&id=$runID");
}
?>