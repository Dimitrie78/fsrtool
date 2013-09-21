<?php

$ale->setConfig('parserClass', 'SimpleXMLElement');
if ( isset($_GET['cid']) && !empty($_GET['cid']) ){
	$id = $_GET['cid'];
	$ale->setKey($User->alts[$id]['userID'],$User->alts[$id]['userAPI'],$User->alts[$id]['charID']);
} else {
	$ale->setKey($User->keyID,$User->vCODE,$User->charID);
}

try {
	$xml = $ale->char->CharacterSheet();

	$until = strtotime( $xml->cachedUntil );

	$char = $xml->result;
	
	unset ($dataxml);
	unset ($xml);
	
    $characterID     = (string) $char->characterID;

    $name            = (string) $char->name;
    $race            = (string) $char->race;
    $bloodLine       = (string) $char->bloodLine;
    $gender          = (string) $char->gender;
    $corporationName = (string) $char->corporationName;
	$cloneName		 = (string) $char->cloneName;
    $cloneSkillPoints= (string) $char->cloneSkillPoints;
    $balance         = (string) $char->balance;
    $attEnhancers    = $char->attributeEnhancers;
    $attributes      = array('intelligence' => (string) $char->attributes->intelligence,
                             'charisma'     => (string) $char->attributes->charisma,
                             'perception'   => (string) $char->attributes->perception,
                             'memory'       => (string) $char->attributes->memory,
                             'willpower'    => (string) $char->attributes->willpower);

    $implants   = GetImplants($attEnhancers);
	$training   = GetTrainingData();
	$SkillQueue = GetSkillQueue();
	
    //$skillTraining   = $training;
	
    foreach ($char->rowset as $rs)
	{
		$rsatts = $rs->attributes();
		$rsname = $rsatts[(string) "name"];
		
		foreach ($rs->row as $row)
		{
			if(isset($output[(string) $rsname]))
				$index = count($output[(string) $rsname]);
			else $index = 0;
			foreach ($row->attributes() as $xname => $value)
			{
				$output[(string) $rsname][$index][(string) $xname] = (string) $value;
			}
		}
	}
	
	$skills = $output['skills'];
	
	unset ($char);
	
	$assign = BuildSkillSet($skills, $training);
    		
    // Attributes are defined by some skills... changing them here !
    Attributes($attributes, $assign['skilltree'], $implants);

    // Assign the information
    $smarty->assign('name',              $name);
    $smarty->assign('race',              $race);
    $smarty->assign('bloodLine',         $bloodLine);
    $smarty->assign('gender',            $gender);
    $smarty->assign('corporationName',   $corporationName);
	$smarty->assign('cloneName',		 $cloneName);
	$smarty->assign('cloneSkillPoints',	 number_format($cloneSkillPoints, 0, '.', ' '));
    $smarty->assign('balance',           number_format($balance, 2, '.', ' '));
    if($training['skillName'] != '') {
        $smarty->assign('Training',          $training['skillName']);
        $smarty->assign('ToLevel',           $training['trainingToLevel']);
        $smarty->assign('TrainingID',        $training['trainingTypeID']);
        $smarty->assign('trainingStartTime', $training['trainingStartTime']);
        $smarty->assign('trainingEndTime',   $training['trainingEndTime']);
        $smarty->assign('TrainingTimeLeft',  $training['TrainingTimeLeft']);
		$smarty->assign('TrainingStartSP',	 $training['trainingStartSP']);
		$smarty->assign('TrainingDestSP',	 $training['trainingDestinationSP']);
		$smarty->assign('TrainingSPdone',	 $training['trainingSPdone']);
		$smarty->assign('trainingEndFormat', strtotime($training['trainingEndTime'])-(time()-date("Z")));

    }
	if(is_array($SkillQueue)) {
		$smarty->assign('SkillQueue', $SkillQueue);
	}
    $time     = time();
    $filetime = $until;
    $left     = ($filetime + 3600) - $time;
    $diffDate = $until; #$left;
    $days     = floor($diffDate / 24 / 60 / 60 );
    $diffDate = $diffDate - ($days*24*60*60);
    $hours    = floor($diffDate / 60 / 60);
    $diffDate = ($diffDate - ($hours*60*60));
    $minutes  = floor($diffDate/60);
    $diffDate = $diffDate - ($minutes*60);
    $seconds  = floor($diffDate);
    $smarty->assign('cachetimeleft',     date("m/d/Y G:i:s", $until));
    $smarty->assign('skilltree',         $assign['skilltree']);
    $smarty->assign('skillgroups',       $assign['skillgroups']);
    $smarty->assign('characterID',       $characterID);
    $smarty->assign('attributes',        $attributes);
    $smarty->assign('pageupdateminutes', $minutes);
    $smarty->assign('pageupdateseconds', $seconds);
    $smarty->assign('l1total',           $assign['l1total']);
    $smarty->assign('l1spsformat',       number_format($assign['l1sps'], 0, '', ' '));
    $smarty->assign('l2total',           $assign['l2total']);
    $smarty->assign('l2spsformat',       number_format($assign['l2sps'], 0, '', ' '));
    $smarty->assign('l3total',           $assign['l3total']);
    $smarty->assign('l3spsformat',       number_format($assign['l3sps'], 0, '', ' '));
    $smarty->assign('l4total',           $assign['l4total']);
    $smarty->assign('l4spsformat',       number_format($assign['l4sps'], 0, '', ' '));
    $smarty->assign('l5total',           $assign['l5total']);
    $smarty->assign('l5spsformat',       number_format($assign['l5sps'], 0, '', ' '));
    $smarty->assign('l1sps',             $assign['l1sps']);
    $smarty->assign('l2sps',             $assign['l2sps']);
    $smarty->assign('l3sps',             $assign['l3sps']);
    $smarty->assign('l4sps',             $assign['l4sps']);
    $smarty->assign('l5sps',             $assign['l5sps']);
    $smarty->assign('grptable',          $assign['grptable']);
    $smarty->assign('totalsks',          $assign['count']);
    $smarty->assign('totalsps',          $assign['skillpointstotal']);
    $smarty->assign('skillpointstotal',  number_format($assign['skillpointstotal'], 0, '', ' '));

} catch (Exception $e){
	$Messages->addwarning( $e->getMessage() );
} 


// Getting the training stuff... and only the training stuff... rest is useless.
// We also create the time left info.
function GetTrainingData()
{
    global $skilltreeX,$api,$ale,$Messages;
    
	#$dataxml = $api->getSkillInTraining();
	try {
		$xml = $ale->char->SkillInTraining();
		
		$id = (string) $xml->result->trainingTypeID;
		
		$skillTraining['trainingToLevel']   	= (string) $xml->result->trainingToLevel;
		$skillTraining['trainingTypeID']		= (string) $xml->result->trainingTypeID;
		$skillTraining['trainingStartTime'] 	= (string) $xml->result->trainingStartTime;
		$skillTraining['trainingEndTime']		= (string) $xml->result->trainingEndTime;
		$skillTraining['trainingStartSP']	    = (string) $xml->result->trainingStartSP;
		$skillTraining['trainingDestinationSP'] = (string) $xml->result->trainingDestinationSP;
		
		$skillTraining['skillName'] 		= $skilltreeX[$id]['typeName'];
			
		$now       = time();
		$timediff  = date('Z', time());
		$evetime   = $now - $timediff;
		
		$startTime     = strtotime((string) $xml->result->trainingStartTime);
		$endTime   	   = strtotime((string) $xml->result->trainingEndTime);
		$skillTime	   = $endTime - $startTime;
		$skillTimeDiff = strtotime((string) $xml->result->trainingEndTime) - $evetime;
		$SPtoGo 	   = (string) $xml->result->trainingDestinationSP - (string) $xml->result->trainingStartSP;
		if ( $skillTimeDiff > 0 && $skillTime > 0 ) {
			$prozentDone = $skillTimeDiff / $skillTime;
		} else {
			$prozentDone = 1;
		}
		$currentSP = floor($SPtoGo * $prozentDone) + (string) $xml->result->trainingStartSP;
		
		$skillTraining['trainingSPdone'] = $currentSP;
		

		$trainingleft = (string) $xml->result->trainingEndTime;//substr($data[0], 9, 19);
		$ampm = substr($trainingleft, -2);

		// FIX FOR GMT
		$now       = time();
		$gmmktime  = time();
		$finaltime = $gmmktime - $now;

		$year   = (int)substr($trainingleft, 0, 4);
		$month  = (int)substr($trainingleft, 5, 2);
		$day    = (int)substr($trainingleft, 8, 2);
		$hour   = (int)substr($trainingleft, 11, 2) + (($finaltime > 0) ? floor($finaltime / 60 / 60) : 0); //2007-06-22 16:47:50
		$minute = (int)substr($trainingleft, 14, 2);
		$second = (int)substr($trainingleft, 17, 2);

		$difference = gmmktime($hour, $minute, $second, $month, $day, $year) - $now;
		if ($difference >= 1) {
			$days = floor($difference/86400);
			$difference = $difference - ($days*86400);
			$hours = floor($difference/3600);
			$difference = $difference - ($hours*3600);
			$minutes = floor($difference/60);
			$difference = $difference - ($minutes*60);
			$seconds = $difference;
			$output = "$days Days, $hours Hours, $minutes Minutes and $seconds Seconds.";
		} else {
			$output = "Done !";
		}
		$skillTraining['TrainingTimeLeft']	= $output;
		
		unset ($dataxml);
		unset ($xml);

		return $skillTraining;
	
	} catch (Exception $e){
		$Messages->addwarning( $e->getMessage() );
	} 
}

function GetSkillQueue()
{
    global $skilltreeX,$api,$ale;
    
	#$dataxml = $api->getSkillQueue();
	try {
		$xml = $ale->char->SkillQueue();
		
		$skillQueue = array();
		foreach ($xml->result->rowset->row as $row){
			foreach ($row->attributes() as $name => $value){
				if ( (string) $name == 'queuePosition' ) $pos = (string) $value;
				if ( (string) $name == 'typeID' ) $skillQueue[$pos]['typeName'] = $skilltreeX[(string) $value]['typeName'];
				$skillQueue[$pos][(string) $name] = (string) $value;
			}
		}
		
		foreach ($skillQueue as $key => $value){
			$trainingleft = $value['endTime'];
			$ampm = substr($trainingleft, -2);

			// FIX FOR GMT
			$now       = time();
			$gmmktime  = time();
			$finaltime = $gmmktime - $now;

			$year   = (int)substr($trainingleft, 0, 4);
			$month  = (int)substr($trainingleft, 5, 2);
			$day    = (int)substr($trainingleft, 8, 2);
			$hour   = (int)substr($trainingleft, 11, 2); // + (($finaltime > 0) ? floor($finaltime / 60 / 60) : 0); //2007-06-22 16:47:50
			$minute = (int)substr($trainingleft, 14, 2);
			$second = (int)substr($trainingleft, 17, 2);

			$difference = gmmktime($hour, $minute, $second, $month, $day, $year) - $now;
			if ($difference >= 1) {
				$days = floor($difference/86400);
				$difference = $difference - ($days*86400);
				$hours = floor($difference/3600);
				$difference = $difference - ($hours*3600);
				$minutes = floor($difference/60);
				$difference = $difference - ($minutes*60);
				$seconds = $difference;
				$output = "$days Days, $hours Hours, $minutes Minutes and $seconds Seconds.";
			} else {
				$output = "Done !";
			}
			$skillQueue[$key]['timeLeft'] = $output;
			$skillQueue[$key]['formatetEndTime'] = strtotime($value['endTime'])-(time()-date("Z"));
		}		
		//echo'<pre>';print_r($skillQueue);echo'</pre>';
		
		unset ($dataxml);
		unset ($xml);

		return $skillQueue;
	
	} catch (Exception $e){
		$Messages->addwarning( $e->getMessage() );
	} 
}

function GetImplants($attEnhancers = array())
{

    if (!$attEnhancers) {
        return false;
    }
	
	$implants = array();

    if (count($attEnhancers) > 0) {
        $impInt = ((isset($attEnhancers->intelligenceBonus)) ? (string) $attEnhancers->intelligenceBonus->augmentatorValue : 0);
        $impCha = ((isset($attEnhancers->charismaBonus))     ? (string) $attEnhancers->charismaBonus->augmentatorValue     : 0);
        $impPer = ((isset($attEnhancers->perceptionBonus))   ? (string) $attEnhancers->perceptionBonus->augmentatorValue   : 0);
        $impMem = ((isset($attEnhancers->memoryBonus))       ? (string) $attEnhancers->memoryBonus->augmentatorValue       : 0);
        $impWil = ((isset($attEnhancers->willpowerBonus))    ? (string) $attEnhancers->willpowerBonus->augmentatorValue    : 0);
        $implants        = array('intelligence' => $impInt,
                                 'charisma'     => $impCha,
                                 'perception'   => $impPer,
                                 'memory'       => $impMem,
                                 'willpower'    => $impWil);
    }

    return $implants;

}

function BuildSkillSet($skills, $training)
{
    global $skilltreeX;
    reset($skilltreeX);

    $x = 0;
    $y = 0;
    $count            = count($skills);///2;
    $skilltree        = array();
    $skillgroups      = array();
    $skillpointstotal = 0;
    $l1total = $l2total = $l3total = $l4total = $l5total = 0;
	$l1sps = $l2sps = $l3sps = $l4sps = $l5sps = 0;
    $alltotal         = 0;
//echo '<pre>';print_r($skilltreeX);echo '</pre>';exit;

    $dumb = array();
    foreach ($skills as $skill) {
        $dumb[$skill['typeID']] = $skill;
    }
    $skills = $dumb;

    foreach ($skilltreeX as $skill) {
        if (isset($skills[$skill['typeID']])) {

            $typeID      = $skills[$skill['typeID']]['typeID'];
            $groupID     = $skilltreeX[$typeID]['groupID'];
            $groupName   = $skilltreeX[$typeID]['groupName'];
            $skillpoints = $skills[$skill['typeID']]['skillpoints'];
            $level       = $skills[$skill['typeID']]['level'];
            $typeName    = $skilltreeX[$typeID]['typeName'];
            $rank        = $skilltreeX[$typeID]['rank'];

            $skillgroups[$groupID] = $groupName;

            // Temp fix
            $skilllevel1 = 250    * $rank;
            $skilllevel2 = 1414   * $rank;
            $skilllevel3 = 8000   * $rank;
            $skilllevel4 = 45255  * $rank;
            $skilllevel5 = 256000 * $rank;

            $flag = (($typeID == $training['trainingTypeID']) ? 61 : 0);
			$skillpoints = $flag == 0 ? $skillpoints : $training['trainingSPdone'];
            $skilltree[$groupID][$typeID] = array('groupID'     => $groupID,
                                                  'groupName'   => $groupName,
                                                  'typeName'    => $typeName,
                                                  'typeID'      => $typeID,
                                                  'flag'        => $flag,
                                                  'rank'        => $rank,
                                                  'skillpoints' => $skillpoints,
                                                  'level'       => $level,
                                                  'skilllevel1' => $skilllevel1,
                                                  'skilllevel2' => $skilllevel2,
                                                  'skilllevel3' => $skilllevel3,
                                                  'skilllevel4' => $skilllevel4,
                                                  'skilllevel5' => $skilllevel5);

            $skillsearch["$typeName"] = array('level' => $level, 'trained' => 0);

            $alltotal++;
            switch ($level) {
                case 1:
                    $l1total = $l1total+ 1;
                    $l1sps   = $l1sps  + $skillpoints;
                    break;
                case 2:
                    $l2total = $l2total+ 1;
                    $l2sps   = $l2sps  + $skillpoints;
                    break;
                case 3:
                    $l3total = $l3total+ 1;
                    $l3sps   = $l3sps  + $skillpoints;
                    break;
                case 4:
                    $l4total = $l4total+ 1;
                    $l4sps   = $l4sps  + $skillpoints;
                    break;
                case 5:
                    $l5total = $l5total+ 1;
                    $l5sps   = $l5sps  + $skillpoints;
                    break;
                case 0:
                    break;
            }

            $skillpointstotal = $skillpointstotal + $skillpoints;
        }
    }

    foreach ($skilltree as $grpid => $st) {
        $spcount = 0;
		$skcount = 0;
        foreach ($st as $s) {
            $spcount = $spcount+$s['skillpoints'];
			$skcount ++;
        }

        $grptable[$grpid]['grpname'] = $skilltree[$grpid][$s['typeID']]['groupName'];
		$grptable[$grpid]['skcount'] = $skcount;
        $grptable[$grpid]['spcount'] = number_format($spcount, 0, '', '.');
    }

    $return = array('grptable' => $grptable,
                    'skilltree' => $skilltree,
                    'skillgroups' => $skillgroups,
                    'l1sps' => $l1sps,
                    'l1total' => $l1total,
                    'l2sps' => $l2sps,
                    'l2total' => $l2total,
                    'l3sps' => $l3sps,
                    'l3total' => $l3total,
                    'l4sps' => $l4sps,
                    'l4total' => $l4total,
                    'l5sps' => $l5sps,
                    'l5total' => $l5total,
                    'count'   => $count,
                    'skilltreeX' => $skilltreeX,
                    'skillsearch' => $skillsearch,
                    'skillpointstotal' => $skillpointstotal);

    return $return;
}

function Attributes(&$attributes, $skilltree, $implants)
{

    $learning = 1;
    if (isset($skilltree[267][3374]['level'])) {
        $learning = $learning + (float)($skilltree[267][3374]['level'] * 0.02);
    }

    // Intelligence
    $int = $attributes['intelligence'];
    if (isset($skilltree[267][3377]['level'])) {
        $int = $int + $skilltree[267][3377]['level'];
        if (isset($skilltree[267][12376]['level'])) {
            $int = $int + $skilltree[267][12376]['level'];
        }
    }
    if (isset($implants['intelligence'])) {
        $int += $implants['intelligence'];
    }

    $int = (float)($int * $learning);

    $attributes['intelligence'] = $int;

    // Perception
    $per = $attributes['perception'];
    if (isset($skilltree[267][3379]['level'])) {
        $per = $per + $skilltree[267][3379]['level'];
        if (isset($skilltree[267][12387]['level'])) {
            $per = $per + $skilltree[267][12387]['level'];
        }
    }
    if (isset($implants['perception'])) {
        $per += $implants['perception'];
    }

    $per = (float)($per * $learning);

    $attributes['perception'] = $per;

    // Charisma
    $cha = $attributes['charisma'];
    if (isset($skilltree[267][3376]['level'])) {
        $cha = $cha + $skilltree[267][3376]['level'];
        if (isset($skilltree[267][12383]['level'])) {
            $cha = $cha + $skilltree[267][12383]['level'];
        }
    }
    if (isset($implants['charisma'])) {
        $cha += $implants['charisma'];
    }

    $cha = (float)($cha * $learning);

    $attributes['charisma'] = $cha;

    // Willpower
    $wil = $attributes['willpower'];
    if (isset($skilltree[267][3375]['level'])) {
        $wil = $wil + $skilltree[267][3375]['level'];
        if (isset($skilltree[267][12386]['level'])) {
            $wil = $wil + $skilltree[267][12386]['level'];
        }
    }
    if (isset($implants['willpower'])) {
        $wil += $implants['willpower'];
    }

    $wil = (float)($wil * $learning);

    $attributes['willpower'] = $wil;

    // Memory
    $mem = $attributes['memory'];
    if (isset($skilltree[267][3378]['level'])) {
        $mem = $mem + $skilltree[267][3378]['level'];
        if (isset($skilltree[267][12385]['level'])) {
            $mem = $mem + $skilltree[267][12385]['level'];
        }
    }
    if (isset($implants['memory'])) {
        $mem += $implants['memory'];
    }

    $mem = (float)($mem * $learning);

    $attributes['memory'] = $mem;

}

?>
