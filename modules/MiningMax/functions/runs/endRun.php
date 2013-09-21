<?php
/*
* endrun();
* This ends the selected run.
*/
function endrun() {
	global $DB;
	global $TIMEMARK;
	global $MySelf;
//	echo "<pre>GET:";
//	echo print_r($_GET);
//	echo "</pre>";
	
	// Is $_POST[id] truly a number?
if ($_GET[confirmed])
{
	$POSTRUNID=$_GET[runid];

}
else
{
	numericCheck($_POST[runid]);
	$POSTRUNID=$_POST[runid];
}

	// Are we allowed to close runs?
	$res = $DB->query("SELECT supervisor FROM runs WHERE id='".$POSTRUNID."' LIMIT 1");
	$supervisor = $res->fetch_row();
	$res->close();
	if (!$MySelf->canCloseRun() && ($MySelf->getID() != $supervisor[0])) {
		makeNotice("You are not allowed to close runs!", "error", "forbidden");
	}

	// We sure about this?
	confirm("Bist Du sicher das Du OP # $POSTRUNID schliessen willst? " . "Es werden alle Piloten aus der OP " . " entfernt und die OP wird geschlossen.",$_POST);

	// Run already closed?
	if (!miningRunOpen($POSTRUNID)) {
		makeNotice("Diese OP ist bereits zu!", "warning", "OP bereits geschlossen", "{$index}?module=MiningMax?action=show&id=$POSTRUNID");
	}

	// Update the database.
	$DB->query("update runs set endtime = '$TIMEMARK' where id = '$POSTRUNID' and endtime is NULL");

	// now "eject" all members.
	$DB->query("update joinups set parted = '$TIMEMARK' where parted is NULL and run = '$POSTRUNID'");

	// Calculate Payout, IF this is an official run.
	$ID = $POSTRUNID;
	$res = $DB->query("SELECT isOfficial FROM runs WHERE id='$ID'");
	$OfficialRun = $res->fetch_row();
	$res->close();

	// calculate the total value of this op.
	$ISK = getTotalWorth($ID, true);

	
//	if (!$OfficialRun[0] && (getTotalWorth($ID) > 0)) {
// Immer auszahlen
	if ((getTotalWorth($ID) > 0)) {
		// Select all people, except banned ones.		
		$joinedPeople = $DB->query("SELECT DISTINCT userid FROM joinups WHERE run ='$ID' AND status < 2");

		// Also, create the charity array.
		$charityDB = $DB->query("SELECT userid, charity FROM joinups WHERE run ='$ID' AND status < 2");
		while ($c = $charityDB->fetch_assoc()) {
			$charityArray[$c[userid]] = $c[charity];
		}

		// get the payout array. Fun guaranteed.
		while ($peep = $joinedPeople->fetch_assoc()) {
			$payoutArray[$peep[userid]] = calcPayoutPercent($ID, $peep[userid]);
		}

		// Calulate the percent-modifier.
		$percentModifier = 100 / array_sum($payoutArray);

		// Apply the modifier to the percentage.
		$names = array_keys($payoutArray);

		// Add the credit.
		$supervisor = usernameToID(runSupervisor($POSTRUNID));
		foreach ($names as $name) {
			$percent = $payoutArray[$name] * $percentModifier;
			$payout = ($ISK / 100) * $percent;
			// You cannot loose isk from a mission.
			if ($payout > 0 && !$charityArray[$name]) {
				addCredit($name, $supervisor, $payout, $POSTRUNID);
				$finalPercent[$name]=$payout;
			}
		}
// TODO		makeEmailReceipt($ID, $finalPercent);
		// makeNotice("The mining operation has ended. All still active pilots have been removed from the run and each pilot has been credited his share of the net income.", "notice", "Mining Operation closed", "{$index}?module=MiningMax", "[OK]");
	}
	makeNotice("Diese OP wurde geschlossen. Alle aktiven Piloten wurden aus der OP enfernt.", "notice", "Mining Operation geschlossen", "{$index}?module=MiningMax&action=show&runid=$POSTRUNID", "[OK]");
	// wrap things up.

}
?>
