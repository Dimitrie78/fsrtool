<?php
/**
 * @package EDK
 */

if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;
global $smarty;



if (!file_exists('../config/dbconfig.ini')) {
	#chmod('../config/dbconfig.ini', 0777);
	if (writeConfig()) {
		$stoppage = false;
		$config = file_get_contents('../config/dbconfig.ini');
		$smarty->assign('hi_config', highlight_string($config, true));
	}
	chmod('../config/dbconfig.ini', 0440);
}
else {
	chmod('../config/dbconfig.ini', 0777);
	if (writeConfig()) {
		$stoppage = false;
		$config = file_get_contents('../config/dbconfig.ini');
		$smarty->assign('hi_config', highlight_string($config, true));
	}
	chmod('../config/dbconfig.ini', 0440);
}
// config is there, use it to create all config vars which arent there
// to prevent that ppl with running installs get new values

$smarty->assign('conf_exists', file_exists('../config/dbconfig.ini'));
$smarty->assign('stoppage', $stoppage);
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step7.tpl');

function writeConfig() {
    extract($_SESSION['sql']);
	$request = 'Curl';
    $alehost = 'https://api.eveonline.com/';
	$assoc_arr = array(
		'mysql' => array(
			'dbhost'   => $host,
			'dbuname'  => $user,
			'dbpass'   => $pass,
			'dbname'   => $db,
			'dbprefix' => 'fsrtool_',
			'dbeve'    => $dbeve.'.',
			'dbengine' => $engine
		), 'main' => array(
			'request'  => $request,
			'alehost'  => $alehost,
			'salt'     => md5($host.$user.$pass.$db.'fsrtool_'),
		)
	);

	if (write_ini_file($assoc_arr, '../config/dbconfig.ini', true))
		return true;
	return false;
	
}

function write_ini_file($assoc_arr, $path, $has_sections=FALSE) { 
    $content = ""; 
    if ($has_sections) { 
        foreach ($assoc_arr as $key=>$elem) { 
            $content .= "[".$key."]\n"; 
            foreach ($elem as $key2=>$elem2) { 
                if(is_array($elem2)) 
                { 
                    for($i=0;$i<count($elem2);$i++) 
                    { 
                        $content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
                    } 
                } 
                else if($elem2=="") $content .= $key2." = \n"; 
                else $content .= $key2." = \"".$elem2."\"\n"; 
            } 
        } 
    } 
    else { 
        foreach ($assoc_arr as $key=>$elem) { 
            if(is_array($elem)) 
            { 
                for($i=0;$i<count($elem);$i++) 
                { 
                    $content .= $key2."[] = \"".$elem[$i]."\"\n"; 
                } 
            } 
            else if($elem=="") $content .= $key2." = \n"; 
            else $content .= $key2." = \"".$elem."\"\n"; 
        } 
    } 

    if (!$handle = fopen($path, 'w')) { 
        return false; 
    } 
    if (!fwrite($handle, $content)) { 
        return false; 
    } 
    fclose($handle); 
    return true; 
}