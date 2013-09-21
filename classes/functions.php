<?php
defined('FSR_BASE') or die('Restricted access');
/**
 * clean user input
 * <br />
 * Gets a global variable, cleaning it up to try to ensure that
 * hack attacks don't work
 *
 * @param var $ name of variable to get
 * @param  $ ...
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function VarCleanFromInput()
{
	// Create an array of bad objects to clean out of input variables
	$search = array('|</?\s*SCRIPT.*?>|si',
					'|</?\s*FRAME.*?>|si',
					'|</?\s*OBJECT.*?>|si',
					'|</?\s*META.*?>|si',
					'|</?\s*APPLET.*?>|si',
					'|</?\s*LINK.*?>|si',
					'|</?\s*IFRAME.*?>|si',
					'|STYLE\s*=\s*"[^"]*"|si');

	// Create an empty array that will be used to replace any malacious code
	$replace = array('');

	// Create an array to store cleaned variables
	$resarray = array();

	// Loop through the function arguments
	// these arguments are input variables to be cleaned
	foreach (func_get_args() as $var) {

		// If the var is empty return void
		if (empty($var)) {
			return;
		}

		// Identify the correct place to get our variable from
		// and if we should attempt to cleanse the variable
		// content from the $_FILES array is left untouched
		$cleanse = false;
		switch (true) {
			case (isset($_REQUEST[$var]) && !isset($_FILES[$var])):
				// Set $ourvar from the $_REQUEST superglobal
				// but only if it's not also present in the $_FILES array
				// since php < 4.30 includes $_FILES in $_REQUEST
				$ourvar = $_REQUEST[$var];
				$cleanse = true;
				break;
			case isset($_GET[$var]):
				// Set $ourvar from the $_GET superglobal
				$ourvar = $_GET[$var];
				$cleanse = true;
				break;
			case isset($_POST[$var]):
				// Set $ourvar from the $_POST superglobal
				$ourvar = $_POST[$var];
				$cleanse = true;
				break;
			case isset($_COOKIE[$var]):
				// Set $ourvar from the $_COOKIE superglobal
				$ourvar = $_COOKIE[$var];
				$cleanse = true;
				break;
			case isset($_FILES[$var]):
				// Set $ourvar from the $_FILES superglobal
				$ourvar = $_FILES[$var];
				break;
			default:
				$ourvar = null;
				break;
		}

		$alwaysclean = array('name', 'module', 'type', 'file', 'authid');
		if (in_array($var, $alwaysclean)) {
			$cleanse = true;
		}

		if ($cleanse) {
			// If magic_quotes_gpc is on strip out the slashes
			if (get_magic_quotes_gpc()) {
				EveStripslashes($ourvar);
			}

			$ourvar = preg_replace($search, $replace, $ourvar);
		}

		// Add the cleaned var to the return array
		array_push($resarray, $ourvar);
	}

	// If there was only one parameter passed return a variable
	if (func_num_args() == 1) {
		return $resarray[0];
	// Else return an array
	} else {
		return $resarray;
	}
}

/**
 * strip slashes
 *
 * stripslashes on multidimensional arrays.
 * Used in conjunction with pnVarCleanFromInput
 *
 * @access private
 * @param any $ variables or arrays to be stripslashed
 */
function EveStripslashes (&$value)
{
    if(empty($value))
        return;

    if (!is_array($value)) {
        $value = stripslashes($value);
    } else {
        array_walk($value, 'EveStripslashes');
    }
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

?>