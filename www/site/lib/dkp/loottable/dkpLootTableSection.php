<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

include_once("dkpLootTableEntry.php");

class dkpLootTableSection {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $loottable;
	var $name;

	var $loot = array();
	var $tablename;
	const tablename = "dkp_loottable_section";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpLootTableSection::tablename;
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
	Loads the information for this class by using a loot table id and
	a name
	============================================================*/
	function loadFromDatabaseByName($loottable, $name)
	{
		global $sql;
		$name = sql::Escape($name);
		$loottable = sql::Escape($loottable);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE loottable='$loottable' AND name='$name'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->loottable = $row["loottable"] ?? null;
		$this->name = $row["name"] ?? null;
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
					loottable = '$this->loottable',
					name = '$name'
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
					loottable = '$this->loottable',
					name = '$name'
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
		if($this->id != "") {
			$datatable = dkpLootTableEntry::tablename;
			$sql->Query("DELETE FROM $datatable WHERE section='$this->id'");
		}
	}
	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($loottable, $name)
	{
		global $sql;
		$name = sql::escape($name);
		$table = sql::Escape($loottable);
		$tablename = dkpLootTableSection::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE loottable='$table' AND name='$name'");
		return ($exists != "");
	}
	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpLootTableSection::tablename)) {
			$tablename = dkpLootTableSection::tablename;
			global $sql;
			$sql->Query("CREATE TABLE IF NOT EXISTS `$tablename` (
				  `id` int(11) NOT NULL auto_increment,
				  `loottable` int(11) NOT NULL,
				  `name` varchar(256) character set utf8 NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `loottable` (`loottable`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpLootTableSection::setupTable()
?>