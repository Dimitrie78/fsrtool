<?php
defined('ACTIVE_MODULE') or die('Restricted access');

date_default_timezone_set('UTC');

$status =  ReadConfigFile(MODULE_DIR . ACTIVE_MODULE . '/templates/status.' . $_SESSION['chosenLanguage'] . '.txt');
$dread =   ReadConfigFile(MODULE_DIR . ACTIVE_MODULE . '/templates/dreads.txt');
$carrier = ReadConfigFile(MODULE_DIR . ACTIVE_MODULE . '/templates/carrier.txt');
$timeZ =   ReadConfigFile(MODULE_DIR . ACTIVE_MODULE . '/templates/timezone.txt');

$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="modules/Member/inc/styles.css" />'."\n",
								));

$_SESSION['corpID'] = $User->corpID;

define('ACTION', $action);

# Load Smarty Plugins
if (is_dir(MODULE_DIR . ACTIVE_MODULE . '/plugins')) {
	$smarty->addPluginsDir(MODULE_DIR . ACTIVE_MODULE . '/plugins');
}

if($world->isHighCommand()) $smarty->assign('HighCommand',1);
if($world->isLeader()) 		$smarty->assign('Leader',1);


$smarty->assign('updateTime', $world->get_updateTime());
$smarty->assign('eveTime',	  eveTime());
$smarty->assign('urlMember',  MODULE_DIR . ACTIVE_MODULE);
$smarty->assign('action',     $action);
#$smarty->assign('bar',  '.' . MODULE_DIR . ACTIVE_MODULE . '/templates/bar');

if ( $world->isHighCommand() || $world->isLeader() || $User->Manager ) {
  if ($world->getApiStatus()) {
	#header("Location: ".URL_INDEX ."?module=Pos");
	#exit;
  }
  
  $snow = new Snow($User);
  
  switch ($action) {
	case 'test':
		echo 'test';
		#echo '<pre>'; print_r($_POST); echo '</pre>';
		
	break;
	
	case 'main':
	default:
		if(isset($_POST['search'])){
			$smarty->assign('Member_s', $world->get_mainchar_m($_POST['search']));
			$smarty->assign('alts_s',   $world->get_atls_m($_POST['search']));
		}
		$Alts=$world->get_atls($User->charID);
		$a=0;
		while($Alts[$a]['name']) {
			$AltsNamen .= $Alts[$a]['name'].', ';
			$a++;
		}
		$AltsNamen=substr($AltsNamen,0,-2);
		$smarty->assign('afk',       $world->get_afk());
		$smarty->assign('Eval',      $world->get_Evaluation($User->charID));
		$smarty->assign('altsNamen', $AltsNamen);
		$smarty->assign('Member',    $world->get_mainchar($User->charID));
		$smarty->assign('alts',      $Alts);
		$smarty->assign('status',    $status);
		$smarty->assign('dread',     $dread);
		$smarty->assign('carrier',   $carrier);
		$smarty->assign('timeZ',     $timeZ);
		$smarty->assign('url_dowork', URL_DOWORK.'?module='.ACTIVE_MODULE.'&amp;action=update');
		$smarty->assign('url_dowork_search', URL_INDEX .'?module='.ACTIVE_MODULE);
		$smarty->assign('manager',   $User->Manager );
//		$smarty->assign('mbuddy',    $User->isMiningBuddy());
		$smarty->display('file:['.ACTIVE_MODULE.']Member.tpl');
	break;

	case 'showChar':
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))   addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag'])) updateFlags();
		$smarty->assign('char', 	  showChar($_REQUEST['charID']));
		$smarty->display('file:['.ACTIVE_MODULE.']showChar.tpl');
	break;
	
	case 'news':
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))   addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag'])) updateFlags();
		
		$smarty->assign('news', 	  $snow->listNews());
		$smarty->display('file:['.ACTIVE_MODULE.']listNews.tpl');
	break;
	
	case 'member':
		#print_r($snow->chars); die;
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))   addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag'])) updateFlags();
		
		$smarty->assign('members', 	  $snow->listMembers($smarty));
		$smarty->display('file:['.ACTIVE_MODULE.']listMembers.tpl');
	break;
	
	case 'div':
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))   addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag'])) updateFlags();
		
		$snow->listDivision($smarty);
		
		if (isset($_GET['division']))
			$smarty->assign('division', $_GET['division']);
		else
			$smarty->assign('division', 1);	
		$smarty->display('file:['.ACTIVE_MODULE.']listDivs.tpl');
	break;
	
	case 'flags':
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))   addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag'])) updateFlags();
		
		$snow->listFlags($smarty);
		
		if (isset($_GET['flag']))
			$smarty->assign('flag', $_GET['flag']);
		else
			$smarty->assign('flag', 1);	
		$smarty->display('file:['.ACTIVE_MODULE.']listFlags.tpl');
	break;
	
	case 'stats':
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))   addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag'])) updateFlags();
		
		$snow->listStats($smarty);
		
		if (isset($_GET['stat']))
			$smarty->assign('stat', $_GET['stat']);
		else
			$smarty->assign('stat', 1);	
		$smarty->display('file:['.ACTIVE_MODULE.']listStats.tpl');
	break;
	
	case 'eval':
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))    addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag']))  updateFlags();
		if (isset($_POST['editEval'])) updateEval();
		if (isset($_POST['addEval']))  addEval();
		
		$snow->listEval($smarty);
		
		if (isset($_GET['eva']))
			$smarty->assign('eva', $_GET['eva']);
		else
			$smarty->assign('eva', 1);
		#echo '<pre>'; print_r(listEvalPvP()); echo '</pre>'; die;
		#echo '<pre>'; print_r($snow->listEval($smarty)); echo '</pre>'; die;
		$smarty->display('file:['.ACTIVE_MODULE.']listEval.tpl');
	break;
	
	case 'kill':
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))   addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag'])) updateFlags();
		
		if (isset($_GET['state']))
			$smarty->assign('state', $_GET['state']);
		else
			$smarty->assign('state', 0);
		
		$kill = new Kill($User);
		$smarty->assign('months', $kill->months);
		$smarty->assign('kills', $kill->chars);
		#$smarty->assign('kills', listKills($_GET['state']));
		$smarty->display('file:['.ACTIVE_MODULE.']listKills.tpl');
	break;
	
	case 'carebears':
		if (isset($_GET['del']) && $_GET['del'] == 1) delAlt($_GET['charID'],$_GET['altOf']);
		if (isset($_POST['altOf']))   addAlt($_POST['charID'],$_POST['altOf']);
		if (isset($_POST['addFlag'])) updateFlags();
		
		#$stime = microtime(true);
		#listCarebears();
		#date_default_timezone_set('UTC');
		$a = new Ratter( $User );
		
		
		$smarty->assign('Ratter', $a);
		
		$smarty->assign('charts', $a->charts( $world->get_fullApi_corps( $User->corpID ) ));
		$smarty->assign('content', $a->get_content());
		#$_SESSION['messages']->addwarning(round((microtime(true) - $stime),3));
		$smarty->assign('period', $_GET['period']);
		$smarty->assign('ratting', $_GET['ratting']);
		$smarty->assign('char', $_GET['char']);
		
		#echo '<pre>'; print_r( $a->get_content() ); echo '</pre>'; die;
		
		$smarty->display('file:['.ACTIVE_MODULE.']listCarebears.tpl');
	break;
  }
} else {
  switch ($action) {
	default:
		if(isset($_POST['search'])){
			$smarty->assign('Member_s', $world->get_mainchar_m($_POST['search']));
			$smarty->assign('alts_s',   $world->get_atls_m($_POST['search']));
		}
		$Alts=$world->get_atls($User->charID);
		$a=0;
		while($Alts[$a]['name']) {
			$AltsNamen .= $Alts[$a]['name'].', ';
			$a++;
		}
		$AltsNamen=substr($AltsNamen,0,-2);
		$smarty->assign('afk',       $world->get_afk());
		$smarty->assign('Eval',      $world->get_Evaluation($User->charID));
		$smarty->assign('altsNamen', $AltsNamen);
		$smarty->assign('Member',    $world->get_mainchar($User->charID));
		$smarty->assign('alts',      $Alts);
		$smarty->assign('status',    $status);
		$smarty->assign('dread',     $dread);
		$smarty->assign('carrier',   $carrier);
		$smarty->assign('timeZ',     $timeZ);
		$smarty->assign('url_dowork', URL_DOWORK.'?module='.ACTIVE_MODULE.'&amp;action=update');
		$smarty->assign('url_dowork_search', URL_INDEX .'?module='.ACTIVE_MODULE);
		$smarty->assign('manager',   $User->Manager);
//		$smarty->assign('mbuddy',    $User->isMiningBuddy());
		$smarty->display('file:['.ACTIVE_MODULE.']Member.tpl');
		
	break;
  }
}
?>