<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class file {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $title;
	var $originalname;
	var $context;
	var $path;
	var $uploaddateDate;
	var $uploaddateTime;
	const tablename = "site_files";
	var $tablename;
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = file::tablename;
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
		$this->title = $row["title"] ?? null;
		$this->originalname = $row["originalname"] ?? null;
		$this->context = $row["context"] ?? null;
		$this->path = $row["path"] ?? null;
		if($row["uploaddate"]!="")
		{
			$this->uploaddateDate = date("F j, Y", strtotime($row["uploaddate"]));
			$this->uploaddateTime = date("g:i A", strtotime($row["uploaddate"]));
		}
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$title = sql::Escape($this->title);
		$originalname = sql::Escape($this->originalname);
		$context = sql::Escape($this->context);
		$path = sql::Escape($this->path);
		$sql->Query("UPDATE $this->tablename SET
					title = '$title',
					originalname = '$originalname',
					context = '$context',
					path = '$path',
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
		$originalname = sql::Escape($this->originalname);
		$context = sql::Escape($this->context);
		$path = sql::Escape($this->path);
		$sql->Query("INSERT INTO $this->tablename SET
					title = '$title',
					originalname = '$originalname',
					context = '$context',
					path = '$path',
					uploaddate = 'NOW()'
					");
		$this->id=$sql->GetLastId();
	}
	/*===========================================================
	getExt()
	Returns the extension / file type of the file. Is always lower
	case. Does NOT include the "."
	============================================================*/
	function getExt(){
		if(empty($this->path))
			return "";
		return fileutil::getExt($this->path);
	}

	/*===========================================================
	getExtType()
	Returns a string that represents the type of document that the
	extension represents. Used to map extensions to icons.
	============================================================*/
	function getExtType($ext = ""){
		if(empty($ext))
			$ext = $this->getExt();

		return fileutil::getExtType($ext);
	}

	/*===========================================================
	Returns a complete url to the file.
	============================================================*/
	function getPath(){
		return $GLOBALS["SiteRoot"]. $this->path;
	}

	/*===========================================================
	uploadImage()
	Uploads an image. This method can be called after an image has
	been uploaded / posted to a page. It will copy the image from the
	temporary upload directory to the given upload directory and
	store the images new path in this instances $path variable

	Accepts parameters:
	$uploadPath - The directory to place the uploaded file in. Thisis
				  relative to the working directory, which defaults to the
				  site directory in the framework
				  Example:
				  control\News\bin\images\posts\2\
				  Folder will be created as needed
	$formName -   Optional. The name of the form / input element
				  that submitted the image upload. Defaults to "Filedata"
				  but will need to be changed based on what the form
				  called the browse element in html.

	Returns true if upload succedded. False if failure.
	============================================================*/
	function uploadFile($uploadPath, $formName = "Filedata"){

		$this->originalname = $_FILES[$formName]['name'];

		//make sure the upload path is formatted
		$uploadPath = fileutil::stripExt($uploadPath);
		if($uploadPath != "") {
			if($uploadPath[(strlen($uploadPath)-1)]!="/")
				$uploadPath .= "/";
			//make sure the upload path exists
			fileutil::mkdir($uploadPath);
		}

		$source = $_FILES[$formName]['tmp_name'];
		$dest = $uploadPath. $_FILES[$formName]['name'];

		//if the file already exists, append a number at the end
		//to make it unique
		$ext = fileutil::getExt($dest);
		$originalDest = $dest;  //kept track of to generate new file names
		while(file_exists($dest)) {
			$tempCount++;
			//come up with a new name by incremented a counter at the end
			$dest = fileutil::stripExt($originalDest)."_".$tempCount.".".$ext;
		}

		$ok = move_uploaded_file($source,$dest);
		if($ok) {
			$this->path = $dest;
		}
		return $ok;
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
		if($this->path) {
			@unlink($this->path);
		}
	}

	/*===========================================================
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = file::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}
	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(file::tablename)) {
			$tablename = file::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`title` VARCHAR (256) NOT NULL,
						`originalname` VARCHAR (256) NOT NULL,
						`context` VARCHAR (256) NOT NULL,
						`path` VARCHAR (256) NOT NULL,
						`uploaddate` DATETIME NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
file::setupTable()
?>