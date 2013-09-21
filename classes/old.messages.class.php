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

	private static $instance;
	
	// private
	private static $warnings = array();
	private static $confirms = array();
	
	static public function instance() {
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}

		return self::$instance;
	}
	// public
	
	/**
	* Fügt dem Array $warnings eine Warnnachricht an.
	*
	* $msg ist die anzuzeigende Warnachricht (kann auch Meldung wie "Bitte einen Namen eingeben" sein)
	* $id ist optional. eintraege mit $id=0 werden am Anfang der Seite ausgegeben
	* alle anderen $ids werden dort benutzt, wo sie gebracht werden. Template-Abhaenging!
	*/
	public static function addwarning($msg, $id = 0){
		self::$warnings[$id][] = $msg;
	}
	
	/**
	* Fügt dem Array $confirms eine Nachricht an.
	*
	* $msg ist die anzuzeigende Nachricht
	* $id ist optional. eintraege mit $id=0 werden am Anfang der Seite ausgegeben
	* alle anderen $ids werden dort benutzt, wo sie gebracht werden. Template-Abhaenging!
	*/
	public static function addconfirm($msg, $id = 0){
		self::$confirms[$id][] = $msg;
	}
	
	/**
	* Zeigt ein Fehler an, und beendet das Programm.
	*/
	public static function showerror($msg){
		global $smarty;
		$smarty->assign('Messages',	self::$instance);
		$smarty->assign('msg_error', $msg);
		$smarty->assign('a_confirms', self::getconfirms());
		$smarty->assign('a_warnings', self::getwarnings());
		$smarty->display('showerror.tpl');
		exit;
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
	public static function getwarnings(){
		$temp = self::$warnings;
		self::$warnings = array();
		$res = '';
		if( is_array($temp[0]) ) {
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
	public static function getconfirms(){
		$temp = self::$confirms;
		self::$confirms = array();
		$res = '';
		if( is_array($temp[0]) ) {
			foreach( $temp[0] as $key => $msg )
				$res .= '<div style="font-size:16px;color:#00BB00;">'.$msg.'</div>';
		}
		return($res);
	}
}
?>
