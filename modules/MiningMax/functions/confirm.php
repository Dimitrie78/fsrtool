<?php

function confirm($question = 'Are you sure?', $post = false) { 

	// switch post or get.
	if (isset ($_POST['check'])) {

		// The user confirmed the box. Dont loop. Accept it already ;)
		if ($_POST['confirmed'] == true) {
			return (true);
		}

		$MODE = 'POST';
		$FORM = $_POST;
		$keys = array_keys($_POST);

	} else {

		// The user confirmed the box. Dont loop. Accept it already ;)
		if ($_POST[confirmed] == true) {
			return (true);
		}

		$MODE = 'POST';
		$FORM = $_GET;
		$keys = array_keys($_GET);

	}

	// Assemble hidden values for the confirm form.
	foreach ($keys as $key) {
		$html .= '<input type="hidden" name="' . $key . '" value="' . $FORM[$key] . '">';
	}
	
	if ($post) {
		$keys = array_keys($post);
		foreach ($keys as $key) {
			$html .= '<input type="hidden" name="' . $key . '" value="' . $post[$key] . '">';
		}
	}
	// Cancel button
	$cancel  = '<form action="'.MBPATH.'" method="POST">';
	$cancel .= '<input type="submit" name="confirmed" value="CANCEL">';
	$cancel .= '</form>';

	// OK button
	$ok = '<form action="'.MBPATH.'" method="'.$MODE.'">';
	$ok .= $html;
	$ok .= '<input type="submit" name="confirmed" value="OK">';
	$ok .= '</form>';

	$img = '<img src="'. MODULE_DIR . ACTIVE_MODULE . '/images/warning.png">';
	
	
	$confirm = '>> Bitte best&auml;tigen';
	
	global $smarty;
	
	$smarty->assign('IMG', 		   $img);
	$smarty->assign('CONFIRM', 	   $confirm);
	$smarty->assign('CONFIRMTEXT', $question);
	$smarty->assign('CANCEL',      $cancel);
	$smarty->assign('OK',          $ok);
	$smarty->assign("a_confirms", $_SESSION['messages']->getconfirms());
	$smarty->assign("a_warnings", $_SESSION['messages']->getwarnings());
	
	$smarty->display( TPL_DIR . "confirm.tpl");
	exit;
}
?>