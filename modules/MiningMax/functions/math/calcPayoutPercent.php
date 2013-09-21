<?php

function calcPayoutPercent($run, $pilot) {

	// Sanity check.
	numericCheck($run, 0);
	numericCheck($pilot, 0);

	// Globals.
	global $DB;
	global $TIMEMARK;

	// Lets get the total jointime.
	$joinTimes = $DB->query("SELECT * FROM joinups WHERE userid='$pilot' AND run='$run'");

	// We got any results? If not, that pilot never attended!
	if ($joinTimes->num_rows == 0) {
		return (0);
	}

	// Assemble the time.
	while ($joinTime = $joinTimes->fetch_assoc()) {
		// Joinup time.
		$joinup = $joinTime[joined];

		// Part time., handle still-active folks.
		if ($joinTime[parted]) {
			// Pilot left.
			$left = $joinTime[parted];
		} else {
			// Pilot still active: Set current time as part-time.
			$left = $TIMEMARK;
		}

		// Add his active seconds to a batch.
		$totalSeconds = $totalSeconds + ($left - $joinup);
	}

	// Get the run's start and endtime.
	$Run_DS = $DB->query("SELECT starttime, endtime FROM runs WHERE id='$run' LIMIT 1");
	$Run = $Run_DS->fetch_assoc();

	// Endtime, handle still-open cases.
	$run_starttime = $Run[starttime];
	if ($Run[endtime] > 0) {
		$run_endtime = $Run[endtime];
	} else {
		$run_endtime = $TIMEMARK;
	}

	// runSeconds is the total number of seconds the run is open.
	$runSeconds = $run_endtime - $run_starttime;
	$timePercent = 100 / ($runSeconds / $totalSeconds);

	// How many people joined this run?
	$res = $DB->query("SELECT COUNT(DISTINCT userid) FROM joinups AS count WHERE run='$run'");
	$totalPilots = $res->fetch_row();
	$res->close();
	$totalPilots = $totalPilots[0];

	$myPart = (100 / $totalPilots) * ($timePercent / 100);

	// Return the Percentage.
	return ($myPart);
}


// PROJEKT
function calcPayoutPercentProject($run, $pilot) {

	// Sanity check.
	// numericCheck($run, 0);
	numericCheck($pilot, 0);

	// Globals.
	global $DB;
	global $TIMEMARK;

	// Lets get the total jointime.
	$joinTimes = $DB->query("SELECT * FROM joinups WHERE userid='$pilot' AND run in (".$run.")");

	// We got any results? If not, that pilot never attended!
	if ($joinTimes->num_rows == 0) {
		return (0);
	}

	// Assemble the time.
	while ($joinTime = $joinTimes->fetch_assoc()) {
		// Joinup time.
		$joinup = $joinTime[joined];

		// Part time., handle still-active folks.
		if ($joinTime[parted]) {
			// Pilot left.
			$left = $joinTime[parted];
		} else {
			// Pilot still active: Set current time as part-time.
			$left = $TIMEMARK;
		}

		// Add his active seconds to a batch.
		$totalSeconds = $totalSeconds + ($left - $joinup);
	}

	// Get the run's start and endtime.
	// ok ein RUN $Run_DS = $DB->query("SELECT starttime, endtime FROM runs WHERE id in (".$run.") LIMIT 1");
	$Run_DS = $DB->query("SELECT sum(endtime-starttime) FROM runs WHERE id in (".$run.")");
	$Run = $Run_DS->fetch_assoc();
//  print_r($Run);
  $run_starttime=1;
  $run_endtime=$Run['sum(endtime-starttime)']+1;
//  echo $run_starttime."-".$run_endtime;

	// runSeconds is the total number of seconds the run is open.
	$runSeconds = $run_endtime - $run_starttime;
	$timePercent = 100 / ($runSeconds / $totalSeconds);

	// How many people joined this run?
	$res = $DB->query("SELECT COUNT(DISTINCT userid) FROM joinups AS count WHERE run in (".$run.")");
	$totalPilots = $res->fetch_row();
	$res->close();
	$totalPilots = $totalPilots[0];

	$myPart = (100 / $totalPilots) * ($timePercent / 100);

	// Return the Percentage.
	return ($myPart);
}
?>