<?php



/*
 * This allows the user to join a run.
 */

function joinRun() {
	// Access the globals.    
	global $DB;
	global $TIMEMARK;
	global $MySelf;
	global $smarty;
	global $SHIPTYPES;
// Grundwerte lesen
	$runid=sanitize($_POST['runid']);
  	if ($runid<1) $runid=sanitize($_POST['runid']);
	numericCheck($runid);
	
    $res = $DB->query("SELECT isOfficial FROM runs WHERE id='$runid' LIMIT 1");
	$isOfficial = $res->fetch_row();
	$res->close();
    $res = $DB->query("SELECT tmec FROM runs WHERE id='$runid' LIMIT 1");
	$SPEZIALOP = $res->fetch_row();
	$res->close();
	$userid = $MySelf->GetID();
	// Welche Seite soll angezeigt werden
  	$SMARTY_JOINOP_STEP=$_POST['step'];
	// Is the run still open?
	if (!miningRunOpen($runid)) {
		$SMARTY_JOINOP_STEP=4;
		$FEHLERMELDUNG="This mining $runid operation has been closed!";
	}
	// Warn the user if he is already in another run.
	$joinedothers = $DB->query("select run from joinups where userid='$userid' and parted IS NULL order by run");
	if ($joinedothers->num_rows > 0) {
		$FEHLERMELDUNG="Du bist schon in einer OP.";
		$SMARTY_JOINOP_STEP=4;
	}	
	// *********** Formular zuerst *********
	if ($SMARTY_JOINOP_STEP<1) {
	 $SMARTY_JOINOP_STEP=2;
	 $FEHLERMELDUNG="Formular zuerst";
	}
	// ********* join  durchführen *********
  if (($SMARTY_JOINOP_STEP==3) ) 
  {
			$SMARTY_LOCATION= ucfirst(getLocationOfRun($runid));
//     	$isOfficial = $DB->getCol("SELECT isOfficial FROM runs WHERE id='$runid' LIMIT 1");
     	
    // echo "isO:".$isOfficial[0].":";
  // Shiptype übernehmen
  $shiptype = sanitize($_POST['shiptype']);
  // if ($_POST['shiptype']<=1)	{	 } else { $shiptype=2; }
	// Get the correct time to join (in case event hasnt started yet)
	$res = $DB->query("SELECT starttime FROM runs WHERE id='$runid' LIMIT 1");
	$startOfRun = $res->fetch_row();
	$res->close();
	if ($startOfRun[0] > $TIMEMARK) {
		$time = $startOfRun[0];
	} else {
		$time = $TIMEMARK;
	}
	// Dont allow him to join the same mining run twice.
	if (userInRun($MySelf->getID(), "$runid") == "none") {
	// Mark user as joined.
  // add him the number of Accounts he had choosen
  $set_number_of_accounts=$_POST['num-of-accounts'];
 
 // 14.03.2011 - $wants_charity=$_POST['wants-charity'];
 // charity ausgebaut, stattdessen:
 $wants_charity = "Nein";
 // 14.03.2011 ENDE
 
 // DEBUG echo "<br>W1:".$wants_charity;
// Check if he has already been in this OP

// War er überhaupt mal da dann ist COUNT>1 
$res = $DB->query("select count(charity) from joinups where userid='$userid' and run='$runid'");
$wasalreadyhere = $res->fetch_row();
$res->close();
// ist der Wert = 0 dann ist alles ok
// DEBUG  echo "<br>War schon ".$wasalreadyhere[0]." mal in der OP";

 $res = $DB->query("select charity from joinups where userid='$userid' and run='$runid'");
$wasalreadyhere_with_this_Charityflag = $res->fetch_row();
$res->close();

$MAXMESSAGE="";
// $wasalreadyhere_with_this_Charityflag=0;
// Charity ist 1 und schon mal dagewesen = yes setzeh

// if ($isOfficial[0]==1) { $wants_charity="Nein"; }
 
 // if (($wasalreadyhere[0]==0) and ($wasalreadyhere_with_this_Charityflag[0]==1)) { $wants_charity="Ja"; }
// schon mal dagewesen 
  if (($wasalreadyhere[0]>0) and ($wasalreadyhere_with_this_Charityflag[0]==0)) { $wants_charity="Nein"; $MAXMESSAGE="<br><br><b>Charity wurde wieder ausgeschaltet!</b>";	 	}
  if (($wasalreadyhere[0]>0) and ($wasalreadyhere_with_this_Charityflag[0]==1)) { $wants_charity="Ja"; $MAXMESSAGE="<br><br>Charity wurde wieder eingeschaltet!"; }
/* Debug
echo "<br>Meldung:";
 echo "<br>wasalreadyhere_with_this_charity_flag:".$wasalreadyhere_with_this_Charityflag[0];
 echo "<br>wasalreadyhere:".$wasalreadyhere[0];
 echo "<br>";
 print_r($wasalreadyhere_with_this_Charityflag);
 echo "<br>W2:".$wants_charity;
 echo "<br>ifOfficial:".$isOfficial[0];
echo "<pre>";
print_r($_POST);
echo "</pre>";
 // exit;
*/

switch ($wants_charity) {
	case "Ja" :
	 if ($set_number_of_accounts>1) { $MAXMESSAGE.="<br>Anzahl der Accounts wurde auf 1 gesetzt, da Charity aktiv ist."; }
	 $set_wants_charity=1;
	 $set_number_of_accounts=1;
	 break;
	case "Nein" :
	 $set_wants_charity="0";
	 break;
	default :
	 $set_wants_charity=0;
	 break;
}

	
if (($set_number_of_accounts<1) or ($set_number_of_accounts>10)) {$set_number_of_accounts=1; }
if ($isOfficial[0]==1) {$set_number_of_accounts=1; }

for ($count_accounts=1;$count < $set_number_of_accounts;$count++)
  {
	
/*		echo "userid:".$userid."<br>";
		echo "runid:".$runid."<br>";
		echo "time:".$time."<br>";
		echo "shiptype:".$shiptype."<br>";
		echo "set_wants_charity:".$set_wants_charity."<br>";
*/	
		$stmt = $DB->prepare("insert into joinups (userid, run, joined, shiptype,charity) values (?,?,?,?,?)");
		$stmt->bind_param('sssss',$userid, $runid, $time, $shiptype, $set_wants_charity);
		
		/* execute prepared statement */
		$stmt->execute(); 
 }
    $SMARTY_JOINOP_STEP=4;
	 $FEHLERMELDUNG="Du bist der OP mit $set_number_of_accounts Schiffe(n) beigetreten.";
	 
	 if ($isOfficial[0]==1) { $FEHLERMELDUNG.="<br><br>F&uuml;r die Corp: Ja. <br><br>Es ist ein Corpmining."; } else { $FEHLERMELDUNG.="<br><br>Dein Charityflag sagt:".$wants_charity."."; }


} else {
		// Hes already in that run.
	 $SMARTY_JOINOP_STEP=4;
	 $FEHLERMELDUNG="Du bist schon in einer Mining OP.";
	 // Fehlermeldung 
 	}

} 

// AUSGABE

if ($SMARTY_JOINOP_STEP==4)
	{
		makeNotice("$FEHLERMELDUNG", "notice", "Information", MBPATH."&action=show&id=$runid", "[OK]");
	}
	else
	{
  	$smarty->assign('date', date('d.m.Y H:i'));
    $smarty->assign('SMARTY_JOINOP_STEP',$SMARTY_JOINOP_STEP);
    $smarty->assign('FEHLERMELDUNG',$FEHLERMELDUNG);
    $smarty->assign('isOfficial',$isOfficial[0]);
    $smarty->assign('SPEZIALOP',$SPEZIALOP[0]);
    $smarty->assign('runid',$runid);
	$smarty->assign('shiptypes', $SHIPTYPES);
		$smarty->display("../modules/MiningMax/templates/joinop.tpl");
	}
	

}
?>