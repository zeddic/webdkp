<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
include_once("dkpRemoteStyle.php");
class dkpCustomRemoteStyle {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $guild;
	var $content;
	var $tablename;
	const tablename = "dkp_remote_custom";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpCustomRemoteStyle::tablename;
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
	loadFromDatabase($id)
	Loads the information for this class for the given guild
	============================================================*/
	function loadFromGuild($guildid){
		global $sql;
		$guildid = sql::Escape($guildid);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE guild='$guildid'");
		$this->loadFromRow($row);
	}

	/*===========================================================
	Ensures that a custom style exists for the given guild
	============================================================*/
	static function ensureExists($guildid){
		if( !dkpCustomRemoteStyle::exists($guildid) ) {

			$custom = new dkpCustomRemoteStyle();
			$custom->guild = $guildid;
			$custom->saveNew();
			$custom->loadFromPremade(0);
		}
	}

	/*===========================================================
	Sets the contents of this custom style to be the same
	of the premade style with the given id
	============================================================*/
	function loadFromPremade($styleid){
		global $sql;
		if($styleid == 0) {
			$table = dkpRemoteStyle::tablename;
			$styleid = $sql->QueryItem("SELECT id FROM $table WHERE file='standard'");
		}

		$style = new dkpRemoteStyle();
		$style->loadFromDatabase($styleid);
		$content = $style->getContent();
		$this->content = $content;
		$this->save();
	}

	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->guild = $row["guild"] ?? null;
		$this->content = $row["content"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$content = sql::Escape($this->content);
		$sql->Query("UPDATE $this->tablename SET
					guild = '$this->guild',
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
		$content = sql::Escape($this->content);
		$sql->Query("INSERT INTO $this->tablename SET
					guild = '$this->guild',
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
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($guildid)
	{
		global $sql;
		$guildid = sql::escape($guildid);
		$tablename = dkpCustomRemoteStyle::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE guild='$guildid'");
		return ($exists != "");
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpCustomRemoteStyle::tablename)) {
			$tablename = dkpCustomRemoteStyle::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`guild` INT NOT NULL,
						`content` TEXT,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
dkpCustomRemoteStyle::setupTable()
?>