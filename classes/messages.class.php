<?php
defined('FSR_BASE') or die('Restricted access');
/**
* Dieses File message.php beinhaltet die Klasse Message
* message.php
* $Revision: 1.10 $
*
* @author Sven Stefani, Stephan Oeste
* $Date: 2004/06/23 09:23:21 $
*
* @package func
*/

/**
* Die Message-Klasse gibt Nachrichten an den Benutzer weiter, dies können auch
* Programm Warnungen oder Fehler sein.
* 
*/

class Messages {

	// private
	private $warnings = array();
	private $confirms = array();
	
	// public
	
	public function __construct() {
		if(isset($_SESSION["messages"]) && is_array($_SESSION["messages"])){
			if(isset($_SESSION["messages"]["warnings"]))
				$this->warnings = $_SESSION["messages"]["warnings"];
			if(isset($_SESSION["messages"]["confirms"]))
				$this->confirms = $_SESSION["messages"]["confirms"];
		}
	}
	
	public function __destruct() {
		$_SESSION["messages"]["warnings"] = $this->warnings;
		$_SESSION["messages"]["confirms"] = $this->confirms;
	}
	
	/**
	* Fügt dem Array $warnings eine Warnnachricht an.
	*
	* $msg ist die anzuzeigende Warnachricht (kann auch Meldung wie "Bitte einen Namen eingeben" sein)
	* $id ist optional. eintraege mit $id=0 werden am Anfang der Seite ausgegeben
	* alle anderen $ids werden dort benutzt, wo sie gebracht werden. Template-Abhaenging!
	*/
	public function addwarning($msg, $id = 0){
		$this->warnings[$id][] = $msg;
	}
	
	/**
	* Fügt dem Array $confirms eine Nachricht an.
	*
	* $msg ist die anzuzeigende Nachricht
	* $id ist optional. eintraege mit $id=0 werden am Anfang der Seite ausgegeben
	* alle anderen $ids werden dort benutzt, wo sie gebracht werden. Template-Abhaenging!
	*/
	public function addconfirm($msg, $id = 0){
		$this->confirms[$id][] = $msg;
	}
	
	/**
	* Zeigt ein Fehler an, und beendet das Programm.
	*/
	public function showerror($msg){
		global $language,$User,$smarty;
		
		$smarty->assign('Messages',	$this);
		$smarty->assign('language', $language);
		$smarty->assign('curUser', $User );
		$smarty->assign('msg_error', $msg);
		$smarty->assign('a_confirms', $_SESSION['messages']['confirms']);
		$smarty->assign('a_warnings', $_SESSION['messages']['warnings']);
		$smarty->display('showerror.tpl');
		exit();
	}
	
	/**
	* Gibt den Array der Warnungsnachrichten zurück, sie werden dabei gelöscht.
	*
	* Rückgabe ist ein zweidimensionales Array der Form
	*
	* <pre>
	* [ID] = array [messages]
	* zum Beispiel:
	* [0] = array("Fehler in x", "Fehler 7", "Fehler 3")    // Allgemeine Warnings in [0]
	* [1] = array("Sie habe keinen Namen eingegeben")		// andere Warnings
	* </pre>
	*/
	public function getwarnings(){
		$temp = $this->warnings;
		$this->warnings = array();
		$res = '';
		if( isset($temp[0]) && is_array($temp[0]) ) {
			foreach( $temp[0] as $key => $msg )
				$res .= '<div style="font-size:16px;color:#BB0000;">'.$msg.'</div>';
		}
		return($res);
	}
	
	/**
	* Gibt den Array der Nachrichten zurück, sie werden dabei gelöscht.
	*
	* Rückgabe ist ein zweidimensionales Array der Form wie bei getwarnings()
	*/
	public function getconfirms(){
		$temp = $this->confirms;
		$this->confirms = array();
		$res = '';
		if( isset($temp[0]) && is_array($temp[0]) ) {
			foreach( $temp[0] as $key => $msg )
				$res .= '<div style="font-size:16px;color:#00BB00;">'.$msg.'</div>';
		}
		return($res);
	}
}
?>
