<?php
defined('ACTIVE_MODULE') or die();


define('MBPATH', URL.'/index.php?module='.ACTIVE_MODULE);

// 
#$database = new MineralsDatabase();
// $miningbuddyDB = new MiningbuddyDB();
// DB Connect mit den MB Routinen:
$DB=makeDB();
// Time stuff
// ALT aus MB $TIMEMARK = date(U) - (getConfig("timeOffset") * 60 * 60);
// Besser:
$TIMEMARK = time() - date('Z', time());
// echo "TIMEMARK:".$TIMEMARK." date:".date("d.m.Y H:i",$TIMEMARK);
// echo "ccptime:".$ccptime." date:".date("d.m.Y H:i",$ccptime);

// eveorder _eveorderDB verbindung
# $databaseEVEORDER = new Database();

// $database = new MemberDatabase();
// ..... $eveorderDB = new EveorderDatabase();

//$world = new Minerals_world();
// $memberDB = new MemberDB();

if ($User->MB_id<1) {
	// prüfen ob der User mit der eveorder.charid hier ist
	if (userExists_with_CharID($User->charID)>0) 
	{ 
		// echo "ja existiert mit CharID"; 
		$MySelf = auth($User->charID,1);
	}
	else
	{
//	makeNotice("Du hast noch keinen MB Account.<br>Wir legen Dir einen Account an. Einen Moment. Gut, bitte klicke OK.", "Information.");
//  echo "Addnew User würde aufgerufen werden!";
//	echo "<pre>";
//	print_r($User);
 	$charID=$User->charID;
  //	$USERNAME=$User->uname;
  // echo "ID:".$charID;
  // echo "NA:".$USERNAME;
//	echo "</pre>";
	 addNewUser($User->charID,$User->username);	
//	echo "zu der CharID im MB:".$charID;
	$MySelf = auth($User->charID,1);
	}
}
else
{
  $MySelf = auth($User->MB_id,0);
}
// echo "Name:".$MySelf->getUsername();
// echo '<pre>'; print_r($MySelf); echo '</pre>';
// DIMITRIE:  Warum ist das hier -1 ?
// echo "Die ID im MB ist:".$MySelf->getID();

// echo "ok";

$mineralprices = $world->minerals_get_mineralprices_new();
foreach($mineralprices as $mineral){
  $dates[] = $mineral['Date'];
}

$smarty->assign('MySelf', $MySelf);
$smarty->assign("mineralprices",	$mineralprices);
$smarty->assign("old_minprices",	$world->minerals_get_mineralprices_new(2));
$smarty->assign("date", min($dates));
$smarty->assign("action", $action);
$smarty->assign("title", 'MiningMax');

switch ($action)
{
// ************** einer OP beitreten joinop ************ 	
	case "joinop":
	//	print_r ($_POST);
	joinRun();
	exit;

	case "leaveop":
	// print_r ($_POST);
	leaveRun($_POST['runid']);
	exit;
	
// ************** Highscore ************ 	
	case "highscore":
	highscore();
	#$smarty->assign("a_confirms", $Messages->getconfirms());
	#$smarty->assign("a_warnings", $Messages->getwarnings());
	$smarty->display( TPL_DIR . 'highscore.tpl');
	exit;

// ************** eine neue OP erstellen ****************************
	case "newrun":
		makeNewRun();
		#$smarty->assign("a_confirms", $Messages->getconfirms());
		#$smarty->assign("a_warnings", $Messages->getwarnings());
		 $smarty->display( TPL_DIR . 'makeNewRun.tpl');
	break;
	
	case "addrun":
		//echo '<pre>'; print_r($_POST); echo '</pre>';
		addRun();
	break;
	
  case "delrun":
//		$Messages->addconfirm("Op l&ouml;schen?");
		deleteRun();
		// Mit Dialog oben
		// $smarty->assign("a_confirms", $Messages->getconfirms()); // aus default
		//$smarty->assign("a_warnings", $Messages->getwarnings());// aus default
		// RunsListing();// aus default 
			break;

  case "payout":
   togglePay();
  break;

//
  case "comment":

   saveComment();

// ************** eine  OP schliessen ****************************
	case "endrun":
		endRun();
	break;	
// ************** Mineralien Haulern ****************************
	case "addhaulpage":
		addhaulpage();
		#$smarty->assign("a_confirms", $Messages->getconfirms());
		#$smarty->assign("a_warnings", $Messages->getwarnings());
// 		$smarty->display( TPL_DIR . 'addhaulpage.tpl');
// echo date("d.m.Y H:i",$TIMEMARK+86400);
		if ( ($TIMEMARK>=1278756900) and ($TIMEMARK<=1278756900+76400))
		{
		$smarty->display( TPL_DIR . 'addhaulpage_MARATHON.tpl');
		}
		else
    {
		$smarty->display( TPL_DIR . 'addhaulpage.tpl');
    }
	break;
	case "addhaul":
		addHaul();
	break;
// ************** Kickban aus Run *****************************************
	case "kickban":
		kick();
	break;
// ************** Show Runs *****************************************
	case "show":
		if (isset($_GET['id'])) {
			listRun();
		  $runid=$_GET['id'];
		  // if (userInRun($MySelf->getID(), "$runid") == "none") { $zeige_beitreten=1; } else { $zeige_verlassen=1; }
			$smarty->assign("userisinop", userInRun($MySelf->getID(), "$runid")   );
			#$smarty->assign("a_confirms", $Messages->getconfirms());
			#$smarty->assign("a_warnings", $Messages->getwarnings());
			$smarty->display( TPL_DIR . 'showRun.tpl');
		} else {
			header("Location: ".URL_INDEX .'?module='.ACTIVE_MODULE);
			exit;
		}
	break;
	
	// ************** Show Project *****************************************
	case "project":
		
			listProject();
			#$smarty->assign("a_confirms", $Messages->getconfirms());
			#$smarty->assign("a_warnings", $Messages->getwarnings());
			$smarty->display( TPL_DIR . 'showProject.tpl');

	break;

// ************** Mineral Preise aus altem mod *****************************************	
	/*case "MinsPreise":
		$result = $database->minerals_get_mineralprices();
			while ($row = mysql_fetch_assoc($result)) {
				$mineralprices[] = $row;
			}
		// echo '<pre>'; print_r($mineralprices); echo '</pre>';
		
				// MB Preise
		 $orevalues=showOreValue();
		 
//	  print_r($orevalues);
// 		exit;
    
		$smarty->assign("url_dowork_mins", URL_DOWORK.'?module='.ACTIVE_MODULE."&amp;action=update");
		$smarty->assign("mineralArraySize",$database->get_num_rows($result));
		$smarty->assign("mineralprices",   $mineralprices);
		$smarty->assign("ov",   $orevalues);
		$smarty->assign("date",            $mineralprices[0]['ChangeDate']);
		//$smarty->assign("manager",         $User->isManager());
		//$smarty->assign("mbuddy",          $User->isMiningBuddy());
		$smarty->assign("a_confirms", $Messages->getconfirms());
		$smarty->assign("a_warnings", $Messages->getwarnings());
		$smarty->display( TPL_DIR . 'Minerals.tpl');
		
		mysql_free_result($result);
	break; */
	
  case "MinsPreise":

		$orevalues=showOreValue();
     
		$smarty->assign("url_dowork_mins", URL_DOWORK.'?module='.ACTIVE_MODULE."&amp;action=update");
		$smarty->assign("ov",   $orevalues);
		//$smarty->assign("manager",         $User->isManager());
		//$smarty->assign("mbuddy",          $User->isMiningBuddy());
		#$smarty->assign("a_confirms", $Messages->getconfirms());
		#$smarty->assign("a_warnings", $Messages->getwarnings());
		$smarty->display( TPL_DIR . 'Minerals.tpl');
		
	break;

// ************** Mining Div Skills *********************************
	case "Skills":
/*
			$smarty->assign('bgcolor', array('-' => '#FFFFFF','#FF0000','#FF0000','#FF8000','#FFFF00','#00FF00','#FF80FF'));
		if ($User->alts) {
			$alts    = array_keys($User->alts);
			$charIDs = array_merge(array($User->id),$alts);
			$skills  = MiningDivSkills($charIDs);
		} else {
			$skills  = MiningDivSkills(array($User->id));
		}
		$smarty->assign('skills', $skills);
		$smarty->assign("a_confirms", $Messages->getconfirms());
		$smarty->assign("a_warnings", $Messages->getwarnings());
		$smarty->display( TPL_DIR . 'MiningDivSkills.tpl');
	break;

*/
// BALD NEU   
		Memberliste($User->charID);
		#$smarty->assign("a_confirms", $Messages->getconfirms());
		#$smarty->assign("a_warnings", $Messages->getwarnings());
		$smarty->display( TPL_DIR . 'MiningDivSkills.tpl');
	break;

		$smarty->assign('skills', $skills);
		#$smarty->assign("a_confirms", $Messages->getconfirms());
		#$smarty->assign("a_warnings", $Messages->getwarnings());
		$smarty->display( TPL_DIR . 'MiningDivSkills.tpl');

   
	break;
	
// ************** DEFAULT - Anzeigen der Mining OPS *****************	
	case "main":
	case "runlist":
	default:
		RunsListing();
		#$smarty->assign("a_confirms", $Messages->getconfirms());
		#$smarty->assign("a_warnings", $Messages->getwarnings());
	  
	  $smarty->display( TPL_DIR . 'listruns.tpl');

 // ************** DEFAULT - Anzeigen der Mining OPS ENDE ************ 

	break;
}
?>