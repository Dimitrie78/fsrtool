<?php

class eveMail extends ooeWorld {
	
	public $User;
	public $db;
	
	public $offset;
	public $messages = array();
	
	private $messIDs = array();
	
	private $mailingListsCache = array();
	private $charIDtoNameCache = array();
	
	public function __construct( $User, $ale ) {
		if ( !$this->db ) parent::__construct( $User );
		$this->ale = $ale;
		$this->ale->setConfig('serverError', 'returnParsed');
		
		if ( isset($_POST['cid']) && !empty($_POST['cid']) && $_POST['cid'] != $this->User->charID ){
			$id = $_POST['cid'];
			$this->ale->setKey( $this->User->alts[$id]['userID'], 
										$this->User->alts[$id]['userAPI'], 
										$this->User->alts[$id]['charID'] 
										);
		} else {
			$this->ale->setKey( $this->User->keyID, $this->User->vCODE, $this->User->charID );
		}
		#$this->ale->setKey( $this->User->userID, $this->User->apikey, $this->User->charID );
		#$this->ale->setKey( '1777788', 'Iokh5ZNiYAfrhLM1nkJyVrqAEUfzzOS4kdM9Sp2fj6fZ54WriU1xsiqOj5njW1rS', '268036874' );
		
		$this->getMailingLists();
		$this->getMails();
	}
	
	private function getMails() {
		try {
			$xml = $this->ale->char->MailMessages();
			if ( $xml->error ) 
				return $this->messages['error'][] = (int)$xml->error->attributes() . ' - ' . (string)$xml->error . ' ( MailMessages )';
			$time = time() - date( 'Z' );
			$this->offset = (strtotime( $xml->cachedUntil ) - $time);
			$messages = $xml->result->messages->toArray();
			usort( $messages, 'mailDateSort' );
			//echo '<pre>'; print_r($messages); die;
			$this->messages['Private'] = array();
			$this->messages['Corp'] = array();
			$this->messages['Ally'] = array();
			$this->messages['List'] = array();
			
			$a=0; $b=0; $c=0; $d=0;
			foreach ( $messages as $key => $mail ) {
				if ( trim( (string)$mail['toCharacterIDs'] ) != '' ) {			
					if ( in_array ( $this->User->charID, explode(",", $mail['toCharacterIDs']) ) || $mail['senderID'] == $this->User->charID ) {
						$this->messages['Private'][ $a ]['messageID'] = $mail['messageID'];
						$this->messages['Private'][ $a ]['senderName'] = $this->IDtoName( $mail['senderID'] );
						$this->messages['Private'][ $a ]['sentDate'] = $this->mytime( $mail['sentDate'] );
						$this->messages['Private'][ $a ]['title'] = $mail['title'];
						$this->messIDs[ $mail['messageID'] ]['empf'] = $this->IDtoName( $mail['toCharacterIDs'] );
						$this->messIDs[ $mail['messageID'] ]['titl'] = $mail['title'];
						$this->messIDs[ $mail['messageID'] ]['date'] = $this->mytime( $mail['sentDate'] );
						$a++;
					}
				}
				if ( trim( (string)$mail['toCorpOrAllianceID'] ) != '' && $mail['toCorpOrAllianceID'] == $this->User->corpID ) {			
					$this->messages['Corp'][ $b ]['messageID'] = $mail['messageID'];
					$this->messages['Corp'][ $b ]['senderName'] = $this->IDtoName( $mail['senderID'] );
					$this->messages['Corp'][ $b ]['sentDate'] = $this->mytime( $mail['sentDate'] );
					$this->messages['Corp'][ $b ]['title'] = $mail['title'];
					#$this->messages['Corp'][ $b ]['toCorpOrAllianceID'] = $this->IDtoName( $mail['toCorpOrAllianceID'] );
					$this->messIDs[ $mail['messageID'] ]['empf'] = $this->IDtoName( $mail['toCorpOrAllianceID'] );
					$this->messIDs[ $mail['messageID'] ]['titl'] = $mail['title'];
					$this->messIDs[ $mail['messageID'] ]['date'] = $this->mytime( $mail['sentDate'] );
					$b++;
				}
				if ( trim( (string)$mail['toCorpOrAllianceID'] ) != '' && $mail['toCorpOrAllianceID'] != $this->User->corpID ) {			
					$this->messages['Ally'][ $c ]['messageID'] = $mail['messageID'];
					$this->messages['Ally'][ $c ]['senderName'] = $this->IDtoName( $mail['senderID'] );
					$this->messages['Ally'][ $c ]['sentDate'] = $this->mytime( $mail['sentDate'] );
					$this->messages['Ally'][ $c ]['title'] = $mail['title'];
					#$this->messages['Ally'][ $c ]['toCorpOrAllianceID'] = $this->IDtoName( $mail['toCorpOrAllianceID'] );
					$this->messIDs[ $mail['messageID'] ]['empf'] = $this->IDtoName( $mail['toCorpOrAllianceID'] );
					$this->messIDs[ $mail['messageID'] ]['titl'] = $mail['title'];
					$this->messIDs[ $mail['messageID'] ]['date'] = $this->mytime( $mail['sentDate'] );
					$c++;
				}
				if ( trim( (string)$mail['toListID'] ) != '' ) {			
					$this->messages['List'][ $d ]['messageID'] = $mail['messageID'];
					$this->messages['List'][ $d ]['senderName'] = $this->IDtoName( $mail['senderID'] );
					$this->messages['List'][ $d ]['sentDate'] = $this->mytime( $mail['sentDate'] );
					$this->messages['List'][ $d ]['title'] = $mail['title'];
					$this->messages['List'][ $d ]['toListID'] = $this->IDtoName( $mail['toListID'] );
					$this->messIDs[ $mail['messageID'] ]['empf'] = $this->IDtoName( $mail['toListID'] );
					$this->messIDs[ $mail['messageID'] ]['titl'] = $mail['title'];
					$this->messIDs[ $mail['messageID'] ]['date'] = $this->mytime( $mail['sentDate'] );
					$d++;
				}
			}
		} catch (Exception $e) {
			$this->db->msg->addwarning( $e->getMessage() );
		}
	}
	
	private function getMailingLists() {
		try {
			$list = $this->ale->char->mailinglists();
			if ( $list->error ) 
				return $this->messages['error'][] = (int)$list->error->attributes() . ' - ' . (string)$list->error . ' ( MailingLists )';
			else {
				$list = $list->result->mailingLists->toArray();
		
				foreach ( $list as $row )
					$this->mailingListsCache[ $row['listID'] ] = $row['displayName'];
			}
		} catch (Exception $e) {
			$this->db->msg->addwarning( $e->getMessage() );
		}
	}
	
	public function getMailBodies( $messageID ) {
		#$messageID = str_replace( 'id_', '', $messageID );
		$params = array( 'ids' => $messageID );
		try {
			$mailBodie = $this->ale->char->MailBodies( $params );
			$mails = $mailBodie->result->messages->toArray();
			
			$mail = array();
			$mail['head'] = $this->messIDs[ $messageID ];
			$mail['body'] = $mails[ $messageID ]['nodeText'];
			
			$body = str_replace('size="13"', '', $mails[ $messageID ]['nodeText']);
			$body = str_replace('size="12"', '', $body);
			$body = str_replace('size="11"', '', $body);
			$body = str_replace('size="10"', '', $body);
			$body = str_replace('size="9"', '', $body);
			$body = str_replace('size="8"', '', $body);
			return $body;
			#return json_encode( $mail );
			#return $mails[ $messageID ]['nodeText'];
			/*
			if ( strpos($mails[ $messageID ]['nodeText'], '<font' ) === 0 ) {
				$x=substr($mails[ $messageID ]['nodeText'], 0, strpos($mails[ $messageID ]['nodeText'],'>')+1);
				$body = str_replace($x, '', $mails[ $messageID ]['nodeText']);
				$body = str_replace('</font>', '', $body);
				return $body;
			}
			else return $mails[ $messageID ]['nodeText'];
			*/
		} catch (Exception $e) {
			$this->db->msg->addwarning( $e->getMessage() );
		}
	}
	
	private function IDtoName( $charID ) {
		$charID = trim( (string)$charID );
		if ( $charID == '' ) return null;
		
		$x='';
		$ids = explode( ',', $charID );
		
		foreach ( $ids as $id ) {
			
			if ( isset( $this->mailingListsCache[ $id ] ) ) {
				if ( $x == '' )
					$x .= $this->mailingListsCache[ $id ];
				else $x .= ', ' . $this->mailingListsCache[ $id ];
				continue;
			}
			
			if ( !isset( $this->charIDtoNameCache[ $id ] ) ) {
				$params = array( 'ids' => $id );
				try {
					$ids = $this->ale->eve->CharacterName( $params, ALE_AUTH_NONE );
					if ( $ids->error ) {
						#return '--> API error <--';
						if ( $x == '' )
							$x .= '--> API error <--';
						else $x .= ', --> API error <--';
					} else {
						$chars = $ids->result->characters->toArray();
						foreach ( $chars as $char )
							$this->charIDtoNameCache[ $char['characterID'] ] = $char['name'];
					}
				} catch (Exception $e) {
					$this->db->msg->addwarning( $e->getMessage() );
				}
			}
			if ( $x == '' )
				$x .= $this->charIDtoNameCache[ $id ];
			else $x .= ', ' . $this->charIDtoNameCache[ $id ];
		
		}

		return $x;
	}
	
	private function mytime( $time ) {
		$time = strtotime( $time );
		return array('day' => date( 'D', $time ), 'time' => date( 'd.m.Y H:i:s', $time ) );
	}

}

function mailDateSort($a, $b) {
	if ($a['sentDate'] == $b['sentDate'])
		return 0;
	return ($a['sentDate'] > $b['sentDate']) ? -1 : 1;
}
/*
		(
            [messageID] => 305308434
            [senderID] => 1689477804
            [sentDate] => 2011-07-07 16:04:00
            [title] => Kontakt
            [toCorpOrAllianceID] => 
            [toCharacterIDs] => 816707343,285591396,378124749
            [toListID] => 
        )
*/
?>