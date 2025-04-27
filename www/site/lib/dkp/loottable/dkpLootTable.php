<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
include_once("dkpLootTableSection.php");
include_once("dkpLootTableEntry.php");
include_once("lib/stats/wowstats.php");
class dkpLootTable {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;
	var $guild;

	var $sections = array();
	var $loot = array();

	var $tablename;
	const tablename = "dkp_loottable";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpLootTable::tablename;
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
	loadFromDatabaseByName($name)
	Loads the loot table information given a guild id and a name
	============================================================*/
	function loadFromDatabaseByName($guildid, $name){
		global $sql;
		$guildid = sql::Escape($guildid);
		$name = sql::Escape($name);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE guild='$guildid' AND name='$name'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->name = $row["name"] ?? null;
		$this->guild = $row["guild"] ?? null;
	}

	/*===========================================================
	loadTableData
	Loads loot table information for this single table.
	This will load the loot table sections as well as individual loot
	table data.

	The parameter "mode" specifies how the loot table should be
	Possible values are:
	"section" - (default) Loot table data is stored within each section
	"flat" - all loot data is stored at the root level in $this->loot
	"both" - all loot data is loaded and stored in both locations
			 (uses twice as much memory)
	============================================================*/
	function loadTableData($mode = "section"){
		global $sql;

		//load loot sections
		$this->sections = array();
		$id = sql::Escape($this->id);
		$result = $sql->Query("SELECT * FROM dkp_loottable_section
							   WHERE loottable = '$id'");
		while($row = mysqli_fetch_array($result)) {
			$section = new dkpLootTableSection();
			$section->loadFromRow($row);
			$this->sections[$section->id] = $section;
		}

		//load loot data
		$this->loot = array();
		$result = $sql->Query("SELECT * FROM dkp_loottable_data
							   WHERE loottable = '$id'");
		while($row = mysqli_fetch_array($result)) {
			$loot = new dkpLootTableEntry();
			$loot->loadFromRow($row);
			//depending on mode, store the loaded data
			//differently in memory
			if($mode == "both" || $mode == "flat")
				$this->loot[] = $loot;
			if($mode == "both" || $mode == "section") {
				if(isset($this->sections[$loot->section])) {
					$this->sections[$loot->section]->loot[] = $loot;
				}
			}
		}
	}

	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$guild = sql::Escape($this->guild);
		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					guild = '$guild'
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
		$guild = sql::Escape($this->guild);
		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					guild = '$guild'
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
		$id = sql::Escape($this->id);
		$sql->Query("DELETE FROM $this->tablename WHERE id = '$id'");
		$sql->Query("DELETE FROM dkp_loottable_section WHERE loottable = '$id'");
		$sql->Query("DELETE FROM dkp_loottable_data WHERE loottable = '$id'");
	}
	/*===========================================================
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($guildid, $name)
	{
		global $sql;
		$name = sql::escape($name);
		$guildid = sql::escape($guildid);
		$tablename = dkpLootTable::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE guild='$guildid' AND name='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpLootTable::tablename)) {
			$tablename = dkpLootTable::tablename;
			global $sql;
			$sql->Query("CREATE TABLE IF NOT EXISTS `$tablename` (
				  `id` int(11) NOT NULL auto_increment,
				  `name` varchar(256) character set utf8 NOT NULL,
				  `guild` int(11) NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `guild` (`guild`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpLootTable::setupTable()
?>