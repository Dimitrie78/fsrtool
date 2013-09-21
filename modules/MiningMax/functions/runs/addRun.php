<?php

function addRun() {
	// We need some more globals.
	global $DB;
	global $ORENAMES;
	global $DBORE;
	global $ORENAMES_STR;
	global $MySelf;
	global $TIMEMARK;

	// Set the userID
	$userID = $MySelf->getID();

	// Are we permitted to create a new run?    
	if (!$MySelf->canCreateRun()) {
		makeNotice("You are not allowed to create a mining op!", "error", "forbidden");
	}

	if ($_POST['startnow']) {
		$starttime = $TIMEMARK;
	} else {
		// Startting time goodness.
		$myTime = array (
			"day"    => $_POST['ST_day'],
			"month"  => $_POST['ST_month'],
			"year"   => $_POST['ST_year'],
			"hour"   => $_POST['ST_hour'],
			"minute" => $_POST['ST_minute']			
		);
		$starttime = humanTime("toUnix", $myTime);
	}

	// Having fun with checkboxes, yet again.
	if ($_POST['isOfficial'] == "on") {
		$official = true;
	} else {
		$official = false;
	}
	
	
	// We using either predefined locations.
	if (empty ($_POST['location'])) {
		$location = $_POST['locations'];
	} else {
		$location = $_POST['location'];
	}
	
	if (empty ($location)) {
		makeNotice("You need to specify the location of the Mining Operation!", "notice", "Where again?", MBPATH."&action=newrun", "[Cancel]");
	}

	// Supervisor
	if ($MySelf->isOfficial()) {
		if (empty ($_POST['supervisor'])) {
			// Is official, but no one named!
			makeNotice("You need to name someone as the supervisor for this run!", "warning", "Missing Information", MBPATH."&action=newrun", "[Cancel]");
		} else {
			// Grab ID of named supervisor.
			$supervisor = usernameToID(sanitize($_POST['supervisor']));
		}
	} else {
		// Non official, use own ID
		$supervisor = $MySelf->getID();
	}
	
	// Corp tax
	if ($MySelf->isOfficial()) {
		if ($_POST['corpkeeps'] > 100 || $_POST['corpkeeps'] < 0 || !numericCheck($_POST['corpkeeps'])) {
			makeNotice("The corporation can not keep more than 100% and most certainly wont pay out more than the gross worth (values below 0%). A value of " . $_POST['corpkeeps'] . " is really not valid.", "warning", "Out of range", MBPATH."&action=newrun", "[Cancel]");
		} else {
			$tax = $_POST['corpkeeps'];
		}
	} else {
		$tax = "0";
	}

	// SpezialOPs Haken
	if ($_POST['SPEZIALOP'] == "on") {
		$SPEZIALOP = 99;
		$official=false;
	  $_POST['corpkeeps'];
	} else {
		$SPEZIALOP = 0;
	}
// echo "SP:".$SPEZIALOP." T:".$tax;

	
	// Get the current ore-values.
	$res = $DB->query("SELECT max(id) FROM orevalues");
	$oreValue = $res->fetch_row();
	$res->close();
	$oreValue = $oreValue[0];
	//  Official Runs = 100% Tax
	if ($official==true) { $tax="100"; }

	$stmt = $DB->prepare("insert into runs (location, starttime, supervisor, corpkeeps, isOfficial, oreGlue, tmec) " . "values (?,?,?,?,?,?,?)");
	
	$stmt->bind_param('siiiiis',$location, $starttime, $supervisor, $tax, $official, $oreValue, $SPEZIALOP);
		
	/* execute prepared statement */
	$stmt->execute(); 
	
	// Check for success.
	if ($DB->affected_rows != 1){
		makeNotice("DB Error: Could not add run to database!", "error", "DB Error");
	}
/*
	// Now update the "required" ore values.
	foreach ($DBORE as $ORE) {
		// But the ore needs to be set, valid (numeric) and must be activated.
		if ((isset ($_POST[$ORE])) && (is_numeric($_POST[$ORE])) && (getOreSettings($ORE) == true) && ($_POST[$ORE] > 0)) {
			$DB->query("UPDATE runs SET " . $ORE . "Wanted='" . $_POST[$ORE] . "' WHERE $starttime='$starttime'");
		}
	}
*/
	// And return the user to the run-list overview page.
	makeNotice("Die neue Mining OP wurde angelegt.", "notice", "Danke Dave", "{$index}?module=MiningMax", "[OK]");
}
?>
