<?php

$Payouts = array();

// Calculate Payout: 

$joinedPeople = $DB->query("SELECT DISTINCT userid FROM joinups WHERE run ='$ID' AND status<2");
$ISK = getTotalWorth($ID, true);

if ($ISK > 0) {

	$Payouts['colspan'] = 3;

//	$payout_info->addCol("Pilot", array (
	//$payout_info->addCol("Percent of Net", array (
//	$payout_info->addCol("Payout", array (


	// get the payout array. Fun guaranteed.
	while ($peep = $joinedPeople->fetchRow()) {
		$payoutArray[idToUsername($peep[userid])] = calcPayoutPercent($ID, $peep[userid]);
		
	}

	// Calulate the percent-modifier.
	$percentModifier = 100 / array_sum($payoutArray);
$users=0;
	// Apply the modifier to the percentage.
	$names = array_keys($payoutArray);
	foreach ($names as $name) {
		$percent = $payoutArray[$name] * $percentModifier;

		$payout = ($ISK / 100) * $percent;
//		$payout_info->addRow();

// Charityflag noch mal holen :(((
$CharityFlag = $DB->getCol("select charity from joinups where userid='".usernameToID($name)."' and run='".$general['miningID']."'");

$Payouts[$users]['betrag']  = $payout ;
$Payouts[$users]['pilot']  = usernameToID($name);
$Payouts[$users]['pilotname']  = ucfirst($name);
$Payouts[$users]['prozent']  = $percent;
$Payouts[$users]['charity']  = $CharityFlag[0];
		
//		if($MySelf->isAccountant()){
//			$payout_info->addCol("<a href=\"index.php?action=showTransactions&id=".usernameToID($name)."\">".number_format($payout, 2) . " ISK</a>");
//		} else {
//			$payout_info->addCol(number_format($payout, 2) . " ISK");
//		}
		$totalPayout = $totalPayout + $payout;
		$totalPercent = $totalPercent + $percent;
		$users++;
	}


}

?>