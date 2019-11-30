<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
The partOption class contains the information about a specific
option that is available to be set for a single type of part.
This does not store a value of a selected option, this is just a
definition of an option that is available to be set.
For example, a weather part might have options for the location
and whether the user wants tempeatures in C of F.

The actual options that they set will be available in the part
instance (see class part);
*/

class partOption {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;					//unique database id
	var $partDefinition;		//the part definition that this option belongs to
	var $name;					//the name of the option
	var $type;					//the type of option (see TYPE_ constants)
	var $default;				//default value for the option
	var $choices= array();		//if TYPE_DROPDOWN, holds the choices that are available

	//Different option types available
	const TYPE_TEXT = 0;		//textbox
	const TYPE_CHECKBOX = 1;	//checkbox
	const TYPE_DROPDOWN = 2;	//dropdown

	const tablename = "site_part_library_options";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function partOption($name = "", $type=null, $default="", $choices=null){
		$this->name = $name;
		if($type == null)
			$type = partOption::TYPE_TEXT;
		$this->type = $type;
		$this->default = $default;
		if($choices!=null) {
			if (!is_array($choices)) {
				$choices = explode(",",$choices);
			}
			$this->choices = $choices;
		}
		$this->tablename = partOption::tablename;
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
		$this->partDefinition = $row["partDefinition"];
		$this->name = $row["name"];
		$this->type = $row["type"];
		$this->default = $row["defaultValue"];
		$this->choices = explode("$|$", $row["choices"]);
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$default = sql::Escape($this->default);
		$choices = sql::Escape(implode("$|$",$this->choices));
		$sql->Query("UPDATE $this->tablename SET
					partDefinition = '$this->partDefinition',
					name = '$name',
					type = '$this->type',
					defaultValue = '$default',
					choices = '$choices'
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
		$default = sql::Escape($this->default);
		$choices = sql::Escape(implode("$|$",$this->choices));
		$sql->Query("INSERT INTO $this->tablename SET
					partDefinition = '$this->partDefinition',
					name = '$name',
					type = '$this->type',
					defaultValue = '$default',
					choices = '$choices'
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
	deleteDefinitionOptions()
	Deletes all part options that are related to a given
	part definition. Accepts a part definition id
	============================================================*/
	function deleteDefinitionOptions($partDefinitionId)
	{
		global $sql;
		$tablename = partOption::tablename;
		$sql->Query("DELETE FROM $tablename WHERE partDefinition='$partDefinitionId'");
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
		$tablename = partOption::tablename;
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
		if(!sql::TableExists(partOption::tablename)) {
			$tablename = partOption::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`partDefinition` INT NOT NULL,
						`name` VARCHAR (256) NOT NULL,
						`type` INT NOT NULL,
						`defaultValue` VARCHAR (256) NOT NULL,
						`choices` VARCHAR (512) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
partOption::setupTable();
?>
