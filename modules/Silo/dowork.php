<?php
defined('ACTIVE_MODULE') or die('Restricted access');

$corpID = isset( $_SESSION['corpID'] ) ? $_SESSION['corpID'] : $User->corpID;

switch($action)
{
	case 'json':
		if(isset($_POST['moonID'])){
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}		
	break;
	
	case 'jsonempty':#emptyItemID
		if(isset($_POST['moonID']) && isset($_POST['emptyItemID'])){
			$world->emptySilo($_POST['emptyItemID']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}		
	break;
	
	case 'jsononline':#itemID
		if(isset($_POST['moonID']) && isset($_POST['itemID'])){
			$world->onlineSilo($_POST['itemID']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}		
	break;
	
	case 'jsonsetsilo':
		if(isset($_POST['moonID']) && isset($_POST['IDtofill'])){
			$world->setSiloInput($_POST['IDtofill']);
			$world->setSiloFill($_POST['IDtofill']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
		if(isset($_POST['moonID']) && isset($_POST['IDtoempty'])){
			$world->setSiloOutput($_POST['IDtoempty']);
			$world->setSiloEmpty($_POST['IDtoempty']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
		if(isset($_POST['moonID']) && isset($_POST['IDinput'])){
			$world->setSiloInput($_POST['IDinput']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
		if(isset($_POST['moonID']) && isset($_POST['IDoutput'])){
			$world->setSiloOutput($_POST['IDoutput']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
		if(isset($_POST['moonID']) && isset($_POST['IDfill'])){
			$world->setSiloFill($_POST['IDfill']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
		if(isset($_POST['moonID']) && isset($_POST['IDempty'])){
			$world->setSiloEmpty($_POST['IDempty']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
		if(isset($_POST['moonID']) && isset($_POST['setstackID'])){
			$world->setSiloStacked($_POST['setstackID']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
		if(isset($_POST['moonID']) && isset($_POST['unsetstackID'])){
			$world->setSiloUnStack($_POST['unsetstackID']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
		if(isset($_POST['moonID']) && isset($_POST['delitemID'])){
			$world->unAssignSilo($_POST['delitemID']);
			$silos = new Silos($corpID, $world);
			header('Content-type: application/json');
			echo json_encode($silos->getFillStatus($_POST['moonID']));
		}
	break;
	
	case 'eve-central':
		echo $world->updatePrice($corpID);
	break;
	
	case 'delall':
		header('Content-type: application/json');
		echo json_encode($world->delAllStuff($_POST));
		//echo print_r($_POST);
	break;
	
	case 'addEvent':
		echo $world->calendarAddEvent();
	break;
	case 'dropEvent':
		echo $world->calendarDropEvent();
	break;
	case 'resizeEvent':
		echo $world->calendarResizeEvent();
	break;
	case 'delEvent':
		echo $world->calendarDelEvent();
	break;
		
	case 'calendar':
		header('Content-type: application/json');
		$events=array();
		
		$silos 	= new Silos($corpID, $world);
		$silo 	= $silos->getMinTimeLeft();
		$pos 	= $silos->StarbaseFuel();
		$events	= $world->calendarGetEvent();
		
		foreach($silo as $key => $val) {
			$events[] = array(
				'title' => "Silos: $key",
				'start' => $val['event'],
				'allDay' => false,
				'color' => 'green',
				'editable' => false
				);
		}
		
		foreach($pos as $key => $val) {
			$events[] = array(
				'title' => $key,
				'start' => $val,
				'allDay' => false,
				'editable' => false
				);
		}

		echo json_encode($events);
	break;
}

?>