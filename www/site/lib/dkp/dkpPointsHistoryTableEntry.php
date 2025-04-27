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
include_once("dkpAward.php");
class dkpPointsHistoryTableEntry {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;			//unique id in the database
	var $user;			//id of user recieving item
	var $award;		//the id for the award
	var $guild;
	var $tablename;
	const tablename = "dkp_pointhistory";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
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
		$this->id=$row["id"] ?? null;
		$this->guild = $row["guild"] ?? null;
		$this->user = $row["user"] ?? null;
		$this->award = $row["award"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$guild = sql::Escape($this->guild);
		$user = sql::Escape($this->user);
		$award = sql::Escape($this->award);
		$sql->Query("UPDATE $this->tablename SET
					guild = '$guild',
					user = '$user',
					award = '$award'
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
		$guild = sql::Escape($this->guild);
		$user = sql::Escape($this->user);
		$award = sql::Escape($this->award);
		$sql->Query("INSERT INTO $this->tablename SET
					guild = '$guild',
					award = '$award',
					user = '$user'
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
	function exists($id)
	{
		global $sql;
		$id = sql::escape($id);
		$tablename = dkpPointsHistoryTableEntry::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$id'");
		return ($exists != "");
	}
	/*===========================================================
	getAward()
	Returns the award that this history refers to
	============================================================*/
	function getAward(){
		$award = new dkpAward();
		$award->loadFromDatabase($this->award);
		return $award;
	}

	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpPointsHistoryTableEntry::tablename)) {
			$tablename = dkpPointsHistoryTableEntry::tablename;
			global $sql;
			$sql->Query("
				CREATE TABLE IF NOT EXISTS `$tablename` (
				  `id` int(11) NOT NULL auto_increment,
				  `user` int(11) NOT NULL,
				  `award` int(11) NOT NULL,
				  `guild` int(11) NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `user` (`user`),
				  KEY `award` (`award`),
				  KEY `guild` (`guild`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpPointsHistoryTableEntry::setupTable();
?>