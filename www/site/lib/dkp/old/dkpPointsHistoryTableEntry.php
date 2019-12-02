<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Contains information for a single reward that was made to a person.
This include the reward amount, why it was given, who gave the reward,
where it was given, and more.

Note that a reward belongs to a dkp table which in change belongs
to a guild. (one guild can have multiple dkp tables)
*/

class dkpPointsHistoryTableEntry {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;			//unique id in the database
	var $guild;			//the guild that the entry belongs to
	var $tableid;		//the id of the dkp table from within a guild
	var $user;			//the id of the user who's history entry this is
	var $points;		//The number of points that were awarded
	var $reason;		//The reason for the reward
	var $location;		//The location of the reward
	var $awardedby;		//The name of the person who made the reward
	var $date;			//A raw date timestamp. Can be formated using php's date() function
	var $dateDate;		//The date that the reward was made
	var $dateTime;		//The time that the reward was made
	var $foritem;		//int. 0 = not for item. 1 = for item
	const tablename = "dkp_pointhistory";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function dkpPointsHistoryTableEntry()
	{
		$this->tablename = dkpPointsHistoryTableEntry::tablename;
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
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"];
		$this->guild = $row["guild"];
		$this->tableid = $row["tableid"];
		$this->user = $row["user"];
		$this->points = $row["points"];
		$this->reason = $row["reason"];
		$this->location = $row["location"];
		$this->awardedby = $row["awardedby"];
		$this->date = $row["date"];
		if($row["date"]!="")
		{
			$this->dateDate = date("F j, Y", strtotime($row["date"]));
			$this->dateTime = date("g:i A", strtotime($row["date"]));
		}
		$this->foritem = $row["foritem"];
		$this->foritem = $row["foritem"];
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$reason = sql::Escape($this->reason);
		$location = sql::Escape($this->location);
		$awardedby = sql::Escape($this->awardedby);
		$sql->Query("UPDATE $this->tablename SET
					guild = '$this->guild',
					tableid = '$this->tableid',
					user = '$this->user',
					points = '$this->points',
					reason = '$reason',
					location = '$location',
					awardedby = '$awardedby',
					foritem = '$this->foritem',
					foritem = '$this->foritem'
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
		$reason = sql::Escape($this->reason);
		$location = sql::Escape($this->location);
		$awardedby = sql::Escape($this->awardedby);
		$sql->Query("INSERT INTO $this->tablename SET
					guild = '$this->guild',
					tableid = '$this->tableid',
					user = '$this->user',
					points = '$this->points',
					reason = '$reason',
					location = '$location',
					awardedby = '$awardedby',
					foritem = '$this->foritem',
					foritem = '$this->foritem'
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
	function exists($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = dkpPointsHistoryTableEntry::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}

	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	function setupTable()
	{
		if(!sql::TableExists(dkpPointsHistoryTableEntry::tablename)) {
			$tablename = dkpPointsHistoryTableEntry::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`guild` INT NOT NULL,
						`tableid` INT NOT NULL,
						`user` INT NOT NULL,
						`points` DECIMAL(11,2) NOT NULL,
						`reason` VARCHAR (256) NOT NULL,
						`location` VARCHAR (256) NOT NULL,
						`awardedby` VARCHAR (256) NOT NULL,
						`date` DATETIME NOT NULL,
						`foritem` INT (1) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
dkpPointsHistoryTableEntry::setupTable();
?>
