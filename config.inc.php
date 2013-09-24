<?php
defined('FSR_BASE') or die('Restricted access');
date_default_timezone_set('Europe/Berlin');

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

$old_error_handler = set_error_handler("myErrorHandler");
Smarty::unmuteExpectedErrors();

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
   if ($errno != 8) #8=notice
   {
        if (is_object($_SESSION["messages"]))
        {
            $_SESSION["messages"]->addwarning ("INFO: [$errno] $errstr<br />in File $errfile on line $errline\n");
        }
        else
        {
            print "<HTML><BODY>INFO: [$errno] $errstr<br />in File $errfile on line $errline\n</BODY></HTML>";
        }
		my_errorlog("INFO: [$errno] $errstr\n in File $errfile on line $errline\n");
   }
}

function my_errorlog($msg=null) {
	if ( !is_dir( "./cache/errorlog/" ) ) mkdir( "./cache/errorlog/" );
	$logfilenew = './cache/errorlog/last_Handler.log';
	$logfileall = './cache/errorlog/Handler.log';
	file_put_contents( $logfilenew, date('r')."\n".$msg );
	file_put_contents( $logfileall, date('r')."\n".$msg."\n", FILE_APPEND );
}

function ReadConfigFile( $cfgfilename )
{
    # array mit key/value - werten wie im config-file
    $cfgARRAY = array();

    # datei einlesen in array
    # code from http://de.php.net/manual/en/function.file.php
    # by mvanbeek at supporting-role dot co dot uk /  31-Dec-2003 03:39
	
	if (file_exists($cfgfilename)) {
		$config = file($cfgfilename);
		reset ($config);
		foreach ($config as $line) {
			 if ( $line == "" ) next($config);            # Ignore blankline
			 elseif ( $line == "\n" ) next($config);      # Ignore newline
			 elseif ( strstr($line,"#")) next($config);   # Ignore comments
			 else {
				 $line = rtrim($line);  # Get rid of newline characters
				 $line = ltrim($line);  # Get rid of any leading spaces
				 $value = preg_split("/\s*=\s*/", $line, 2); # split by "=" and removing blank space either side of it.
				 if (isset($value[1]))
					 $cfgARRAY["$value[0]"] = $value[1]; # Create a new array with all the values.
	//			 else
	//			 	var_dump($value[0]);
			 }
		}
	}
    return $cfgARRAY;
}

?>