<?php

/*
 * This handy little function will create a small dead-end html
 * page, used for notices, actions and the like.
 */

function makeNotice($body, $type = "notice", $title = "", $backlink = URL, $backlinkdesc = "[OK]") {

	global $smarty;
	
	// Check for valid type
	switch ($type) {
		case ("notice") :
		default :
			$typeText = "Hinweis";
			$color = "#444455";
			$img =  MODULE_DIR . ACTIVE_MODULE . '/images/ok.png';
			break;

		case ("warning") :
			$typeText = "Warnung";
			$color = "#904000";
			$img =  MODULE_DIR . ACTIVE_MODULE . "/images/warning.png";
			break;

		case ("error") :
			$typeText = "Error";
			$color = "#772222";
			$img =  MODULE_DIR . ACTIVE_MODULE . "/images/error.png";
			break;
	}

	// Do we have a title? 
	if (empty ($title)) {
		$title = "Notice";
	}
	
	// Beautify the time.
	$STAMP = date('d.m.Y H:i');

	// Replace placeholders with information.
	$smarty->assign("TITLE", 		$title);
	$smarty->assign("BODY", 		$body);
	$smarty->assign("WHAT", 		$typeText);
	$smarty->assign("TIME", 		$STAMP);
	$smarty->assign("COLOR", 		$color);
	$smarty->assign("IMG", 			$img);
	$smarty->assign("BACKLINK", 	$backlink);
	$smarty->assign("BACKLINKDESC", $backlinkdesc);
	$smarty->assign("a_confirms", $_SESSION['messages']->getconfirms());
	$smarty->assign("a_warnings", $_SESSION['messages']->getwarnings());

	// Spill it out
		
	$smarty->display( TPL_DIR . "makeNotice.tpl");
}
?>