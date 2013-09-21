<?php

$ORENAMES = array (
		// Standard ore
	"Arkonor",	"Crimson arkonor",	"Prime arkonor",	"Bistot",	"Triclinic bistot",	"Monoclinic bistot",	"Crokite",
	"Sharp crokite",	"Crystalline crokite",	"Dark Ochre",	"Onyx ochre",	"Obsidian ochre",	"Gneiss",	"Iridescent gneiss",
	"Prismatic gneiss",	"Hedbergite",	"Glazed hedbergite",	"Hemorphite",	"Vitric hedbergite",	"Vivid hemorphite",	"Radiant hemorphite",
	"Jaspet",	"Pure jaspet",	"Pristine jaspet",	"Kernite",	"Luminous kernite",	"Fiery kernite",	"Mercoxit",	"Magma mercoxit",
	"Vitreous mercoxit",	"Omber",	"Silvery omber",	"Golden omber",	"Bright Spodumain",	"Spodumain",	"Gleaming spodumain",	"Plagioclase",
	"Azure plagioclase",	"Rich plagioclase",	"Pyroxeres",	"Solid pyroxeres",	"Viscous pyroxeres",	"Scordite",	"Condensed scordite",	"Massive scordite",
	"Veldspar",	"Concentrated veldspar",	"Dense veldspar",
		// Ice
	"Blue ice",	"Clear icicle",	"Dark glitter",	"Enriched clear icicle",	"Gelidus",	"Glacial mass",	"Glare crust",	"Krystallos",	"Pristine white glaze",	"Smooth glacial mass",	"Thick blue ice",	"White glaze");

$SHIPTYPES = array (
	"Assault Ship",
	"Battlecruiser",
	"Battleship",
	"Carrier",
	"Command Ship",
	"Covert Ops",
	"Cruiser",
	"Destroyer",
	"Dreadnought",
	"Exhumer",
	"Freighter",
	"Frigate",
	"Heavy Assault Ship",
	"Industrial Ship",
	"Interceptor",
	"Interdictor",
	"Logistic",
	"Mining Barge",
	"Recon Ship",
	"Shuttle",
	"Transport Ship"
);

$SHIPTYPES[99] = "unclassified";
foreach ($ORENAMES as $ore) {
	$dbfriendly = str_replace(" ", "", ucwords($ore));
	if (!empty ($ORENAME_STR)) {
		$ORENAME_STR .= ", " . $dbfriendly;
	} else {
		$ORENAME_STR = $dbfriendly;
	}
	$DBORE[$ore] = $dbfriendly;
}

function makeDB() {
	/* Connects to the database.
	* Configuration is taken from the MiningMax.db.conf.php file.
	* Returns a database object uppon success,
	* die()'s on any kind of error.
	*/
	
	global $Messages;

	// Create the database object.
	$database = @new mysqli( db_host_fsrclan_miningbuddy,
							 db_user_fsrclan_miningbuddy, 
							 db_pass_fsrclan_miningbuddy, 
							 db_name_fsrclan_miningbuddy );

	/* check connection */
	if (mysqli_connect_errno()) {
		$_SESSION['messages']->showerror("Connect failed: \n". mysqli_connect_error());
	}
	
	
	// and return the database object.
	return $database;
}
?>