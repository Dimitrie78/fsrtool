<?php

// gehaultes erz + Aufruf für Metallmengen

$hauler = array();
$erzcount=0;


// Load current payout values.
while ($val = $values->fetchRow()) {
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
calcmetall($hauler[$erzcount]['name'],$tmp_ore);


$erzcount++;

			$gotOre = true; // We set this so we know we have SOME ore.
		}
	}
}




// Math fun.
$taxes = ($totalworth * $row[corpkeeps]) / 100;
$net = $totalworth - $taxes;



?>