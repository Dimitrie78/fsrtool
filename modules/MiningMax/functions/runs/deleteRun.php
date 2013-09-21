<?php

function deleteRun() {

	// We need some globals.
	global $DB;
	global $MySelf;
	global $READONLY;


	// Are we allowed to delete runs?
	if (!$MySelf->canDeleteRun() || $READONLY) {
		makeNotice("Du darfst keine OPs l&ouml;schen!", "error", "forbidden");
		exit;
	}

	// Set the ID.
	$ID = sanitize("$_POST[runid]");
	if (!is_numeric($ID) || $ID < 0) {
		makeNotice("Unbekannte OP. Vielleicht schon gel&ouml;scht!", "error");
		exit;
	}
			confirm("M&ouml;chtest Du die OP #$ID wirklich l&ouml;schen?",$_POST);
			
	// Are we sure? Haben wir schon gefragt
	// confirm("Do you really want to delete run #$ID ?");

	// Get the run in question.
	$res = $DB->query("SELECT * FROM runs WHERE id = '$ID' LIMIT 1");
	$run = $res->fetch_assoc();
	$res->close();

	// is it closed?
	if ("$run[endtime]" < "0") {
		makeNotice("Es k&ouml;nnen nur geschlossene OPs gel&ouml;scht werden!", "error", "L&ouml;schen abgebrochen.", "index.php?action=list", "[cancel]");
		exit;
	}

/*
echo "<pre>";
echo "DELETE FROM runs WHERE id ='$ID'";
echo "DELETE FROM hauled WHERE miningrun='$ID'";
echo "DELETE FROM joinups WHERE runid='$ID'";
echo"DB delete waere nun !";
echo "</pre>";
echo"DB delete waere nun";
exit;
*/

	// delete it.
	$DB->query("DELETE FROM runs WHERE id ='$ID'");

	// Also delete all hauls.
	$DB->query("DELETE FROM hauled WHERE miningrun='$ID'");

	// And joinups.
	$DB->query("DELETE FROM joinups WHERE runid='$ID'");

	makeNotice("Die Mining OP #$ID wurde gel&ouml;scht.", "notice", "Mining Op gel&ouml;scht.", "{$index}?module=MiningMax", "[OK]");
	exit;
}
?>