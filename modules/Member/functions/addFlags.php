<?php

function updateFlags() {
	global $world;
	if (@$_POST['afk'] == "true") $_POST['afk'] = 1;
	else {
		$_POST['afk']     = 0;
		$_POST['afkText'] = NULL;
	}
	if (@$_POST['investigate'] != "true") $_POST['investigate'] = 0; else $_POST['investigate'] = 1;
	if (@$_POST['posgunner'] != "true")   $_POST['posgunner']   = 0; else $_POST['posgunner']   = 1;
	
	//$_POST['notes'] = nl2br($_POST['notes']);
	//echo $_POST['notes'];

	$division 	 = addslashes($_POST['division']);
	$afk      	 = addslashes($_POST['afk']);
	
	$afkText  	 = stripslashes($_POST['afkText']);
	$afkText  	 = addslashes($afkText);
	#$afkText  	 = utf8_decode($afkText);
	
	$tz       	 = addslashes($_POST['tz']);
	$carrier  	 = addslashes($_POST['carrier']);
	$dread    	 = addslashes($_POST['dread']);
	$investigate = addslashes($_POST['investigate']);
	$posgunner 	 = addslashes($_POST['posgunner']);
	
	$notes 		 = stripslashes($_POST['notes']);
	$notes 		 = addslashes($notes);
	#$notes 	 = utf8_decode($notes);

	$charID 	 = addslashes($_POST['charID']);
	$posd 		 = addslashes($_POST['posd']);
	$exempt 	 = addslashes($_POST['exempt']);
	$legacy 	 = addslashes($_POST['legacy']);
	$probation   = addslashes($_POST['probation']);
	
	if($posd != 'true')      $posd      = 0; else $posd      = 1;
	if($exempt != 'true')    $exempt    = 0; else $exempt    = 1;
	if($legacy != 'true')    $legacy    = 0; else $legacy    = 1;
	if($probation != 'true') $probation = 0; else $probation = 1;

	$query = "UPDATE ".$world->_table['snow_characters']." SET
		division     = '".$division."',
		afk          = '".$afk."',
		afkText      = '".$afkText."',
		tz           = '".$tz."',
		carrier      = '".$carrier."',
		dread        = '".$dread."',
		investigate  = '".$investigate."',
		posgunner    = '".$posgunner."',
		notes        = '".$notes."'
		WHERE charID = '".$charID."'";
	$result = $world->db->exec_query($query);

	#if( $result ) {
		//inserting the data into the job table, making sure that the table
		//will not be full of 0's due to being afk
		$res = $world->db->query("SELECT * FROM ".$world->_table['snow_jobs']." WHERE charID = ".$charID);
		$row = $res->fetch_array();
		if( !empty($row[0]) )	{
			$query = "UPDATE ".$world->_table['snow_jobs']." SET
			   pos       = '".$posd."',
			   exempt    = '".$exempt."',
			   legacy    = '".$legacy."',
			   probation = '".$probation."'
			WHERE charID = '".$charID."'";
			$res = $world->db->exec_query($query);
			if($posd != 1 && $exempt != 1 && $legacy != 1 && $probation != 1) {
				$world->db->exec_query("DELETE FROM ".$world->_table['snow_jobs']." WHERE charID = '".$charID."'");
				#$result = 1;
			}
		}
		elseif( !($posd != 1 && $exempt != 1 && $legacy != 1 && $probation != 1) ) {
			$world->db->exec_query("INSERT INTO ".$world->_table['snow_jobs']." VALUES ('".$charID."', '".$posd."','".$exempt."','".$legacy."','".$probation."')");
			#$result = 1;
		}
		return (1);
	#} else
		#return (0);
	//$main = addslashes($_POST['main2']);
	//if ($_POST['redirect'] == '/search.php') $_POST['redirect'] = '/index.php';
	//if ($_POST['redirect'] == '/snowflake/characters.php') $_POST['redirect'] = "/snowflake/characters.php?id=".$main."";
	//if ($result) header("Location: ".$_POST['redirect']);
}
?>
