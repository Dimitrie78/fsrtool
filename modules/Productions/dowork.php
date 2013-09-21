<?php
defined('FSR_BASE') or die('Restricted access');

switch($action)
{
	case 'get':
		$job = new Productions($world);
		header('Content-type: application/json');
		echo(json_encode($job->getJobs()));
	break;
	
	default:
		$string = URL_INDEX ."?module=Productions";
		header("Location: ".$string."\n");
		exit;
	break;
}

?>