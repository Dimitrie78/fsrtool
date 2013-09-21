<?php

class transaction {

	// Declare variables
	private $from; // INT: userid where money originates from / goes to
	private $to; // INT: userid affected
	private $amount; // INT: amount of isk
	private $type; // 0 = deposit, 1 = withdrawal
	private $isTransfer; // bool: 0 = create new money, 1 = deduct money from sender
	private $reason; // String with the reason.

	// Constructor
	public function __construct($to, $type, $amount) {
		// We need some more globals at this stage
		global $MySelf;

		// Check for validity..
		numericCheck($to);
		numericCheck($amount, 1);
		numericCheck($type, 0, 1);

		// .. and set the variables.
		$this->to = $to;
		$this->type = $type;

		// In case of a withdrawal, -*1 the amount.
		if ($type == 1) {
			$this->amount = ($amount * -1);
		} else {
			$this->amount = $amount;
		}

		// Define standard content for remaining variables.
		$this->isTransfer = false;
		$this->from = $MySelf->getID();
	}

	// Make this a transfer.
	public function isTransfer($bool) {
		if ($bool) {
			$this->isTransfer = 1;
		} else {
			$this->isTransfer = 0;
		}
	}

	// Set the reason.
	public function setReason($reason) {
		// Cut it down to 500 chars. 		
		$this->reason = substr($reason, 0, 499);
	}

	// Make the transfer.
	public function commit() {
		// Indeed, we need the database.
		global $DB;
		global $TIMEMARK;

		// Do the transfer. 		
		
		if ($stmt = $DB->prepare("INSERT INTO transactions (owner, banker, type, amount, reason, time) VALUES (?,?,?,?,?,?)")) {
			$stmt->bind_param('iiiisi', $this->to, $this->from, $this->type, $this->amount, $this->reason, $TIMEMARK);
			/* execute prepared statement */
			$stmt->execute();
			/* Set true/1 on success. */
			$status = $stmt->affected_rows;
			/* close statement and connection */
			$stmt->close();
		}
		
		// On success, and if this is a transaction, do the counterpart now.
		if ($status == 1 && $this->isTransfer) {
			if ($stmt = $DB->prepare("INSERT INTO transactions (owner, banker, type, amount, reason, time) VALUES (?,?,?,?,?,?)")) {
				$stmt->bind_param('iiiisi', $this->from, $this->from, (1 - $this->type), ($this->amount * -1), $this->reason, $TIMEMARK);
				/* execute prepared statement */
				$stmt->execute();
				/* Set true/1 on success. */
				$status = $stmt->affected_rows;
				/* close statement and connection */
				$stmt->close();
			}
		}

		// If one/both status are true, return just that.
		if ($status) {
			return ($status);
		} else {
			return ($status);
		}
	}

}
?>