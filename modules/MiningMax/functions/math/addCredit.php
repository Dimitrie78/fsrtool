<?php


/*
 * This adds a transaction log to the database, 
 * ADDING credits to the users account.
 */

function addCredit($userID, $banker, $credit, $runID) {

	// Sane?
	numericCheck($userID, 0);
	numericCheck($credit, 0);
	numericCheck($banker, 0);

	// Globals, YAY!
	global $DB;
	global $TIMEMARK;

	// Create a transaction.
	$transaction = new transaction($userID, 0, $credit);
	$transaction->setReason("mining operation #".str_pad($runID, 5, "0", STR_PAD_LEFT)." payout");
	
	$state = $transaction->commit();
	
	if ($state) {
		return (true);
	} else {
		makeNotice("Unable to grant money to user #$userID!", "error", "Unable to comply!");
	}
}
?>
