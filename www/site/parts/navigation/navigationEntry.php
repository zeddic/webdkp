<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class navigationEntry {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;
	var $url;
	var $type;
	var $permission;
	var $usergroups;
	var $partid;
	const tablename = "part_navigation";
	const TYPE_ABSOLUTE = 1;
	const TYPE_RELATIVE_SITE = 2;
	const TYPE_RELATIVE_PAGE = 3;
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function navigationEntry()
	{
		$this->tablename = navigationEntry::tablename;
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
		$this->id=$row["id"];
		$this->name = $row["name"];
		$this->url = $row["url"];
		$this->type = $row["type"];
		$this->partid = $row["partid"];
		$this->permission = $row["permission"];//explode(",", $row["permission"]);
		$this->userGroups = explode(",",$row["usergroups"]);
		//$this->images = explode(",", $row["images"]);
		//$this->files = explode(",", $row["files"]);
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$url = sql::Escape($this->url);
		//$permissions = sql::Escape(implode(",",$this->permissions));
		$userGroups = sql::Escape(implode(",",$this->userGroups));

		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					url = '$url',
					type = '$this->type',
					partid = '$this->partid',
					permission = '$this->permission',
					usergroups = '$userGroups'
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
		$url = sql::Escape($this->url);
		$userGroups = sql::Escape(implode(",",$this->userGroups));

		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					url = '$url',
					type = '$this->type',
					partid = '$this->partid',
					permission = '$this->permission',
					usergroups = '$userGroups'
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
	function exists($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = navigationEntry::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}

	/*===========================================================
	setValues()
	Sets all of the link values at once
	============================================================*/
	function setValues($title, $url, $type, $partid){
		$this->name = $title;
		$this->url = $url;

		if(is_int($type) && $type > 0 && $type <= 3)
			$this->type = $type;
		else if($type == "relativeSite")
			$this->type = navigationEntry::TYPE_RELATIVE_SITE;
		else if($type == "relativePage")
			$this->type = navigationEntry::TYPE_RELATIVE_PAGE;
		else
			$this->type = navigationEntry::TYPE_ABSOLUTE;

		$this->partid =$partid;

	}
	/*===========================================================
	getLink()
	Gets a valid link. This is a valid url taking into consideration
	the type of the url (relative , absolute, etc)
	============================================================*/
	function getLink(){
		global $SiteRoot;
		$self = $_SERVER["PHP_SELF"];
		$dir = fileutil::upPath($self);

		if($this->type == navigationEntry::TYPE_ABSOLUTE) {
			return $this->url;
		}
		else if($this->type == navigationEntry::TYPE_RELATIVE_PAGE) {
			//echo($self);
			return $dir."/".$this->url;
		}
		else {
			return $SiteRoot . $this->url;
		}
	}

	/*===========================================================
	isHere()
	Returns true if the link is the current page
	============================================================*/
	function isHere(){

		$link = $this->getLink();
		$request = $GLOBALS["SiteRoot"].fileutil::stripExt(dispatcher::getUrl());

		return  fileutil::containsPath($request,$link);
	}
	/*===========================================================
	canView()
	Returns true if the current user is allowed to view the given link.
	False otherwise.
	============================================================*/
	function canView(){
		return ($this->meetsPermissionReqs() &&
			    $this->meetsUserGroupReqs());
	}
	/*===========================================================
	meetsPermissionReqs()
	Returns true if the current user meets the permission requirments
	neccessary to view this link: ie, the user has the permission
	required
	============================================================*/
	function meetsPermissionReqs(){
		if ($this->permission == "" || $this->permission == "0") {
			return true;
		}

		return security::hasPermissionIdAccess($this->permission);
	}

	/*===========================================================
	meetsUserGroupReqs()
	Returns true if the current user meets the usergroup requirements
	neccessary to view this lik: ie, the use belongs to one of the specified
	user groups.
	============================================================*/
	function meetsUserGroupReqs(){
		//if the usergroup 'everyone' is set, every user meets this requirement
		if( sizeof($this->userGroups)==0 || in_array("everyone",$this->userGroups) )
			return true;

		//otherwise, see if the users current group is in the list of groups
		//with access
		global $siteUser;
		$groupid = $siteUser->usergroup->id;

		return in_array($groupid,$this->userGroups);
	}

	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	function setupTable()
	{
		if(!sql::TableExists(navigationEntry::tablename)) {
			$tablename = navigationEntry::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR (256) NOT NULL,
						`url` VARCHAR (256) NOT NULL,
						`permission` INT NOT NULL,
						`usergroups` VARCHAR (512) NOT NULL,
						`type` INT NOT NULL,
						`partid` INT NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
navigationEntry::setupTable()
?>
