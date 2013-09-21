<?php


function makeEmailReceipt($runid, $array) {

	// Set variables.
	global $DB;
	global $DBORE;
	global $ORENAMES;
	global $VERSION;
	global $URL;
	$email = getTemplate("receipt", "email");
	$sitename = getConfig("sitename");

	// Load the run
	$RUN = $DB->query("SELECT * FROM runs WHERE id='$runid' LIMIT 2");
	if ($RUN->num_rows != 1) {
		// This run does not exist!
		return;
	} else {
		$RUN = $RUN->fetch_assoc();
	}

	// Load the ore values
	$oreValues = $DB->query("SELECT * FROM orevalues WHERE id='".$RUN[oreGlue]."' LIMIT 1");
	$oreValues = $oreValues->fetch_row();

	// Get total number of people who joined.
	$res = $DB->query("SELECT count(DISTINCT userid) FROM joinups WHERE run='$runid'");
	$totalPeople = $res->fetch_row();
	$res->close();
	$totalPeople = $totalPeople[0];

	/*
	 * Dividers
	 */
	// Create the top divider	
	$l1 = strlen($sitename);
	$dots = 60 - ($l1 -4);
	$dividerTop = "=[ " . $sitename . " ]";
	for ($i = 0; $i <= $dots; $i++) {
		$dividerTop .= "=";
	}

	// Create the bottom divider	
	$l1 = strlen($VERSION);
	$dots = 59 - ($l1 -4);
	for ($i = 0; $i <= $dots; $i++) {
		$dividerBot .= "=";
	}
	$dividerBot .= "=[ " . $VERSION . " ]=";

	/*
	 * Taxes, net value etc
	 */
	$grossValue = getTotalWorth($runid);
	$res = $DB->query("SELECT corpkeeps FROM runs WHERE id='$runid' LIMIT 1");
	$corpTax = $res->fetch_row();
	$res->close();
	$corpTax = $corpTax[0];
	$taxes = ($grossValue * $corpTax) / 100;
	$netValue = $grossValue - $taxes;
	$myShareGross = $grossValue / $totalPeople;

	// No ores mined. Bye-bye.
	if ($grossValue < 1) {
		//		return;
	}

	/*
	 * Get the longest name of the ores.
	 */
	foreach ($ORENAMES as $howlong) {
		$length = strlen($howlong);
		if ($length > $winner) {
			$winner = $length;
		}
	}

	/*
	 * Get all the ores.
	 */
	foreach ($DBORE as $ORE) {
		if ($RUN[$ORE] > 0) {
			$oreType = str_pad(array_search($ORE, $DBORE), $winner, " ");
			$oreAmount = str_pad(number_format($RUN[$ORE]), 11, " ");
			$ppu = $oreValues[$ORE . "Worth"];
			$oreValue = str_pad((number_format($ppu) . " ISK"), 11, " ");

			$remainder = 70 - (strlen($oreType) + strlen($oreAmount) + strlen($oreValue));

			$oreTotalValue = str_pad(((number_format($ppu * $RUN[$ORE])) . " ISK"), $remainder, " ", STR_PAD_LEFT);

			$l1 = strlen($oreAmount);
			$l1 = strlen($oreValue);
			$l1 = strlen($oreTotalValue);

			if ($OreLine) {
				$OreLine .= "\n";
			}
			$OreLine .= $oreType . $oreAmount . $oreValue . $oreTotalValue;
		}
	}

	/*
	 * Replace the placeholders
	 */
	$email = str_replace("{{DIVIDERTOP}}", "$dividerTop", $email);
	$email = str_replace("{{DIVIDERBOT}}", "$dividerBot", $email);
	$email = str_replace("{{ID}}", str_pad($runid, 5, "0", STR_PAD_LEFT), $email);
	$email = str_replace("{{SITENAME}}", $sitename, $email);
	$email = str_replace("{{ORESMINED}}", $OreLine, $email);
	$email = str_replace("{{VALUE}}", number_format($grossValue) . " ISK", $email);
	$email = str_replace("{{CORPTAXES}}", number_format($taxes) . " ISK", $email);
	$email = str_replace("{{NETVALUE}}", number_format($netValue) . " ISK", $email);
	$email = str_replace("{{GROSSSHARE}}", number_format($myShareGross) . " ISK", $email);
	$email = str_replace("{{URL}}", $URL, $email);

	$template = $email;
	/*
	 * This ends the part thats generic for everyone. Now the personalized stuff.
	 */
	$Atendees = $DB->query("SELECT DISTINCT userid FROM joinups WHERE run='$runid'");
	while ($atendee = $Atendees->fetch_assoc()) {
		// Reset the email back to the template.
		$email = $template;
		
		// Do some personalized stuff.
		$pilot = idToUsername(($atendee[userid]));
		$email = str_replace("{{USERNAME}}", ucfirst($pilot), $email);
		$email = str_replace("{{ACCOUNTBALANCE}}", str_pad("BALANCE: " . number_format(getCredits($atendee[userid])), 66, " ", STR_PAD_LEFT) . " ISK", $email);
		$myShare = $array[$atendee[userid]];
		$email = str_replace("{{NETSHARE}}", number_format($myShare) . " ISK", $email);

		/*
		 * transactions
		 */
		$transactions = $DB->query("SELECT * FROM transactions WHERE owner ='".$atendee[userid]."' ORDER BY id DESC LIMIT 10");
		
		if ($transactions->num_rows > 0) {
			while ($trans = $transactions->fetch_assoc()) {
				// time type amount reason
				$transLine .= date("m.d.y h:i:", $trans[time]);
				if ($trans[type]) {
					$transLine .= " [W]";
				} else {
					$transLine .= " [D]";	
				}
				
				$transLine .= " \"".substr($trans[reason], 0, 33)."\"";
				$length = strlen($transLine);
				$remainder = 70 - $length;				
				$transLine .= str_pad((number_format($trans[amount]). " ISK"), $remainder, " ", STR_PAD_LEFT);
				
				// Add the line to the block.
				if ($transBlock) {
					$transBlock .= "\n";
				}
				$transBlock .= $transLine;
				unset($transLine);
			}
		} else {
			$transLine="No recent transactions.";
		}
		$email = str_replace("{{ACCOUNT}}", $transBlock, $email);
		unset($transBlock);
		unset($transLine);

		// Mail, if opt-in.		
		$userInfo = $DB->query("SELECT username, id, optIn, email FROM users WHERE id='".$atendee[userid]."' AND deleted='0'");
		$userInfo = $userInfo->fetch_assoc();
		
		if ($userInfo[email] && $userInfo[optIn]) {
			mail($userInfo[email], "MiningBuddy Payout", $email);
		}
	}
}
?>
