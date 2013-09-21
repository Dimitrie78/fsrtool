<?php



/*
* addNewUser();
* This adds a new user to the database.
*/
function addNewUser($charID,$USERNAME) {
	// globals
	global $DB;
	global $MySelf;

// echo $charID.$Username;
	
	// Lets prevent adding multiple users with the same name.
	if (userExists($NEW_USER) >= 1) {
		makeNotice("User already exists!", "error", "Duplicate User", "index.php?action=MiningMax", "[Cancel]");
	}


	// Inser the new user into the database!

// echo "hier ist der insert $ID $USERNAME ";
	  

	$DB->query("insert into users (charID, username, password, email, addedby, confirmed, canLogin, canJoinRun, canCreateRun, canCloseRun, canAddHaul, isOfficial) 
	values ($charID, '$USERNAME', 'FILLER', 'root@localhost',2,1,0,1,1,1,1,1)");
	
	// Were we successfull?
	if ($DB->affected_rows == 0) {
		makeNotice("Could not create user!", "error");
	} 
	// else {
//		makeNotice("User added and confirmed.", "notice", "Account created", "index.php?action=editusers");
//	}

}
?>