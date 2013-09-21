<?php
defined('ACTIVE_MODULE') or die('Restricted access');

class parseFit{

	private $itemlist;
	private $output;
	private $db;
	
	public function parseFit($world){
		$this->db = $world->db;
		$this->_table = $world->_table;
		$res = $this->db->query("SELECT typeID, typeName FROM {$this->_table['invtypes']} WHERE published = 1;");
		while( $row = $res->fetch_assoc() ){
			$this->itemlist[$row['typeID']] = $row['typeName'];
		}
	}
	
	public function eft($str){
		$arr=preg_split("/\r\n|\r|\n/",$str);
		foreach($arr as $line){
			if(!trim($line)) continue;
			$line = stripslashes($line);
			if(preg_match("/\[(.*),.*\]/",$line,$ship) && $this->toTypeID($ship[1])){
				$this->output[$this->toTypeID($ship[1])] = 1; continue;
			}
 			$items = preg_split("/(.*), .*|(.*) x([0-9]*)/",$line,-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
			for($i=0;count($items)>$i;$i++){
				if(is_numeric($items[$i]))
					$this->output[$this->toTypeID(trim($items[$i-1]))]+=$items[$i]-1;	
				if(!$this->toTypeID($items[$i])) continue;
				$this->output[$this->toTypeID($items[$i])]++;
			}
		}
	}
	
	public function igf($str){
		$str = substr($str,(int)strpos($str,'fitting:'),(int)strpos($str,'::')-(int)strpos($str,'fitting:'));
		#$str = strstr(strstr($str,'::',true),'fitting:'); // PHP 5.3 -.-
		$arr = explode(':',$str);
		foreach($arr as $item){
			if(!preg_match("/[0-9]|[0-9]*;[0-9]/",$item)) continue;
			$item = explode(';',$item);
			if(empty($item[1])) $item[1] = 1;
			$this->output[$item[0]] = (int)$item[1];
		}
	}
	
	public function xml($url){
		$dom = DOMDocument::load($url);
		$xpath = new DOMXPath($dom);
		$fittings = $xpath->query('//fitting');
		foreach($fittings as $fit){
			$ship = $xpath->query('./shipType',$fit);
			$this->output[$this->toTypeID($ship->item(0)->getAttribute('value'))] += 1;
			$mods = $xpath->query('./hardware',$fit);
			for($i = 0;$i < $mods->length;$i++){
				if($mods->item($i)->hasAttribute('qty'))
					$qty = $mods->item($i)->getAttribute('qty');
				else $qty = 1;
				$this->output[$this->toTypeID($mods->item($i)->getAttribute('type'))] += $qty;
			}
		}
	}
	
	private function toTypeID($name){
		return array_search($name,$this->itemlist);
	}
	
	public function output(){
		if(is_array($this->output)){
			asort($this->output);
			return $this->output;
		}
		return false;
	}

}

?>