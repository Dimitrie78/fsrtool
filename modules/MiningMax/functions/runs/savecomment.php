<?php
// Kommentarfeld speichern
function saveComment() {
	global $MySelf;
	// Check the runID for validity.


		$ID = $_POST[runid];
		$ID=sanitize($ID);
		$comment=sanitize($_POST[comment]);
    //  $comment = ereg_replace("[^\s\nA-Za-z0-9,.,--:!?=()/[üöäÜÖÄß]/ ]", " ", $comment );
// $comment = ereg_replace("[^\s\nA-Za-z0-9,.,--:!?=() ]", " ",$comment );
            // $comment = htmlspecialchars($comment);
            $comment = stripslashes($comment); 




	// Update the database!
	  global $DB;
  $DB->query("UPDATE runs SET comment='$comment' WHERE id='$ID' LIMIT 1");
	// Success?
	if ($DB->affectedRows != 1) {
		header("Location: index.php?module=MiningMax&action=show&id=$ID");
	} else {
		makeNotice("Kann Kommentar nicht speichern.", "warning", "Cannot write to database.");
	}

}
?>
