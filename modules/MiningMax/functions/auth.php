<?php

/*
* Auth()
* This is called at the very moment someone calls the page, and
* then on *every* page thereafter. This may consume some CPU power,
* but it prevents cross-site code injection and other ebil things.
*/

function auth($SUPPLIED_USERID,$eveorder) {
	// Globals
	global $DB;
	global $TIMEMARK;
	global $IGB;

	// Handle possible logouts, activations et all.
//	include_once ('./functions/login/preAuth.php');

	// Trust, INC.
	$alert = getConfig("trustSetting");
			
//			$SUPPLIED_PASSWORD = "DUMMY";

			// Check for validity.

			// Lets check the password.
			if ($eveorder==0) { $MySelf = authVerify($SUPPLIED_USERID);} else { $MySelf = authVerify_charid($SUPPLIED_USERID);}

			if ($MySelf == false) {
				// Lets try again, shall we?
       // Nein				makeLoginPage($SUPPLIED_USERNAME);

			} else
				if ($MySelf->isValid()) {
					// storing the new login time.
					if ($eveorder==0)  { $DB->query("update users set lastlogin = '$TIMEMARK' where id = '$SUPPLIED_USERID'"); } else { $DB->query("update users set lastlogin = '$TIMEMARK' where charid = '$SUPPLIED_USERID'"); }
					// Create the auth-key.
					// createAuthKey($MySelf);
				}
			// We are done here.
			$_SESSION['MySelf'] = base64_encode(serialize($MySelf));

	return ($MySelf);

}
?>