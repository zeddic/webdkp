<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

include_once("permission.php");

class userGroup {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;					//the name of the user group
	var $permissions = array();	//an array of permission instances that this group has
	var $default = 0;			//if = 1 this is the default group that all new users go to
	var $system = 0;			//if 1 this is a system group and cannot be edited / deleted
	var $visitor = 0;			//if 1 this is a vistor group that unregistered users are pushed to
	var $tablename;
	const tablename = "security_usergroups";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = userGroup::tablename;
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
	loadFromDatabaseByName
	Loads a user group's information from the user groups name
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
		$this->default = $row["defaultuser"] ?? null;
		$this->system = $row["system"] ?? null;
		$this->visitor = $row["visitor"] ?? null;
		$permissions = $row["permissions"] ?? null;
		//permissions are stored as an array of permission ids in the database
		//we need to iterate through each of these permissions id and convert
		//them to their actual instances
		$permissions = explode(",",$permissions);
		if($permissions != ""){
			foreach($permissions as $permissionId){
				$permission = new permission();
				$permission->loadFromDatabase($permissionId);
				$this->permissions[]=$permission;
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
		$permissions = $this->getPermissionIds();
		$permissions = implode(",",$permissions);
		$permissions = sql::Escape($permissions); //just a precaution

		//$accessrights = sql::Escape($this->accessrights);
		$sql->Query("UPDATE $this->tablename SET
		          name = '$name',
		          permissions = '$permissions',
		          defaultuser = '$this->default',
		          system = '$this->system',
		          visitor = '$this->visitor'
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
		$permissions = $this->getPermissionIds();
		$permissions = implode(",",$permissions);
		$permissions = sql::Escape($permissions); //just a precaution
		$sql->Query(" INSERT INTO $this->tablename SET
		          name = '$name',
		          permissions = '$permissions',
		          defaultuser = '$this->default',
		          system ='$this->system',
		          visitor = '$this->visitor'
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
		//don't delete if this is the default user group
		$defaultGroup = $sql->QueryItem("SELECT id FROM $this->tablename WHERE defaultuser=1");
		if($this->id == $defaultGroup) {
			return;
		}

		$this->loadFromDatabase($this->id);

		if( $this->system == 1 ||
			$this->default == 1 ||
			$this->visitor == 1) {
			return;
		}

		//delete the current group
		$sql->Query("DELETE FROM $this->tablename WHERE id = '$this->id'");

		//shift all users to the new group
		$usertable = user::tablename;
		$sql->Query("UPDATE $usertable SET usergroup='$defaultGroup' WHERE usergroup='$this->id'");
	}
	/*===========================================================
	getPermissionIds()
	Returns an array of ids of all the permissions that this
	user group has.
	============================================================*/
	function getPermissionIds(){
		if(!is_array($this->permissions))
			return explode(",",$this->permissions);

		$toReturn = array();
		if($this->permissions != ""){
			foreach($this->permissions as $permission){
				$toReturn[]=$permission->id;
			}
		}
		return $toReturn;
	}

	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if a user group already exists with the given name
	============================================================*/
	static function exists($name){
		global $sql;
		$name = sql::Escape($name);
		$tablename = userGroup::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'");
		return ($exists != "");
	}

	/*===========================================================
	hasPermission()
	Returns true if this user group has the given permission
	in its array of permissions
	============================================================*/
	function hasPermission($permissionName){
		if($this->permissions != ""){
			foreach($this->permissions as $permission){
				if($permission->name == $permissionName){
					return true;
				}
			}
		}
		return false;
	}
	/*===========================================================
	hasPermissionId()
	Returns true if this user group has the given permission (id)
	in its array of permissions
	============================================================*/
	function hasPermissionId($permissionId){
		if($this->permissions != ""){
			foreach($this->permissions as $permission){
				if($permission->id == $permissionId){
					return true;
				}
			}
		}
		return false;
	}
	/*===========================================================
	getUserGroupIdByName()
	STATIC METHOD
	Returns the id of the usergroup with the given name. If the user
	group does not exist, "" is returned.
	============================================================*/
	static function getUserGroupIdByName($name){
		$usergroup = new userGroup();
		$usergroup->loadFromDatabaseByName($name);
		return $usergroup->id;
	}
	/*===========================================================
	getVisitorUserGroup()
	Returns the visitor user group. This is the user group that a user
	visiting the site (without registering) would go to.
	============================================================*/
	static function getVisitorUserGroup(){
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE visitor='1'");
		$userGroup = new userGroup();
		$userGroup->loadFromRow($row);
		return $userGroup;
	}
	/*===========================================================
	getDefaultUserGroup()
	Returns the default user group. This is the user group that a default,
	newly registered user would be placed in.
	============================================================*/
	static function getDefaultUserGroup(){
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE defaultuser='1'");
		$userGroup = new userGroup();
		$userGroup->loadFromRow($row);
		return $userGroup;
	}

	/*===========================================================
	setVisitorUserGroup()
	Sets the id of the usergroup that will the the 'visitor' user group.
	This is the group that all un registered users will automattically
	be put in.
	============================================================*/
	static function setVisitorUserGroup($groupid){
		global $sql;
		if ($groupid == "" || !userGroup::userGroupExists($groupid)) {
			return;
		}
		$table = userGroup::tablename;

		$sql->Query("UPDATE $table SET visitor = 0");
		$sql->Query("UPDATE $table SET visitor = 1 WHERE id='$groupid'");
	}
	/*===========================================================
	setDefaultUserGroup()
	Sets the id of the usergroup that new users will be put into
	when they register.
	============================================================*/
	static function setDefaultUserGroup($groupid){
		global $sql;

		if ($groupid == "" || !userGroup::userGroupExists($groupid)) {
			return;
		}
		$table = userGroup::tablename;

		$sql->Query("UPDATE $table SET defaultuser = 0");
		$sql->Query("UPDATE $table SET defaultuser = 1 WHERE id='$groupid'");
	}

	/*===========================================================
	Returns true if a usergroup with the given id exists
	============================================================*/
	static function userGroupExists($groupid){
		global $sql;
		$table = userGroup::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $table WHERE id='$groupid'");
		return ($exists!="");
	}

	/*===========================================================
	Checks to see if the  table exists. If it does not, a new
	instance is created for it in the database.
	============================================================*/
	static function setupTable(){
		if(!sql::tableExists(userGroup::tablename)) {
			$tablename = userGroup::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR( 256 ) NOT NULL ,
						`permissions` VARCHAR( 512 ) NOT NULL ,
						`defaultuser` TINYINT( 1 ) DEFAULT'0' NOT NULL ,
						`visitor` TINYINT( 1 ) DEFAULT'0' NOT NULL ,
						`system` TINYINT( 1 ) DEFAULT'0' NOT NULL ,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
userGroup::setupTable();
?>