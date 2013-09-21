<?php
/*
 * This class loads and supplies functions with eve data, like security 
 * level, constellation etc.
 */

class solarSystem {

	// Variables needed.
	private $valid;
	private $DB;

	// Solar System stuff
	private $solarSystemName;
	private $solarSystemRegion;
	private $solarSystemConstellation;
	private $solarSystemID;
	private $solarSystemSecurity;

	// Constellation Stuff
	private $constellationName;
	private $constellationID;
	private $constellationRegion;

	// Region Stuff
	private $regionName;
	private $regionID;

	// Constructor, called with the name of the Solar System.
	public function __construct($assumedName) {

		// We need the DB to load stuff.
		global $DB;
		$this->DB = & $DB;

		// Try to load the System.
		if (is_numeric($assumedName)) {
			$assumedSystem = $this->DB->query("SELECT * FROM eve_SolarSystems WHERE solarSystemID = '$assumedName' LIMIT 1");
		} else {
			
			$assumedSystem = $this->DB->query("SELECT * FROM eve_SolarSystems WHERE UPPER(solarSystemName) = '".strtoupper($assumedName)."' LIMIT 1");
			// echo "SELECT * FROM eve_SolarSystems WHERE UPPER(solarSystemName) = '".$assumedName."' LIMIT 1";
			
		}

		// Check if there is such a system
		if ($assumedSystem->num_rows == 0) {
			// echo "nope";
			// Nope  :(
			$this->solarSystemName = $assumedName;
			return (false);
		} else {
			// echo "yes";
			// Yes :)
			$SystemDB = $assumedSystem->fetch_assoc();
			$this->solarSystemName 			= $SystemDB[solarSystemName];
			$this->solarSystemRegion 		= $SystemDB[regionID];
			$this->solarSystemConstellation = $SystemDB[constellationID];
			$this->solarSystemID 			= $SystemDB[solarSystemID];
			$this->solarSystemSecurity 		= number_format($SystemDB[security], 1);

			// But wait, there is more! We need to load the Constellation!
			$ConstellationDB = $this->DB->query("SELECT * FROM eve_Constellations WHERE ConstellationID ='" . $this->solarSystemConstellation . "' LIMIT 1");
			$Constellation = $ConstellationDB->fetch_assoc();
			$this->constellationName   = $Constellation[constellationName];
			$this->constellationID     = $Constellation[constellationID];
			$this->constellationRegion = $Constellation[regionID];

			// Even more! Region!
			$RegionDB = $this->DB->query("SELECT * FROM eve_Regions WHERE regionID = '" . $this->solarSystemRegion . "' LIMIT 1");
			$Region = $RegionDB->fetch_assoc();
			$this->regionName = $Region[regionName];
			$this->regionID   = $Region[regionID];

			// We found something!
			$this->valid = true;
			return (true);
		}
	}

	public function getName() {
		return $this->solarSystemName;
	}

	public function getSolarSystemID() {
		return $this->solarSystemID;
	}

	public function getRegion() {
		return $this->regionName;
	}

	public function getConstellation() {
		return $this->constellationName;
	}

	public function getSecurity() {
		return $this->solarSystemSecurity;
	}

	public function valid() {
		return $this->valid;
	}

	public function getNeighbouringSystems() {
		/*
		 * This will query the database for other systems in the same region and constellation.
		 */
		$nbs = $this->DB->query("SELECT solarSystemName FROM eve_SolarSystems WHERE regionID='" . $this->regionID . "' AND constellationID='" . $this->constellationID . "' ORDER BY solarSystemName ASC");

		// Are there any systems?	
		if ($nbs->num_rows > 0) {
			// There are! Now loop'em!
			while ($neighbour = $nbs->fetch_assoc()) {
				// Add to array.
				$array[] = $neighbour[solarSystemName];
			}
			// Return array.
			return ($array);
		} else {
			// This system has its own constellation.
			return (false);
		}
	}

	public function makeFancyLink() {
		if ($this->valid) {
			return ("<a href=\"index.php?action=browse&mode=0&id=" . $this->solarSystemID . "\">" . ucfirst($this->solarSystemName) . "</a>");
		} else {
			return (ucfirst($this->solarSystemName));
		}
	}

	public function makeInfoTable() {
		$systemTable = new table(2, true);
		$systemTable->addHeader("System Information");
		if ($this->valid()) {

			$systemTable->addRow();
			$systemTable->addCol("System Name:");
			$systemTable->addCol("<a href=\"index.php?action=browse&mode=0&id=" . $this->solarSystemID . "\">" . $this->getName() . "</a>");

			$systemTable->addRow();
			$systemTable->addCol("Constellation:");
			$systemTable->addCol($this->getConstellation());

			$systemTable->addRow();
			$systemTable->addCol("Region:");
			$systemTable->addCol($this->getRegion());

			$systemTable->addRow();
			$systemTable->addCol("Security Status:");
			$systemTable->addCol($this->getSecurity());

		} else {
			$systemTable = new table(2, true);
			$systemTable->addHeader("System Information");

			$systemTable->addRow();
			$systemTable->addCol("System Name:");
			$systemTable->addCol(ucfirst($this->solarSystemName));

			$systemTable->addHeaderCentered("No EVE data has been found for this system in the database.");
		}
		return ($systemTable->flush());
	}

	// Show other Systems.
	public function makeConstellationTable() {

		// First we check if the system name we got is valid.
		if ($this->valid()) {

			// It is, so lets load all other systems in the same region and constellation.
			$otherSystems = $this->DB->query("SELECT * FROM eve_SolarSystems WHERE constellationID = '" . $this->constellationID . "' AND regionID ='" . $this->regionID . "' ORDER BY solarSystemName ASC");

			// Sanity check: Do we have more than 0?
			if ($otherSystems->num_rows > 0) {

				// Create the table header.
				$table = new table(2, true);
				$table->addHeader(">> Other Solarsystems in " . $this->regionName);

				// We need this for a new table line.
				$newline = true;

				// Loop through all solarsystems.
				while ($otherSystem = $otherSystems->fetch_assoc()) {

					// If this is a new table row, add one.
					if ($newline) {
						$table->addRow();
						$newline = false;
					} else {
						$newline = true;
					}

					// Add the information.
					$curRunsDB = $this->DB->query("SELECT COUNT(location) FROM runs WHERE location = '" . $otherSystem[solarSystemName] . "' AND endtime IS NULL");
					$curRuns = $curRunsDB->fetch_row();
					$curRunsDB->close();
					if ($curRuns[0] > 0) {
						$curRuns = "(" . $curRuns[0] . " active runs)";
					} else {
						$curRuns = "";
					}

					$table->addCol("<a href=\"index.php?action=browse&mode=0&id=" . $otherSystem[solarSystemID] . "\">" . $otherSystem[solarSystemName] . "</a> (" . number_format($otherSystem[security], 1) . ") $curRuns");
				}
			}

			// Add missing column, if any.
			if (!$newline) {
				$table->addCol();
			}

			// Return the table.
			return ($table->flush());
		}
	}

}
?>