<?php

class eveNotifications {
	
	private $_User;
	private $_db;
	private $_ale;
	
	private $_table = array();
	private $_table_eveNotifications = "fsrtool_user_notifications";
	
	public $Notifications = array();
	public $error = false;
	public $pushMail = array();
	
	private $IDtoNameCache = array();
	public $NotificationTypes = array();
	
	public function __construct( User $User ) {
		$this->_db = $User->db;
		$this->_table = $User->_table;
		$this->_User = $User;
		$this->_ale = AleFactory::getEVEOnline();
		
		if ( isset($_POST['cid']) && !empty($_POST['cid']) && $_POST['cid'] != $this->_User->charID ){
			$id = $_POST['cid'];
			$this->_ale->setKey( $this->_User->alts[$id]['userID'], 
										$this->_User->alts[$id]['userAPI'], 
										$this->_User->alts[$id]['charID'] 
										);
		} else {
			$this->_ale->setKey( $this->_User->keyID, $this->_User->vCODE, $this->_User->charID );
		}
		
		$this->NotificationTypes();
		$this->getNotifications();
		if($this->_db !== NULL) $this->getPushMail();
	}
	
	private function getNotifications() {
		try {
			$xml = $this->_ale->char->Notifications();
			//if ( $xml->error ) return $this->Notifications['error'] = (string)$xml->error;
			$Notifications = $xml->result->notifications->toArray();
			usort( $Notifications, array($this, 'mailDateSort') );
			$NotificationsIDs = array();
			foreach ( $Notifications as $key => $row ) {
				$Notifications[ $key ]['typeName'] = $this->NotificationTypes[ $row['typeID'] ];
				$Notifications[ $key ]['senderName'] = $this->IDtoName( $row['senderID'] );
				$Notifications[ $key ]['sendTime'] = $this->mytime( $row['sentDate'] );
				$NotificationsIDs[] = $row['notificationID'];
			}
			if(count($NotificationsIDs) >= 1){
				$xml = $this->_ale->char->NotificationTexts(array('IDs' => implode(',', $NotificationsIDs)));
				$NotificationTexts = $xml->result->notifications->toArray();
				foreach($Notifications as $key => $row ) {
					$Notifications[ $key ]['nodeText'] = $NotificationTexts[ $row['notificationID'] ]['nodeText'];
					$this->praser($NotificationTexts[ $row['notificationID'] ]['nodeText']);
				}
			}
			$this->Notifications = $Notifications;
		} catch (Exception $e) {
			$this->error =  $e->getCode() . ' - ' . $e->getMessage();
		}
	}
	
	private function praser($nodeText) {
		$arr=preg_split("/\r\n|\r|\n/",$nodeText);
		foreach($arr as $line){
			if(!trim($line)) continue;
			$this->foo($line);
			
		}
	}
	
	private function foo($line) {
		$arr2 = explode(':',$line);
		$itemName = trim($arr2[0]);
		$itemID = trim($arr2[1]);
		
		switch($itemName) {
			case 'againstID':
				#echo $itemName.' - '.$this->IDtoName( $itemID ).'<br>';
			break;
			case 'declaredByID':
				#echo $itemName.' - '.$this->IDtoName( $itemID ).'<br>';
			break;
		}
		//echo $itemName.' - '.$itemID.'<br>';
	}
	
	private function getPushMail() {
		$str = "SELECT * FROM {$this->_table_eveNotifications} WHERE charID = ?";
		$stmt = $this->_db->prepare($str);
		$res = $this->_db->fetch($stmt, $this->_User->charID);
		foreach($res as $k => $v){
			if($v['notivications'] !== NULL)
				$res[$k]['notivications'] = explode(',', $v['notivications']);
		}
		$this->pushMail = $res;
	}
	
	public function setPushMail() {
		$data = $this->_db->escape($_POST);
		switch($data['type']) {
			case 'addMail':
				$str = "INSERT INTO {$this->_table_eveNotifications} (charID, email, email_valid) VALUES (?,?,?) ON DUPLICATE KEY UPDATE email = VALUES(email), email_valid = VALUES(email_valid)";
				$stmt = $this->_db->prepare($str);
				$charID = $this->_User->charID;
				$email = $data['values'][0]['value'];
				$status = 1;
				$stmt->bind_param('isi', $charID, $email, $status);
				$stmt->execute();
				$stmt->close();
			break;
			case 'addPush':
				$user = trim($data['values'][0]['value']);
				$token = trim($data['values'][1]['value']);
				require_once(FSR_BASE.'/classes/class.Pushover.php');
				$push = new Pushover();
				$push->setToken($token);
				$push->setUser($user);
				$push->setTitle('FsrTool');
				$push->setMessage('TEST');
				if($push->send()) {
					$str = "INSERT INTO {$this->_table_eveNotifications} (charID, push_user, push_token, push_valid) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE push_user = VALUES(push_user), push_token = VALUES(push_token), push_valid = VALUES(push_valid)";
					$stmt = $this->_db->prepare($str);
					$charID = $this->_User->charID;
					$status = 1;
					$stmt->bind_param('issi', $charID, $user, $token, $status);
					$stmt->execute();
					$stmt->close();
				} else return false;
			break;
			case 'noti':
				$charID = $this->_User->charID;
				$stmt = $this->_db->prepare("SELECT * FROM {$this->_table_eveNotifications} WHERE charID = ?");
				$res = $this->_db->fetch($stmt, $charID);
				$str = "INSERT INTO {$this->_table_eveNotifications} (charID, notivications) VALUES (?,?) ON DUPLICATE KEY UPDATE notivications = VALUES(notivications)";
				$status = $data['status'];
				if($res[0]['notivications'] !== NULL ) {
					$notivications = explode(',', $res[0]['notivications']);
					if($status === 'true') array_push($notivications, $data['id']); 
					else {
						$key = array_search($data['id'], $notivications);
						if($key !== false) unset($notivications[$key]);
					}
					$notivications = array_unique($notivications);
					$notivications = implode(',', $notivications);
					if($notivications == '') $notivications = NULL;
				} else {
					if($status === 'true')
						$notivications = $data['id'];
					else $notivications = NULL;
				}
				$stmt = $this->_db->prepare($str);
				$stmt->bind_param('is', $charID, $notivications);
				$stmt->execute();
				$stmt->close();
			break;
		}
		
		return true;
	}
	
	public function delPushMail() {
		$data = $this->_db->escape($_POST);
		switch($data['type']) {
			case 'delPush':
				$charID = $this->_User->charID;
				$stmt = $this->_db->prepare("UPDATE {$this->_table_eveNotifications} SET push_user=NULL, push_token=NULL, push_valid=0 WHERE charID=?");
				$stmt->bind_param('i', $charID);
				$stmt->execute();
				$stmt->close();
			break;
			case 'delMail':
				$charID = $this->_User->charID;
				$stmt = $this->_db->prepare("UPDATE {$this->_table_eveNotifications} SET email=NULL, email_valid=0 WHERE charID = ?");
				$stmt->bind_param('i', $charID);
				$stmt->execute();
				$stmt->close();
			break;
		}
		return true;
	}
	
	private function IDtoName( $ID ) {
		$ID = trim( (string)$ID );
		if ( $ID == '' ) return null;
		
		if ( !isset( $this->charIDtoNameCache[ $ID ] ) ) {
			$params = array( 'ids' => $ID );
			try {
				$ids = $this->_ale->eve->CharacterName( $params, ALE_AUTH_NONE );
				$chars = $ids->result->characters->toArray();
				foreach ( $chars as $char )
					$this->IDtoNameCache[ $char['characterID'] ] = $char['name'];
			} catch (Exception $e) {
				return '--> API error (IDtoName) <--';
			}
		}
		
		return $this->IDtoNameCache[ $ID ];
	}
	
	private function mytime( $time ) {
		$time = strtotime( $time );
		return array('day' => date( 'D', $time ), 'time' => date( 'd.m.Y H:i:s', $time ) );
	}
	
	private function NotificationTypes() {
		$this->NotificationTypes = array(
			1   => 'Legacy',
			2   => 'Character deleted',
			3   => 'Give medal to character',
			4   => 'Alliance maintenance bill',
			5   => 'Alliance war declared',
			6   => 'Alliance war surrender',
			7   => 'Alliance war retracted',
			8   => 'Alliance war invalidated by Concord',
			9   => 'Bill issued to a character',
			10  => 'Bill issued to corporation or alliance',
			11  => 'Bill not paid because there\'s not enough ISK available',
			12  => 'Bill, issued by a character, paid',
			13  => 'Bill, issued by a corporation or alliance, paid',
			14  => 'Bounty claimed',
			15  => 'Clone activated',
			16  => 'New corp member application',
			17  => 'Corp application rejected',
			18  => 'Corp application accepted',
			19  => 'Corp tax rate changed',
			20  => 'Corp news report, typically for shareholders',
			21  => 'Player leaves corp',
			22  => 'Corp news, new CEO',
			23  => 'Corp dividend/liquidation, sent to shareholders',
			24  => 'Corp dividend payout, sent to shareholders',
			25  => 'Corp vote created',
			26  => 'Corp CEO votes revoked during voting',
			27  => 'Corp declares war',
			28  => 'Corp war has started',
			29  => 'Corp surrenders war',
			30  => 'Corp retracts war',
			31  => 'Corp war invalidated by Concord',
			32  => 'Container password retrieval',
			33  => 'Contraband or low standings cause an attack or items being confiscated',
			34  => 'First ship insurance',
			35  => 'Ship destroyed, insurance payed',
			36  => 'Insurance contract invalidated/runs out',
			37  => 'Sovereignty claim fails (alliance)',
			38  => 'Sovereignty claim fails (corporation)',
			39  => 'Sovereignty bill late (alliance)',
			40  => 'Sovereignty bill late (corporation)',
			41  => 'Sovereignty claim lost (alliance)',
			42  => 'Sovereignty claim lost (corporation)',
			43  => 'Sovereignty claim acquired (alliance)',
			44  => 'Sovereignty claim acquired (corporation)',
			45  => 'Alliance anchoring _alert',
			46  => 'Alliance structure turns vulnerable',
			47  => 'Alliance structure turns invulnerable',
			48  => 'Sovereignty disruptor anchored',
			49  => 'Structure won/lost',
			50  => 'Corp office lease expiration notice',
			51  => 'Clone contract revoked by station manager',
			52  => 'Corp member clones moved between stations',
			53  => 'Clone contract revoked by station manager',
			54  => 'Insurance contract expired',
			55  => 'Insurance contract issued',
			56  => 'Jump clone destroyed',
			57  => 'Jump clone destroyed',
			58  => 'Corporation joining factional warfare',
			59  => 'Corporation leaving factional warfare',
			60  => 'Corporation kicked from factional warfare on startup because of too low standing to the faction',
			61  => 'Character kicked from factional warfare on startup because of too low standing to the faction',
			62  => 'Corporation in factional warfare warned on startup because of too low standing to the faction',
			63  => 'Character in factional warfare warned on startup because of too low standing to the faction',
			64  => 'Character loses factional warfare rank',
			65  => 'Character gains factional warfare rank',
			66  => 'Agent has moved',
			67  => 'Mass transaction reversal message',
			68  => 'Reimbursement message',
			69  => 'Agent locates a character',
			70  => 'Research mission becomes available from an agent',
			71  => 'Agent mission offer expires',
			72  => 'Agent mission times out',
			73  => 'Agent offers a storyline mission',
			74  => 'Tutorial message sent on character creation',
			75  => 'Tower alert',
			76  => 'Tower resource alert',
			77  => 'Station service aggression message',
			78  => 'Station state change message',
			79  => 'Station conquered message',
			80  => 'Station aggression message',
			81  => 'Corporation requests joining factional warfare',
			82  => 'Corporation requests leaving factional warfare',
			83  => 'Corporation withdrawing a request to join factional warfare',
			84  => 'Corporation withdrawing a request to leave factional warfare',
			85  => 'Corporation liquidation',
			86  => 'Territorial Claim Unit under attack',
			87  => 'Sovereignty Blockade Unit under attack',
			88  => 'Infrastructure Hub under attack',
			89  => 'Contact add notification',
			90  => 'Contact edit notification',
			91  => 'Incursion Completed',
			92  => 'Corp Kicked',
			93  => 'Customs office has been attacked',
			94  => 'Customs office has entered reinforced',
			95  => 'Customs office has been transferred',
			96  => 'FW Alliance Warning',
			97  => 'FW Alliance Kick',
			98  => 'AllWarCorpJoined Msg',
			99  => 'Ally Joined Defender',
			100 => 'Ally Has Joined a War Aggressor',
			101 => 'Ally Joined War Ally',
			102 => 'New war system: entity is offering assistance in a war.',
			103 => 'War Surrender Offer',
			104 => 'War Surrender Declined',
			105 => 'FacWar LP Payout Kill',
			106 => 'FacWar LP Payout Event',
			107 => 'FacWar LP Disqualified Eventd',
			108 => 'FacWar LP Disqualified Kill',
			109 => 'Alliance Contract Cancelled',
			110 => 'War Ally Declined Offer',
			111 => 'Your Bounty Claimed',
			112 => 'Bounty Placed (Char)',
			113 => 'Bounty Placed (Corp)',
			114 => 'Bounty Placed (Alliance)',
			115 => 'Kill Right Available',
			116 => 'Kill Right Available Open',
			117 => 'Kill Right Earned',
			118 => 'Kill Right Used',
			119 => 'Kill Right Unavailable',
			120 => 'Kill Right Unavailable Open',
			121 => 'Declare War',
			122 => 'Offered Surrender',
			123 => 'Accepted Surrender',
			124 => 'Made War Mutual',
			125 => 'Retracts War',
			126 => 'Offered To Ally',
			127 => 'Accepted Ally',
			128 => 'Character Application Accept Message',
			129 => 'Character Application Reject Message',
			130 => 'Character Application Withdraw Message',
		);
	}
	
	private function mailDateSort($a, $b) {
		if ($a['sentDate'] == $b['sentDate'])
			return 0;
		return ($a['sentDate'] > $b['sentDate']) ? -1 : 1;
	}
}

?>