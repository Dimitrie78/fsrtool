<?php

function getMondaysAndSundays($offset){
// this week
	if(date('w',$offset) == 1) { $mas['monday'] = date('Y-m-d',$offset); } else { $mas['monday'] = date('Y-m-d',strtotime("last Monday",$offset)); 	}
	if(date('w',$offset) == 6) { $mas['sunday'] = date('Y-m-d',$offset); } else { $mas['sunday'] = date('Y-m-d',strtotime("next Sunday",$offset)); 	}
// last week
	if(date('w',$offset) == 1) { $mas['lastmonday'] = date('Y-m-d',strtotime('-1 week',$offset)); } else { $mas['lastmonday'] = date('Y-m-d',strtotime('-1 week', strtotime(date('Y-m-d',strtotime("last Monday",$offset))))); 	}
	if(date('w') == 6) { 		 $mas['lastsunday'] = date('Y-m-d',strtotime('-1 week',$offset)); } else { $mas['lastsunday'] = date('Y-m-d',strtotime("last Sunday",$offset)); 	}
	return $mas;
}

function highscore() {
	global $MySelf;
	global $DB;
	global $smarty;

	$thisweek = strtotime(date('Y-m-d'));
// Offset übergeben? Wenn ja, übernehmen, sonst -> trash
	if ($_POST['offset']) {  		$offset = sanitize($_POST['offset']);  	$RANGE  = "currentweek"; 	} else {  		$offset= strtotime(date('Y-m-d')); 	}
// Offset2 übergeben?
	if ($_POST['offset2']) {  		$offset2 = sanitize($_POST['offset2']);  	} else {  		$offset2= strtotime(date('Y-m-d')+604799); 	}
// Null check
	if ($offset<=0) { 		$offset= strtotime(date('Y-m-d')); 	} 
	if ($offset2<=0) { 		$offset2= strtotime(date('Y-m-d')+604799); 	}

// parameter
	if ((!$_POST[modus]=="month") or ($_POST[modus]=="week")){
	// den Montag herausfinden
		$mas = getMondaysAndSundays($offset);
		$RANGE_START     = $mas['monday'];
		$RANGE_SQL_START = strtotime($RANGE_START." 00:00");
		$RANGE_SQL_ENDE  = $RANGE_SQL_START+604799;
	}else{
	// Wert uebernommen (Monatsanzeige)
		$RANGE_SQL_START = $offset;
		$RANGE_SQL_ENDE  = $offset2;
	}	  
// einlesen aller User im RANGE Zeitraum:
	$res = $DB->query("SELECT userid FROM joinups WHERE parted >1 and joined>='".$RANGE_SQL_START."' and parted<='".$RANGE_SQL_ENDE."' and parted>1 group by userid");
	$total_userids = array();
	while($row = $res->fetch_row()) {
		$total_userids[] = $row[0];
	}
	$res->close();
	$comma_separated_userids = implode(",", $total_userids);
	#echo $comma_separated_userids.'<br>';
	#echo $RANGE_SQL_START.'<br>'; echo $RANGE_SQL_ENDE.'<br>';
	if ($timeallres = $DB->query("SELECT Sum(joinups.parted - joinups.joined) AS summe, joinups.userid
							FROM joinups 
							INNER JOIN runs ON joinups.run = runs.id
							WHERE (joinups.parted > 1 AND
								  runs.starttime >= '".$RANGE_SQL_START."' AND
								  runs.endtime   <= '".$RANGE_SQL_ENDE ."' AND
								  runs.isOfficial = 0 AND
								  joinups.charity = 1)
								  OR
								  (joinups.parted  > 1 AND
								  runs.starttime  >= '".$RANGE_SQL_START."' AND
								  runs.endtime    <= '".$RANGE_SQL_ENDE ."' AND
								  runs.isOfficial  = 1)
							GROUP BY joinups.userid
							HAVING joinups.userid IN (".$comma_separated_userids.")
							ORDER BY summe DESC;")) {
		while( $timeall[] = $timeallres->fetch_assoc() );
		$timeallres->close();
		
		$i=0;
		foreach ($timeall as $time) {
			if ($time['summe']>0) {
				$i++;
				$highscore[$i]['position'] = $i;
				$highscore[$i]['zeit']     = numberToString($time['summe']);
				//$highscore[$i]['userid']   = $time['userid'];
				$highscore[$i]['name']     = ucfirst(idToUsername($time['userid']));
			}
		}
	}
// Monatsliste
	$y=date('y');$m=date('n');$d=date('j');
	for ($az=13; $az>-1;$az--){
		$st=mktime(0,0,1,$m-$az,1,$y);
		$ed=mktime(23,59,59,$m-$az+1,1-1,$y);
		$text=date("m/y",$st);
		$HIGHSCORE_MONATSARRAY[$az]['offset']=$st;
		$HIGHSCORE_MONATSARRAY[$az]['offset2']=$ed;
		$HIGHSCORE_MONATSARRAY[$az]['mtext']=$text;
	}


	$smarty->assign('HIGHSCORE_STARTDATE', 	date('d.m.Y',$RANGE_SQL_START) );
	$smarty->assign('HIGHSCORE_ENDDATE',   	date('d.m.Y',$RANGE_SQL_ENDE)  );
	$smarty->assign('Highscore',           	$highscore);
	$smarty->assign('offset',              	$offset);
	$smarty->assign('thisweek',             $thisweek);
	$smarty->assign('HIGHSCORE_MONATSARRAY', $HIGHSCORE_MONATSARRAY);

}
?>
