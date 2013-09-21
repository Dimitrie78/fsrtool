<?php

echo '<pre>';

/** wget.php created by Mario Priebe (c)2008 */
	$url[0] = "http://content.eveonline.com/data/Odyssey_1.1_Icons.zip";
	$url[1] = "http://content.eveonline.com/data/Odyssey_1.1_Renders.zip";
	$url[2] = "http://content.eveonline.com/data/Odyssey_1.1_Types.zip";
	$file[0] = "Odyssey_1.1_Icons.zip";
	$file[1] = "Odyssey_1.1_Renders.zip";
	$file[2] = "Odyssey_1.1_Types.zip";
	
	foreach( $url as $key => $u ) {
		$esc = escapeshellarg($u);
		exec("wget " . $esc);
	
		//nachfolgend entsprechende extension auskommentieren
		#$shell = "tar -xzvf $file[$key]"; //tar
		$shell = "unzip $file[$key]"; //zip
		$shell = escapeshellcmd($shell);
		#exec($shell,$nu);
		
		print_r($nu);
		//file loeschen
		$del = "rm $file[$key]";
		$del = escapeshellcmd($del);
		#exec($del,$na);
		print_r($na);
		
	}
echo '</pre>';  
    print_r("done");
?>