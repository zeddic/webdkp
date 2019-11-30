<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class bbcodeContent {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $iid;
	var $createdby;
	var $lastupdateDate;
	var $lastupdateTime;
	var $content;
	const tablename = "part_bbcode";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function bbcodeContent()
	{
		$this->tablename = bbcodeContent::tablename;
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
	loadFromDatabaseInstance($id)
	Loads the information for an html content based on the instance number
	that is passed. This instance number would coresspond to the instance number
	of the module that the content page is showing on.
	============================================================*/
	function loadFromDatabaseByInstance($instance)
	{
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE iid='$instance'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"];
		$this->iid = $row["iid"];
		$this->createdby = $row["createdby"];
		if($row["lastupdate"]!="")
		{
			$this->lastupdateDate = date("F j, Y", strtotime($row["lastupdate"]));
			$this->lastupdateTime = date("g:i A", strtotime($row["lastupdate"]));
		}
		$this->content = $row["content"];
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$createdby = sql::Escape($this->createdby);
		$content = sql::Escape($this->content);
		$sql->Query("UPDATE $this->tablename SET
					iid = '$this->iid',
					createdby = '$createdby',
					content = '$content'
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
		$createdby = sql::Escape($this->createdby);
		$content = sql::Escape($this->content);
		$sql->Query("INSERT INTO $this->tablename SET
					iid = '$this->iid',
					createdby = '$createdby',
					content = '$content'
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
		$tablename = bbcodeContent::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	function setupTable()
	{
		if(!sql::TableExists(bbcodeContent::tablename)) {
			$tablename = bbcodeContent::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`iid` INT NOT NULL,
						`createdby` VARCHAR (256) NOT NULL,
						`lastupdate` DATETIME NOT NULL,
						`content` TEXT NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
bbcodeContent::setupTable()
?>
