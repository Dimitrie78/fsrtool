<?php

function smarty_function_getalts($params, &$smarty) {
    global $snow;
	if (!isset($params['mainID'])) {
        return null;
    }

    if (isset($params['mainID'])) {
		/*** CHECKING FOR REMAINING ALTS ***/
		$return = array();
		$altquery = "SELECT c.charID, c.name FROM {$snow->_table['snow_characters']} c, {$snow->_table['snow_alts']} a
			WHERE a.charID = c.charID
			AND a.altOf = '{$params['mainID']}'
			AND c.inCorp = 1
			ORDER BY c.name";
		$altresults = $snow->db->query( $altquery );
		$altnum = $altresults->num_rows;
		if ($altnum == 0) {
			$mainres = $snow->db->query("SELECT c.charID, c.name FROM {$snow->_table['snow_characters']} c 
				INNER JOIN {$snow->_table['snow_alts']} a on a.altOF = c.charID
				WHERE a.charID='{$params['mainID']}';");
			$mainnum = $mainres->num_rows;
			if ($mainnum == 0) {
				$return[] = ", leaving behind no alts";
			} else {
				$main = $mainres->fetch_assoc();
				$return[] = ", was an alt from <b><i>{$main['name']}</b></i>";
			}
		}
		elseif ($altnum == 1) $return[] = ", leaving behind 1 alt (";
		elseif ($altnum > 1)  $return[] = ", leaving behind {$altnum} alts (";
		for ($i=0; $i < $altnum; $i++) {
			$alt = $altresults->fetch_array();
			if ($i+1 < $altnum) $return [] = "{$alt['name']}, ";
			else $return [] = "{$alt['name']}";
		}
		if ($altnum > 0) $return [] = ")";
		/*** END CHECKING FOR REMAINING ALTS ***/
	}

    if (isset($params['assign']) && !empty($params['assign'])) {
        $smarty->assign($params['assign'], implode($return));
    } else {
        return implode($return);
    }

}

?>