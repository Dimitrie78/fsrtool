<?php

/*
* Details eines Runs anzeigen
* listRun()
* will return the run id requested in the GET array and
* print a nice, friendly html page.
*/
/* Berechnen der Metallmengen für ein Erz */
function calcmetall($name,$menge)
{
// if ($name <>"Veldspar") return(0);
	global $DB;
  global $S_Tritanium,$S_Pyerite,$S_Mexallon,$S_Isogen,$S_Megacyte,$S_Zydrine,$S_Nocxium,$S_Morphite;

	$menge=str_replace(",","",$menge); 	$menge=str_replace(".","",$menge);
 $line = $DB->query("SELECT * FROM metallwert WHERE metall='$name' LIMIT 1");
 $mengen=$line->fetchRow();
 $numrows=$line->numRows();
 // Kein Erz dieser Art geminert, raus hier
 if ($numrows==0) return;
// print_r($mengen);
// echo "Name:".$name." Menge:".$menge." Batch:".$mengen['batch']."<br>";
$S_Tritanium=$S_Tritanium+($mengen['Tritanium']*$menge/$mengen['batch']);
$S_Pyerite=$S_Pyerite+($mengen['Pyerite']*$menge/$mengen['batch']);
$S_Mexallon=$S_Mexallon+($mengen['Mexallon']*$menge/$mengen['batch']);
$S_Isogen=$S_Isogen+($mengen['Isogen']*$menge/$mengen['batch']);
$S_Megacyte=$S_Megacyte+($mengen['Megacyte']*$menge/$mengen['batch']);
$S_Zydrine=$S_Zydrine+($mengen['Zydrine']*$menge/$mengen['batch']);
$S_Nocxium=$S_Nocxium+($mengen['Nocxium']*$menge/$mengen['batch']);
$S_Morphite=$S_Morphite+($mengen['Morphite']*$menge/$mengen['batch']);
/*
echo "Tri:".$S_Tritanium."<br>";
echo "Pyr:".$S_Pyerite."<br>";
echo "Mex:".$S_Mexallon."<br>";
echo "Iso:".$S_Isogen."<br>";
echo "Meg:".$S_Megacyte."<br>";
echo "Zyd:".$S_Zydrine."<br>";
echo "Noc:".$S_Nocxium."<br>";
echo "Mor:".$S_Morphite."<br>";
*/

// echo "<br><pre>";
// print_r($mengen);
// echo "</pre>";
}

function listRun() {

	/*
	 * STEP ZERO:
	 * Import variables, and define needed things.
	 */
	global $DB; 	   // Database connection
	global $ORENAMES;  // A list of all the orenames
	global $DBORE; 	   // An array of db friendly orenames
	global $TIMEMARK;  // The "current" timestamp
	global $MySelf;    // Ourself, and along with that, the permissions.
	global $SHIPTYPES; // We dont want numbers to memorize.
	global $smarty;    // Smarty Class
  // Spezial Ops
  global $S_Tritanium,$S_Pyerite,$S_Mexallon,$S_Isogen,$S_Megacyte,$S_Zydrine,$S_Nocxium,$S_Morphite;

  $S_Tritanium=0;
  $S_Pyerite=0;
  $S_Mexallon=0;
  $S_Isogen=0;
  $S_Megacyte=0;
  $S_Zydrine=0;
  $S_Nocxium=0;
  $S_Morphite=0;


	$userID = $MySelf->getID(); // Shortcut: Assign the UserID to userID.
	
	/* STEP ONE: 	 * Load the database row into $row. This requires us to look up the minigrun ID first.	 */
	include ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/listRun_inc_step1.php');

	/* STEP TWO	 * Gather some vital information.	 */
	include ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/listRun_inc_step2.php');
	$smarty->assign('general', $general);

	/* 	 * STEP THREE - brauchen wir nicht  Create a table with the System Information. */
	//include (MODULE_DIR.'functions/runs/listRun_inc_step3.php');

	/*  STEP FOUR The Join and Part log. 	 */
	include ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/listRun_inc_step4.php');
	$smarty->assign('Join', $Joinups);
	//echo '<pre>'; print_r($Joinups); echo '</pre>';

	/* Was wurde alles erminert
	 * STEP FIVE - gehaultes Erz / Eis The Ressources Information Table	 */
	include ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/listRun_inc_step5.php');
  $smarty->assign('oretotalworth', $totalworth);
  $smarty->assign('oretotaltax', $taxes);
  $smarty->assign('oretotalnetworth', $net);
	$smarty->assign('hauler', $hauler);
	// ob derjenige kicken kann
	$smarty->assign('icankick',$icankick);
	
	// Spezial OPS, Metalle
if ($general['SPEZIALOP']==99)
{	
	$smarty->assign('S_Tritanium',$S_Tritanium);
	$smarty->assign('S_Pyerite',$S_Pyerite);
	$smarty->assign('S_Mexallon',$S_Mexallon);
	$smarty->assign('S_Isogen',$S_Isogen);
	$smarty->assign('S_Megacyte',$S_Megacyte);
	$smarty->assign('S_Zydrine',$S_Zydrine);
	$smarty->assign('S_Nocxium',$S_Nocxium);
	$smarty->assign('S_Morphite',$S_Morphite);
	if ($general['Tritanium']>0) { $smarty->assign('SP_Tritanium',$S_Tritanium/$general['Tritanium']*100); }
	if ($general['Pyerite']>0) { $smarty->assign('SP_Pyerite',$S_Pyerite/$general['Pyerite']*100);}
	if ($general['Mexallon']>0) { $smarty->assign('SP_Mexallon',$S_Mexallon/$general['Mexallon']*100);}
	if ($general['Isogen']>0) { $smarty->assign('SP_Isogen',$S_Isogen/$general['Isogen']*100);}
	if ($general['Nocxium']>0) { $smarty->assign('SP_Nocxium',$S_Nocxium/$general['Nocxium']*100);}
	if ($general['Megacyte']>0) { $smarty->assign('SP_Megacyte',$S_Megacyte/$general['Megacyte']*100);}
	if ($general['Zydrine']>0) { $smarty->assign('SP_Zydrine',$S_Zydrine/$general['Zydrine']*100);}
	if ($general['Morphite']>0) { $smarty->assign('SP_Morphite',$S_Zydrine/$general['Morphite']*100);}
	
}
  
	
	$smarty->assign('transportcount',$erzcount);
//	 echo '<pre>'; print_r($hauler); echo '</pre>';

		
	/* STEP SIX - brauchen wir nicht Gather all cans that belong to this miningrun.  */
	//include (MODULE_DIR.'functions/runs/listRun_inc_step6.php');

	/* STEP SEVEN - brauchen wir nicht unbedingt
	 - Show the transport manifest - War hat was gehaulert */
	include ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/listRun_inc_step7.php');
	$smarty->assign('transport', $transports);
 //echo '<pre>'; print_r($transports); echo '</pre>';


	/* 	 * STEP EIGHT Calculate the payout.	 */
	include ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/listRun_inc_step8.php');
	$smarty->assign('Payinfo', $Payouts);
	
//	echo "S:".date("d.m.Y H:i",$row['starttime'])." T:".date("d.m.Y H:i",$TIMEMARK);
// liegt der OP Start in der Zukunft     	
	if  ($row['starttime'] >=$TIMEMARK)  {$isrunning=0;} else {$isrunning=1;}
	$smarty->assign('isrunning', $isrunning);
	
	// $smarty->assign('comment', $row['comment']);
	$smarty->assign('PayinfoTotal', $totalPayout);
	$smarty->assign('PayinfoTotalPercent', $totalPercent);

}
?>