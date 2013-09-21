<?php

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
	while ($row = $haulingDB->fetchRow()) {

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
			    $oc++;
			}
			
		}
		$transportcount++;
	}

	// offer full view.	statt limit auf 6 
//	if ($limit) {
//		$hauled_information->addHeader("Only the 6 most recent hauls are shown. [<a href=\"index.php?action=show&id=".$ID."&detailed=true\">show all<a>]");
//	} else {
//		$hauled_information->addHeader("All hauls are shown. [<a href=\"index.php?action=show&id=".$ID."\">show only recent<a>]");
//	}

}
?>