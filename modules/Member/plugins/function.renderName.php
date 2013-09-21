<?php

function smarty_function_renderName($params, &$smarty) {
	global $snow;
	$return = array();
	if (!isset($params['charID'])) {
        return null;
    }

    if (isset($params['charID'])) {
		$query = "SELECT * FROM {$snow->_table['snow_characters']} WHERE charID = '{$params['charID']}'";
		$results = $snow->db->query( $query );
		
		if ( $char = $results->fetch_array() ) {
			$nameSlash = $char['name'];
			unset($flags);
						
			$altQuery = "SELECT distinct c.charID, c.name, c.lastSeen
				FROM {$snow->_table['snow_characters']} c JOIN {$snow->_table['snow_alts']} a
				ON a.charID = c.charID
				WHERE a.altOf = '{$params['charID']}'
				AND c.inCorp = 1
				ORDER BY c.name";
	
			$altResults = $snow->db->query( $altQuery );
			$numAlts = $altResults->num_rows;
			
			/*** RENDER NAME ***/
			$return[] = "<a href=\"".URL_INDEX.'?module='.ACTIVE_MODULE."&action=showChar&charID={$char['charID']}\">{$char['name']}</a> ";
			if ($numAlts == 1) 		$return[] = "- <a href=\"javascript:toggleDiv('altWin{$char['charID']}')\">1 alt</a> ";
			elseif ($numAlts != 0)  $return[] = "- <a href=\"javascript:toggleDiv('altWin{$char['charID']}')\">$numAlts alts</a> ";
			
			/***RENDER FLAGS ***/
			if ($char['afk'] == 1) {
				$return[] = "<img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/isafk.gif\" alt=\"{$char['afkText']}\" title=\"{$char['afkText']}\"> ";
				$isFlags = 1;
			}
			if ($char['notes'] != NULL) {
				$return[] = "<img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/iscustom.gif\" alt=\"{$char['notes']}\" title=\"{$char['notes']}\"> ";
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
			$result = $snow->db->query("SELECT * FROM {$snow->_table['snow_jobs']} WHERE charID = '".$params['charID']."'");
			$row = $result->fetch_array();
			if ($char['investigate'] == 1) $flags['investigate'] = 'CHECKED';
			if ($char['posgunner'] == 1) $flags['posgunner'] = 'CHECKED';
			if ($row['pos'] == 1) $flags['posd'] = 'CHECKED';
			if ($row['exempt'] == 1) $flags['exempt'] = 'CHECKED';
			if ($row['legacy'] == 1) $flags['legacy'] = 'CHECKED';
			if ($row['probation'] == 1) $flags['probation'] = 'CHECKED';
			
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
			
			$isaltResult = $snow->db->query("SELECT distinct charID, altOf FROM {$snow->_table['snow_alts']} WHERE charID = '{$params['charID']}';");
			$numisAlt = $isaltResult->num_rows;
			if ($numisAlt > 0) {
				$isAlt = $isaltResult->fetch_assoc();
				$return[] = "<a href=\"".URL_INDEX.'?module='.ACTIVE_MODULE."&action=".ACTION."&charID={$isAlt['charID']}&altOf={$isAlt['altOf']}&del=1\"><img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/notalt.gif\"></a>";
			}
			elseif ($numAlts == 0) 
				$return[] = "<a href=\"javascript:showIsAltWin('".$nameSlash."', '".$char['charID']."', '".URL_INDEX.'?module='.ACTIVE_MODULE."', '" . ACTION . "', '".$snow->User->corpID."')\"><img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/isalt.gif\"></a> ";
			
			$main = $snow->db->fetch_one("SELECT altOf FROM {$snow->_table['snow_alts']} WHERE charID = '".$char['charID']."'", 'altOf');
			
			if(empty($main)) $main = $char['charID'];
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
				 "'" . $pvpDiv          	. "', ".
				 "'" . ACTION . "')\"><img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/plusflag.gif\"></a> ";
			
			/*** RENDER POP UPS **/
			if ($numAlts != 0) {
				$return[] = "<div class=\"altWin\" id=\"altWin{$char['charID']}\" style=\"display: none\">";
				while ( $alts = $altResults->fetch_array() ) {
					$return[] = "<li>{$alts['name']} <a href=\"".URL_INDEX.'?module='.ACTIVE_MODULE."&action=".ACTION."&charID={$alts['charID']}
						&altOf={$char['charID']}&del=1\"><img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/notalt.gif\"></a></li>";
				}
				$return[] = "</div>";
			}
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