<?php

function smarty_function_renderCarebear($params, &$smarty) {
	global $snow;
	$last7days = time() - (60*60*24*7);
	
	$return = array();
	if (!isset($params['char'])) {
        return null;
    }
	$char = $params['char'];
	
	
	if( $char ) {
			$nameSlash = $char['name'];
			$numAlts = count($char['alts']);
			$x=0;
			if( $numAlts != 0 ) {
				foreach( $char['alts'] as $alt ) {
					if( $alt['isk'] ) $x++;
				}
			}
			$numAlts = $x;
			
			/*** RENDER NAME ***/
			if( $char['isk'] == 0 )
				$return[] = $char['name'];
			else {
			$link = $_SERVER['QUERY_STRING'].'&char='.urlencode($char['name']);
			$link = URL_INDEX."?".$link;
			
			#$return[] = "<a href=\"javascript:showCarebearWin('{$char['charID']}')\">{$char['name']}</a> ";
			$return[] = "<a href=\"{$link}\">{$char['name']}</a> ";
			}
			
			if ($numAlts != 0)  $return[] = "- <a href=\"javascript:toggleDiv('altWin{$char['charID']}')\">show more</a> ";
			
			/***RENDER FLAGS ***/
			if ($char['afk'] == 1) {
				$return[] = "<img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/isafk.gif\" alt=\"{$char['afkText']}\"> ";
				$isFlags = 1;
			}
			if ($char['notes'] != NULL) {
				$return[] = '<img src="'. MODULE_DIR . ACTIVE_MODULE . '/images/iscustom.gif"> ';
				$isFlags = 1;
			}
			if ($char['investigate'] == 1) {
				$return[] = '<img src="'. MODULE_DIR . ACTIVE_MODULE . '/images/iswarning.gif"> ';
				$isFlags = 1;
			}
			if ($char['inactive'] == 1) {
				$return[] = '<img src="'. MODULE_DIR . ACTIVE_MODULE . '/images/ismia.gif"> ';
				$isFlags = 1;
			}
			
			if ($isFlags == 1) $return[] = "| ";
			
			/*** RENDER ACTIONS ***/
			if ($char['afk'] == 1) {
				$flags['afk'] = 'CHECKED';
				$flags['afkText'] = addslashes($char['afkText']);
				$flags['afkText'] = preg_replace("/\r\n|\n|\r/", "<br />", $char['afkText']); 
				$flags['afkText'] = str_replace('<br />', '\n', $flags['afkText']);
			}
			
			if ($char['investigate'] == 1) $flags['investigate'] = 'CHECKED';
			if ($char['posgunner'] == 1) $flags['posgunner'] = 'CHECKED';
			if ($char['pos'] == 1) $flags['posd'] = 'CHECKED';
			if ($char['exempt'] == 1) $flags['exempt'] = 'CHECKED';
			if ($char['legacy'] == 1) $flags['legacy'] = 'CHECKED';
			if ($char['probation'] == 1) $flags['probation'] = 'CHECKED';
			
			$flags['notes'] = addslashes($char['notes']);
			$flags['notes'] = preg_replace("/\r\n|\n|\r/", "<br />", $flags['notes']); 
			$flags['notes'] = str_replace('<br />', '\n', $flags['notes']);
			
			//allowing both kinds of quotes to be used in the notes section
			
			#$flags['notes'] = str_replace(' ','&nbsp;',$flags['notes']);
			#$flags['notes'] = str_replace('\'','c39c',$flags['notes']);
			#$flags['notes'] = str_replace('"','c34c',$flags['notes']);
			#$flags['notes'] = stripslashes($flags['notes']);
			$nameSlash = str_replace('\'','c39c',$nameSlash);
			$nameSlash = str_replace('"','c34c',$nameSlash);
			
			if ($numAlts == 0) 
				$return[] = "<a href=\"javascript:showIsAltWin('".$nameSlash."', '".$char['charID']."', '".URL_INDEX.'?module='.ACTIVE_MODULE."', '" . ACTION . "', '".$snow->User->corpID."')\"><img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/isalt.gif\"></a> ";
			
			$main = $char['charID'];
			if ($snow->User->corpID == 147849586) {
				$stuff = 'FSR Urgestein';
				$pvpDiv = 'FSR Rangers';
			}
			elseif ($snow->User->corpID == 144965822) {
				$stuff = 'OI Legend';
				$pvpDiv = 'PvP Division';
			}
			else {
				$stuff = 'Legend';
				$pvpDiv = 'PvP Division';
			}
			
			$return[] = "<a href=\"javascript:showFlagWin('" . $nameSlash . "', ".
				 "'" . $main 				. "', ".
				 "'" . $char['charID']		. "', ".
				 "'" . $char['division'] 	. "', ".
				 "'" . $flags['afk'] 		. "', ".
				 "'" . $flags['afkText'] 	. "', ".
				 "'" . $char['tz'] 			. "', ".
				 "'" . $char['carrier'] 	. "', ".
				 "'" . $char['dread'] 		. "', ".
				 "'" . $flags['investigate']. "', ".
				 "'" . $flags['posgunner'] 	. "', ".
				 "'" . $flags['notes']		. "', ".
				 "'" . URL_INDEX .'?module='.ACTIVE_MODULE . "', ".
				 "'" . $flags['posd'] 		. "', ".
				 "'" . $flags['exempt'] 	. "', ".
				 "'" . $flags['legacy'] 	. "', ".
				 "'" . $flags['probation']	. "', ".
				 "'" . $stuff          	    . "', ".
				 "'" . $pvpDiv         	    . "', ".
				 "'" . ACTION . "')\"><img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/plusflag.gif\"></a> ";
			
			/*** RENDER POP UPS **/
			if( $numAlts != 0 ) {
				$return[] = "<div class=\"altWin\" id=\"altWin{$char['charID']}\" style=\"display: none\">";
				$return[] = "<li>{$char['name']}<span style=\"float:right\">".number_format($char['isk'],2,',','.')."</span></li>";
				
				foreach( $char['alts'] as $alt ) {
					if( $alt['isk'] ) {
						$link = $_SERVER['QUERY_STRING'].'&char='.urlencode($alt['name']);
						$link = URL_INDEX."?".$link;
						#$return[] = "<li><a href=\"javascript:showCarebearWin('{$alt['charID']}')\">{$alt['name']}</a> <span style=\"float:right\">".number_format($alt['isk'],2,',','.')."</span></li>";
						$return[] = "<li><a href=\"{$link}\">{$alt['name']}</a> <span style=\"float:right\">".number_format($alt['isk'],2,',','.')."</span></li>";
					}
				}
				$return[] = "</div>";
			}
	}
	
	 
	$ret = implode($return);
	
	
    if (isset($params['assign']) && !empty($params['assign'])) {
        $smarty->assign($params['assign'], $ret);
    } else {
        return $ret;
    }

}

?>