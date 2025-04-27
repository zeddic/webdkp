<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class itemcache {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $itemid;
	var $name;
	var $link;
	var $quality;
	var $icon;
	var $tablename;
	const tablename = "dkp_itemcache";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = itemcache::tablename;
	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabase($id)
	{
		global $sql;
		$id = sql::Escape($id);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'LIMIT 1");
		$this->loadFromRow($row);
	}

	/*===========================================================
	loadFromDatabaseByName($name)
	Loads the information for this class from the backend database
	using the passed item name
	============================================================*/
	function loadFromDatabaseByName($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE name='$name' LIMIT 1");
		$this->loadFromRow($row);
	}

	/*===========================================================
	loadItemFromDatabaseByName($name)
	Loads the information for this class from the backend database
	using the passed item name (For Loot Page, Called from wowstats.php)
	============================================================*/
	function loadItemFromDatabaseByName($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE name='$name' LIMIT 1");
		$this->loadFromRow($row);
	}

	/*===========================================================
	loadFromDatabaseByItemID($itemid)
	Loads the information for this class from the backend database
	using the passed item name
	============================================================*/
	function loadFromDatabaseByItemID($itemid)
	{
		global $sql;
		$name = sql::Escape($itemid);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE itemid='$itemid' LIMIT 1");
		$this->loadFromRow($row);
	}


	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->itemid = $row["itemid"] ?? null;
		$this->name = $row["name"] ?? null;
		$this->link = $row["link"] ?? null;
		$this->quality = $row["quality"] ?? null;
		$this->icon = $row["icon"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$itemid = sql::Escape($this->itemid);
		$link = sql::Escape($this->link);
		$quality = sql::Escape($this->quality);
		$icon = sql::Escape($this->icon);
		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					itemid = '$itemid',
					link = '$link',
					quality = '$quality',
					icon = '$icon'
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
		$itemid = sql::Escape($this->itemid);
		$link = sql::Escape($this->link);
		$quality = sql::Escape($this->quality);
		$icon = sql::Escape($this->icon);
		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					itemid = '$itemid',
					link = '$link',
					quality = '$quality',
					icon = '$icon'
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
	static function exists($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$tablename = itemcache::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'");
		return ($exists != "");
	}
	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(itemcache::tablename)) {
			$tablename = itemcache::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`itemid` INT NOT NULL,
						`name` VARCHAR (256) NOT NULL,
						`link` VARCHAR (256) NOT NULL,
						`quality` VARCHAR (256) NOT NULL,
						`icon` VARCHAR (256) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
itemcache::setupTable();
?>