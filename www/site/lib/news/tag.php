<?php

/*===========================================================

CLASS DESCRIPTION

=============================================================

Class Description should be placed here.

*/



class tag {

	/*===========================================================

	MEMBER VARIABLES

	============================================================*/

	var $id;

	var $name;

	const tablename = "content_news_tags";

	const maptablename = "content_news_tags_map";

	/*===========================================================

	DEFAULT CONSTRUCTOR

	============================================================*/

	function tag()

	{

		$this->tablename = tag::tablename;

		$this->maptablename = tag::maptablename;

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

	loadFromDatabaseByName($id)

	Loads the information for this class from the backend database

	using the passed tag name (instead of database id).

	============================================================*/

	function loadFromDatabaseByName($name){

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

		$this->id=$row["id"];

		$this->name = $row["name"];

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

	exists()

	STATIC METHOD

	Returns true if the given entry exists in the database

	database

	============================================================*/

	function exists($name)

	{

		global $sql;

		$name = sql::escape($name);

		$tablename = tag::tablename;

		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'");

		return ($exists != "");

	}



	/*===========================================================

	cleanUnusedTags()

	STATIC METHOD

	Looks into the database for any tags that are not currently being

	used and removes them from the database.

	============================================================*/

	function cleanUnusedTags()

	{

		global $sql;

		$tablename = tag::tablename;

		$maptablename = tag::maptablename;



		//makes use of a dependent subquery to handle this

		//sql statement translates into:

		//"Delete all tags that do not appear in a table that shows

		//all tags that are used at least one."

		//Depdentent subquery is stored into a temp array so that it

		//can be reused instead of being processed over and over again.

		$sql->Query("DELETE FROM $tablename

					 WHERE id NOT IN (

					 	SELECT postid FROM (

						 	SELECT $tablename.id as postid

							FROM $tablename, $maptablename

							WHERE $tablename.id = $maptablename.tag

							GROUP BY $tablename.id

							) as temp

					 )");

	}

	/*===========================================================

	setupTable()

	Checks to see if the classes database table exists. If it does not

	the table is created.

	============================================================*/

	function setupTable()

	{

		if(!sql::TableExists(tag::tablename)) {

			$tablename = tag::tablename;

			global $sql;

			$sql->Query("CREATE TABLE `$tablename` (

						`id` INT NOT NULL AUTO_INCREMENT ,

						`name` VARCHAR (256) NOT NULL,

						PRIMARY KEY ( `id` )

						) TYPE = innodb;");

		}



		if(!sql::TableExists(tag::maptablename)) {

			$tablename = tag::maptablename;

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

tag::setupTable()

?>