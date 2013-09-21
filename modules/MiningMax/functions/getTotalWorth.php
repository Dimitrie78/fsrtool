<?php


/*
 * This function takes an int and queries the database.
 * It returns the total worth of isk for that mining run.
 */

function getTotalWorth($id, $net = false) {
	// First, we need the globals
	global $DB;
	global $ORENAMES;
	global $DBORE;
  
  
	// Is $id truly an integer?
	numericCheck($id);

	// we need some results.
	$runs = $DB->query("select * from runs where id = '$id' limit 1");
	$run = $runs->fetch_assoc();
	
	if ($runs->num_rows != 1) {
		
		echo " existiert nicht ".$id."!";
		makeNotice("Specified run not found, or does no longer exist!", "warning", "Internal Error");
	}

	// Load the appropiate values.
	if ($run[oreGlue] <= 0) {
		$orevalues = $DB->query("select * from orevalues order by id desc limit 1");
	} else {
		$orevalues = $DB->query("select * from orevalues where id='" . $run[oreGlue] . "' limit 1");
	}
	$row = $orevalues->fetch_assoc();

	// Create variables according to ore names, fill them with price info.
	foreach ($DBORE as $ORE) {
		$oreValue[$ORE] = $row[$ORE . Worth];
	}

	// Now multiply each ore amount with raw value, add it to total value.
	foreach ($DBORE as $ORE) {
		$value = $value + ($run[$ORE] * $oreValue[$ORE]);
	}

	// Deduct corp tax.
	if ($net) {
		$res = $DB->query("SELECT corpkeeps FROM runs WHERE id='$id'");
		$CorpTax = $res->fetch_row();
		$res->close();
		$taxes = ($value * $CorpTax[0]) / 100;
		$value = $value - $taxes;
	}

	return ($value);
}
?>