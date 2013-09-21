<?php


function smarty_function_getportrait($params, &$smarty)
{
    if (!isset($params['charID'])) {
        return '0';
    }

    if (!isset($params['size'])) { $params['size'] = 128; }
    
	$path = 'cache/imgcache';
	if (!is_dir($path)) mkdir($path);
	$file = $path . '/' . $params['charID'] . '_' . $params['size'] . '.jpg';
	
	$url = 'https://image.eveonline.com/Character/' . $params['charID'] . '_' . $params['size'] . '.jpg';
	$lifetime = time() - 60*60*24*15;
	
	if (!file_exists($file)) {
        $ch = curl_init(); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);		
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  
		$content = curl_exec ($ch);  
		curl_close ($ch);

        file_put_contents($file, $content);
    }
	
	if ( file_exists($file) && filemtime($file) < $lifetime ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  
		$content = curl_exec ($ch);  
		curl_close ($ch);

        file_put_contents($file, $content);
	}

    if (isset($params['assign']) && !empty($params['assign'])) {
        $smarty->assign( $params['assign'], $file );
    } else {
        return $file;
    }

}

?>