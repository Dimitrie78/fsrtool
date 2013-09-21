<?php

function updateEval() {
	global $world;
	global $Messages;
	
	$charID     = addslashes($_POST['charID']);
	$comment    = addslashes($_POST['comment']);
	$evaluation = addslashes($_POST['evaluation']);
	$date       = addslashes($_POST['date']);
	$division   = addslashes($_POST['division']);
	
	$_GET['eva'] = $division;
	
	$update = $world->db->doQuery("UPDATE ".$world->_table['snow_evaluation']." SET 
		evaluation = '".$evaluation."',
      	comment    = '".$comment."' 
      WHERE 
      	charID = '".$charID."' 
      AND 
      	date = '".$date."';");
	if(!$update){
		$Messages->addwarning('Ooops:'.mysql_error());
	}
}
	
?>