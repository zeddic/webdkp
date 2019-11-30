<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
include_once("navigationEntry.php");
class navigationList {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $partid;
	var $list= array();
	const tablename = "part_navigation_list";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function navigationList()
	{
		$this->tablename = navigationList::tablename;
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
	loadFromDatabasePartId($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabasePartId($partid)
	{
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE partid='$partid'");
		$this->loadFromRow($row);
	}

	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row, $loadEntries = true)
	{
		$this->id= $row["id"];
		$this->partid = $row["partid"];
		$this->list = explode(",", $row["list"]);

		//load each navigation line entry
		if($loadEntries) {
			$list = implode(",",$this->list);
			$orderbylist = "ORDER BY ";
			foreach($this->list as $id)
				$orderbylist .= "id=$id DESC, ";
			$orderbylist = rtrim($orderbylist,", ");
			$table = navigationEntry::tablename;


			$this->list = array();
			if($list != "") {
				global $sql;
				$result = $sql->Query("SELECT * FROM $table WHERE id IN ($list) $orderbylist ");
				while($row = mysql_fetch_array($result)) {
					$entry = & new navigationEntry();
					$entry->loadFromRow($row);
					$this->list[] = $entry;
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
		$toSave = array();
		foreach($this->list as $entry) {
			if(is_a($entry,"navigationEntry"))
				$toSave[]=$entry->id;
			else
				$toSave[]=$entry;
		}
		$toSave = sql::Escape(implode(",",$toSave));
		$partid = sql::Escape($this->partid);
		$sql->Query("UPDATE $this->tablename SET
					partid = '$partid',
					list = '$toSave'
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
		$toSave = array();
		foreach($this->list as $entry) {
			if(is_a($entry,"navigationEntry"))
				$toSave[]=$entry->id;
			else
				$toSave[]=$entry;
		}
		$toSave = sql::Escape(implode(",",$toSave));
		$partid = sql::Escape($this->partid);
		$sql->Query("INSERT INTO $this->tablename SET
					partid = '$partid',
					list = '$toSave'
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
	function exists($id)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = navigationList::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$id'"); //MODIFY THIS LINE
		return ($exists != "");
	}

	/*===========================================================
	addNewLink()
	Adds a new link to the list.
	============================================================*/
	function addNewLink($title, $url, $type, $permission, $usergroups){
		$link = & new navigationEntry();
		$link->name = $title;
		$link->url = $url;

		if(is_int($type) && $type > 0 && $type <= 3)
			$link->type = $type;
		else if($type == "relativeSite")
			$link->type = navigationEntry::TYPE_RELATIVE_SITE;
		else if($type == "relativePage")
			$link->type = navigationEntry::TYPE_RELATIVE_PAGE;
		else
			$link->type = navigationEntry::TYPE_ABSOLUTE;

		$link->partid = $this->partid;
		$link->permission = $permission;
		$link->userGroups = $usergroups;

		$link->saveNew();

		//$temp = array($link);
		//$this->list = array_merge($temp,$this->list);
		$this->list[]=$link;
		return $link->id;
	}

	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	function setupTable()
	{
		if(!sql::TableExists(navigationList::tablename)) {
			$tablename = navigationList::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`partid` INT NOT NULL,
						`list` VARCHAR (512) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
navigationList::setupTable()
?>
