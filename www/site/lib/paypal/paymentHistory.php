<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class paymentHistory {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $type;
	var $reason;
	var $amount;
	var $fee;
	var $income;
	var $dateDate;
	var $dateTime;
	var $guild;
	var $transactionNumber;
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{

	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabase($id)
	{
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM paymenthistory WHERE id='$id'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->reason = $row["reason"] ?? null;
		$this->amount = $row["amount"] ?? null;
		$this->fee = $row["fee"] ?? null;
		$this->income = $row["income"] ?? null;
		$this->type = $row["type"] ?? null;
		if($row["date"]!="")
		{
		  $this->dateDate = date("F j, Y", strtotime($row["date"]));
		  $this->dateTime = date("g:i A", strtotime($row["date"]));
		}
		$this->guild = $row["guild"] ?? null;
		$this->transactionNumber = $row["txn"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$reason = stripSlashes($this->reason);
		$reason = addSlashes($reason);
		$type = stripSlashes($this->type);
      	$type = addSlashes($type);
		$sql->Query("UPDATE paymenthistory SET
		          reason = '$reason',
		          amount = '$this->amount',
		          fee = '$this->fee',
		          income = '$this->income',
		          guild = '$this->guild',
		          txn = '$this->transactionNumber',
		          type = '$this->type'
		          WHERE id='$this->id'");
	}
	/*===========================================================
	saveNew()
	Saves data into the backend database as a new row entry. After
	calling this method $id will be filled with a new value
	matching the new row for the data
	============================================================*/
	function saveNew()
	{
		global $sql;
		$reason = stripSlashes($this->reason);
		$reason = addSlashes($reason);
		$sql->Query(" INSERT INTO paymenthistory SET
		          reason = '$reason',
		          amount = '$this->amount',
		          fee = '$this->fee',
		          income = '$this->income',
		          guild = '$this->guild',
		          date = now(),
		          txn = '$this->transactionNumber',
		          type = '$this->type'
		          ");
		$this->id=$sql->QueryItem("SELECT id FROM paymenthistory ORDER BY ID DESC");
	}
	/*===========================================================
	saveAttempt()
	Checks to see if an entry already exists for a payment with the same
	transaction number. If it does, it doesn't record the entry.
	============================================================*/
	function saveAttempt()
	{
		global $sql;
		$exists = $sql->QueryItem("SELECT id FROM paymenthistory WHERE txn = '$this->transactionNumber' LIMIT 1");
		if($this->transactionNumber=="" || $exists==""){
			$this->saveNew();
		}
	}

	/*===========================================================
	delete()
	Deletes the row with the current id of this instance from the
	database
	============================================================*/
	function delete()
	{
		global $sql;
		$sql->Query("DELETE FROM paymenthistory WHERE id = '$this->id'");
	}
}
?>