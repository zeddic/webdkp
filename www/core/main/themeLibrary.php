<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
The theme library's main purpose is to scan for newly placed
themes and install them into the system. It also has the ability
to load a list of currently available themes.
*/
include_once("theme.php");
class themeLibrary {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/

	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{

	}

	/*===========================================================
	STATIC
	Returns a list of all themes currently available. This is an array
	of class theeme.
	============================================================*/
	function getThemes(){
		global $sql;
		$toReturn = array();
		$table = theme::tablename;
		$result = $sql->Query("SELECT * FROM $table ORDER BY name ASC");
		while($row = mysqli_fetch_array($result)){
			$theme = new theme();
			$theme->loadFromRow($row);
			$toReturn[] = $theme;
		}
		return $toReturn;
	}

	/*===========================================================
	Scans the theme directory for any new themes. Newly discovered
	themes are installed and placed as a new entry in the themes
	database.

	If any error occurs during the scan process, the user can check
	the global variable $themeScanErrors which will contain an array
	of $themeScanError instances.
	============================================================*/
	function scanForThemes($directoryName = "")
	{
		if ($directoryName == "") {
			$directoryName = "site/themes/";
		}
		global $themeScanErrors;
		$themeScanErrors = array();
		$directory = opendir($directoryName);
		$discoveredThemes = array();

		while($filename = readdir($directory)) {
			//if its not a module directory, skip to the next one
			if(!is_dir($directoryName.$filename) || $filename == "." || $filename==".." || $filename=="common" || $filename==".svn")
				continue;

			$discoveredThemes[] = $filename;

			//we found a module directory, before doing anything, check to see
			//if we already know about it. If we do, we can skip it.
			if(themeLibrary::themeKnown($filename))
				continue;

			//make sure the module file exists
			$infoFileName = $directoryName.$filename."/info.php";
			if(!fileutil::file_exists_incpath($infoFileName)){
				$themeScanErrors[]=new themeScanError($filename,themeScanError::ERR_THEME_FILE_NOT_FOUND);
				continue;
			}


			//unset values that previous info file may have set set
			unset($name, $description, $createdBy);

			//load info file
			$ok = include($infoFileName);
			if(!$ok) {
				$themeScanErrors[]=new themeScanError($filename,themeScanError::ERR_THEME_PARSE_ERROR,$filename);
				continue;
			}

			//at this point theme data has been loaded and set in local variables
			$theme = new theme();
			$theme->name = $name;
			$theme->description = $description;
			$theme->createdby = $createdby;
			$theme->directory = $filename;
			$theme->saveNew();
		}
		closedir($directory);

		themeLibrary::removeDeletedThemes($discoveredThemes);

	}
	/*===========================================================
	Returns true if the given theme already exists. Accepts
	a theme system name. This is the name of the directory
	that the theme resides in.
	============================================================*/
	function themeKnown($themeName){
		return (theme::getThemeIdBySystemName($themeName)!="");
	}

	/*===========================================================
	Removes any themes from the database that no longer exist.
	Must be passed an array of theme system (directory) names
	of those themes that still do exist.
	============================================================*/
	function removeDeletedThemes($discoveredThemeNames){
		global $sql;
		if(count($discoveredThemeNames) == 0 ) {
			return;
		}
		//iterate through all the discovered theme names and prepare
		//a where clause
		for($i = 0 ; $i < count($discoveredThemeNames) ; $i++ ){
			$temp = $discoveredThemeNames[$i];
			$temp = "directory != '$temp'";
			$discoveredThemeNames[$i] = $temp;
		}
		$clause = implode(" AND ",$discoveredThemeNames);
		$table = theme::tablename;

		//delete all themes that are not in the list of discovered themes
		$sql->Query("DELETE FROM $table WHERE $clause");
	}

	/*===========================================================
	Returns an array of themeScanError instances that holds information
	on any themes that had trouble being loaded. Must be called after
	a themeLibrary::scanForThemes() call.
	============================================================*/
	function getScanErrors(){
		global $themeScanErrors;
		return $themeScanErrors;
	}
}

/*===========================================================
Helper data structure. Contains the data for a single error
that was encountered while scanning for new themes
============================================================*/
class themeScanError {
	var $themename;
	var $error;

	const ERR_UNKNOWN = 0;
	const ERR_THEME_FILE_NOT_FOUND = 1;
	const ERR_THEME_CLASS_NOT_FOUND = 2;
	const ERR_THEME_PARSE_ERROR = 3;
	function themeScanError($themename, $error = 0){
		$this->themename = $themename;
		$this->error = $error;
	}
}

?>