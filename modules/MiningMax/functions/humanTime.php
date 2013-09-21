<?php


function humanTime($mode, $playdoo = false) {

	/*
	 * Mode is either toUnix or toHuman.
	 * toUnix converts the given array to an UNIX timestamp,.
	 * toHuman returns an array with split up time.
	 */

	switch ($mode) {

		case ("toUnix") :
			// To convert something back, we need an array.
			if (!is_array($playdoo)) {
				makeNotice("Internal Error: given argument is not an array in humanTime.", "error", "Internal Error");
			}

			// Check for validity.
			numericCheck($playdoo[day]);
			numericCheck($playdoo[month]);
			numericCheck($playdoo[year]);
			numericCheck($playdoo[hour]);
			numericCheck($playdoo[minute]); 

			// Assemble the time.
			$humantime = $playdoo[day] . "." . $playdoo[month] . "." . $playdoo[year] . " " . $playdoo[hour] . ":" . $playdoo[minute];

			// Convert it.
			$timestamp = date("U", strtotime($humantime));

			// Check and return.
			if ($timestamp >= 0) {
				// Its greater of equal zero, so we were successful.
				return ($timestamp);
			} else {
				// Ugh, something did not go right. False, FALSE!
				return (false);
			}
			break;

		case ("toHuman") :

			// We need a VALID timestamp.
			numericCheck($playdoo, 0);

			// Assemble and return.
			return (array (
				"day"    => date("d", $playdoo), 
				"month"  => date("m", $playdoo), 
				"year"   => date("Y", $playdoo), 
				"hour"   => date("H", $playdoo), 
				"minute" => date("i", $playdoo)));
			break;
	}
}
?>