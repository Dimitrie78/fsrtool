<?php

function addEval() {
	global $world;
	global $Messages;
	
	$charID     = addslashes($_POST['charID']);
	$comment    = addslashes($_POST['comment']);
	$evaluation = addslashes($_POST['evaluation']);
	$division   = addslashes($_POST['division']);
	
	$_GET['eva'] = $division;

	if (!isset($_POST['evaluation'])){
		$Messages->addwarning('Ooops: no Evaluation selected'.$evaluation);
	} else {
		$add = $world->db->exec_query("INSERT INTO ".$world->_table['snow_evaluation']." SET
									charID     = '$charID',
									evaluation = '$evaluation',
									comment    = '$comment',
									date       = NOW();");
		if(!$add){
			#$Messages->addwarning('Ooops:'.mysql_error());
			$Messages->addwarning('Ooops: <br> There is already an entry for this month. You can no longer new to add, edit this month only.<br>You can do this, click the icon on the evaluation');
		}
	}
}
	
?>