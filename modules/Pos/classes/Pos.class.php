<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class Pos {

	public $posID = 0;
	public $corpID = 0;
	public $allyID = 0;
	public $manager = '';
	
	public $sma = 0;
	public $cha = 0;
	public $jb = 0;
	public $cj = 0;
	public $SeeGlobal = 0;
	
	public $usageFlags = 0;
    public $deployFlags = 0;
    public $allowCorporationMembers = 0;
    public $allowAllianceMembers = 0;
    public $useStandingsFrom = 0;
    public $onStandingDrop = 0;
    public $onStatusDrop_enabled = 0;
    public $onStatusDrop_standing = 0;
    public $onAggression = 0;
    public $onCorporationWar = 0;
  
	public $towercpu = 0;
	public $towerpg = 0;
	public $towercar = 0;
	public $towerstr = 0;
	
	public $sov = false;
	public $charters_need = false;
	
	public $tower;
	public $region;
	public $conste;
	public $moon;
	public $state;
	public $stateTimestamp;
	
	public $icon32 = '';
	public $icon64 = '';
	
	
	private $towerfuel;
	private $hoursago = 0;
	private $secsago  = 0;
	
	private $world;
	
	public function __construct( $id, $world = null ) {
		$this->world = $world;
		if(!empty($id)) {
			$row = $world->getPosByID($id);
			#echo '<pre>'; print_r($row); echo '</pre>'; die;
			$this->posID = $id;
			$this->allyID = $row['allyID'];
			$this->set($row);
			
		}
	}
	
	private function set($row) {
		$this->stateTimestamp = $row['stateTimestamp'];
		$offset = date_offset_get(date_create());
		$now = (time()-$offset)/3600;
		if($row['state'] == 4) { 
			$this->hoursago = ceil($now-(strtotime($this->stateTimestamp)/3600));
			$ago = ((time()-$offset)-(strtotime($this->stateTimestamp)));
			$this->secsago = $ago;
		}
		if($row['state'] == 3) {
			$this->rftime = $this->daycalc(ceil($now-(strtotime($this->stateTimestamp)/3600)));
		}
		$security = $this->world->SystemSecurity($row['locationID']);
        if($security>=0.45) $this->charters_need = true;
        else 				$this->charters_need = false;
        
		$this->sov 		= $this->world->Sovereignty($row['locationID'],$this->allyID);
		
		$this->corpID 	= $row['corpID'];
		$this->manager 	= $row['manager'];
		
		$this->sma		= $row['sma'];
		$this->cha		= $row['cha'];
		$this->jb		= $row['jb'];
		$this->cj		= $row['cj'];
		$this->SeeGlobal = $row['global'];
		
		$this->usageFlags 			   	= $row['usageFlags'];
		$this->deployFlags 			   	= $row['deployFlags'];
		$this->allowCorporationMembers 	= $row['allowCorporationMembers'];
		$this->allowAllianceMembers    	= $row['allowAllianceMembers'];
		$this->useStandingsFrom 	   	= $row['useStandingsFrom'] == $this->allyID ? 1 : 0;
		$this->onStandingDrop 			= $row['onStandingDrop'];
		$this->onStandingDrop_standing	= $row['onStandingDrop'] > 0 ? $row['onStandingDrop']/100 : '0.0';
		$this->onStatusDrop_enabled 	= $row['onStatusDrop_enabled'];
		$this->onStatusDrop_standing 	= $row['onStatusDrop_standing'];
		$this->onAggression 			= $row['onAggression'];
		$this->onCorporationWar 		= $row['onCorporationWar'];
		
		$this->cpu	 	= $row['cpu'];
		$this->pg	 	= $row['pg'];
		$this->itemName	= $row['itemName'];
		$this->towercar = $row['towercapacity'];
		$this->towerstr = $row['towerstrontbay'];
		$this->towercpu	= $row['towercpu'];
		$this->towerpg	= $row['towerpg'];
		$this->tower 	= str_replace("Control Tower","",$row['typeName']);
		$this->region 	= $row['regionName'];
		$this->conste 	= $row['constellationName'];
		$this->system 	= $row['solarSystemName'];
		$this->moon 	= $row['moonName'];
		$this->moonName	= str_replace($row['solarSystemName'],"",$row['moonName']);
		$this->icon32 	= "<img src=\"".MODULE_DIR . ACTIVE_MODULE."/img/".$row['typeID']."_32.png\" width=\"32\" height=\"32\" />";
		$this->icon64 	= "<img src=\"".MODULE_DIR . ACTIVE_MODULE."/img/".$row['typeID']."_64.png\" width=\"64\" height=\"64\" />";
		$this->status   = $row['state'];
		$this->state 	= ($row['state'] == 4 ? 'Online' : 
						  ($row['state'] == 3 ? 'Reinforced' : 'Offline'));
						  
		$this->resource= $this->get_towerresurce($row['typeID']);
		
		$this->towerfuel= array('Stront'	=> isset($row['fuel'][16275]) ? $row['fuel'][16275] : 0,
								'Blocks'	=> (isset($row['fuel'][4247]) ? $row['fuel'][4247] :		// Helium Blocks
											(isset($row['fuel'][4312]) ? $row['fuel'][4312] :			// Oxygen Blocks
											(isset($row['fuel'][4051]) ? $row['fuel'][4051] :			// Nitrogen Blocks
											(isset($row['fuel'][4246]) ? $row['fuel'][4246] : 0)))), 	// Hydrogen Blocks
								'Charters' => (isset($row['fuel'][24592]) ? $row['fuel'][24592] :		// Amarr Empire Starbase Charter
											(isset($row['fuel'][24593]) ? $row['fuel'][24593] :			// Caldari State Starbase Charter
											(isset($row['fuel'][24594]) ? $row['fuel'][24594] :			// Gallente Federation Starbase Charter
											(isset($row['fuel'][24595]) ? $row['fuel'][24595] :			// Minmatar Republic Starbase Charter
											(isset($row['fuel'][24596]) ? $row['fuel'][24596] :			// Khanid Kingdom Starbase Charter
											(isset($row['fuel'][24597]) ? $row['fuel'][24597] : 0)))))));// Ammatar Mandate Starbase Charter
		
		
		$this->uptime = $this->uptimecalc();
		
		
		$this->Blocks 		   = $this->daycalc($this->uptime['Blocks']);
		$this->Charters 		   = $this->daycalc($this->uptime['charters']);
		$this->StrontiumCalthrates = $this->daycalc($this->uptime['strontium']);
				
		$this->online 	  = $this->online($this->uptime);
		$this->onlinetime = $this->daycalc($this->online);
		$this->fuel_opti  = $this->fuel_calc();
		$this->fuel_diff  = $this->getOptimalDifference($this->fuel_opti);
		
		#echo '<pre>'; print_r($this); echo '</pre>'; die;
	}
	
	private function get_towerresurce( $id ) {
		if($this->sov) $sov = '0.75'; else $sov = 1;
		
		$res = $this->world->towerresurce( $id );
		foreach ( $res as $row ) {
			if($row) {
				switch ($row['resourceTypeID']) {
					case '16275': $resource['StrontiumClathrates'] = ceil($row['quantity']*$sov); break;
					case '4247': $resource['Blocks']	= ceil($row['quantity']*$sov); $this->raseBlocks = 'Amarr Blocks'; break;
					case '4312': $resource['Blocks']	= ceil($row['quantity']*$sov); $this->raseBlocks = 'Gallente Blocks'; break;
					case '4051': $resource['Blocks']	= ceil($row['quantity']*$sov); $this->raseBlocks = 'Caldari Blocks'; break;
					case '4246': $resource['Blocks']	= ceil($row['quantity']*$sov); $this->raseBlocks = 'Minmatar Blocks'; break;
				}
			}
		}
		#echo '<pre>'; print_r($resource); die;
		return $resource;
	}
	
	private function daycalc($hours) {
        if ($hours >= "24") {
            $d = floor($hours / 24);
            $h = floor($hours - ($d * 24));
            $daycalc = $d . "d " . $h . "h";
            } else {
            $h = floor($hours);
            $daycalc = $h . "h";
        }
        return $daycalc;
    }
	
	private function uptimecalc() {
					
		$current_Blocks         = $this->towerfuel['Blocks'];
        
		$current_strontium       = $this->towerfuel['Stront'];
		$current_charters        = $this->towerfuel['Charters'];
		$charters_needed         = $this->charters_need;
		
		//Use new Soveignty Function

		$sovereignty = $this->sov;
		$required_Blocks           = $this->resource['Blocks'];
		
		$required_ozone             = $this->pg;
		$required_heavy_water       = $this->cpu;
		$required_strontium         = $this->resource['StrontiumClathrates'];
		$required_charters          = $charters_needed?1:0;
		$total_pg                   = $this->towerpg;
		$total_cpu                  = $this->towercpu;

        $current_pg   = $this->pg;
        $current_cpu  = $this->cpu;
        
		$hoursago = $this->hoursago;

        $calc_Blocks = (floor($current_Blocks / $required_Blocks)) - $hoursago;
        

        if ($required_charters) {
            $calc_charters = (floor($current_charters / $required_charters)) - $hoursago;
        } else {
            $calc_charters = 0;
        }

        $calc_strontium = floor($current_strontium / $required_strontium);
        
        $calc_Blocks = (($calc_Blocks <= 0) ? 0 : $calc_Blocks);
        
        $calc_charters = (($calc_charters <= 0) ? 0 : $calc_charters);
		
        $uptimecalc['Blocks']    = $calc_Blocks;
        $uptimecalc['strontium'] = $calc_strontium;
        
        if ($charters_needed) {
            $uptimecalc['charters'] = $calc_charters;
        } else {
            $uptimecalc['charters'] = false;
        }

        return $uptimecalc;
    }
	
	private function online($fuel) {
        if (count($fuel) != 0) {
            $Blocks          = $fuel['Blocks'];
            
            $charters         = $fuel['charters'];
            $strontium        = $fuel['strontium'];

            $fuel_array = array($Blocks);
           
            if ($charters !== false) {
                $fuel_array[] = $charters;
            }
            array_multisort($fuel_array);
            $online = $fuel_array[0];
			if ($this->state == 'Reinforced')
				return 0;
			else
            	return $online;
        }
    }
	
	private function fuel_calc() {
		// posoptimaluptime
		
		$required_Blocks           = $this->resource['Blocks'];

		$required_strontium         = $this->resource['StrontiumClathrates'];
		$required_charters          = $this->charters_need?1:0;
		$total_pg                   = $this->towerpg;
        $total_cpu                  = $this->towercpu;
        $current_pg                 = $this->pg;
        $current_cpu                = $this->cpu;
        //$tower['uptimecalc']      = $this->uptimecalc($pos_id);
        $strontium_capacity       	= $this->towerstr;
        $pos_capacity               = $this->towercar;
        $optimal['pos_capacity']    = $pos_capacity;
		
		//Calculate Optimal cycles
        $volume_per_cycle  = 0;
        $volume_per_cycle += ($required_Blocks * 5);
        $volume_per_cycle += ($required_charters * 0.1);
        $optimum_cycles    = floor($pos_capacity/$volume_per_cycle);
		
		//calculate optimal
        $optimal['optimum_cycles']			 = $optimum_cycles;
		$optimal['optimum_cycles_h']		 = $this->daycalc($optimum_cycles);
        $optimal['optimal_strontium_cycles'] = $optimal_strontium_cycles = floor($strontium_capacity/($required_strontium*3));
        $optimal['optimum_Blocks']          = $required_Blocks * $optimum_cycles;
        $optimal['optimum_charters']         = $required_charters * $optimum_cycles;
        $optimal['optimum_strontium']        = $required_strontium * $optimal_strontium_cycles;
        		      
		//calculate 7 Days
        $optimal['7day_Blocks']          = $required_Blocks * (24 * 7);
        $optimal['7day_charters']         = $required_charters * (24 * 7);
		
		//calculate 14 Days
        $optimal['14day_Blocks']          = $required_Blocks * (24 * 14);
        $optimal['14day_charters']         = $required_charters * (24 * 14);
		
		//calculate 21 Days
        $optimal['21day_Blocks']          = $required_Blocks * (24 * 21);
        $optimal['21day_charters']         = $required_charters * (24 * 21);
        
		return $optimal;
	}
	
	private function getOptimalDifference( $optimal ) {
        //Diff clear
        $diff=array();
        //Calculate the difference between whats in the tower and optimal
        $diff['Blocks']			= $optimal['optimum_Blocks']			- $this->towerfuel['Blocks'];
        $diff['charters']			= $optimal['optimum_charters']			- $this->towerfuel['Charters'];
        $diff['strontium']			= $optimal['optimum_strontium']			- $this->towerfuel['Stront'];
		
		$diff['7day_Blocks']			= $optimal['7day_Blocks']			- $this->towerfuel['Blocks'];
        $diff['7day_charters']			= $optimal['7day_charters']			- $this->towerfuel['Charters'];
        
		$diff['14day_Blocks']			= $optimal['14day_Blocks']			- $this->towerfuel['Blocks'];
        $diff['14day_charters']			= $optimal['14day_charters']		- $this->towerfuel['Charters'];
        
		$diff['21day_Blocks']			= $optimal['21day_Blocks']			- $this->towerfuel['Blocks'];
        $diff['21day_charters']			= $optimal['21day_charters']		- $this->towerfuel['Charters'];
        
        //calculate optimal difference in m3
        $diff['Blocks_m3']		= $diff['Blocks']		   	* 5;
        $diff['charters_m3']		= $diff['charters']		  	* 0.1;
        $diff['strontium_m3']		= $diff['strontium']		* 3;
		
		$diff['Blocks_7day_m3']		  = $diff['7day_Blocks']		   	* 5;
        $diff['charters_7day_m3']		  = $diff['7day_charters']		  	* 0.1;
        
		$diff['Blocks_14day_m3']		  = $diff['14day_Blocks']		   	* 5;
        $diff['charters_14day_m3']		  = $diff['14day_charters']		  	* 0.1;
        
		$diff['Blocks_21day_m3']		  = $diff['21day_Blocks']		   	* 5;
        $diff['charters_21day_m3']		  = $diff['21day_charters']		  	* 0.1;
        
		$diff['opti_m3'] = (($optimal['optimum_Blocks']*5)
						 +	($optimal['optimum_charters']*0.1)
						 );
		
		$diff['7day_m3'] = (($optimal['7day_Blocks']*5)
						 +	($optimal['7day_charters']*0.1)
						 );
		
		$diff['14day_m3'] = (($optimal['14day_Blocks']*5)
						 +	($optimal['14day_charters']*0.1)
						 );
		
		$diff['21day_m3'] = (($optimal['21day_Blocks']*5)
						 +	($optimal['21day_charters']*0.1)
						 );
		
		$diff['diff_m3'] = 	($diff['Blocks_m3']
						 +  $diff['charters_m3']);
		
		$diff['7day_diff_m3'] = ($diff['Blocks_7day_m3']
						 +  $diff['charters_7day_m3']);
		
		$diff['14day_diff_m3'] = ($diff['Blocks_14day_m3']
						 +  $diff['charters_14day_m3']);
						 
		$diff['21day_diff_m3'] = ($diff['Blocks_21day_m3']
						 +  $diff['charters_21day_m3']);

        return $diff;
    }
	
	
	
	public function toArray() {
		$array = get_object_vars($this);
		foreach ( $array as $key => $val )
			if ( is_object($val) ) unset ( $array[ $key ] );
		return $array;
	}

}
?>