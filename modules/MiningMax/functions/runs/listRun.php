<?php

/*
* Details eines Runs anzeigen
* listRun()
* will return the run id requested in the GET array and
* print a nice, friendly html page.
*/
/* Berechnen der Metallmengen für ein Erz */
// gibt es schon in listrun.php
// diese Funktion wird auch von listProject verwendet
function calcmetall($name,$menge)
{
	// if ($name <>"Veldspar") return(0);
	global $DB;
  	global $S_Tritanium,$S_Pyerite,$S_Mexallon,$S_Isogen,$S_Megacyte,$S_Zydrine,$S_Nocxium,$S_Morphite;

	$menge=str_replace(",","",$menge); 	$menge=str_replace(".","",$menge);
	$line = $DB->query("SELECT * FROM metallwert WHERE metall='$name' LIMIT 1");
	$mengen=$line->fetch_assoc();
	$numrows=$line->num_rows;
	// Kein Erz dieser Art geminert, raus hier
	if ($numrows==0) return;
	// print_r($mengen);
	 // echo "Name:".$name." Menge:".$menge." Batch:".$mengen['batch']."<br>";
	$S_Tritanium=$S_Tritanium+($mengen['Tritanium']*$menge/$mengen['batch']);
	$S_Pyerite=$S_Pyerite+($mengen['Pyerite']*$menge/$mengen['batch']);
	$S_Mexallon=$S_Mexallon+($mengen['Mexallon']*$menge/$mengen['batch']);
	$S_Isogen=$S_Isogen+($mengen['Isogen']*$menge/$mengen['batch']);
	$S_Megacyte=$S_Megacyte+($mengen['Megacyte']*$menge/$mengen['batch']);
	$S_Zydrine=$S_Zydrine+($mengen['Zydrine']*$menge/$mengen['batch']);
	$S_Nocxium=$S_Nocxium+($mengen['Nocxium']*$menge/$mengen['batch']);
	$S_Morphite=$S_Morphite+($mengen['Morphite']*$menge/$mengen['batch']);
	/*
	echo "Tri:".$S_Tritanium."<br>";
	echo "Pyr:".$S_Pyerite."<br>";
	echo "Mex:".$S_Mexallon."<br>";
	echo "Iso:".$S_Isogen."<br>";
	echo "Meg:".$S_Megacyte."<br>";
	echo "Zyd:".$S_Zydrine."<br>";
	echo "Noc:".$S_Nocxium."<br>";
	echo "Mor:".$S_Morphite."<br>";
	*/
	
	// echo "<br><pre>";
	// print_r($mengen);
	// echo "</pre>";
}

function listRun() {

	/*
	 * STEP ZERO:
	 * Import variables, and define needed things.
	 */
	global $DB; 	   // Database connection
	global $ORENAMES;  // A list of all the orenames
	global $DBORE; 	   // An array of db friendly orenames
	global $TIMEMARK;  // The "current" timestamp
	global $MySelf;    // Ourself, and along with that, the permissions.
	global $SHIPTYPES; // We dont want numbers to memorize.
	global $smarty;    // Smarty Class
	// Spezial Ops
	global $S_Tritanium,$S_Pyerite,$S_Mexallon,$S_Isogen,$S_Megacyte,$S_Zydrine,$S_Nocxium,$S_Morphite;
	
	$S_Tritanium=0;
	$S_Pyerite=0;
	$S_Mexallon=0;
	$S_Isogen=0;
	$S_Megacyte=0;
	$S_Zydrine=0;
	$S_Nocxium=0;
	$S_Morphite=0;


	$userID = $MySelf->getID(); // Shortcut: Assign the UserID to userID.
	
	/* STEP ONE: 	 * Load the database row into $row. This requires us to look up the minigrun ID first.	 */

	// We have to select the most fitting ID. This can be done in three ways.
	if (($_GET['id'] >= 0) && (is_numeric($_GET['id']))) {
		// Way Nr. 1: The user specified an ID.
		$ID = $_GET['id'];
	} else {
		// Way Nr. 2: The user is in a Mining run, but has not given us an ID. Use the joined MiningOP ID.		
		$ID = userInRun($userID, "check");
		if (!$ID) {
			// Way Nr. 2: The user is not in a run and has not given us an ID. Select the most up to date, not-yet-closed OP.			
			$results = $DB->query("select * from runs where endtime is NULL order by id desc limit 1");
			if (($results->num_rows) == "0") {
				// Total failure: No operations in Database!				
				MakeNotice("There are no mining operations in the database! You have to create an operation prior to join.", "warning", "Not joined");
			}
			$getid = $results->fetch_assoc();
			$ID = $getid['run'];
		}
	}

	// Now lets fetch the Dataset.
	$results = $DB->query("SELECT * from runs where id = '$ID' limit 1");
	// And check that we actually suceeded.
	if ($results->num_rows != 1) {
		makeNotice("Internal error: Could not load dataset from Database.", "error", "Internal Error!");
	} else {
		$row = $results->fetch_assoc();
	}

	// Now that we have the run loaded in RAM, we can load several other things.
	$joinlog = $DB->query("select * from joinups where run = '$ID' order by ID DESC");
	$activelog = $DB->query("select * from joinups where run = '$ID' and parted is NULL");
	if ($row['oreGlue'] <= 0) {
		$values = $DB->query("select * from orevalues order by id desc limit 1");
	} else {
		$values = $DB->query("select * from orevalues where id='" . $row['oreGlue'] . "' limit 1");
	}

	// Load cargo container database.
	if (getConfig("cargocontainer")) {
		$CansDS = $DB->query("SELECT id, location, droptime, name, pilot, isFull, miningrun FROM cans WHERE miningrun='$ID' ORDER BY droptime ASC");
	}

	// note: hauling DB queries have been move into the according step-file

	/* STEP TWO	 * Gather some vital information.	 */

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
		$res = $DB->query("SELECT endtime FROM runs WHERE id='" . $ID . "' LIMIT 1");
		$closed = $res->fetch_row();
		$res->close();
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
		$res = $DB->query("select count(charity) from joinups where userid='" . $MySelf->getID() . "' and run='$ID'");
		$wasalreadyhere = $res->fetch_row();
		$res->close();
		// Make the charity button if not more then ONE Joinups
		// if ($MySelf->getID()==145) 
		 if ($row['isOfficial'] == false) // MAX???? keine funktion: Dimi: darf nur verfügbar sein wenn isoffical =0
			if ($wasalreadyhere[0]==1)
			{
				$res = $DB->query("SELECT charity FROM joinups WHERE run='$ID' AND userid='" . $MySelf->getID() . "'");
				$charityFlag = $res->fetch_row();
				$res->close();
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
	$res = $DB->query("SELECT id FROM orevalues ORDER BY time DESC LIMIT 1");
	$cur = $res->fetch_row();
	$res->close();
	
	if ($cur[0] == $row['oreGlue']) {
		// it is!
		$general['currentOreGlue'] = '1';
		//$cur = "<font color=\"#00ff00\"><b>(current)</b></font>";
	} else {
		$general['currentOreGlue'] = '0';
		//$cur = "<font color=\"#ff0000\"><b>(not using current quotes)</b></font>";
	}

	// Date of mod?
	$res = $DB->query("SELECT time FROM orevalues WHERE id='" . $row['oreGlue'] . "' LIMIT 1");
	$modTime = $res->fetch_row();
	$res->close();
	$general['OreGlueTime'] = date("d.m.y", $modTime[0]);
	//$modDate = date("d.m.y", $modTime[0]);
	//$general_info->addCol("[<a href=\"".MBpath."&action=showorevalue&id=" . $row[oreGlue] . "\">$modDate</a>] $cur");

}
	$smarty->assign('general', $general);

	/*  STEP FOUR The Join and Part log. 	 */

// Teilnehmer
// Row: Joinups.
$Joinups = array();
$icankick=0;

// Are we the supervisor of this run and/or an official?
if ((runSupervisor($ID) == $MySelf->getID()) || $MySelf->isOfficial()) {
	// We are.
	$Joinups['colspan'] = 9;
	$Joinups['cankick'] = 1;
	$icankick = 1;
} else {
	// We are not.
	$Joinups['colspan'] = 6;
	$Joinups['cankick'] = 0;
}

//$join_info->addHeader(">> Active Pilots");

if ($joinlog->num_rows > 0) {
	// Someone or more joined.
	
	// Loop through all users who joined up.
	$gotActivePeople = false;
	
	$users = 0;
	while ($alog = $activelog->fetch_assoc()) {
		
		// People counter
		$activePeople++;
				
		if ($alog['info']<>"") { 
			$infofeld=" (".$alog['info'].")"; 
		} else { 
			$infofeld=""; 
		}
		$Joinups['active'][$users]['user'] = ucfirst(idToUsername($alog[userid])).$infofeld;
		//$join_info->addCol(makeProfileLink($alog[userid]).$infofeld); 

		if ($TIMEMARK < $alog['joined']) {
			$Joinups['active'][$users]['joined'] = 'wartend';
			//$join_info->addCol("request pending");
		} else {
			$Joinups['active'][$users]['joined'] = date("H:i:s", $alog[joined]);
			//$join_info->addCol(date("H:i:s", $alog[joined]));
		}

		$time = numberToString($TIMEMARK - $alog['joined']);
		if ($time) {
			$Joinups['active'][$users]['time']  = $time;
			$Joinups['active'][$users]['timer']  = strtotime("now")-$alog['joined']-date('Z');
			$Joinups['active'][$users]['state'] = 1;
			//$join_info->addCol($time);
			//$join_info->addCol("<font color=\"#00ff00\">ACTIVE</font>");
		} else {
			$Joinups['active'][$users]['time']  = 'OP startet bald';
			$Joinups['active'][$users]['state'] = 0;
			//$join_info->addCol("request pending");
			//$join_info->addCol("<font color=\"#FFff00\">PENDING</font>");
		}
		$Joinups['active'][$users]['shiptype'] = $SHIPTYPES[$alog['shiptype']];
		//$join_info->addCol($SHIPTYPES[$alog['shiptype']]);

		$Joinups['active'][$users]['charity'] = $alog['charity'];
		//$join_info->addCol(yesno($alog[charity], 1, 0));

		// Print the kick/ban/remove headers.
		$Joinups['active'][$users]['ID'] = $alog['id'];
		$Joinups['active'][$users]['userID'] = $alog['userid'];
		/*if ($icankick) {
			if ($alog['userid'] == $MySelf->getID()) {
				// Cant kick yourself.
				$join_info->addCol("---");
				$join_info->addCol("---");
				$join_info->addCol("---");
			} else {
				$join_info->addCol("[<a href=\"".MBpath."&action=kickban&state=1&joinid=$alog[id]\">remove</a>]");
				$join_info->addCol("[<a href=\"".MBpath."&action=kickban&state=2&joinid=$alog[id]\">kick</a>]");
				$join_info->addCol("[<a href=\"".MBpath."&action=kickban&state=3&joinid=$alog[id]\">ban</a>]");
			}
		}*/
		$users++;
		$gotActivePeople = true;
	}
	
	// Tell the folks how many active pilots we have, switching none, one or many.
	
	$Joinups['activeUser'] = $activePeople;
	/*
	switch($join_info){
		case("0"):
			$join_info->addHeader("There are no active pilots.");
		break;
		
		case("1"):
			$join_info->addHeader("There is one pilot.");
		break;
		
		default:
			$join_info->addHeader("There are " . $activePeople . " active pilots.");	
		break;
	}
	*/
	

	/*
	 * Show what ships are currently online.
	 */
	if (!$DontShips) {
		$OnlineShips = $DB->query("SELECT count(shiptype) as count, shiptype FROM joinups WHERE parted is NULL GROUP BY shiptype");

		// Active Ships
		/*$shiptype_info->addRow("#060622");
		$shiptype_info->addCol("Shiptype", array (
			"bold" => true
		));
		$shiptype_info->addCol("Active count", array (
			"bold" => true
		));*/
		
		$ships = 0;
		while ($ship_data = $OnlineShips->fetch_assoc()) {
			$shiptype = $ship_data['shiptype'];
			$count 	  = $ship_data['count'];

			$Joinups['ships'][$ships]['type']  = $SHIPTYPES[$shiptype];
			$Joinups['ships'][$ships]['count'] = $count;
			//$shiptype_info->addRow();
			//$shiptype_info->addCol($SHIPTYPES[$shiptype]);
			//$shiptype_info->addCol($count . " active");
			//$gotShips = true;
			$ships++;
		}
	}

	/*
	 * Now that we know that there was at least ONE user who is active we can
	 * assemble a join and part log.
	 */
	
	/* $partlog_info->addHeader(">> Attendance Log");
	$partlog_info->addRow("#080822");
	$partlog_info->addCol("Pilot", array (
		"bold" => true
	));
	$partlog_info->addCol("Joined", array (
		"bold" => true
	));
	$partlog_info->addCol("Parted", array (
		"bold" => true
	));
	$partlog_info->addCol("Active Time", array (
		"bold" => true
	));
	$partlog_info->addCol("State", array (
		"bold" => true
	));
	$partlog_info->addCol("Charity", array (
		"bold" => true
	));
	$partlog_info->addCol("Totaltime", array (
		"bold" => true
	));
	$partlog_info->addCol("Notes", array (
		"bold" => true
	)); 
	*/


	$part = 0;
	while ($join = $joinlog->fetch_assoc()) {
		
		//$partlog_info->addRow();
		if ($join['info']<>"") { 
			$infofeld=" (".$join['info'].")"; 
		} else { 
			$infofeld=""; 
		}
		$Joinups['attendance'][$part]['user'] = ucfirst(idToUsername($join['userid'])).$infofeld;

//		$partlog_info->addCol(makeProfileLink($join[userid]));


		// geändert das es immer aufgerufen wird	
    if ($join['joined'] >= 1) {			
			$Joinups['attendance'][$part]['joined'] = date("H:i:s", $join['joined']);
			//$partlog_info->addCol(date("H:i:s", $join['joined']));
			if ($join['parted'] != "") {
				$Joinups['attendance'][$part]['parted']['time'] = date("H:i:s", $join['parted']);
				//$partlog_info->addCol(date("H:i:s", $join['parted']));


// Marathon
/*
 $DG = $DB->query("select sum(DarkGlitter) from hauled where miningrun>=1544");		
 $PWG =$DB->query("select sum(PristineWhiteGlaze) from hauled where miningrun>=1544");		
 echo "DG:".$DG." PWG:".$PWG;
*/

				// Alte Anzeige: Nur vom Datensatz
				//				$partlog_info->addCol(numberToString((($join[parted] - $join[joined])))."sx");
				// Start Summe h:i:s
				// if ($MySelf->getID() ==145)
				//{
				$results_marathon = $DB->query("SELECT sum(parted-joined) as summe from joinups where run = '".$join['run']."' and userid = '".$join['userid']."';");
				$row_marathon = $results_marathon->fetch_assoc();
				// print_r($join[userid]."->".$row_marathon['sum(parted-joined)']);
				$gesamtzeitdesspielers = date("H:i:s", $row_marathon['summe']-(60*60));
				// echo $join[run];
				// }
				// ENDE Summe h:i:s				
				// Neue Anzeige mit TOTALTIME
				// neee $Joinups['attendance'][$part]['parted']['total'] = numberToString((($join['parted'] - $join['joined'])))." Total: ".$gesamtzeitdesspielers;
				// besser
				$Joinups['attendance'][$part]['parted']['total'] = date("H:i:s",(($join['parted'] - $join['joined'])-60*60));
				$Joinups['attendance'][$part]['parted']['state'] = 0;
				//$partlog_info->addCol(numberToString((($join['parted'] - $join['joined'])))." Total:".$gesamtzeitdesspielers);
				//$partlog_info->addCol("<font color=\"#ff0000\">INACTIVE</font>");
			} else {
				$Joinups['attendance'][$part]['parted']['time']  = '&nbsp;'; // soon(tm)
				$Joinups['attendance'][$part]['parted']['total'] = numberToString($TIMEMARK - $join['joined']);
				$Joinups['attendance'][$part]['parted']['state'] = 1;
				//$partlog_info->addCol("<i>soon(tm)</i>");
				//$partlog_info->addCol(numberToString((($TIMEMARK - $join[joined]))));
				//$partlog_info->addCol("<font color=\"#00ff00\">ACTIVE</font>");
			}
			$Joinups['attendance'][$part]['charity'] = $join['charity'];
			//$partlog_info->addCol(yesno($join[charity], 1, 0));
			// $partlog_info->addCol(joinAs($join[shiptype]));
			$Joinups['attendance'][$part]['sumtime'] = $gesamtzeitdesspielers;
			//$partlog_info->addCol($gesamtzeitdesspielers);
		} 
/*
		else {
			$Joinups['attendance'][$part]['joinAs'] = joinAs($join['shiptype']);
			partlog_info->addCol("request pending");
			$partlog_info->addCol("request pending");
			$partlog_info->addCol("request pending");
			$partlog_info->addCol("request pending");
			$partlog_info->addCol(joinAs($join[shiptype])); 
		}
	*/	

		// Get the removal reason.
		switch ($join['status']) {
			default :
			case ("0") :
				$reason = "&nbsp;";
				break;
			case ("1") :
				$reason = "entfernt von " . ucfirst(idToUsername($join['remover']));
				break;
			case ("2") :
				$reason = "<font color=\"#ffff00\">gekickt von </font> by " . ucfirst(idToUsername($join['remover']));
				break;
			case ("3") :
				$reason = "<font color=\"#ff0000\">gebannt von </font> by " . ucfirst(idToUsername($join['remover']));
				break;
		}
		$Joinups['attendance'][$part]['reason'] = $reason;
		
		//$partlog_info->addCol($reason);
		$part++;
	}

}

	$smarty->assign('Join', $Joinups);


	/* Was wurde alles erminert
	 * STEP FIVE - gehaultes Erz / Eis The Ressources Information Table	 */

// gehaultes erz + Aufruf für Metallmengen

$hauler = array();
$erzcount=0;


// Load current payout values.
while ($val = $values->fetch_assoc()) {
	// Voila, le scary monster!

	foreach ($DBORE as $ORE) {

		// We need a Variable name with the word Wanted (for the wanted columns)
		$OREWANTED = $ORE . "Wanted";

		/* If an ore is neither wanted nor has been harvested so far, we dont print
		 * that row to save precious in game browser space.
		 */

		if (("$row[$ORE]" >= 1) || ("$row[$OREWANTED]" >= 1)) {

			/* This is actually the main table. It prints the associated array
			 * lists into a neat human readable output.
			 */

			// Calculates the Worth of this ore.
			$worth = (($val[$ORE . Worth]) * $row[$ORE]);
			$totalworth = $totalworth + $worth;

			if ($row[$ORE] <= 0) {
				$tmp_ore = "<i>none</i>";
			} else {
				$tmp_ore = number_format($row[$ORE], 0);
			}

			if ($row[$OREWANTED] > 0) {
				$tmp_ore_wanted = number_format($row[$OREWANTED], 0);
			} else {
				$tmp_ore_wanted = "<i>none</i>";
			}

			// Fetch the right image for the ore.
			$ri_words = str_word_count(array_search($ORE, $DBORE), 1);
			$ri_max = count($ri_words);
			$ri = strtolower($ri_words[$ri_max -1]);

$hauler[$erzcount]['picture']=$ri.".png";
$hauler[$erzcount]['name']=array_search($ORE, $DBORE);
$hauler[$erzcount]['menge']=$tmp_ore;
$hauler[$erzcount]['wert']=$val[$ORE . Worth];
$hauler[$erzcount]['gesamtwert']=$worth;


// Metallmengen für Spezialops (TMEC 99) 
// CALC ALT calcmetall($hauler[$erzcount]['name'],$tmp_ore);


$erzcount++;

			$gotOre = true; // We set this so we know we have SOME ore.
		}
	}
}




// Math fun.
$taxes = ($totalworth * $row[corpkeeps]) / 100;
$net = $totalworth - $taxes;
// STEP 5 ende

  $smarty->assign('oretotalworth', $totalworth);
  $smarty->assign('oretotaltax', $taxes);
  $smarty->assign('oretotalnetworth', $net);
	$smarty->assign('hauler', $hauler);
	// ob derjenige kicken kann
	$smarty->assign('icankick',$icankick);
	

  
	
	$smarty->assign('transportcount',$erzcount);
//	 echo '<pre>'; print_r($hauler); echo '</pre>';
	

	/* STEP SEVEN - brauchen wir nicht unbedingt
	 - Show the transport manifest - War hat was gehaulert */

$transports = array();
$transportcount=0;


// Are there any hauls at all?
if (getTotalHaulRuns($ID) > 0) {

	// Ask the oracle.
	if ($_GET[detailed] != true){
		$limit = "LIMIT 6";
	}
	
	$haulingDB = $DB->query("select * from hauled where miningrun = '$ID' ORDER BY time DESC $limit");

/*	
	// Create the table header.
	$hauled_information = new table(4, true);
	$hauled_information->addHeader(">> Transport Manifest");
	$hauled_information->addRow("#060622");
	$hauled_information->addCol("Hauler");
	$hauled_information->addCol("Time");
	$hauled_information->addCol("Location");
	$hauled_information->addCol("Freight");
*/

	
	// Lets loop through the results!
	while ($row = $haulingDB->fetch_assoc()) {

		// The who hauled to where when stuff.

$transports[$transportcount]['name']=idToUsername($row[hauler]);
$transports[$transportcount]['date']=date("H:i:s", $row[time]);

/*
		$hauled_information->addRow(false, top);
		$hauled_information->addCol(makeProfileLink($row[hauler]));
		$hauled_information->addCol(date("H:i:s", $row[time]));
		$hauled_information->addCol(ucfirst($system->makeFancyLink()));
*/
// naja:		$system = new solarSystem($row[location]);


		/* 
		 * Now we loop through all the ore in the hauled database (result)
		 * and print a Oretype: Amount for each Oretype that has an amount
		 * greater or lesser than zero, but not zero.
		 */

		$oc = 0;
		foreach ($DBORE as $ORE) {
			if ($row[$ORE]<>0) {
				$transports[$transportcount]['erz'][$oc]['name'] = array_search($ORE, $DBORE);
			    $transports[$transportcount]['erz'][$oc]['menge']	 = $row[$ORE];
			    // Neu Calc hier
			    calcmetall($ORE,$row[$ORE]);
			    $oc++;
			}
			
		}
		$transportcount++;
	}
	
	// SPEZIAL OPS METALLE
	// Spezial OPS, Metalle
if ($general['SPEZIALOP']==99)
{	
	$smarty->assign('S_Tritanium',$S_Tritanium);
	$smarty->assign('S_Pyerite',$S_Pyerite);
	$smarty->assign('S_Mexallon',$S_Mexallon);
	$smarty->assign('S_Isogen',$S_Isogen);
	$smarty->assign('S_Megacyte',$S_Megacyte);
	$smarty->assign('S_Zydrine',$S_Zydrine);
	$smarty->assign('S_Nocxium',$S_Nocxium);
	$smarty->assign('S_Morphite',$S_Morphite);
	if ($general['Tritanium']>0) { $smarty->assign('SP_Tritanium',$S_Tritanium/$general['Tritanium']*100); }
	if ($general['Pyerite']>0) { $smarty->assign('SP_Pyerite',$S_Pyerite/$general['Pyerite']*100);}
	if ($general['Mexallon']>0) { $smarty->assign('SP_Mexallon',$S_Mexallon/$general['Mexallon']*100);}
	if ($general['Isogen']>0) { $smarty->assign('SP_Isogen',$S_Isogen/$general['Isogen']*100);}
	if ($general['Nocxium']>0) { $smarty->assign('SP_Nocxium',$S_Nocxium/$general['Nocxium']*100);}
	if ($general['Megacyte']>0) { $smarty->assign('SP_Megacyte',$S_Megacyte/$general['Megacyte']*100);}
	if ($general['Zydrine']>0) { $smarty->assign('SP_Zydrine',$S_Zydrine/$general['Zydrine']*100);}
	if ($general['Morphite']>0) { $smarty->assign('SP_Morphite',$S_Zydrine/$general['Morphite']*100);}
	
}
	// offer full view.	statt limit auf 6 
//	if ($limit) {
//		$hauled_information->addHeader("Only the 6 most recent hauls are shown. [<a href=\"index.php?action=show&id=".$ID."&detailed=true\">show all<a>]");
//	} else {
//		$hauled_information->addHeader("All hauls are shown. [<a href=\"index.php?action=show&id=".$ID."\">show only recent<a>]");
//	}

}

	$smarty->assign('transport', $transports);
 //echo '<pre>'; print_r($transports); echo '</pre>';


	/* 	 * STEP EIGHT Calculate the payout.	 */

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
	while ($peep = $joinedPeople->fetch_assoc()) {
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
$res = $DB->query("select charity from joinups where userid='".usernameToID($name)."' and run='".$general['miningID']."'");
$CharityFlag = $res->fetch_row();
$res->close();
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

	$smarty->assign('Payinfo', $Payouts);
	
//	echo "S:".date("d.m.Y H:i",$row['starttime'])." T:".date("d.m.Y H:i",$TIMEMARK);
// liegt der OP Start in der Zukunft     	
	if  ($row['starttime'] >=$TIMEMARK)  {$isrunning=0;} else {$isrunning=1;}
	$smarty->assign('isrunning', $isrunning);
	
	// $smarty->assign('comment', $row['comment']);
	$smarty->assign('PayinfoTotal', $totalPayout);
	$smarty->assign('PayinfoTotalPercent', $totalPercent);

}
?>