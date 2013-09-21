<?php
/* Liste der Miningruns anzeigen */

function RunsListing() {
	global $DB; 	   // Database connection
	global $TIMEMARK;  // The "current" timestamp
	global $MySelf;    // Ourself, and along with that, the permissions.
	global $smarty;    // Smarty Class
	// Query it. 
	if (is_numeric($_GET[offset]) && $_GET[offset] > 0) { 		$page = "LIMIT ". ($_GET[offset] * 20) . ", 20" ; 	}
	elseif ($_GET[offset] == "all") {		$page = "";	} else { 		$page = "LIMIT 20";	}
	
	$result = $DB->query("SELECT * FROM runs ORDER BY id DESC $page");
 	$datensatznummer=0;
  		
  	while ($run = $result->fetch_assoc()) {
		if ($run) {
			$runs[$datensatznummer] = $run;
			$runs[$datensatznummer]['ertrag'] = getTotalWorth($run['id']);
			$runs[$datensatznummer]['supervisor'] = runSupervisor($run['id']);
		  $runid=$run['id'];
		  if (userInRun($MySelf->getID(), "$runid") == "none") { $runs[$datensatznummer]['zeige_beitreten']=1; } else { $runs[$datensatznummer]['zeige_verlassen']=1; }
			$datensatznummer++;
		}
	}

    $deleteimg =  MODULE_DIR . ACTIVE_MODULE . '/images/delete.png';
    
    $smarty->assign('offset',$_GET[offset]);
  	$smarty->assign('date', date('d.m.Y H:i'));
    $smarty->assign('runs',$runs);
    $smarty->assign('deleteimg',$deleteimg);
}
?>