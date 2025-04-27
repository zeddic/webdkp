<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class permission {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;
	var $category;
	var $tablename;
	const tablename = "security_permissions";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = permission::tablename;
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
	using the passed string.
	============================================================*/
	function loadFromDatabaseByName($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE name='$name'");
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
		$this->category = $row["category"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$category = sql::Escape($this->category);
		$sql->Query("UPDATE $this->tablename SET
		          name = '$name',
		          category = '$category'
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
		$category = sql::Escape($this->category);
		$sql->Query(" INSERT INTO $this->tablename SET
				  name = '$name',
		          category = '$category'
		          ");
		$this->id=$sql->getLastId();
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
	Returns true if a user group already exists with the given name
	============================================================*/
	function exists($name){
		global $sql;
		$name = sql::Escape($name);
		$tablename = permission::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'");
		return ($exists != "");
	}
	/*===========================================================
	getPermissionList()
	STATIC METHOD
	Returns an array containing every permission in the system.
	============================================================*/
	function getPermissionList(){
		global $sql;
		$toReturn = array();
		$tablename = permission::tablename;
		$result = $sql->Query("SELECT * FROM $tablename");
		while($row = mysqli_fetch_array($result)){
			$permission = new permission();
			$permission->loadFromRow($row);
			$toReturn[]=$permission;
		}
		return $permission;
	}
	/*===========================================================
	Returns the id of a permission with the given name. Returns
	"" if the permission does not exist.
	============================================================*/
	static function getPermissionIdByName($permissionName){
		$permission = new permission();
		$permission->loadFromDatabaseByName($permissionName);
		return $permission->id;
	}

	/*===========================================================
	Checks to see if the table for this structure exists in the database
	If it does not, the table is created.
	============================================================*/
	static function setupTable(){
		if(!sql::tableExists(permission::tablename)) {
			$tablename = permission::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR( 256 ) NOT NULL ,
						`category` VARCHAR ( 256 ) NOT NULL ,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");

			//setup the baisc permissions
			//$permission = new permission();
			//$permission->name = "User Rights";
			//$permission->saveNew();

			//$permission->name = "Moderator Rights";
			//$permission->saveNew();

			//$permission->name = "Admin Rights";
			//$permission->saveNew();
		}
	}
}
permission::setupTable();
?>