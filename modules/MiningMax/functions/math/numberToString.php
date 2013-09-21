<?php

function numberToString($id) {

	// We need a number. GOE0.
	if (!number_format($id, 0)) {
		$BT = nl2br(print_r(debug_backtrace(), true));		
		makeNotice("Thats not a real timeindex in numberToString!<br><br>$BT", "warning", "Err..");
	}

	if ($id < 0) {
		return (false);
	}

	if ($id >= 86400) {
		$days = floor($id / 86400);
		$thingies++;
	}

	if ($id >= 3600) {
		$hours = floor(($id % 86400) / 3600);
		$thingies++;
	}

	if ($id >= 60) {
		$minutes = floor((($id % 86400) % 3600) / 60);
		$thingies++;
	}

	$seconds = number_format(((($id % 86400) % 3600) % 60), 0);

	if ($days > 1) {
		$days = "$days days";
	}
	elseif ($days == 1) {
		$days = "$days day";
	}

	if ($hours > 1) {
		$hours = "$hours Stunden";
	}
	elseif ($hours == 1) {
		$hours = "$hours Stunde";
	}
	elseif ($hours == 0 && $days) {
		$hours = "0 Stunden";
	}

	if ($minutes > 1) {
		$minutes = "$minutes minuten";
	}
	elseif ($minutes == 1) {
		$minutes = "$minutes minute";
	}
	elseif ($minutes == 0 && $hours) {
		$minutes = "0 minuten";
	}

	if ($seconds > 1) {
		$seconds = "$seconds sekunden";
	}
	elseif ($seconds == 1) {
		$seconds = "$seconds sekunde";
	}
	elseif ($seconds == 0 && $minutes) {
		$seconds = "0 sekunden";
	}

	if ($days) {
		$string .= $days . numberToString_internal($thingies);
		$thingies--;
	}

	if ($hours) {
		$string .= $hours . numberToString_internal($thingies);
		$thingies--;
	}

	if ($minutes) {
		$string .= $minutes . numberToString_internal($thingies);
		$thingies--;
	}

	if ($seconds) {
		$string .= $seconds . numberToString_internal($thingies);
		$thingies--;
	}

	return ($string);

}

function numberToString_internal($thingies) {

	switch ($thingies) {
		case (0) :
			$string .= ".";
			break;

		case (1) :
			$string .= " and ";
			break;

		default :
			$string .= ", ";
			break;
	}
	return ($string);

}
?>