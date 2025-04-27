<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================

*/

class dkpRemoteStyle {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;
	var $description;
	var $createdby;
	var $file;
	var $tablename;
	const tablename = "dkp_remote_style";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpRemoteStyle::tablename;
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
		$this->id=$row["id"] ?? null;
		$this->name = $row["name"] ?? null;
		$this->description = $row["description"] ?? null;
		$this->createdby = $row["createdby"] ?? null;
		$this->file = $row["file"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$description = sql::Escape($this->description);
		$createdby = sql::Escape($this->createdby);
		$file = sql::Escape($this->file);
		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					description = '$description',
					createdby = '$createdby',
					file = '$file'
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
		$description = sql::Escape($this->description);
		$createdby = sql::Escape($this->createdby);
		$file = sql::Escape($this->file);
		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					description = '$description',
					createdby = '$createdby',
					file = '$file'
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
	static function exists($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = dkpRemoteStyle::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'");
		return ($exists != "");
	}

	/*===========================================================
	Gets an array of all available styles
	============================================================*/
	static function getStyles(){
		global $sql;

		$table = dkpRemoteStyle::tablename;
		$result = $sql->Query("SELECT * FROM $table ORDER BY name ASC");
		$styles = array();
		while($row = mysqli_fetch_array($result)) {
			$style = new dkpRemoteStyle();
			$style->loadFromRow($row);
			$styles[] = $style;
		}
		return $styles;
	}

	/*===========================================================
	Loads the styles for the site that are in the styles info file.
	Any new items in the file are saved to the database automatically.
	============================================================*/
	static function loadStyles(){
		$styles = array();
		include_once("control/dkp/bin/styles.php");
		foreach($styles as $style) {
			if(!dkpRemoteStyle::exists($style["name"])) {
				$temp = new dkpRemoteStyle();
				$temp->name = $style["name"];
				$temp->description = $style["description"];
				$temp->createdby = $style["createdby"];
				$temp->file = $style["file"];

				$temp->saveNew();
			}
		}
	}
	/*===========================================================
	Gets the content of the currently active style
	============================================================*/
	function getContent(){
		$template = new template("site/control/dkp/bin/templates/remote/styles/".$this->file.".css");
		$content = $template->fetch();
		return $content;
	}

	/*===========================================================
	Returns a url to a screenshot for this style
	============================================================*/
	function getScreenshot(){
		global $siteRoot;

		$temp = "webroot/images/remote/styles/".$this->file.".jpg";

		if(!fileutil::file_exists_incpath($temp)) {
			return $siteRoot."images/remote/styles/default.jpg";
		}
		else {
			return $siteRoot."images/remote/styles/".$this->file.".jpg";
		}
	}

	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpRemoteStyle::tablename)) {
			$tablename = dkpRemoteStyle::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR (256) NOT NULL,
						`description` VARCHAR (512) NOT NULL,
						`createdby` VARCHAR (256) NOT NULL,
						`file` VARCHAR (256) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
dkpRemoteStyle::setupTable();
dkpRemoteStyle::loadStyles();
?>