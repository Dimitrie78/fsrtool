<?php


function authVerify($userID, $trust = false) {
	global $DB;
	global $TIMEMARK;
	$userDS = $DB->query("select * from users where id='".$userID."' AND deleted='0' limit 1");
	// No one found
	if ($userDS->num_rows == 0) {
    makeNotice("Du hast noch keinen MB Account.<br>\"$userID\" is deine ID", "error");
		return (false);
	} else {
		$user = $userDS->fetch_assoc();
		$MyAccount = new userMB($user, $TIMEMARK);
		return ($MyAccount);
	}
	return (false);
}

function authVerify_charid($userID, $trust = false) {
	global $DB;
	global $TIMEMARK;
	$userDS = $DB->query("select * from users where charID='".$userID."' AND deleted='0' limit 1");
	// No one found
	if ($userDS->num_rows == 0) {
    makeNotice("Du hast noch keinen MB Account.<br>\"$userID\" is deine ID", "error");
		return (false);
	} else {
		$user = $userDS->fetch_assoc();
		$MyAccount = new userMB($user, $TIMEMARK);
		return ($MyAccount);
	}
	return (false);
}

?>