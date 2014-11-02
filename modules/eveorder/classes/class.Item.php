<?php

class Item
{
	function Item($site, $id = 0){
		$this->site = $site;
		$this->id_ = $id;
        $this->executed_ = false;
    }

    function getID(){
        return $this->id_;
    }

    function getName(){
        if(!$this->row_['typeName'])$this->execQuery();
        return $this->row_['typeName'];  
    }
	
	function getIcon($size = 32){
        $this->execQuery();
        global $smarty;
	
        // cat 18 are combat drones
        if ($this->row_['categoryID'] == 18)
        {
            $img = IMG_URL.'/types/dronetypes_png/'.$size.'_'.$size.'/'.$this->row_['itm_externalid'].'.png';
        }
        // cat 6 are ships (destroyed in cargo)
        elseif ($this->row_['categoryID'] == 6)
        {
            $img = IMG_URL.'/types/shiptypes_png/'.$size.'_'.$size.'/'.$this->row_['itm_externalid'].'.png';
        }
		// cat 9 are blueprints
		elseif ($this->row_['categoryID'] == 9)
		{
			$img = IMG_URL.'/blueprints/64_64/'.$this->row_['itm_externalid'].'.png';
		}
		else
        { 
			// fix for new db structure, just make sure old clients dont break
			if (!strstr($this->row_['itm_icon'], 'icon')) 
			{
				$this->row_['itm_icon'] = 'icon'.$this->row_['itm_icon'];
			}
			$img = IMG_URL.'/icons/'.$size.'_'.$size.'/'.$this->row_['itm_icon'].'.png';
        }  

        $it_name = $this->getName();
		if (($this->row_['itm_techlevel'] == 5)) // is a T2?
		{
			$icon .= IMG_URL.'/fit/'.$size.'_'.$size.'/t2'.$show_style.'.png';
		}
		elseif (($this->row_['itm_techlevel'] > 5) && ($this->row_['itm_techlevel'] < 10)) // is a faction item?
		{
			$icon .= IMG_URL.'/fit/'.$size.'_'.$size.'/f'.$show_style.'.png';
		}
		elseif (($this->row_['itm_techlevel'] > 10) && strstr($it_name,"Modified")) // or it's an officer?
		{
			$icon .= IMG_URL.'/fit/'.$size.'_'.$size.'/o'.$show_style.'.png';
		}
		elseif (($this->row_['itm_techlevel'] > 10) && !(strstr($it_name,"Modified"))) // or it's just a deadspace item.
		{
			$icon .= IMG_URL.'/fit/'.$size.'_'.$size.'/d'.$show_style.'.png';
		}
		elseif (
			strstr($it_name,"Blood ")
			|| strstr($it_name,"Sansha")
			|| strstr($it_name,"Arch")
			|| strstr($it_name,"Domination")
			|| strstr($it_name,"Republic")
			|| strstr($it_name,"Navy")
			|| strstr($it_name,"Guardian")
			|| strstr($it_name,"Guristas")
			|| strstr($it_name,"Shadow")
			) // finally if it's a faction should have its prefix
		{
			$icon = IMG_URL.'/fit/'.$size.'_'.$size.'/f'.$show_style.'.png';
		}
		else // but maybe it was only a T1 item :P
		{
			$icon = IMG_URL.'/fit/'.$size.'_'.$size.'/blank.gif';
		}
		
        #$smarty->assign('img', $img);
        #$smarty->assign('icon', $icon);
        #$smarty->assign('name', $it_name);
        #return $smarty->fetch(TPL_DIR .'icon.tpl');
		return array('img' => $img, 'icon' => $icon, 'name' => $it_name);
    }
	
	function getIconJson($size = 32){
        $this->execQuery();
		
		$dir = 'icons/Types/'.$this->row_['itm_externalid'].'_'.$size.'.png';
        
		if ( is_file( $dir ) ) 
			$img = $dir;
		else {
			// $img = 'icons/Icons/items/'.$row['icon'].'.png';
			$cacheFile = 'cache/imgcache/' . $this->typeID . '_' . $size . '.png';
			if ( is_file ( $cacheFile ) ) {
				$img = $cacheFile;
			} else {
				$img = $this->getImageFromEve($this->typeID);
			}
		}
        $it_name = $this->getName();
		
		return array('img' => $img, 'name' => $it_name);
    }
	
	function execQuery(){
        if (!$this->executed_)
        {
            if (!$this->id_)return false;
            
            $this->sql_ = ("SELECT i.*, IfNull(dgm.valueInt, dgm.valueFloat) tech, g.categoryID /* , ei.iconFile AS icon */
				FROM {$this->site->_table['invtypes']} i 
				LEFT JOIN {$this->site->_table['dgmtypeattributes']} dgm ON i.typeID = dgm.typeID AND dgm.attributeID IN (633) 
				LEFT JOIN {$this->site->_table['invgroups']} g ON i.groupID = g.groupID 
				/* LEFT JOIN {$this->site->_table['eveicons']} ei ON i.iconID = ei.iconID */
				WHERE i.typeID = '{$this->id_}';");
            $this->qry_ = $this->site->db->query($this->sql_);
            $this->row_ = $this->qry_->fetch_assoc();
            $this->row_['itm_icon'] = $this->row_['icon'];
            $this->row_['itm_techlevel'] = $this->row_['tech'];
            $this->row_['itm_externalid'] = $this->row_['typeID'];
            $this->executed_ = true;
        }
    }

    // loads $this->id_  by name 
    function lookup($name){
		$name = addslashes(trim($name));
        $query = "SELECT typeID as itm_id FROM {$this->site->_table['invtypes']}
                  WHERE typeName = '{$name}'";
        $result = $this->site->db->query($query);
        $row = $result->fetch_assoc();
        if (!isset($row['itm_id']))
        {
        	$query = "SELECT typeID as itm_id FROM {$this->site->_table['invtypes']}
                  WHERE typeName = 'Large {$name}'";
			$result = $this->site->db->query($query);
			$row = $result->fetch_assoc();
			if (!isset($row['itm_id']))
        	{
				return false;	
			}
        }
        $this->id_ = $row['itm_id'];
		#$result->close();
		$this->executed_ = false;
		return true;
    }
 
    // return typeID by name, dont change $this->id_
    function get_item_id($name){
		$name = addslashes(trim($name));
		$query = "SELECT typeID as itm_id FROM {$this->site->_table['invtypes']}
                  WHERE typeName = '{$name}'";
        $result = $this->site->db->query($query);
        $row = $result->fetch_assoc();
		#$result->close();
		if ($row['itm_id']) return $row['itm_id'];
    }
	
	private function getImageFromEve($typeID) {
		// https://image.eveonline.com/Type/{typeID}_{width}.png
		
		$path = 'cache/imgcache';
		$file = $path . '/' . $typeID . '_64.png';
		
		$url = 'https://image.eveonline.com/Type/' . $typeID . '_64.png';
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);		
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  
		$content = curl_exec ($ch);  
		curl_close ($ch);

        file_put_contents($file, $content);
		
		return $file;
	}
}
?>