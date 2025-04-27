<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class dkpLootTableEntry {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $section;
	var $loottable;
	var $name;
	var $cost;
	var $tablename;
	const tablename = "dkp_loottable_data";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpLootTableEntry::tablename;
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
	loadFromDatabaseByName($id)
	Loads the information for this class from the backend database
	using the passed section id and loot name
	============================================================*/
	function loadFromDatabaseByName($section, $name)
	{
		global $sql;
		$section = sql::Escape($section);
		$name = sql::Escape($name);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE section='$section' AND name='$name'");
		$this->loadFromRow($row);
	}

	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->section = $row["section"] ?? null;
		$this->loottable = $row["loottable"] ?? null;
		$this->name = $row["name"] ?? null;
		$this->cost = $row["cost"] ?? null;
		$this->cost = str_replace(".00", "", $this->cost);
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$sql->Query("UPDATE $this->tablename SET
					section = '$this->section',
					loottable = '$this->loottable',
					name = '$name',
					cost = '$this->cost'
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
		$name = sql::Escape($this->name);
		$sql->Query("INSERT INTO $this->tablename SET
					section = '$this->section',
					loottable = '$this->loottable',
					name = '$name',
					cost = '$this->cost'
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
	static function exists($loottable, $section, $name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = dkpLootTableEntry::tablename;
		$section = sql::Escape($section);
		$loottable = sql::Escape($loottable);
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE loottable='$loottable' AND section='$section' AND name='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpLootTableEntry::tablename)) {
			$tablename = dkpLootTableEntry::tablename;
			global $sql;
			$sql->Query("CREATE TABLE IF NOT EXISTS `$tablename` (
				  `id` int(11) NOT NULL auto_increment,
				  `section` int(11) NOT NULL,
				  `loottable` int(11) NOT NULL,
				  `name` varchar(256) character set utf8 NOT NULL,
				  `cost` decimal(11,2) NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `loottable` (`loottable`,`section`,`name`(255))
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpLootTableEntry::setupTable()
?>