<?php
/*===========================================================
CLASS DESCRIPTION - dkpUserPermissions
=============================================================
The user permissions class provides functionality to load
security permissions for a given user and see what actions
they have access to.

These permissions all relate to performubg dkp actions, such as
editing a table or uploading log file settings.

This also provides to static methods that can be used to check
if the current signed in user has access to perform specific
actions: currentUserHasPermission
*/
include_once("dkpPermission.php");
class dkpUserPermissions {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//the id of this entry in the database
	var $id;
	//the user whos information this belong to
	var $user;
	//the id of the guild that these permissions relate to
	var $guildid;
	//the permissions that this user has
	var $permissions= array();
	//the tables that this user has access to
	var $tables= array();
	//whether or not this is an admin account that has unlimited access
	//to all permissions
	var $isAdmin;
	//a list of all permissions available. This isn't limited to the
	//permissions that the user has, but is an exhastive list.
	//key = permisson string name
	//value = permission id
	var $permissionList = array();
	
	var $tablename;
	const tablename = "dkp_userpermissions";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpUserPermissions::tablename;
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
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadUserPermissions($id)
	Load permissions for a particular user
	============================================================*/
	function loadUserPermissions($userid){
		global $sql;
		$userid = sql::Escape($userid);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE user='$userid'");
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
		$this->permissions = explode(",", $row["permissions"]);
		$this->tables = explode(",", $row["tables"]);
		$this->isAdmin = $row["isadmin"] ?? null;
		$this->guildid = $this->getUserGuildId();
		$this->loadPermissionList();
	}

	/*===========================================================
	getUserGuildId()
	Loads the guild id for the user that these permissions belong to
	============================================================*/
	function getUserGuildId() {
		global $sql;
		$userid = sql::Escape($this->user);
		$guildid = $sql->QueryItem("SELECT guild FROM security_users WHERE id='$userid'");
		return $guildid;
	}

	/*===========================================================
	loadPermissionList()
	Loads up a list of all permissions in the system. These arn't permissions
	that this user neccessarily has... just a list of all of them that
	are potentionally available to have.
	============================================================*/
	function loadPermissionList(){
		global $sql;
		//check to see if a cached version is available
		if(isset($GLOBALS["dkpPermissionList"]))
			$this->permissionList = $GLOBALS["dkpPermissionList"];

		$this->permissionList = array();
		$result = $sql->Query("SELECT * FROM dkp_permissions");
		while($row = mysqli_fetch_array($result)){
			$this->permissionList[$row["name"]] = $row["id"] ?? null;
		}

		//cache a version in case someone else needs to load it in the future
		$GLOBALS["dkpPermissionList"] = $this->permissionList;
	}

	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$permissions = sql::Escape(implode(",",$this->permissions));
		$tables = sql::Escape(implode(",",$this->tables));
		$sql->Query("UPDATE $this->tablename SET
					user = '$this->user',
					permissions = '$permissions',
					tables = '$tables',
					isadmin = '$this->isAdmin'
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
		$permissions = sql::Escape(implode(",",$this->permissions));
		$tables = sql::Escape(implode(",",$this->tables));
		$sql->Query("INSERT INTO $this->tablename SET
					user = '$this->user',
					permissions = '$permissions',
					tables = '$tables',
					isadmin = '$this->isAdmin'
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
	userHasPermission()
	Returns true if the given user has permissions to the specified
	dkp permission.
	============================================================*/
	function userHasPermission($permissionName, $tableid = -1){
		if ($this->isAdmin == 1) {
			return true;
		}
		//check to see if this permission exists at all...
		if (!array_key_exists($permissionName,$this->permissionList)) {
			return false;
		}

		//translate the permission name to its id
		$permissionId = $this->permissionList[$permissionName];

		//see if the user has this permission
		if (!in_array($permissionId, $this->permissions)) {
			return false;
		}

		//if the user also passed a tableid, we will need to check to see
		//if the user has access to this tableid. If they do not - then they
		//will not have access to the specified permission.
		if( $tableid != -1 &&
			$this->permissionRequiresTableCheck($permissionName)  &&
			!$this->userHasAccessToTable($tableid)) {
			return false;
		}

		//passed all the hurdles - they have access
		return true;
	}

	/*===========================================================
	userHasAccessToTable()
	Returns true if the user has access to the specified table.
	They have access if one of the following is true:
	1 - They have the AllTableAccess flag
	2 - Their tables datastructure contains the table database id of the passed tableid

	Note that this function is passed a tableid while the users
	table data memeber is an array of table database ids , which are different.
	Before checking our internal data structure, the passed tableid needs to be
	mapped to its passed table database id
	============================================================*/
	function userHasAccessToTable($tableid){
		if ($this->userHasPermission("AllTableAccess")) {
			return true;
		}

		global $sql;
		//Translate the tableid to the tables database id. (tableids are unique per guild, table db id are unique
		//for all guilds).
		//$guildid = sql::Escape($this->guildid);
		//$tableid = sql::Escape($tableid);
		//$tableDbId = $sql->QueryItem("SELECT id FROM dkp_tables WHERE guild='$guildid' AND tableid='$tableid'");
		//now see if this table database id is in the tables array
		return in_array($tableid,$this->tables);

	}


	/*===========================================================
	permissionRequiresTableCheck()
	Returns true if the given permission depends on whether or not
	the user has access to a given table. This only applies to certain
	permissions - such as adding or removing a player, editing history,
	adding points, etc. Other permissions it would not apply to, such
	as making a backup or changing settings.
	A permission is assumed to require a table check if the substring "Table" is
	present as the first part of the permission. For example:
	TableAddPlayer
	TableDeletePlayer, etc.
	============================================================*/
	function permissionRequiresTableCheck($permissionName){
		return (strpos($permissionName,"Table")===0);
	}

	/*===========================================================
	addPermission()
	Adds a given permission to the users list of available permissions.
	This is a permission name, not a permission id.
	============================================================*/
	function addPermission($permissionName){
		//if the permission is invalid do nothing
		$permissionId = $this->permissionList[$permissionName];
		if(empty($permissionId)) {
			return;
		}
		//if they already have the permission do nothing
		if (in_array($permissionId,$this->permissions)) {
			return;
		}
		//save
		$this->permissions[]=$permissionId;
	}

	/*===========================================================
	addTable()
	Adds a table to the list of tables that a user has access to.
	$guildid = id of guild that this table is in
	$tablid = tableid of the table (NOT the table database id as it is used internally)
	============================================================*/
	function addTable($tableid){
		$this->tables[]=$tableid;
	}

	/*===========================================================
	addTableByDatabaseId()
	Adds a table to the list of tables that a user has access to.
	$tableDbId - the id of the table in the database, which is unique in the database table.
				 This is different from $tableid, which is unique only for a given guild.
	============================================================*/
	/*function addTableByDatabaseId($tableDbId){
		$this->tables[]=$tableDbId;
	}*/

	/*===========================================================
	loadDefaultPermissions()
	Loads up a list of default permissions for this user. Used when creating a new
	secondary account from scatch so they can be setup with some
	general permissions and restrictions. Save() must still be called
	after this.
	============================================================*/
	function loadDefaultPermissions(){
		$this->isAdmin = 0;
		$this->loadPermissionList();
		$this->permissions = array();
		$this->addPermission("BackupCreate");
		$this->addPermission("Repair");
		$this->addPermission("ChangeSettings");
		$this->addPermission("AllTableAccess");
		$this->addPermission("TableAddPlayer");
		$this->addPermission("TableAddPoints");
		$this->addPermission("TableEditHistory");
		$this->addPermission("TableEditPlayers");
		$this->addPermission("TableUploadLog");
		$this->addPermission("LootTable");
	}


	/*===========================================================
	currentUserHasPermission()
	STATIC METHOD
	This method will return true if the _current user_ has permission
	to peform the specified action in the given guild and tableid.
	Parameters:
	$permissionName - the name of the permission to check (Ex: "MakeBackup")
	$guildid - The id of the guild where they are requested to perform the action
	$tableid - the id of the table where they are performing the action
	============================================================*/
	static function currentUserHasPermission($permissionName, $guildid, $tableid = -1){
		global $siteUser;
		global $dkpUserPermissions;

		//admin for the entire domain always has access to everything
		if($siteUser->usergroup->name == "Admin")
			return true;

		//1 - check if the user even belongs to this guild
		if(($siteUser->guild ?? null) != $guildid) {
			return false;
		}

		//2 - check to see if the information is in a global cache.
		if( !isset($dkpUserPermissions)) {
			//information not in global cache, reload it and store it in cache
			//(this will save subsequent calls to this method from having to
			// making a lot of sql queries)

			$permissions = new dkpUserPermissions();
			$permissions->loadUserPermissions($siteUser->id);

			$dkpUserPermissions = $permissions;
			$GLOBALS["dkpUserPermissions"] = $permissions;
		}

		//3 - now that we have the permission object, use it to check if
		//    if the user has access
		$result =  $dkpUserPermissions->userHasPermission($permissionName, $tableid);

		return $result;
	}

	/*===========================================================
	currentUserHasPermissionAnyTable()
	STATIC METHOD
	This method will return true if the _current user_ has permission
	to peform the specified action in the given guild in ANY of the given guilds
	tables. Use: For example determine whether the upload log file link should
	be displayed at all for table uploads, which would hinge on having rights to
	show any table.
	Parameters:
	$permissionName - the name of the permission to check (Ex: "MakeBackup")
	$guildid - The id of the guild where they are requested to perform the action
	$tableid - optional - the id of the table where they are performing the action
	============================================================*/
	static function currentUserHasPermissionAnyTable($permissionName, $guildid){
		global $siteUser;

		//get a list of all tables for this guild
		$tables = dkpPointsTable::getTableList($guildid);

		if(empty($tables))
			return false;

		//iterate through the tables - if they have rights in any of them, return true
		foreach($tables as $table) {
			if (dkpUserPermissions::currentUserHasPermission($permissionName, $guildid, $table->tableid)) {
				return true;
			}
		}
		//no matches
		return false;
	}

	/*===========================================================
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = dkpUserPermissions::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'");
		return ($exists != "");
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpUserPermissions::tablename)) {
			$tablename = dkpUserPermissions::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`user` INT NOT NULL,
						`permissions` VARCHAR (512) NOT NULL,
						`tables` VARCHAR (512) NOT NULL,
						`isadmin` INT NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
dkpUserPermissions::setupTable();
?>