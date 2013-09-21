<?php
if(!ob_start("ob_gzhandler")) ob_start();
$time_start = microtime(true);

if (!defined('FSR_BASE')) {
	define('FSR_BASE', dirname(__FILE__));
}

require_once ("init.inc.php");

$online = $world->userOnline();

if ($User->alts) {
	foreach ($User->alts as $var => $val) {
		if (isset($val['fullApi']) && $val['fullApi'] == 1)
			$smarty->assign('fullApi', 1);
	}
}

$allow = array ( 	'login',
					'main',
					'showCharSelection',
					'trustMe',
					'passwd',
					'email',
					'addalt',
					'showAltCharSelection',
					'About',
					'accessMask',
				);

if ( $User->charID == 0 && !in_array( $action, $allow )) $action = '';


if ( !isset($_REQUEST['module']) )
{
	$smarty->assign("url_dowork_assign",URL_DOWORK."?action=assign");
	switch ($action)
	{
		case "showCharSelection":
			$smarty->assign("noRegisterLogin","1");
			$smarty->assign("charList",$_SESSION['modules']['login']['charList']);
			$smarty->display("showCharSelection.tpl");
		break;
		
		case "showAltCharSelection":
			$smarty->assign("charList",$_SESSION['modules']['login']['charList']);
			$smarty->display("showAltCharSel.tpl");
		break;
		
		case "accessMask":
			$smarty->assign('list', $world->assessMask());
			$smarty->assign('uName', $User->username);
			$smarty->assign('mask', (float)$_SESSION['modules']['login']['accessMask']);
			$smarty->assign('error', array('CharacterInfo'));
			$smarty->display("main.tpl");
		break;

		case "trustMe":
			$smarty->assign("url_dowork_login",URL_DOWORK);
			$smarty->display("trustMe.tpl");
		break;

    	case "passwd":
			$smarty->display("passwd.tpl");
		break;
    
    	case "email":
			$smarty->display("mail.tpl");
		break;
		
		case "addalt":
			$smarty->display("addalt.tpl");
		break;
		
		case "About":
			$smarty->assign('addheader', array( '<script src="lightbox/js/lightbox.js"></script>'."\n",
									'<link href="lightbox/css/lightbox.css" rel="stylesheet" />'."\n",
			));
			$smarty->display("about.tpl");
		break;
		
		default:
		case "login":
		case "main":
			$smarty->assign("url_email",  		 URL_INDEX."?action=email");			
			$smarty->assign("url_dowork_login",  URL_DOWORK);
			$smarty->assign("url_dowork_assign", URL_DOWORK);
			$smarty->display("main.tpl");
		break;
		
		/*** Only when User is logged in ***/
		
		case "SkillSheet":
			$smarty->assign('addheader', array( '<link rel="stylesheet" type="text/css" href="classes/skillsheet.css" />'."\n",));
			$skilltreeX = $world->getSkilltree();
			require_once('inc/skillsheet.php');
			$smarty->display("skillsheet.tpl");
		break;
		
		case "skills":
			require_once( './inc/CapDivSkills.php' );
			$capSkills = new CapSkills( $User );
			$smarty->assign('bgcolor', array('-' => '#FFFFFF','#FF0000','#FF0000','#FF0000','#FF0000','#00FF00','#FF80FF'));
			if ($User->alts) {
				$alts    = array_keys($User->alts);
				$charIDs = array_merge(array($User->charID),$alts);
				$skills  = $capSkills->CapDivSkills($charIDs);
			} else {
				$skills  = $capSkills->CapDivSkills(array($User->charID));
			}
			$smarty->assign('skills', $skills);
			$smarty->display('CapDivSkills.tpl');
		break;
		
		case "chat":
			$smarty->assign("chatuser", $world->getChatUser());
			$smarty->display("chat.tpl");
		break;
		
		case "eveNotifications":
			$smarty->assign('addheader', array( '<link href="inc/css/eveNotifications.css" rel="stylesheet" />'."\n",
												'<link rel="stylesheet" type="text/css" href="classes/jqry_plugins/msgbox/Styles/msgBoxLight.css" />'."\n",
												'<script type="text/javascript" src="classes/jqry_plugins/msgbox/Scripts/jquery.msgBox.js"></script>'."\n",
												'<script type="text/javascript" src="inc/js/eveNotifications.js"></script>'."\n",));
			$smarty->assign('sub', isset($_GET['sub']) ? $_GET['sub'] : false );
			require_once('inc/eveNotifications.class.php');
			$eveNotifications = new eveNotifications($User);
			$smarty->assign("eveNotifications", $eveNotifications);
			$smarty->display("eveNotifications.tpl");
		break;
	}
}

$querys = count($database->queries);
$queries_time = round($database->queries_time, 4);
$time_end = microtime(true);
$time = $time_end - $time_start;
$time = round($time, 4);
$mem = round((memory_get_usage()/1024), 2);
$endhtml = <<<EOD
	<script type="text/javascript">document.getElementById('footer').innerHTML = document.getElementById('footer').innerHTML 
		+ '<br /><small><small>'
		+ 'Hamsters made this page in {$time} seconds<br />'
		+ '{$querys} querys in {$queries_time} and {$mem} kb memory used.<br />'
		+ '<div id="online">User online: {$online}</div>'
		+ '</small></small>'
	</script>
EOD;

if ($action != 'ajaxfit' && $action != 'json') {
	echo $endhtml;
}
echo "\n</body></html>";
ob_end_flush();

?>