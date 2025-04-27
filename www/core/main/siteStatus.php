<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

include_once("theme.php");
include_once("themeMap.php");


class siteStatus {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $defaultTheme;
	var $theme;
	var $setup;
	var $tablename;
	const tablename = "site_status";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = siteStatus::tablename;
	}
	/*===========================================================
	load()
	Loads the current site status
	============================================================*/
	function load($url = "-1"){
		$this->loadFromDatabase(1,$url);
	}

	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabase($id,$url)
	{
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'");
		$this->loadFromRow($row, $url);
	}

	/*===========================================================
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row, $url)
	{
		$mustResave = false;
		$this->id=$row["id"] ?? null;
		$themeid = $row["theme"] ?? null;

		if($themeid == 0) {
			$themeid = theme::getThemeIdBySystemName("default");
			$mustResave = true;
		}

		//load the default theme
		$this->defaultTheme = new theme();
		$this->defaultTheme->loadFromDatabase($themeid);

		//load the theme for this page now (may end up being the default theme)
		$this->loadTheme($url);

		$this->setup = $row["setup"] ?? null;

		if($mustResave) {
			$this->save();
		}
	}

	/*===========================================================
	loadTheme($url)
	Loads the theme for the current page. Note that the theme may vary
	from page to page, based on the pages path. Themes may be
	defined for different site paths, such as "ControlPanel\", resulting
	in all pages in that path getting a different theme. If no
	special theme is discovered for a given path, the default theme is assumed.
	============================================================*/
	function loadTheme($url){
		//if($url != "-1")
		$this->theme = themeMap::getThemeForPath($url);

		if(empty($this->theme)) {
			$this->theme = $this->defaultTheme;
		}
	}

	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		if(is_a($this->defaultTheme,"theme"))
			$defaultTheme = $this->defaultTheme->id;
		else
			$defaultTheme = $this->defaultTheme;

		$sql->Query("UPDATE $this->tablename SET
					theme = '$defaultTheme',
					setup ='$this->setup'
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
		if(is_a($this->defaultTheme,"theme"))
			$defaultTheme = $this->defaultTheme->id;
		else
			$defaultTheme = $this->defaultTheme;

		$sql->Query("INSERT INTO $this->tablename SET
					theme = '$defaultTheme',
					setup ='$this->setup'
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
	static function exists($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = siteStatus::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(siteStatus::tablename)) {
			$tablename = siteStatus::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`theme` INT NOT NULL,
						`setup` INT NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
			//create the intial site status
			$status = new siteStatus();
			$status->theme = theme::getThemeIdBySystemName("default");
			$status->setup = 0;
			$status->saveNew();
		}
	}
}
siteStatus::setupTable();
?>