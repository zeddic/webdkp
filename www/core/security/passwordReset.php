<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Contains information for a single password reset request
for a single user.
When a user wants to reset thier password they must submit
a password reset request. This causes a md5 hash to be
generated and sent to the users email address and stored
into the database via this class. When the user hits a link
in their reset email, we can use it to verify that they own
the email account in question.

This class is responsible for keeping tracking of every
unique password reset request.

Only one reset request is maintained for each user
*/

class passwordReset {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $user;
	var $key;
	var $requestDate;
	var $requestTime;
	var $tablename;
	const tablename = "security_reset_password";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = passwordReset::tablename;
	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabase($id)
	{
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromDatabaseByUser($id)
	Loads the information for this class from the backend database
	using the id of the user who requested a reset
	============================================================*/
	function loadFromDatabaseByUser($id)
	{
		global $sql;
		$id = sql::Escape($id);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE user='$id'");
		$this->loadFromRow($row);
	}

	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->user = $row["user"] ?? null;
		$this->key = $row["requestkey"] ?? null;
		if($row["request"]!="")
		{
			$this->requestDate = date("F j, Y", strtotime($row["request"]));
			$this->requestTime = date("g:i A", strtotime($row["request"]));
		}
		$this->request = $row["request"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$key = sql::Escape($this->key);
		$user = sql::Escape($this->user);
		$id = sql::Escape($this->id);
		$sql->Query("UPDATE $this->tablename SET
					user = '$user',
					requestkey = '$key'
					WHERE id='$id'");
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
		$key = sql::Escape($this->key);
		$user = sql::Escape($this->user);
		$sql->Query("INSERT INTO $this->tablename SET
					user = '$user',
					requestkey = '$key',
					request = NOW()
					");
		$this->id=$sql->GetLastId();
	}
	/*===========================================================
	delete()
	Deletes the row with the current id of this instance from the
	database
	============================================================*/
	function delete()
	{
		global $sql;
		$sql->Query("DELETE FROM $this->tablename WHERE id = '$this->id'");
	}
	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($userid)
	{
		global $sql;
		$userid = sql::escape($userid);
		$tablename = passwordReset::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE user='$userid'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(passwordReset::tablename)) {
			$tablename = passwordReset::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`user` INT NOT NULL,
						`requestkey` VARCHAR (256) NOT NULL,
						`request` DATETIME NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
passwordReset::setupTable()
?>