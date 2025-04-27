<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class theme {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;
	var $directory;
	var $description;
	var $createdby;
	var $dateadded;
	var $dateaddedDate;
	var $dateaddedTime;
	var $numberOfBorders = -1; //Call getNumberOfBorders to populate. Acts as cache.
	var $tablename;
	const tablename = "site_themes";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = theme::tablename;
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
	Loads the information for a instance by looking up the themes
	system name (directory name);
	============================================================*/
	function loadFromDatabaseBySystemName($name){
		global $sql;
		$name = sql::Escape($name);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE directory='$name'");
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
		$this->directory = $row["directory"] ?? null;
		$this->description = $row["description"] ?? null;
		$this->createdby = $row["createdby"] ?? null;
		$this->dateadded = $row["dateadded"] ?? null;
		if($this->dateadded){
			$this->dateaddedDate = date("F j, Y", strtotime($row["dateadded"]));
			$this->dateaddedTime = date("g:i A", strtotime($row["dateadded"]));
		}
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$directory = sql::Escape($this->directory);
		$description = sql::Escape($this->description);
		$createdby = sql::Escape($this->createdby);
		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					directory = '$directory',
					description = '$description',
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
		$directory = sql::Escape($this->directory);
		$description = sql::Escape($this->description);
		$createdby = sql::Escape($this->createdby);
		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					directory = '$directory',
					description = '$description',
					createdby = '$createdby',
					dateadded = NOW()
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
	numberOfBorderTypes()
	Returns the number of border types available for this theme.
	Note: Border0 counts as a border, so if border 0-4 were present,
	this method would return 5.
	============================================================*/
	function numberOfBorderTypes(){
		//is cache available?
		if ( $this->numberOfBorders != -1 ) {
		    return $this->numberOfBorders;
		}

		//no cache, determine number of borders via file scan
		$dir = $this->getDirectory()."borders/";
		$counter = 0;
		while(fileutil::file_exists_incpath($dir."border".$counter.".tmpl.php")) {
			$counter++;
		}
		$counter--;

		//save result in cache (per page load)
		$this->numberOfBorders = $counter;
		return $counter;
	}
	/*===========================================================
	getCssFiles()
	Returns an array of css files that this theme has specified
	that it wishes to include. These paths are NOT friendly paths
	that can be passed and linked to the browser directly.
	============================================================*/
	function getCssFiles(){
		$toReturn = array();
		//css files are defined in the themes info file
		$file = $this->getDirectory()."info.php";
		if( !fileutil::file_exists_incpath($file) ) {
			return $toReturn;
		}
		include($file);

		if(!is_array($css))
			return $toReturn;

		foreach($css as $k => $file) {
			$toReturn[$k] = $this->getDirectory().$file;
		}

		return $toReturn;
	}

	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$tablename = theme::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if a theme exists with the given id
	============================================================*/
	static function idExists($id)
	{
		global $sql;
		$tablename = theme::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$id'"); //MODIFY THIS LINE
		return ($exists != "");
	}

	/*===========================================================
	getDirectory()
	Returns the directory of the theme.
	If passed true, it will return the absolute path. The absolute
	path is appropriate when linking to this directory for the user
	The non absolute path is used when including files from the
	theme directory
	============================================================*/
	function getDirectory($absolute = false){
		if($absolute)
			return $GLOBALS["SiteRoot"] . "themes/" . strtolower($this->directory)."/";
		return "site/themes/".strtolower($this->directory)."/";
	}
	/*===========================================================
	getAbsDirectory()
	Returns the absolute directory to the theme directory.
	Such as http://www.site.com/themes/etc.
	============================================================*/
	function getAbsDirectory(){
		return  $GLOBALS["SiteRoot"] . "themes/".strtolower($this->directory)."/";
	}

	/*===========================================================
	getCommonDirectory()
	Returns a path to the common theme directory. These is a directory
	that contains information that all themes share.
	If passed true, it will return the absolute path. The absolute
	path is appropriate when linking to this directory for the user
	The non absolute path is used when including files from the
	theme directory
	============================================================*/
	function getCommonDirectory($absolute = false){

		if($absolute)
			return $GLOBALS["SiteRoot"] . "themes/common/";
		return "site/themes/common/";
	}

	/*===========================================================
	getAbsCommonDirectory()
	Returns the absolute directory to the common theme directory.
	Such as http://www.site.com/themes/etc.
	============================================================*/
	function getAbsCommonDirectory(){
		return $GLOBALS["SiteRoot"] . "themes/common/";
	}


	/*===========================================================
	getThemeIdByDirectoryName()
	STATIC METHOD
	Returns the id of the theme with the given directory name
	============================================================*/
	function getThemeIdBySystemName($name){
		$theme = new theme();
		$theme->loadFromDatabaseBySystemName($name);
		return $theme->id;
	}
	/*===========================================================
	loadLayouts()
	Loads all layouts for the current theme. This will remove
	all current layouts from the layout table and replace
	them with the newly discovered ones.
	============================================================*/
	function loadLayouts(){
		//first, get rid of all the old layouts
		global $sql;
		$table = layout::tablename;
		$sql->Query("DELETE FROM $table");

		$themeDirectory = $this->getDirectory()."layouts/";
		$commonDirectory = $this->getCommonDirectory()."layouts/";

		//scan both the theme's layout directory and
		//the common layout directory
		$this->scanDirectoryForLayouts($themeDirectory,0);
		$this->scanDirectoryForLayouts($commonDirectory,1);
	}
	/*===========================================================
	scanDirectoryForLayouts()
	Scans a given directory for layouts. Any layouts that are
	discovered are saved into the layouts database.
	$directoryName = The name of the directory to scan
	$isCommon = if "1" discovered layouts are labeled as coming
				from the 'common' layouts directory. (ie, they
				are shared among all theme are appear in a
				directory not linked to the current theme alone)
	============================================================*/
	function scanDirectoryForLayouts($directoryName, $isCommon = 0)
	{
		//open the directory
		$directory = opendir($directoryName);
		$layouts = array();

		//iterate through the directory, finding all of the
		//layout files
		while($filename = readdir($directory)) {
			//make sure it isn't a directory or the special order file
			if( is_dir($directoryName.$filename) || $filename == "." || $filename==".." || $filename==".svn" || $filename=="order.php")
				continue;
			if (strpos($filename,".tmpl.php")==0) {
				continue;
			}
			$layouts[] = $filename;
		}
		//close directory
		closedir($directory);

		//order array will be an associative array
		//that maps the order of the layouts
		$order = array();

		//check to see if there is an order file present, it will
		//point out the order / precedence of some of the layouts
		//(though not necessarily all of them)
		if (file_exists($directoryName."order.php")) {
			include($directoryName."order.php");
			foreach($order as $key => $layout) {
				if ( strpos($layout,".tmpl.php") === false) {
				    $order[$key] = $layout;
				}
			}
		}

		//if any layouts weren't specified in the order file,
		//add them in their alphabetical order AFTER the ones
		//specified in the order file
		foreach($layouts as $key => $layout) {
			if (!in_array($layout,$order)) {
				$order[]=$layout;
			}
		}

		//save the layouts in the database
		foreach( $order as $number => $layoutfile ) {
			$number++;
			$name = str_replace(".tmpl.php","",$layoutfile);
			$layout = new layout();
			$layout->id = $number;
			$layout->name = $name;
			$layout->system = ($isCommon && strpos($layoutfile,"System")===0);
			$layout->common = $isCommon;
			$layout->filename = $name;
			$layout->saveNew();
		}
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable() {
		if(!sql::TableExists(theme::tablename)) {

			$tablename = theme::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR (256) NOT NULL,
						`directory` VARCHAR (256) NOT NULL,
						`description` VARCHAR (256) NOT NULL,
						`createdby` VARCHAR (256) NOT NULL,
						`dateadded` VARCHAR (256) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");

			$defaultTheme = new theme();
			$defaultTheme->createdby 	= "Scott Bailey";
			$defaultTheme->description	= "The default theme.";
			$defaultTheme->name 	 	= "Default";
			$defaultTheme->directory 	= "default";
			$defaultTheme->saveNew();
		}
	}
}
theme::setupTable(); 
?>