<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class layout {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;			//database id
	var $name;			//friendly name of the layout
	var $description;	//
	var $filename;		//physical file name (not including path). Example: "EditAlone.tmpl.php"
	var $common = 0;	//Common layouts are layouts that are shared among all styles. Located
						//in the common layouts director
	var $system = 0;	//System layouts are layouts reserved for the control panel
						//pages.
	const tablename = "site_layouts";
	var $tablename;
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = layout::tablename;
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
	loadFromDatabaseByName($id)
	Loads the information for the layout using the given name
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
		//$this->description = $row["description"] ?? null;
		$this->filename = $row["filename"] ?? null;
		$this->system = $row["system"] ?? null;
		$this->common = $row["common"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$filename = sql::Escape($this->filename);
		$system = sql::Escape($this->system);
		$common = sql::Escape($this->common);
		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					filename = '$filename',
					system = '$system',
					common = '$common'
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
		$filename = sql::Escape($this->filename);
		$system = sql::Escape($this->system);
		$common = sql::Escape($this->common);
		$id = sql::Escape($this->id);
		$idClause = "";
		if (!empty($id)) {
			$idClause = ",id = '$id'";
		}
		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					filename = '$filename',
					system = '$system',
					common = '$common'
					$idClause
					");

		if (empty($id)) {
			$this->id=$sql->GetLastId();
		}
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
	static function exists($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = layout::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}

	/*===========================================================
	Returns the id of a layout with the given name. Returns "" if
	it doens't exist.
	============================================================*/
	static function getLayoutIdByName($name){
		$layout = new layout();
		$layout->loadFromDatabaseByName($name);
		return $layout->id;
	}

	/*===========================================================
	Returns a path to a sample image for this layout.
	============================================================*/
	function getLayoutSample()
	{
		global $theme;

		$directory = $this->getDirectory();
		$file = $directory."samples/".$this->filename.".gif";
		if(fileutil::file_exists_incpath($file)) {
			return $GLOBALS["SiteRoot"].$file;
		}

		return theme::getAbsCommonDirectory()."layouts/samples/None.gif";
	}

	/*===========================================================
	getDirectory()
	Returns the directory of the given layout. If this is a common layout,
	it will return the common layout directory, if it is a theme layout,
	it will return a theme layout directory. Note that common layouts can
	be overriden, and if a theme layout exists with the same name,
	its directory will be returned instead.
	============================================================*/
	function getDirectory(){
		global $theme;
		//first check to see if the file exists in a common path
		if($this->existsInThemeDirectory()){
			return $theme->getDirectory()."layouts/";
		}
		if($this->common ==1 || $this->system == 1) {
			return theme::getCommonDirectory()."layouts/";
		}
		else {
			return $theme->getDirectory()."layouts/";
		}
	}

	/*===========================================================
	existsInThemeDirectory()
	Returns true if layout information for this layout is in the
	theme dependent folder. If it is, it could override any layout
	stored in the common directory
	============================================================*/
	function existsInThemeDirectory(){
		global $theme;
		$file = $theme->getDirectory()."layouts/".$this->filename.".tmpl.php";
		return (fileutil::file_exists_incpath($file));
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(layout::tablename)) {
			$tablename = layout::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR (256) NOT NULL,
						`filename` VARCHAR (256) NOT NULL,
						`common` TINYINT (1) NOT NULL,
						`system` TINYINT (1) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
layout::setupTable()
?>