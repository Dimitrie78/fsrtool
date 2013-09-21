<?php

/*
* userExists (user)
* This takes an username as argument and returns an int (accesslevel)
*/
function userExists($user_unclean) {

	// We need the database.
	global $DB;

	// Cleanup.
	$user = sanitize($user_unclean);

	// Query the database.
	$res = $DB->query("select COUNT(username) from users where username = '$user' order by id desc limit 1");
	$count = $res->fetch_row();
	$res->close();
	// Return Nr of Users. (0/1)
	return ($count[0]);
}


function userExists_with_CharID($user_unclean) 
{

	// We need the database.
	global $DB;

	// Cleanup.
	$user = sanitize($user_unclean);

	// Query the database.
	$res = $DB->query("select COUNT(username) from users where charID  = '$user' order by id desc limit 1");
	$count = $res->fetch_row();
	$res->close();
	// Return Nr of Users. (0/1)
	return ($count[0]);
}
?>