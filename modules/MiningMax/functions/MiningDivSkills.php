<?php

// Mining DIV Skill abfrage
function MiningDivSkills($charIDs = array())
{
	global $database;
	global $User;

	//global $MiningSkillsX;
	include( MODULE_DIR . ACTIVE_MODULE . '/functions/MiningSkillsX.php');
	
	//$charID    = $database->escape($charID);
	 $commaseperatet_charIDs = implode(',', $charIDs);
	$sqlstring = "SELECT * FROM ".db_tab_fsr_skills." WHERE charID IN (".$commaseperatet_charIDs.");";
  $result    = $database->doQuery($sqlstring,"Database:getSkills");
  
  if ($database->get_num_rows($result) > 0) {
		while ($row = $database->fetch_assoc($result)){
			if ($row){
				$skills[$row['charID']]['charID']        = $row['charID'];
				//$skills[$row['charID']]['charName']      = $charName[$row['charID']];
				$skills[$row['charID']][$row['skillID']] = $row['quantity'];
			}
		}
	} else {
		$skills = false;
	}
	$mini = 0;
	$indu = 0;
	$supp = 0;
	foreach ($charIDs as $charID) {
		$SkillTree[$charID]['charID']   = $skills[$charID]['charID'];
		$SkillTree[$charID]['charName'] = charIDtoName($charID);
		foreach ($MiningSkillsX as $key => $skill) {
			foreach ($skill as $id => $name) {	
				if ($key == 'Hauler') {
					if ($skills[$charID][$id]) {
						$SkillTree[$charID][$key][] = array('name'  => $name,
															'level' => $skills[$charID][$id]);
					}
				} elseif ($key == 'Freighter') {
					if ($skills[$charID][$id]) {
						$SkillTree[$charID][$key][] = array('name'  => $name,
															'level' => $skills[$charID][$id]);
					}
				} elseif ($key == 'CommandShips') {
					if ($skills[$charID][$id] == 5 && $skills[$charID][12096] >= 4 && $skills[$charID][23950] >= 1) {
						$SkillTree[$charID][$key][] = array('name'  => $name,
															'level' => $skills[$charID][23950]);
					}
				} else {
					if ($skills[$charID][$id]) {
						$SkillTree[$charID][$key][] = array('name'  => $name, 
															'level' => $skills[$charID][$id]); 
					} else {
						$SkillTree[$charID][$key][] = array('name'  => $name, 
															'level' => 0);
					}
				}
			}
		}
	}
	//echo '<pre>';print_r($SkillTree);echo '</pre>';exit;
	$database->free_result($result);
	return $SkillTree;
}
if ( !function_exists(charIDtoName) ) {
  function charIDtoName($charID)
  {
	global $database;
	
	$charID = $database->escape($charID);
	$query1 = "SELECT username as name FROM ".db_tab_user." WHERE charID='$charID' LIMIT 1;";
	$query2 = "SELECT charName as name FROM ".db_tab_alts." WHERE charID='$charID' LIMIT 1;";
	$result = $database->doQuery($query1,'MiningMAX::MiningDivSkills.php->charIDtoName');
	if ($database->get_num_rows($result) == 1) {
		$row = $database->fetch_assoc($result);
	} else {
		$result = $database->doQuery($query2,'MiningMAX::MiningDivSkills.php->charIDtoName');
		$row    = $database->fetch_assoc($result);
	}
	$database->free_result($result);
	
	return ($row['name']);
  }
}
?>