<?php

// prepares the add new ore run form.
function makeNewRun() {
	// Load the globals.
		
	global $TIMEMARK;
	global $ORENAMES;
	global $DBORE;
	global $DB;
	global $MySelf;
	global $smarty;

	// We need a list of all the previous run locations.
	$locationPDM = array();
	$locations = $DB->query("SELECT DISTINCT location FROM runs ORDER BY location");
	if ($locations->num_rows > 0) {
		while ($location = $locations->fetch_assoc()) {
			
			$checkexists = $DB->query("SELECT DISTINCT solarSystemName FROM eve_solarsystems where solarSystemName='".$location['location']."' limit 1");
			 $doesitexist = $checkexists->fetch_assoc();
			if ($checkexists->num_rows>0)
			{
			$locationPDM += array(strtoupper($location['location']) => strtoupper($location['location']));
		  }
	    // Fehlerhafte Systeme debuggen	  	 
	    //echo "<br>Does it Exist:".$checkexists->numRows()." LOC:".$location['location'];

		}
	} 
	//echo '<pre>'; print_r($locationPDM); echo '</pre>';
//	exit;
	$smarty->assign('locations', $locationPDM);

	// Field: Officer in Charge
	$seniorUsersPDM = array();
	if ($MySelf->isOfficial()) {
		$res = $DB->query("SELECT DISTINCT username FROM users WHERE canCreateRun = 1 AND deleted='0' ORDER BY username");
		while( $row = $res->fetch_row() ){
			$SeniorUsers[] = $row[0];
		}
		$res->close();
		foreach ($SeniorUsers as $senior) {
			$seniorUsersPDM += array($senior => ucwords($senior));
		}
	}
	
	// We have no senior member (aka: people who may start runs)
	if (!$seniorUsersPDM) {
	 makeNotice('Du hast keine Rechte eine OP anzulegen. Oder Du kannst keine CorpOP anlegen.', 'warning', 'Insufficient Rights');
	 exit;
	} else {
		$smarty->assign('seniorUsers', $seniorUsersPDM);
		$smarty->assign('user', 	   $MySelf->getUsername());
	}

	// Field: Corporation keeps.
	// Get the average amount.
	if($MySelf->isOfficial()){
		if (!getConfig('defaultTax')) {
			// No default tax has been defined in the config file, generate our own.		
			$res = $DB->query("SELECT AVG(corpKeeps) AS tax FROM runs;");
			$tax = $res->fetch_row();
			$res->close();
			$tax = round($tax[0]);

			// in case there are no taxes yet AND no default has been set.
			if (!$tax) {
				$tax = '15';
			}
		} else {
			// Set the default tax, according to config.
			$tax = getConfig('defaultTax');
		}
		$tax = '<input type="text" maxlength="3" value="'.$tax.'" size="4" name="corpkeeps">% of gross value.';
	} else {
		$tax = 'As this is not an official Op, no tax is deducted.';
	}
	$smarty->assign('tax', 		  $tax);
	$smarty->assign('isOfficial', $MySelf->isOfficial());

/*	// Give option to make this run official.
	if ($MySelf->isOfficial()) {
		$table->addRow();
		$table->addCol('Corp Mining:');
		$table->addCol('<input type="checkbox" name="isOfficial" CHECKED>Tick box if this is a Corp Mining.');
	}
*/
	// Field: Starttime.
		
	// Get a time-array and do the human friendly part.
	// Funnies: We always want to use '00' as the minute, and always at the start of the
	// NEXT hour.
	$times = humanTime('toHuman', ($TIMEMARK+3600));
$smarty->assign('TIMEMARK', date("d.m.Y H:i",$TIMEMARK));
	$smarty->assign('times', $times);
	
/*
	// Now we need the sum of all ores. 
	$totalOres = count($ORENAMES);

	// And the sum of all ENABLED ores.
	$totalEnabledOres = $DB->getCol("select count(name) as active from config where name LIKE '%Enabled' AND value='1'");
	$totalEnabledOres = $totalEnabledOres[0];

	// No ores enabled?
	if ($totalEnabledOres == 0) {
		makeNotice('Your CEO has disabled *all* the Oretypes. Please ask your CEO to reactivate at leat one Oretype.', 'error', 'No valid Oretypes!');
	}

	// The table is, rounded up, exactly half the size of all enabled ores.
	$tableLength = ceil($totalEnabledOres / 2);

	/*
	 * This is evil. We have to create an array that we fill up sorted.
	 * It aint cheap. First, we loop through all the ore values.
	 *
	for ($p = 0; $p < $totalOres; $p++) {
		// Then we check each ore if it is enabled.
		$ORE = $DBORE[$ORENAMES[$p]];
		if (getOreSettings($ORE)) {
			// If the ore is enabled, add it to the array.
			$left[] = $ORE;
		} else {
			// add to disabled-array.
			$disabledOres[] = $ORE;
		}
	}
	// Now, copy the lower second half into a new array.
	$right = array_slice($left, $tableLength);

	/*
	 * So now we have an array of all the enabled ores. All we
	 * need to do now, is create a nice, handsome table of it.
	 * Loop through this array.
	 *
	for ($i = 0; $i < $tableLength; $i++) {

		// Fetch the right image for the ore.
		$ri_words = str_word_count(array_search($left[$i], $DBORE), 1);
		$ri_max = count($ri_words);
		$ri = strtolower($ri_words[$ri_max -1]);

		// Add a row.
		$table->addRow();

		// left side.
		$table->addCol('<img width="20" height="20" src="'.MODULE_DIR.'images/ores/' . $ri . '.png"> <input type="text" name="'.$left[$i].'" size="10" value="0"> ' . array_search($left[$i], $DBORE) . ' wanted. ');

		// We need an ore type (just in case of odd ore numbers)
		if ($right[$i] != '') {
			// right side.

			// Fetch the right image for the ore.
			$ri_words = str_word_count(array_search($right[$i], $DBORE), 1);
			$ri_max = count($ri_words);
			$ri = strtolower($ri_words[$ri_max -1]);

			// Add the column.
			$table->addCol('<img width="20" height="20" src="'.MODULE_DIR.'images/ores/' . $ri . '.png"> <input type="text" name="'.$right[$i].'" size="10" value="0"> ' . array_search($right[$i], $DBORE) . ' wanted. ');

		} else {
			// We have an odd number of ores: add empty cell.
			$table->addCol('');
		}

	}

	// Display the ore-disables-disclaimer. (Only if there are disabled oretypes.)
	if (!empty ($disabled)) {
		$disabledText = 'The following Oretypes has been disabled by the CEO: $disabled';
	}

	$submitbutton = '<input type="hidden" name="check" value="true">' .
	'<input type="hidden" value="addrun" name="action">' .
	'<input type="submit" value="Create new Mining Operation" name="submit">';

	// El grande submit button!					
	$table->addHeaderCentered($submitbutton);

	// Show, if any, disabled ore-types.
	if ($disabledText) {
		$table->addRow();
		$table->addCol('<br><br>' . $disabledText . '.', array (
			'colspan' => '2'
		));
	}

	// Render the table, and return it.
	return ('<h2>Create a new Mining Operation</h2><form action="'.MBpath.'" method="POST">' . $table->flush() . '</form>');
*/	
	
}
?>
