<?php

/*
* addhaulpage()
* Prints the form to add a haul-
*/
function addhaulpage() {

	// Needed globals
	global $DB;
	global $ORENAMES;
	global $DBORE;
	global $MySelf;
	global $smarty;    // Smarty Class
	
	// Some needed variables
	$USER = $MySelf->getID();
	$ORESETTINGS = getOreSettings();

	// Get the run the user is on.
	if (!empty ($_GET[id])) {
		// We supplied our own ID.
		$ID = sanitize((int) $_GET[id]);
		numericCheck($_GET[id], 0);
	} else {
		// No idd supplied, get our own :P
		$ID = userInRun($MySelf->getID());
	}

	//   No ID found.
	if (!$ID) {
		makeNotice("Either you have selected an invalid run, you have not joined that run or it is no longer open.", "warning", "Unable to register your haul");
	}

	// Create the table!
//	$haulpage = new table(2, true);
	$mode = array (
		"bold" => true,
		"align" => "right"
	);
/*
	$haulpage->addHeader(">> Register new Hauling");
	$haulpage->addRow();
	$haulpage->addCol("Hauling for Op: #<a href=\"{$index}?action=show&id=$ID\">" . str_pad($ID, 5, "0", STR_PAD_LEFT) . "</a>", array (
		"align" => "left"
	));
*/

	// fetch the system the haul is taking place in..
//	$location = $DB->getCol("select location from runs where endtime is NULL and id='$ID' order by id desc limit 1");
//	$runLocation = $location[0];
	$runLocation = getLocationOfRun($ID);
	
	// make the targeted system click-able.

	$sytem = new solarSystem($runLocation);
/*	echo "<pre>";
	print_r($sytem);
	echo "</pre>";
	echo $runLocation;
*/	

// Kein System gefunden: funzt net:
// if (!is_array($sytem)) {
// echo "angeblich nix";
// makeNotice("Das ausgew&auml;hlte System gibt es nicht.", "warning", "Haul kann nicht eingetragen werden","{$index}?module=MiningMax&action=show&id=$ID");	
// exit;
// }
		
	// Assemble a PDM with all the destinations for the current run.
	$locations = $DB->query("SELECT location FROM hauled WHERE miningrun='$ID' ORDER BY location ASC");
	if ($locations->num_rows) {
		while ($loc = $locations->fetch_assoc()) {
			if ($loc[location] != "") {
				$pdmSystems[] = $loc[location];
			}
		}
	}
	
	// Get the location the last haul was brought to at.
	$res = $DB->query("SELECT location FROM hauled WHERE miningrun='$ID' AND hauler='".$MySelf->getID()."' ORDER BY time DESC LIMIT 1");
	$lastHaulLocation = $res->fetch_row();
	$res->close();
	$lastHaulLocation = $lastHaulLocation[0];
	
	// Get a list of neighbouring systems.
	$neighbouringSystems = $sytem->getNeighbouringSystems();
	
	// Lets pick the right system.
	if($lastHaulLocation) {
		// Use the last system stuff was hauled to.
		$location = $lastHaulLocation;
	} else {
		// Or, if thats empty, the system the op is in.
		$location = $runLocation;
	}

	if (is_array($pdmSystems)) {
		$Systems = array_merge($neighbouringSystems,$pdmSystems);
	} else {
		$Systems = $neighbouringSystems;
	}
	sort($Systems);
	
//	unset($pdmSystems);
//	unset($neighbouringSystems);
//	unset($loc);
//	unset($locations);
	
	$pdm="";
	$i=0;
	foreach($Systems as $s) {
		
		if ($s == strtoupper($location)) {
			$selectedHaulLocation=strtoupper($location);
//			echo "<pre>";print_r($Systems);echo "</pre>";
		}
	$i++;
	}
	$pdm = "<select name=\"location\">" . $pdm . "</select>";
/*	
	$haulpage->addCol("System hauling to: ".$pdm." -or- <input type=\"text\" name=\"location2\" value=\"\">", array (
		"align" => "right"
	));
	$haulpage->addRow();
	$haulpage->addCol("<hr>", array (
		"colspan" => "2"
	));
*/

	// Now we need the sum of all ores. 
	$totalOres = count($ORENAMES);

	// And the sum of all ENABLED ores.
	$res = $DB->query("select count(name) as active from config where name LIKE '%Enabled' AND value='1'");
	$totalEnabledOres = $res->fetch_row();
	$res->close();
	$totalEnabledOres = $totalEnabledOres[0];

	// No ores enabled?
	if ($totalEnabledOres == 0) {
		makeNotice("Your CEO has disabled *all* the Oretypes. Please ask your CEO to reactivate at leat one Oretype.", "error", "No valid Oretypes!");
	}

	// The table is, rounded up, exactly half the size of all enabled ores.
	$tableLength = ceil($totalEnabledOres / 2);

	/*
	 * This is evil. We have to create an array that we fill up sorted.
	 * It aint cheap. First, we loop through all the ore values.
	 */
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
	 */
	for ($i = 0; $i < $tableLength; $i++) {

		// Fetch the right image for the ore.
		$ri_words = str_word_count(array_search($left[$i], $DBORE), 1);
		$ri_max = count($ri_words);
		$ri = strtolower($ri_words[$ri_max -1]);

		// Add a row.
//		$haulpage->addRow();

		// left side.
/*	$haulpage->addCol("<img width=\"20\" height=\"20\" src=\"./images/ores/" . $ri . ".png\">" .
		"Add <input type=\"text\" size=\"5\" name=\"$left[$i]\" value=\"0\"> Units of " . array_search($left[$i], $DBORE));
		*/

		// We need an ore type (just in case of odd ore numbers)
		if ($right[$i] != "") {
			// right side.

			// Fetch the right image for the ore.
			$ri_words = str_word_count(array_search($right[$i], $DBORE), 1);
			$ri_max = count($ri_words);
			$ri = strtolower($ri_words[$ri_max -1]);

			// Add the column.
/*			$haulpage->addCol("<img width=\"20\" height=\"20\" src=\"./images/ores/" . $ri . ".png\">" .
			"Add <input type=\"text\" size=\"5\" name=\"" . $right[$i] . "\" value=\"0\"> Units of " . array_search($right[$i], $DBORE));
*/			
		} else {
			// We have an odd number of ores: add empty cell.
//			$haulpage->addCol("");
		}

	}

	// Print out all disabled ore types:
	$disabledOreCount = count($disabledOres);

	// add the "," between words, but not before the first one, and an "and" between the last one.
	for ($i = 0; $i < $disabledOreCount; $i++) {
		if ($disabledOreCount == $i +1) {
			$disabledOresText .= " and " . array_search($disabledOres[$i], $DBORE);
		} else
			if (empty ($disabledOresText)) {
				$disabledOresText = array_search($disabledOres[$i], $DBORE);
			} else {
				$disabledOresText .= ", " . array_search($disabledOres[$i], $DBORE);
			}
	}

	// Display the ore-disables-disclaimer. (Only if there are disabled oretypes.)
	if (!empty ($disabledOresText)) {
		$disabledOresText = "The following Oretypes has been disabled by the CEO: $disabledOresText.";
	}

/*	$haulpage->addRow();
	$haulpage->addCol("<hr>", array (
		"colspan" => "2"
	));
	*/

//	$haulpage->addHeaderCentered("<input type=\"submit\" name=\"haul\" value=\"Commit haul to database\">");

	// Render the page...
/*	
	$form_stuff .= "<input type=\"hidden\" value=\"check\" name=\"check\">";
	$form_stuff .= "<input type=\"hidden\" value=\"addhaul\" name=\"action\">";
	$form_stuff .= "<input type=\"hidden\" value=\"" . $ID . "\" name=\"id\">";
	$form_stuff .= "</form>";
*/	
//	$html = "<h2>Submit new transport manifest</h2><form action=\"index.php\" method=\"post\">" . $haulpage->flush() . $form_stuff;

	// print out all the disabled oretypes.
//	if (!empty ($disabledOresText)) {
//		$page .= "<br><i>" . $disabledOresText . "</i>";
//	}

	// Return the page
//	return ($html . $page);
$smarty->assign('runid', $ID);
$smarty->assign('location', $runLocation);
$smarty->assign('selectedHaulLocation', $selectedHaulLocation);
// $smarty->assign('deliverysystems', $pdm); ?
$smarty->assign('Systems', $Systems);

return;

}
?>