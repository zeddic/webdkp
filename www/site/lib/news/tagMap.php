<?php

/*===========================================================

CLASS DESCRIPTION

=============================================================

Class Description should be placed here.

*/



class tagMap {

	/*===========================================================

	MEMBER VARIABLES

	============================================================*/

	var $id;

	var $post;

	var $tag;

	const tablename = "post_tags_map";

	/*===========================================================

	DEFAULT CONSTRUCTOR

	============================================================*/

	function tagMap()

	{

		$this->tablename = tagMap::tablename;

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

		$this->post = $row["post"];

		$this->tag = $row["tag"];

	}

	/*===========================================================

	save()

	Saves data into the backend database using the supplied id

	============================================================*/

	function save()

	{

		global $sql;

		$sql->Query("UPDATE $this->tablename SET

					post = '$this->post',

					tag = '$this->tag'

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

		$sql->Query("INSERT INTO $this->tablename SET

					post = '$this->post',

					tag = '$this->tag'

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

		$tablename = tagMap::tablename;

		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'"); //MODIFY THIS LINE

		return ($exists != "");

	}

	/*===========================================================

	setupTable()

	Checks to see if the classes database table exists. If it does not

	the table is created.

	============================================================*/

	function setupTable()

	{

		if(!sql::TableExists(tagMap::tablename)) {

			$tablename = tagMap::tablename;

			global $sql;

			$sql->Query("CREATE TABLE `$tablename` (

						`id` INT NOT NULL AUTO_INCREMENT ,

						`post` INT NOT NULL,

						`tag` INT NOT NULL,

						PRIMARY KEY ( `id` )

						) TYPE = innodb;");

		}

	}

}

tagMap::setupTable()

?>