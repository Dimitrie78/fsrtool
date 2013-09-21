<?php

/*
 * This function returns an array of all the ore types that are known
 * and their respective on/off settings.
 */

function getOreSettings($ORE = "") {

	// Quick, but clean :)
	global $DB;

	// Cache the ressource.
	if (!isset ($_SESSION[oretypes])) {
		$res = $DB->query("SELECT * FROM config WHERE name LIKE '%Enabled'");
		while( $row = $res->fetch_assoc() ) {
			$SETTINGS[$row['name']] = $row['value'];
		}
		$res->close();
	} else {
		$SETTINGS = $_SESSION[oretypes];
	}

	// Return the full array or a single 0/1 statement for a single oretype.
	if ("$ORE" != "") {
		// Single ore type
		if ($SETTINGS[$ORE.Enabled]) {
			return (true);
		} else {
			return (false);
		}
	} else {
		// Entire array
		return ($SETTINGS);
	}
}
?>