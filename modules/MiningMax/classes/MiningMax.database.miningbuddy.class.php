<?php
defined('fsr_tool') or die;

class MiningbuddyDB
{
	private $conn = false;
	private $sqlstrings = array();
	private $debug = 0;
	private $Messages;
	
	function __construct()
	{
		$this->Messages = $_SESSION["messages"];
		if (!($this->conn))
		{
			if(!($this->conn = mysql_connect(db_host_fsrclan_miningbuddy, db_user_fsrclan_miningbuddy, db_pass_fsrclan_miningbuddy))) 
			{
				$_SESSION["messages"]->showerror("SQL-Error Verbindung zum Server nicht erfolgreich!");
				exit();
			} 

			if(!(mysql_select_db(db_name_fsrclan_miningbuddy,$this->conn))) 
			{
				$_SESSION["messages"]->showerror("SQL-Error Datenbank konnte nicht gefunden werden!");
				exit();
			}
		}
	}
	
	function doQuery($sqlstring,$from)
	{
		$result = mysql_query($sqlstring, $this->conn)
			or $_SESSION["messages"]->addwarning("Schwerer Fehler [database/doQuery: $from]<BR>\nDB: ". mysql_error()."<BR>\n".$sqlstring);
		if ($this->debug == "1") $_SESSION["messages"]->addwarning($sqlstring);
		if ($this->debug == "1") $_SESSION["messages"]->addwarning($result);
		
		return $result;
	}
	
	function list_open_runs()
	{
		// nur offene
		//$sqlstring="select r.id,u.username,r.starttime,r.endtime,r.location,r.corpkeeps,r.isofficial,r.islocked from runs as r, users as u where r.supervisor=u.id and endtime is NULL order by id desc limit 20";		
		// alle
		$sqlstring="select r.id,u.username,r.starttime,r.endtime,r.location,r.corpkeeps,r.isofficial,r.islocked from runs as r, users as u where r.supervisor=u.id order by id desc limit 20";
		$result = $this->doQuery($sqlstring,"Database::list_open_runs");
		
		return($result);
	}

	function get_alias_from_mb($wert)
	{
		$sqlstring = "select * from users where id='".$wert."' limit 1";
		$result = $this->doQuery($sqlstring,"Database::list_open_runs");
		$user=mysql_fetch_assoc($result);
		
		while ($user[]=mysql_fetch_assoc($result))
  		{
  			print_r($user);
  		}
		
		
		
		return($result);
	
	}
	
	function changeOreValues($mineralsArray,$userID)
	{
	
		$HeavyWater          = $mineralsArray['Heavy Water'];
		$Isogen              = $mineralsArray['Isogen'];
		$LiquidOzone         = $mineralsArray['Liquid Ozone'];
		$Megacyte            = $mineralsArray['Megacyte'];
		$Mexallon            = $mineralsArray['Mexallon'];
		$Morphite            = $mineralsArray['Morphite'];
		$NitrogenIsotopes    = $mineralsArray['Nitrogen Isotopes'];
		$Nocxium             = $mineralsArray['Nocxium'];
		$Pyerite             = $mineralsArray['Pyerite'];
		$StrontiumClathrates = $mineralsArray['Strontium Clathrates'];
		$Tritanium           = $mineralsArray['Tritanium'];
		$Zydrine             = $mineralsArray['Zydrine'];
	
		$ORENAMES = array (
				// Standard ore
			"Arkonor" => ( ( (333 * $Megacyte) + (300 * $Tritanium) + (166 * $Zydrine) ) / 200 ),
			"AzurePlagioclase" => ( ( (538 * $Pyerite) + (269 * $Mexallon) + (269 * $Tritanium) ) / 333 ),
			"Bistot" => ( ( (341 * $Zydrine) + (170 * $Megacyte) + (170 * $Pyerite) ) / 200 ),
			"BlueIce" => ( ( (50 * $HeavyWater) + (300 * $OxygenIsotopes) + (25 * $LiquidOzone) + (1 * $StrontiumClathrates) ) / 1 ),
			"BrightSpodumain" => ( ( (431 * $Pyerite) + (147 * $Megacyte) + (3350 * $Tritanium) ) / 250 ),
			"ClearIcicle" => ( ( (1 * $StrontiumClathrates) + (25 * $LiquidOzone) + (300 * $HeliumIsotopes) + (50 * $HeavyWater) ) / 1 ),
			"ConcentratedVeldspar" => ( ( (1050 * $Tritanium) ) / 333 ),
			"CondensedScordite" => ( ( (875 * $Tritanium) + (437 * $Pyerite) ) / 333 ),
			"CrimsonArkonor" => ( ( (350 * $Megacyte) + (315 * $Tritanium) + (174 * $Zydrine) ) / 200 ),
			"Crokite" => ( ( (663 * $Zydrine) + (331 * $Tritanium) + (331 * $Nocxium) ) / 250 ),
			"CrystallineCrokite" => ( ( (729 * $Zydrine) + (364 * $Tritanium) + (364 * $Nocxium) ) / 250 ),
			"DarkGlitter" => ( ( (50 * $StrontiumClathrates) + (500 * $HeavyWater) + (1000 * $LiquidOzone) ) / 1 ),
			"DarkOchre" => ( ( (250 * $Zydrine) + (250 * $Tritanium) + (500 * $Nocxium) ) / 400 ),
			"DenseVeldspar" => ( ( (1100 * $Tritanium) ) / 333 ),
			"EnrichedClearIcicle" => ( ( (350 * $HeliumIsotopes) + (75 * $HeavyWater) + (1 * $StrontiumClathrates) + (40 * $LiquidOzone) ) / 1 ),
			"FieryKernite" => ( ( (425 * $Isogen) + (425 * $Tritanium) + (850 * $Mexallon) ) / 400 ),
			"Gelidus" => ( ( (500 * $LiquidOzone) + (75 * $StrontiumClathrates) + (250 * $HeavyWater) ) / 1 ),
			"GlacialMass" => ( ( (1 * $StrontiumClathrates) + (50 * $HeavyWater) + (300 * $HydrogenIsotopes) + (25 * $LiquidOzone) ) / 1 ),
			"Glarecrust" => ( ( (25 * $StrontiumClathrates) + (1000 * $HeavyWater) + (500 * $LiquidOzone) ) / 1 ),
			"GlazedHedbergite" => ( ( (779 * $Isogen) + (389 * $Nocxium) + (319 * $Pyerite) + (35 * $Zydrine) ) / 500 ),
			"GleamingSpodumain" => ( ( (3509 * $Tritanium) + (451 * $Pyerite) + (154 * $Megacyte) ) / 250 ),
			"Gneiss" => ( ( (171 * $Mexallon) + (343 * $Isogen) + (171 * $Tritanium) + (171 * $Zydrine) ) / 400 ),
			"GoldenOmber" => ( ( (135 * $Pyerite) + (338 * $Isogen) + (338 * $Tritanium) ) / 500 ),
			"Hedbergite" => ( ( (32 * $Zydrine) + (708 * $Isogen) + (354 * $Nocxium) + (290 * $Pyerite) ) / 500 ),
			"Hemorphite" => ( ( (212 * $Isogen) + (260 * $Pyerite) + (424 * $Nocxium) + (60 * $Mexallon) + (650 * $Tritanium) + (28 * $Zydrine) ) / 500 ),
			"IridescentGneiss" => ( ( (180 * $Tritanium) + (180 * $Zydrine) + (180 * $Mexallon) + (360 * $Isogen) ) / 400 ),
			"Jaspet" => ( ( (437 * $Pyerite) + (8 * $Zydrine) + (518 * $Mexallon) + (259 * $Tritanium) + (259 * $Nocxium) ) / 500 ),
			"Kernite" => ( ( (386 * $Isogen) + (386 * $Tritanium) + (773 * $Mexallon) ) / 400 ),
			"Krystallos" => ( ( (100 * $HeavyWater) + (250 * $LiquidOzone) + (100 * $StrontiumClathrates) ) / 1 ),
			"LuminousKernite" => ( ( (405 * $Tritanium) + (812 * $Mexallon) + (405 * $Isogen) ) / 400 ),
			"MagmaMercoxit" => ( ( (557 * $Morphite) ) / 250 ),
			"MassiveScordite" => ( ( (916 * $Tritanium) + (458 * $Pyerite) ) / 333 ),
			"Mercoxit" => ( ( (530 * $Morphite) ) / 250 ),
			"MonoclinicBistot" => ( ( (375 * $Zydrine) + (187 * $Megacyte) + (187 * $Pyerite) ) / 200 ),
			"ObsidianOchre" => ( ( (550 * $Nocxium) + (275 * $Zydrine) + (275 * $Tritanium) ) / 400 ),
			"Omber" => ( ( (123 * $Pyerite) + (307 * $Isogen) + (307 * $Tritanium) ) / 500 ),
			"OnyxOchre" => ( ( (263 * $Zydrine) + (263 * $Tritanium) + (525 * $Nocxium) ) / 400 ),
			"Plagioclase" => ( ( (256 * $Tritanium) + (512 * $Pyerite) + (256 * $Mexallon) ) / 333 ),
			"PrimeArkonor" => ( ( (366 * $Megacyte) + (330 * $Tritanium) + (183 * $Zydrine) ) / 200 ),
			"PrismaticGneiss" => ( ( (188 * $Zydrine) + (188 * $Mexallon) + (377 * $Isogen) + (188 * $Tritanium) ) / 400 ),
			"PristineJaspet" => ( ( (285 * $Tritanium) + (285 * $Nocxium) + (481 * $Pyerite) + (9 * $Zydrine) + (570 * $Mexallon) ) / 500 ),
			"PristineWhiteGlaze" => ( ( (1 * $StrontiumClathrates) + (75 * $HeavyWater) + (350 * $NitrogenIsotopes) + (40 * $LiquidOzone) ) / 1 ),
			"PureJaspet" => ( ( (272 * $Tritanium) + (272 * $Nocxium) + (459 * $Pyerite) + (8 * $Zydrine) + (544 * $Mexallon) ) / 500 ),
			"Pyroxeres" => ( ( (11 * $Nocxium) + (59 * $Pyerite) + (120 * $Mexallon) + (844 * $Tritanium) ) / 333 ),
			"RadiantHemorphite" => ( ( (286 * $Pyerite) + (466 * $Nocxium) + (66 * $Mexallon) + (717 * $Tritanium) + (31 * $Zydrine) + (233 * $Isogen) ) / 500 ),
			"RichPlagioclase" => ( ( (563 * $Pyerite) + (282 * $Mexallon) + (282 * $Tritanium) ) / 333 ),
			"Scordite" => ( ( (416 * $Pyerite) + (833 * $Tritanium) ) / 333 ),
			"SharpCrokite" => ( ( (348 * $Tritanium) + (348 * $Nocxium) + (696 * $Zydrine) ) / 250 ),
			"SilveryOmber" => ( ( (322 * $Isogen) + (322 * $Tritanium) + (129 * $Pyerite) ) / 500 ),
			"SmoothGlacialMass" => ( ( (350 * $HydrogenIsotopes) + (40 * $LiquidOzone) + (1 * $StrontiumClathrates) + (75 * $HeavyWater) ) / 1 ),
			"SolidPyroxeres" => ( ( (886 * $Tritanium) + (12 * $Nocxium) + (62 * $Pyerite) + (126 * $Mexallon) ) / 333 ),
			"Spodumain" => ( ( (140 * $Megacyte) + (3190 * $Tritanium) + (410 * $Pyerite) ) / 250 ),
			"ThickBlueIce" => ( ( (40 * $LiquidOzone) + (1 * $StrontiumClathrates) + (75 * $HeavyWater) + (350 * $OxygenIsotopes) ) / 1 ),
			"TriclinicBistot" => ( ( (358 * $Zydrine) + (179 * $Megacyte) + (179 * $Pyerite) ) / 200 ),
			"Veldspar" => ( ( (1000 * $Tritanium) ) / 333 ),
			"ViscousPyroxeres" => ( ( (65 * $Pyerite) + (132 * $Mexallon) + (928 * $Tritanium) + (12 * $Nocxium) ) / 333 ),
			"VitreousMercoxit" => ( ( (583 * $Morphite) ) / 250 ),
			"VitricHedbergite" => ( ( (34 * $Zydrine) + (743 * $Isogen) + (372 * $Nocxium) + (305 * $Pyerite) ) / 500 ),
			"VividHemorphite" => ( ( (63 * $Mexallon) + (683 * $Tritanium) + (29 * $Zydrine) + (223 * $Isogen) + (273 * $Pyerite) + (445 * $Nocxium) ) / 500 ),
			"WhiteGlaze" => ( ( (25 * $LiquidOzone) + (1 * $StrontiumClathrates) + (50 * $HeavyWater) + (300 * $NitrogenIsotopes) ) / 1 )
		);
	
		$this->update_buddy($ORENAMES,$userID);
	}
	
	function update_buddy($ORENAMES,$userID)
	{
		// Lets set the userID(!)
		//$userID = 2;
		$TIMEMARK = time();
		
		//mysql_select_db($db, $connection);
		$insertSQL = "INSERT INTO orevalues (modifier, time) VALUES ('$userID', '$TIMEMARK');";
		$this->doQuery($insertSQL,"Database::update_buddy");
		
		// Now loop through all possible oretypes.
		foreach ($ORENAMES as $ORE => $VALUE) {
			// Write the new, updated values.
			#echo $ORE.' ## '.$VALUE.'<br>';
			$updateSQL = "UPDATE orevalues SET " . $ORE . "Worth='$VALUE' WHERE time='$TIMEMARK';";
		  	$this->doQuery($updateSQL,"Database::update_buddy");
		}
	}

	
	
	
}
?>
