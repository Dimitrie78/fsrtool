<?php

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

if ($joinlog->numRows() > 0) {
	// Someone or more joined.
	
	// Loop through all users who joined up.
	$gotActivePeople = false;
	
	$users = 0;
	while ($alog = $activelog->fetchRow()) {
		
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
		while ($ship_data = $OnlineShips->fetchRow()) {
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
	while ($join = $joinlog->fetchRow()) {
		
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
				$row_marathon = $results_marathon ->fetchRow();
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
?>
