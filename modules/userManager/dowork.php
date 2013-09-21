<?php
defined('ACTIVE_MODULE') or die('Restricted access');


switch($action)
{
	case "delChar":
		if ( $User->Manager ) {
			$charID = str_replace('user_', '', $_POST['charID']);
			$res = $User->delUser( $charID );
			echo $res;
		}
		else echo '0';
		
	break;
	
	case "editChar":
		if ( $User->Manager ) {
			if ( isset($_POST['edit']) ) {
				$res = $User->editUser( $_POST['edit'] );
				echo $res;
			}
			else {
				$charID = str_replace('user_', '', $_POST['charID']);
				$res = $User->editUser( $charID );
				echo $res;
			}
		}
		else echo '0';
		
	break;

	case "editUser":
		if ( $User->Manager ) {
			$charID = str_replace('user_', '', $_POST['charID']);
			$roleID = str_replace('r_', '', $_POST['roleID']);
			if ( $_POST['edit'] == 1 ) $res = $User->setRole($charID, $roleID, true);
			else $res = $User->setRole($charID, $roleID, false);
			echo $res;
		}
		else echo '0';
	break;
	
	case "editAltUser":
		if ( $User->Manager ) {
			$data = str_replace('user_', '', $_POST['charID']);
			list ($mainCharID, $charID) = preg_split('/_/', $data, 2); //preg_split
			$role = $_POST['role'];
			
			if ( $_POST['edit'] == 1 ) $res = $User->setRoleAlt($mainCharID, $charID, $role, true);
			else $res = $User->setRoleAlt($mainCharID, $charID, $role, false);
			echo $res;
		}
		else echo '0';
	break;
}
?>