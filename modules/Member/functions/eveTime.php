<?php
function eveTime() {
	return (time()-date('Z'));
}

function renderName($p) {
    global $memberDB;
	$params['charID'] = $p;
	$return = array();
	if (!isset($params['charID'])) {
        return null;
    }

    if (isset($params['charID'])) {
		$query = "SELECT * FROM characters WHERE charID = '{$params['charID']}'";
		$results = $memberDB->doQuery($query);
		
		if ($char = mysql_fetch_array($results)) {
			$nameSlash = $char['name'];
			unset($flags);
						
			$altQuery = "SELECT distinct characters.charID, characters.name, characters.lastSeen
				FROM characters JOIN alts
				ON alts.charID = characters.charID
				WHERE alts.altOf = '{$params['charID']}'
				AND characters.inCorp = 1
				ORDER BY characters.name";
	
			$altResults = $memberDB->doQuery($altQuery);
			$numAlts = mysql_num_rows($altResults);
			
			/*** RENDER NAME ***/
			$return[] = "<a href=\"characters.php?id={$char['charID']}\">{$char['name']}</a> ";
			if ($numAlts == 1) 		$return[] = "- <a href=\"javascript:toggleDiv('altWin{$char['charID']}')\">1 alt</a> ";
			elseif ($numAlts != 0)  $return[] = "- <a href=\"javascript:toggleDiv('altWin{$char['charID']}')\">$numAlts alts</a> ";
			
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
			}
			$result = $memberDB->doQuery("SELECT * FROM Jobs WHERE charID = '".$params['charID']."'");
			$row = mysql_fetch_array($result);
			if ($char['investigate'] == 1) $flags['investigate'] = 'CHECKED';
			if ($char['posgunner'] == 1) $flags['posgunner'] = 'CHECKED';
			if ($row['pos'] == 1) $flags['posd'] = 'CHECKED';
			if ($row['exempt'] == 1) $flags['exempt'] = 'CHECKED';
			if ($row['legacy'] == 1) $flags['legacy'] = 'CHECKED';
			if ($row['probation'] == 1) $flags['probation'] = 'CHECKED';
			$flags['notes'] = nl2br(htmlentities($char['notes']));
			//$flags['notes'] = htmlentities($char['notes']);
			//$flags['notes'] = preg_replace("/\r\n|\n|\r/", "<br />", $char['notes']); 
			//$flags['notes'] = str_replace('<br /><br />', '<br />', $flags['notes']);
			//$flags['notes'] = str_replace('<br />', '\n', $flags['notes']);
			$flags['notes'] = eregi_replace('<br[[:space:]]*/?'.'[[:space:]]*>',chr(13).chr(10),$flags['notes']);
			//allowing both kinds of quotes to be used in the notes section
			
			//$flags['notes'] = str_replace(' ','&nbsp;',$flags['notes']);
			$flags['notes'] = str_replace('\'','c39c',$flags['notes']);
			$flags['notes'] = str_replace('"','c34c',$flags['notes']);
			$flags['notes'] = stripslashes($flags['notes']);
			$nameSlash = str_replace('\'','c39c',$nameSlash);
			$nameSlash = str_replace('"','c34c',$nameSlash);
			echo '<br>';
			echo $flags['notes'];
			echo '<br>';
			
			if ($numAlts == 0) $return[] = "<a href=\"javascript:showIsAltWin('".$nameSlash."', ".$char['charID'].")\"><img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/isalt.gif\"></a> ";
			
			$result2 = $memberDB->doQuery("SELECT altOf FROM alts WHERE charID = '".$char['charID']."'");
			$row2 = mysql_fetch_array($result2);
			$main = $row2[0];
			if(empty($main)) $main = $char['charID'];
			
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
				 "'" . $flags['probation']	. "')\"><img src=\"". MODULE_DIR . ACTIVE_MODULE . "/images/plusflag.gif\"></a> ";
			
			/*** RENDER POP UPS **/
			if ($numAlts != 0) {
				$return[] = "<div class=\"altWin\" id=\"altWin{$char['charID']}\" style=\"display: none\">";
				while ($alts = mysql_fetch_array($altResults)) {
					$return[] = "<li>{$alts['name']} <a href=\"isAlt.php?charID={$alts['charID']}
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