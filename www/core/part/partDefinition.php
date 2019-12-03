<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
include_once("core/part/partOption.php");
class partDefinition {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;
	var $systemName;
	var $description;
	var $directory;
	var $system = 0;
	var $createdby;
	var $options = array();
	const tablename = "site_part_library";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function partDefinition()
	{
		$this->tablename = partDefinition::tablename;
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
	using the passed name. This is the system name and NOT the
	user friendly name
	============================================================*/
	function loadFromDatabaseBySystemName($name){
		global $sql;
		$name = sql::Escape($name);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE systemName='$name'");
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
		$this->systemName = $row["systemName"];
		$this->description = $row["description"];
		$this->directory = $row["directory"];
		$this->system = $row["system"];
		$this->createdby = $row["createdby"];
		//need to update directory here?
	}

	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		if($this->id == "")
			$this->save();

		$name = sql::Escape($this->name);
		$systemName = sql::Escape($this->systemName);
		$description = sql::Escape($this->description);
		$directory = sql::Escape($this->directory);
		$createdby = sql::Escape($this->createdby);
		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					systemName = '$systemName',
					description = '$description',
					system = '$this->system',
					directory = '$directory',
					createdby = '$createdby'
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
		$systemName = sql::Escape($this->systemName);
		$description = sql::Escape($this->description);
		$directory = sql::Escape($this->directory);
		$createdby = sql::Escape($this->createdby);
		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					systemName = '$systemName',
					description = '$description',
					system = '$this->system',
					directory ='$directory',
					createdby ='$createdby'
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
		$tablename = partDefinition::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	getFilePath()
	Returns the path to the given part class instance. For example,
	if this part was a definition for a news part this might be
	parts/news/newspart.php
	============================================================*/
	function getFilePath(){
		$base.=$this->directory."part".ucfirst($this->systemName).".php";
		return $base;
	}
	/*===========================================================
	getBinDirectory()
	Returns an absolute path to a parts bin directory. This is a directory
	that is accessible from the site root and provides access
	to a parts images, files, cs, and js files.
	============================================================*/
	function getBinDirectory(){
		global $SiteRoot;


		$temp = $SiteRoot.$this->directory."bin/";
		return $temp;
	}

	/*===========================================================
	iconExists()
	Returns true if an icon exists for this part. This is the small
	icon that appears next to the part while in edit page mode
	============================================================*/
	function iconExists(){
		$path=$this->directory."bin/images/icon.gif";
		$ok = fileutil::file_exists_incpath($path);
		if(!$ok) {
			$path=$this->directory."bin/images/icon.png";
			$ok = fileutil::file_exists_incpath($path);
		}
		return ($ok);
	}
	/*===========================================================
	getIcon()
	Gets an absolute path to an image url
	============================================================*/
	function getIcon(){
		global $SiteRoot;
		if($this->iconExists()) {
			$path=$this->directory."bin/images/icon.gif";
			if (fileutil::file_exists_incpath($path))
				return $SiteRoot.$this->directory."bin/images/icon.gif";
			else
				return $SiteRoot.$this->directory."bin/images/icon.png";
		}
		else
			return theme::getAbsCommonDirectory()."images/editpage/icon.png";
	}

	/*===========================================================
	screenshotExists()
	Returns true if a screenshot is available for this part definition
	============================================================*/
	function screenshotExists(){
		//$path = $this->getScreenshot();
		//echo("checking for path $path ");
		$path=$this->directory."bin/images/screenshot/small.gif";
		return (fileutil::file_exists_incpath($path));
	}
	/*===========================================================
	getScreenshot()
	Returns a url to a screenshot image of the part
	============================================================*/
	function getScreenshot(){
		global $SiteRoot;
		$base=$SiteRoot.$this->directory."bin/images/screenshot/small.gif";
		return $base;
	}
	/*===========================================================
	getInstance()
	Returns a specific instance of a part defined by this part
	definition.
	If the part defined by this definition is invalid (file or
	class does not exist), null is returned.
	============================================================*/
	function getInstance(){
		$partClassFile = $this->getFilePath();
		if (!fileutil::file_exists_incpath($partClassFile))
			return null;

		include_once($partClassFile);

		$className = "part".ucfirst($this->systemName);

		if(!class_exists($className))
			return null;

		$part = new $className();

		return $part;
	}
	/*===========================================================
	createInstance()
	Creates an instance of the current definition and saves
	it to the database. Returns the newly created instance.
	============================================================*/
	function createInstance(){
		$newpart = $this->getInstance();
		if ($newpart == null) {
			return null;
		}

		//fill it with some default values
		$newpart->definition = $this;

		//set the default border and title, which may be overriden by the new part
		if (isset($newpart->defaultBorder))
			$newpart->border = $newpart->defaultBorder;
		else
			$newpart->border = 0;

		if( isset($newpart->defaultTitle))
			$newpart->title = $newpart->defaultTitle;
		else
			$newpart->title = $this->name;

		$newpart->loadDefaultOptions();
		$newpart->saveNew();
		return $newpart;
	}

	/*===========================================================
	loadOptions($row)
	Loads all available options associated with this part definition
	into the $this->options data variable
	============================================================*/
	function loadOptions(){
		global $sql;
		$table = partOption::tablename;
		$result = $sql->Query("SELECT * FROM $table WHERE partDefinition='$this->id'");
		while($row = mysql_fetch_array($result)){
			$option = new partOption();
			$option->loadFromRow($row);
			$this->options[$option->name] = $option;
		}
	}

	/*===========================================================
	addOption()
	Adds a new option that is available to be set for this part
	defintion. For example, a weather part might allow
	options to be set for a location and whether a user wants to have
	temperature in C or F. The passed parameter MUST be of type
	partOption

	this part definition MUST have an id before calling this
	method.
	============================================================*/
	function addOption($option){
		if(!is_a($option,"partOption"))
			return;
		$option->partDefinition = $this->id;
		$option->saveNew();
	}
	/*===========================================================
	addOptions()
	Adds a series of options to the part definition.
	============================================================*/
	function addOptions($options){
		if (is_array($options)) {
			foreach($options as $option){
				$this->addOption($option);
			}
		}
	}

	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	function setupTable()
	{
		if(!sql::TableExists(partDefinition::tablename)) {
			$tablename = partDefinition::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR (256) NOT NULL,
						`systemName` VARCHAR (256) NOT NULL,
						`description` VARCHAR (256) NOT NULL,
						`system` INT NOT NULL,
						`directory` VARCHAR (256) NOT NULL,
						`createdby` VARCHAR (256) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
partDefinition::setupTable();


?>