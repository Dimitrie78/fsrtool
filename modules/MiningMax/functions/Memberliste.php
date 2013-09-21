<?php

// Hauptroutine
function Memberliste($choosenOne)

{
	global $database;
	global $User;
	global $smarty;    // Smarty Class
	include( MODULE_DIR . ACTIVE_MODULE . '/functions/MiningSkillsX.php');
// fancy SQL: Alle FSR Mining Div Mitglieder IDs lesen
	$sqlstring="
	SELECT
  u1.charID id, 'main' istAlt, u1.username main, u1.username
  altName
FROM
  stsys_eveorder_user u1 INNER JOIN
  stsys_eveorder_user_roles r1 ON u1.charID = r1.charID
WHERE
  u1.charID > 0 AND
  r1.roleID = 10 AND
  u1.corpID = 147849586
UNION
SELECT
  alt.charID id, 'alt' istAlt, main.username main,
  alt.charName altName
FROM
  stsys_eveorder_user main LEFT JOIN
  stsys_eveorder_user_alts alt ON alt.mainCharID = main.charID
  INNER JOIN
  stsys_eveorder_user_roles r2 ON r2.charID = main.charID
WHERE
  alt.charName IS NOT NULL AND
  main.charID IS NOT NULL AND
  main.corpID = 147849586 AND
  alt.corpID = 147849586 AND
  r2.roleID = 10
ORDER BY
  main, istAlt DESC;
";

// order by main,altName,istAlt desc;
  $result    = $database->doQuery($sqlstring);
  $zeilenzeiger=0;

$neuerMain="";

  if ($database->get_num_rows($result) > 0) {
		while ($row = $database->fetch_row($result))
		{
//			print_r( $row);echo "<br>";
	  $charIDs[$zeilenzeiger]=$row[0];
    $isALT[$row[0]]=$row[1];
    
    if ($row[1]=="main") { $main++; } else { $alt++;   }
    
    $neuerMain=$row[2];
    $hisMAIN[$row[0]]=$neuerMain; 
  	$zeilenzeiger++;
		}
		$skills  = MiningDivSkills($charIDs);
	} else {
		$skills = false;
	}
	$smarty->assign('bgcolor', array('-' => '#FFFFFF','#FF0000','#FF0000','#FF8000','#FFFF00','#00FF00','#FF80FF'));
		$smarty->assign('skills', $skills);
		$smarty->assign('isALT', $isALT);
		$smarty->assign('hisMAIN', $hisMAIN);
//			print_r( $hisMAIN);echo "<br>";
		$smarty->assign('numMains', $main);
		$smarty->assign('numAlts', $alt);

}		
		
?>