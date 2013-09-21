<?php


/*
 * This file reads a value from the database or the session (cached)
 * Its used to quickly get the info we need.
 */

function getConfig($var, $forceFresh = false) {

	// Globals! Yay!
	global $DB;

	// Check that we have a descriptor.
	if ($var == "") {
		makeNotice("Invalid descriptor in getConfig!", "error", "internal Error!");
	}

	// Sanitize it.
	$var = sanitize($var);

	// Check if the value has been cached, unless forced.
	if (!$forceFresh) {
		if (isset ($_SESSION["config_$var"])) {
			return ($_SESSION["config_$var"]);
		}
	}

	// Not cached, get from DB.
	$res = $DB->query("SELECT value FROM config WHERE name='$var' LIMIT 1");
	$setting = $res->fetch_row();
	$res->close();
	// Cache it.
	$_SESSION["config_$var"] = $setting[0];

	// And return it.
//	switch($setting[0]){
//		case("0"):
//			return(false);
//			break;
//		case("false"):
//			return(false);
//			break;
//		case("1"):
//			return(true);
//			break;
//		case("true"):
//			return(true);
//			break;
//		default:
//			return($setting[0]);
//	}
	return($setting[0]);

}
?>