<?php
/*===========================================================
CLASS DESCRIPTION - dkpPermission
=============================================================
The dkp permission class contains a single permission
that is available for dkp users to have. (dkp users being
dkp administrators vs dkp players which just belong to
a table). It is a simple class with a unique database id
and a name. Most dkp permission functionality is in the
dkpUserPermissions class.
*/

class dkpPermission {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;
	var $tablename;
	const tablename = "dkp_permissions";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpPermission::tablename;
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
	}
	/*===========================================================
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$tablename = dkpPermission::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'");
		return ($exists != "");
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpPermission::tablename)) {
			$tablename = dkpPermission::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR (256) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");

			//make sure our table has some default values
			dkpPermission::addPermission("BackupCreate");
			dkpPermission::addPermission("BackupRestore");
			dkpPermission::addPermission("RecalculateDKP");
			dkpPermission::addPermission("DKPTables");
			dkpPermission::addPermission("AccountEditGuild");
			dkpPermission::addPermission("AccountSecondaryUsers");
			dkpPermission::addPermission("ChangeSettings");
			dkpPermission::addPermission("AllTableAccess");
			dkpPermission::addPermission("TableAddPlayer");
			dkpPermission::addPermission("TableDeletePlayer");
			dkpPermission::addPermission("TableAddPoints");
			dkpPermission::addPermission("TableEditHistory");
			dkpPermission::addPermission("TableEditPlayers");
			dkpPermission::addPermission("TableUploadLog");
			dkpPermission::addPermission("Repair");
			dkpPermission::addPermission("LootTable");
		}
	}

	/*===========================================================
	addPermission() (STATIC)
	Creates a new permissions with the given name. If the permission
	already exists, this method does nothing.
	============================================================*/
	function addPermission($name){
		if(!dkpPermission::exists($name)) {
			$permission = new dkpPermission();
			$permission->name = $name;
			$permission->saveNew();
		}
	}
}
dkpPermission::setupTable()
?>