<?php
defined('ACTIVE_MODULE') or die( header("Location: ".URL_INDEX) );

#$memberDB = new MemberDB(db_host_fsrclan_member, db_user_fsrclan_member, db_pass_fsrclan_member, db_name_fsrclan_member);

switch($action)
{
	case 'update':
		if($_POST['new_values'] == 1) { 
		
			if( isset( $_POST['afk'] ) and $_POST['afk'] == 1 and ( isset( $_POST['afk_reason'] ) and empty( $_POST['afk_reason'] ) ) ){
				$Messages->addwarning("Es muss ein Grund f&uuml;r die Abwesendheit angegeben werden!");
				$string = "Location: ".URL_INDEX ."?module=Member";
				header($string);
				exit;
			} elseif(isset($_POST['afk']) and $_POST['afk'] == 1 and (isset($_POST['afk_reason']) and !empty($_POST['afk_reason']))) {
				$afk=1; $afk_reason=$_POST['afk_reason']; 
			} else {
				$afk=0; $afk_reason="";
			}
			$mainID=$_POST['main'];
			if(isset($_POST['posgunner']) and $_POST['posgunner'][$mainID] == 1) $mainposgunner=1; else $mainposgunner=0;
			
			if(isset($_POST['alt'])){
				foreach($_POST['alt'] as $key => $value) {
					if(isset($_POST['posgunner']) and $_POST['posgunner'][$value] == 1) $posgunner=1; else $posgunner=0;
					$result=$world->set_values($_POST['alt'][$key],$_POST['carrier'][$key],$_POST['dread'][$key],$posgunner);
				}
			}
			
			$result=$world->set_values($_POST['main'],$_POST['maincarrier'],$_POST['maindread'],$mainposgunner,$afk,$afk_reason,$_POST['tz']);
			if($result) $Messages->addconfirm("Daten erfolgreich aktualisiert!");
			else $Messages->addwarning("Fehler...");
		}
		$string = "Location: ".URL_INDEX ."?module=Member";
		header($string);
		exit;
	break;
	
	case 'addFlag':
		#print 'test';
		#print_r($_POST); 
		echo updateFlags();
	break;
	
	case 'search':
		if($User->charID == "" || $User->active != '1') {
			header('HTTP/1.1 403 Forbidden');
			die('unauthorized!');
		}
		if( isset($_GET['term']) && strlen($_GET['term']) > 2 ) {  // Post Variable Input Feld ?
			$corpID = $User->corpID;
					
			$sql = "SELECT charID, name FROM ".$world->_table['snow_characters']."	
				WHERE name LIKE '".$world->db->escape( $_GET['term'] )."%'
				AND corpID = '{$corpID}'
				ORDER BY name ASC;";
		
			$return_array = array();
		
			$result = $world->db->query( $sql );
			while ( $row = $result->fetch_assoc() ) {
				$row_array['label'] = $row['name'];
				$row_array['value'] = $row['name'];
				array_push($return_array,$row_array);
			}
			$result->close();
		
			echo json_encode($return_array);
		}
	break;
	
	case 'charSearch':
		if($User->charID == "" || $User->active != '1') {
			header('HTTP/1.1 403 Forbidden');
			die('unauthorized!');
		}
		if(isset($_GET['term']) && strlen($_GET['term']) > 2){  // Post Variable Input Feld ?
			$corpID = $User->corpID;
			
			$sql = "SELECT charID, name FROM ".$world->_table['snow_characters']."	
				WHERE name LIKE '".$world->db->escape( $_GET['term'] )."%'
				AND corpID = '{$corpID}'
				ORDER BY name ASC;";
		
			$return_array = array();
		
			$result = $world->db->query( $sql );
			while ( $row = $result->fetch_assoc() ) {
				$row_array['label'] = $row['name'];
				$row_array['value'] = $row['name'];
				array_push($return_array,$row_array);
			}
			$result->close();
		
			echo json_encode($return_array);
		}
	break;
	
	case 'isAltSearch':
		if($User->charID == "" || $User->active != '1') {
			header('HTTP/1.1 403 Forbidden');
			die('unauthorized!');
		}
		if(isset($_GET['term']) && strlen($_GET['term']) > 2){  // Post Variable Input Feld ?
			#$corpID = $User->corpID;
			$corpID = addslashes($_GET['corpID']);
			
			$sql = "SELECT c.charID, c.name FROM ".$world->_table['snow_characters']." c LEFT JOIN ".$world->_table['snow_alts']." a
				ON c.charID = a.charID WHERE
				c.name LIKE '%{$_GET['term']}%'
				AND a.charID IS NULL
				AND c.inCorp = 1
				AND c.corpID = '{$corpID}'
				ORDER BY name";
		
			$return_array = array();
		
			$result = $world->db->query( $sql );
			while ( $row = $result->fetch_assoc() ) {
				$row_array['label'] = $row['name'];
				$row_array['value'] = $row['name'];
				array_push($return_array,$row_array);
			}
			$result->close();
		
			echo json_encode($return_array);
		}
	break;
}



?>