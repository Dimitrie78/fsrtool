<?php

// Create new Smarty vars for the general info.
$general = array();

// Row: Mining Run ID
$general['miningID'] = str_pad($row['id'], 5, "0", STR_PAD_LEFT);

// Row: Is official run?
$general['official'] = ($row['isOfficial']? 1 : 0);

// Row: Supervisor Name
$general['Supervisor'] = ucfirst(idToUsername($row['supervisor'])); // profilelink ??

// Row: Taxes
$general['CorpTaxes'] = $row['corpkeeps'];

// Row: Location
$general['location'] = $row['location'];

// Row: TMEC - Spezial OP
$general['SPEZIALOP'] = $row['tmec'];
if ($general['SPEZIALOP']==99)
{
	$general['Tritanium']=$row['Tritanium'];
	$general['Pyerite']=$row['Pyerite'];
	$general['Mexallon']=$row['Mexallon'];
	$general['Isogen']=$row['Isogen'];
	$general['Nocxium']=$row['Nocxium'];
	$general['Megacyte']=$row['Megacyte'];
	$general['Zydrine']=$row['Zydrine'];
	$general['Morphite']=$row['Morphite'];
}

// Row: Comment
$general['comment'] = htmlspecialchars_decode($row['comment']);

// Row: Starttime
$general['Starttime'] = date("d.m.y H:i", $row['starttime']);

// Row: Endtime

if ($row['endtime'] == "") {

	// Run is still open.
	$endtime = "ACTIVE";
	
	// Row: Endtime
	$time = numberToString($TIMEMARK - $row['starttime']);
	$secRunTime = $TIMEMARK - $row['starttime'];
	$general['duration']   = $endtime;
	#$general['secRunTime'] = numberToString($secRunTime);
	$general['secRunTime'] = strtotime("now")-$row['starttime']-date('Z');


	// Statistical breakdown
	$totalISK = getTotalWorth($ID);
	if ($totalISK > 0) {
		$closed = $DB->getCol("SELECT endtime FROM runs WHERE id='" . $ID . "' LIMIT 1");
		if ($closed[0] < 1) {
			//Total ISK so far:
			$general['totalISK'] = $totalISK;

			//ISK per hour:
			$general['ISKperHour'] = (($totalISK / ($secRunTime/60)) * 60);
		}
	}

	// Row: Actions
	
	// Lets switch wether the user is currently in this run or not.
	$jointime = userInRun($MySelf->getUsername(), $ID);
	if ($jointime == "none") {
		// Is NOT in this run, give option to join.
		if (!runIsLocked($ID)) {
			if ($MySelf->canJoinRun()) {
				$general['canJoin'] = '1';
				//$join = "[<a href=\"".MBpath."&action=joinrun&id=$ID\">Join this OP</a>]";
			} else {
				$general['canJoin'] = '0';
				//$join = "You are not allowed to join operations.";
			}
		} else {
			$general['canJoin'] = ucfirst(runSupervisor($ID));
			//$join = (ucfirst(runSupervisor($ID)) . " has locked this run.");
		}
	} else {
		// User IS in this run.

		// Are we allowed to haul?
		if (($row['endtime'] == "") && ($MySelf->canAddHaul())) {
			$general['addHaul'] = '1';
			//$addHaul .= " [<a href=\"".MBpath."&action=addhaul&id=$ID\">Haul Ore</a>] ";
		} else {
			$general['addHaul'] = '0';
			//$addHaul .= false;
		}

		// Run-Owner: Add GUEST  for NON Corp Mining
                // needs: gast_a  + gast_b  + gast_c  + gast_d  in user table
		if (runSupervisor($row[id]) == $MySelf->getUsername() and ($MySelf->isOfficial()) ) {
//			if (runIsLocked($row[id])) {
				$general['joinGuest'] = '1';
				//$lock .= " [<a href=\"".MBpath."&action=joinguest&run&id=$row[id]&state=guest\">Insert Guest</a>] ";
//			} else {
//			}
		}

		// IS in the run, give option to leave.
		$general['leaveRun'] = '1';
		//$add .= " [<a href=\"".MBpath."&action=partrun&id=$ID\">Leave Op</a>] [<a href=\"".MBpath."&action=cans\">Manage Cans</a>]";

		
		// pruefen ob derjenige mehr als einmal in der OP eingetragen ist
		$wasalreadyhere = $DB->getCol("select count(charity) from joinups where userid='" . $MySelf->getID() . "' and run='$ID'");
		
		// Make the charity button if not more then ONE Joinups
		// if ($MySelf->getID()==145) 
		 if ($row['isOfficial'] == false) // MAX???? keine funktion: Dimi: darf nur verfügbar sein wenn isoffical =0
			if ($wasalreadyhere[0]==1)
			{
				$charityFlag = $DB->getCol("SELECT charity FROM joinups WHERE run='$ID' AND userid='" . $MySelf->getID() . "'");
				if ($charityFlag[0]) {
					$general['charity'] = '1';
					//$charity = " [<a href=\"".MBpath."&action=toggleCharity&id=$ID\">Unset Charity Flag</a>]";
				} else {
					$general['charity'] = '0';
					//$charity = " [<a href=\"".MBpath."&action=toggleCharity&id=$ID\">Set Charity Flag</a>]";
				}
			}
		// Charity Button Ende

	}
	// Give option to end this op.
	if (($MySelf->getID() == $row[supervisor]) || ($MySelf->canCloseRun() && ($MySelf->isOfficial() || runSupervisor($row[id]) == $MySelf->getUsername()))) {
		$general['closeOP'] = '1';
		//$add2 = " [<a href=\"".MBpath."&action=endrun&id=$ID\">Close Op</a>]";
	}

	// Refresh button.
	//$refresh_button = " [<a href=\"".MBpath."&action=show&id=$row[id]\">Reload page</a>]";
	//$general_info->addCol($join . $addHaul . $add2 . $lock . $add . $charity . $refresh_button);
//	$general_info->addCol($join . $addHaul . $add2 . $lock . $add . $refresh_button);

} else {
	// Mining run ended.

	// Row: Ended
	$general['endTime'] = date("d.m.y H:i", $row['endtime']);
	
	$ranForSecs = $row['endtime'] - $row['starttime'];

	// Duration
	if ($ranForSecs < 0) {
		$general['duration'] = 'Event was canceled before starttime.';
		//$general_info->addCol("Event was canceled before starttime.");
	} else {
		$general['duration'] = numberToString($ranForSecs);
		//$general_info->addCol(numberToString($ranForSecs));
	}

	// Set flag for later that we dont generate active ship data.
	$DontShips = true;
/*
	// Current TMEC
	$general_info->addRow();
	$general_info->addCol("TMEC reached:");
	$general_info->addCol(calcTMEC($row[id]), true);
*/
}
	// Current add Pay Info
if ($row['isOfficial'] == false && $row['corpkeeps']<100)
{
    //$general_info->addCol(yesno($row[isLocked], true));
	$general['payed'] = $row['isLocked'];
	
}
// We have to check for "0" - archiac runs that have no ore values glued to them

if ($row['oreGlue'] > 0) {
	// Ore Quotes:
	// Is this the current ore quote?
	$cur = $DB->getCol("SELECT id FROM orevalues ORDER BY time DESC LIMIT 1");
	if ($cur[0] == $row['oreGlue']) {
		// it is!
		$general['currentOreGlue'] = '1';
		//$cur = "<font color=\"#00ff00\"><b>(current)</b></font>";
	} else {
		$general['currentOreGlue'] = '0';
		//$cur = "<font color=\"#ff0000\"><b>(not using current quotes)</b></font>";
	}

	// Date of mod?
	$modTime = $DB->getCol("SELECT time FROM orevalues WHERE id='" . $row['oreGlue'] . "' LIMIT 1");
	$general['OreGlueTime'] = date("d.m.y", $modTime[0]);
	//$modDate = date("d.m.y", $modTime[0]);
	//$general_info->addCol("[<a href=\"".MBpath."&action=showorevalue&id=" . $row[oreGlue] . "\">$modDate</a>] $cur");

}
?>
