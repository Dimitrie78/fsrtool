<?php
function public_base_directory()
{
    //get public directory structure eg "/top/second/third"
    $public_directory = dirname($_SERVER['PHP_SELF']);
    //place each directory into array
    $directory_array = explode('/', $public_directory);
    //get highest or top level in array of directory strings
    $public_base = max($directory_array);
   
    return $public_base;
}

$dir = dirname($_SERVER[PHP_SELF]);

echo $dir;
echo '<br>';

echo public_base_directory();
echo '<br>';
echo dirname(__FILE__);
echo '<br>';

function url(){
    $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
    $base_url .= '://'. $_SERVER['HTTP_HOST'];
    $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']); 
    return $base_url;
}

echo url();
?>