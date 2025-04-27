<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class song {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $title;
	var $artist;
	var $album;
	var $count;
	var $tablename;
	const tablename = "song";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function song()
	{
		$this->tablename = song::tablename;
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
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadByName($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE title='$name'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->title = $row["title"] ?? null;
		$this->artist = $row["artist"] ?? null;
		$this->album = $row["album"] ?? null;
		$this->count = $row["count"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$title = sql::Escape($this->title);
		$artist = sql::Escape($this->artist);
		$album = sql::Escape($this->album);
		$sql->Query("UPDATE $this->tablename SET
					title = '$title',
					artist = '$artist',
					album = '$album',
					count = '$this->count'
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
		$title = sql::Escape($this->title);
		$artist = sql::Escape($this->artist);
		$album = sql::Escape($this->album);
		$sql->Query("INSERT INTO $this->tablename SET
					title = '$title',
					artist = '$artist',
					album = '$album',
					count = '$this->count'
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
	timestamp()
	Applies a timestamp to the post.
	============================================================*/
	function timestamp() {
		global $sql;
		$table = $this->tablename;
		$sql->Query("UPDATE $table SET played=NOW() WHERE id='$this->id'");
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
		$tablename = song::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE title='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	function setupTable()
	{
		if(!sql::TableExists(song::tablename)) {
			$tablename = song::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`title` VARCHAR (256) NOT NULL,
						`artist` VARCHAR (256) NOT NULL,
						`album` VARCHAR (256) NOT NULL,
						`count` INT NOT NULL,
						`played` DATETIME NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
song::setupTable()
?>
