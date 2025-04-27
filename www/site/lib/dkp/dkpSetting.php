<?php

class dkpSetting {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $guild;
	var $name;
	var $value;
	var $tablename;
	const tablename = "dkp_settings";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpSetting::tablename;
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
		$this->name = $row["name"] ?? null;
		$this->value = $row["value"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);

		if(is_array($this->value))
			$value = implode(",",$this->value);
		else
			$value = $this->value;
		$value = sql::Escape($value);

		$sql->Query("UPDATE $this->tablename SET
					guild = '$this->guild',
					name = '$name',
					value = '$value'
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
		if(is_array($this->value))
			$value = implode(",",$this->value);
		else
			$value = $this->value;
		$value = sql::Escape($value);
		$sql->Query("INSERT INTO $this->tablename SET
					guild = '$this->guild',
					name = '$name',
					value = '$value'
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
	static function exists($guildid, $name)
	{
		global $sql;
		$guildid = sql::Escape($guildid);
		$name = sql::Escape($name);
		$tablename = dkpSetting::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE guild='$guildid' AND name='$name'");
		return ($exists != "");
	}
	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpSetting::tablename)) {
			$tablename = dkpSetting::tablename;
			global $sql;
			$sql->Query("CREATE TABLE IF NOT EXISTS `$tablename` (
				  `id` int(11) NOT NULL auto_increment,
				  `guild` int(11) NOT NULL,
				  `name` varchar(256) NOT NULL,
				  `value` varchar(256) NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `guild` (`guild`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpSetting::setupTable()
?>