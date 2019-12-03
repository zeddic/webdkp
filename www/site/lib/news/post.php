<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
include_once("core/general/image.php");
include_once("core/general/file.php");
include_once("tag.php");
class post {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $title;
	var $content;
	var $status;
	var $createdDate;
	var $createdTime;
	var $images= array();
	var $files= array();
	var $address;
	var $imagesInGallery = array();		//an array of image IDS that designate images
										//from the images array that should be shown in
										//a gallary for this news post. Not all images will
										//be in this array, as some may be embedded in the
										//text of the post directly.
	var $tags = array();
	var $createdby;
	const tablename = "content_news";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function post()
	{
		$this->tablename = post::tablename;
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
	function loadFromRow($row, $loadFiles = true)
	{
		$this->id=$row["id"];
		$this->title = $row["title"];
		$this->content = $row["content"];
		$this->status = $row["status"];
		$this->address = $row["address"];
		if($row["created"]!="")
		{
			$this->created = $row["created"];
			$this->createdDate = date("F j, Y", strtotime($row["created"]));
			$this->createdTime = date("g:i A", strtotime($row["created"]));
			$this->createdDay = date("j",strtotime($row["created"]));
			$this->createdMonth = date("M",strtotime($row["created"]));
			$this->createdYear = date("y",strtotime($row["created"]));
		}
		$this->images = explode(",", $row["images"]);
		$this->files = explode(",", $row["files"]);
		$temp = explode(",", $row["imagesingallery"]);
		foreach($temp as $tempItem)
			if($tempItem != "" && in_array($tempItem,$this->images))
				$this->imagesInGallery[]=$tempItem;

		$this->createdby = $row["createdby"];

		if($loadFiles) {
			$this->loadImages();

			$fileids = $this->files;
			$this->files = array();
			foreach($fileids as $fileid) {
				if($fileid != "") {
					$file = new file();
					$file->loadFromDatabase($fileid);
					$this->files[]=$file;
				}
			}
		}

		//load tags
		//get the tablenames that we are working with
		$maptable = tag::maptablename;	//relation table
		$tablename = tag::tablename;	//tag table
		//get all tags related to this post in one query.
		//The tags to post relation is in a seperate table
		global $sql;
		$result = $sql->Query("SELECT $tablename.id, $tablename.name
							   FROM $tablename, $maptable
							   WHERE $maptable.post = '$this->id'
							   AND $maptable.tag = $tablename.id");
		//load all the tags into a datastructure
		while($row = mysqli_fetch_array($result)) {
			$tag = new tag();
			$tag->loadFromRow($row);
			if($tag->name != "")
				$this->tags[] = $tag;
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
		$content = sql::Escape($this->content);
		$status = sql::Escape($this->status);
		$images = sql::Escape(implode(",",$this->getImageIds()));
		$files = sql::Escape(implode(",",$this->getFileIds()));
		$imagesInGallery = sql::Escape(implode(",",$this->imagesInGallery));
		$address = sql::Escape($this->address);
		$sql->Query("UPDATE $this->tablename SET
					title = '$title',
					content = '$content',
					status = '$status',
					images = '$images',
					files = '$files',
					createdby = '$this->createdby',
					imagesingallery = '$imagesInGallery',
					address = '$address'
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
		$content = sql::Escape($this->content);
		$status = sql::Escape($this->status);
		$images = sql::Escape(implode(",",$this->getImageIds()));
		$files = sql::Escape(implode(",",$this->getFileIds()));
		$imagesInGallery = sql::Escape(implode(",",$this->imagesInGallery));
		$address = sql::Escape($this->address);
		$sql->Query("INSERT INTO $this->tablename SET
					title = '$title',
					content = '$content',
					status = '$status',
					images = '$images',
					files = '$files',
					createdby = '$this->createdby',
					imagesingallery = '$imagesInGallery',
					address = '$address'
					");
		$this->id=$sql->GetLastId();
	}
	/*===========================================================
	timestamp()
	Applies a timestamp to the post.
	============================================================*/
	function timestamp() {
		global $sql;
		$table = $this->tablename;
		$sql->Query("UPDATE $table SET created=NOW() WHERE id='$this->id'");
	}

	/*===========================================================
	loadImages()
	Loads images for the current post. This translates the image
	ids to actual image class instances
	============================================================*/
	function loadImages(){
		$imageids = $this->images;
		$this->images = array();
		foreach($imageids as $imageid) {
			if($imageid != "" && !is_a($imageid,"image")) {
				$image = new image();
				$image->loadFromDatabase($imageid);
				if(in_array($imageid,$this->imagesInGallery))
					$image->inGallery = true;
				else
					$image->inGallery = false;
				$this->images[]=$image;
			}
		}
	}


	/*===========================================================
	Returns an array of the ids of all images in this post.
	============================================================*/
	function getImageIds(){
		//this->images may be composed of a mixture of
		//ids and image instances
		$images = array();
		foreach($this->images as $image) {
			if(is_a($image,"image"))
				$images[]=$image->id;
			else
				if($image != "")
					$images[]=$image;
		}
		return $images;
	}

	/*===========================================================
	Returns an array of the ids of all files in this post.
	============================================================*/
	function getFileIds(){
		//this->files may be composed of a mixture of
		//ids and files instances
		$files = array();
		foreach($this->files as $file) {
			if(is_a($file,"file"))
				$files[]=$file->id;
			else
				if($file != "")
					$files[]=$file;
		}
		return $files;
	}

	/*===========================================================
	Returns all tags for this post represented as a tag string.
	Example: "Work, fun, code"
	============================================================*/
	function getTagString(){
		$names = array();
		foreach($this->tags as $tag)
			$names[] = $tag->name;
		return implode(", ",$names);
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

		//now run though all of its files and images and make sure that they
		//are deleted as well.
		foreach($this->images as $image) {
			if(is_a($image,"image"))
				$image->delete();
		}
		foreach($this->files as $file ){
			if(is_a($file,"file"))
				$file->delete();
		}
	}
	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if the given entry exists in the database
	database
	============================================================*/
	function existsId($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = post::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}

	/*===========================================================
	removeImage()
	Removes the given image from this post. Does NOT save changes.
	You must call save() aftewards to make sure the changes
	are refelected in the database.
	============================================================*/
	function removeImage($imageid){
		foreach($this->images as $key => $image) {
			if($image->id == $imageid) {
				$image->delete();
				unset($this->images[$key]);
				return 1;
			}
		}
		return 0;
	}

	/*===========================================================
	removeFile()
	Removes the given file from this post. Does NOT save changes.
	You must call save() aftewards to make sure the changes
	are refelected in the database.
	============================================================*/
	function removeFile($fileid){
		foreach($this->files as $key => $file) {
			if($file->id == $fileid) {
				$file->delete();
				unset($this->files[$key]);
				return 1;
			}
		}
		return 0;
	}

	/*===========================================================
	Clear all tags
	============================================================*/
	function clearTags(){
		global $sql;
		$table = tag::maptablename;
		$sql->Query("DELETE FROM $table WHERE post = '$this->id'");
	}

	/*===========================================================
	Adds a series of tags to this post. Accepts tags as a compound
	string, such as "hello, world, soctt's posts"
	============================================================*/
	function addTags($tagstring){
		//split the tags
		$tags = explode(",",$tagstring);
		$this->addTagsArray($tags);
	}

	/*===========================================================
	Adds a series of tags to this post. Accepts tags as an array
	of strings such as "hello", "world, "scott's posts"
	============================================================*/
	function addTagsArray($tags){

		if(!is_array($tags))
			return;

		global $sql;

		$table = tag::maptablename;

		//iterate through each tag string
		foreach($tags as $tagstring){
			//trim the tag string to remove excess white space
			$tagstring = trim($tagstring);

			//skip empty strings
			if($tagstring == "")
				continue;

			//get the id of the tag in the database
			$tag = new tag();
			$tag->loadFromDatabaseByName($tagstring);
			//if the tag is new, get it an id
			if($tag->id == "") {
				$tag->name = $tagstring;
				$tag->saveNew();
			}

			//make sure there isn't already a tag-->post map like this
			$exists = $sql->QueryItem("SELECT id FROM $table WHERE post='$this->id' AND tag='$tag->id'");
			if($exists != "")
				continue;

			//add the new tag to tag-->post map database
			$sql->Query("INSERT INTO $table SET post='$this->id', tag='$tag->id'");
		}
	}

	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	function setupTable()
	{
		if(!sql::TableExists(post::tablename)) {
			$tablename = post::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`title` VARCHAR (512) NOT NULL,
						`content` TEXT  NOT NULL,
						`status` TINYINT (1) NOT NULL,
						`created` DATETIME NOT NULL,
						`images` VARCHAR (512) NOT NULL,
						`imagesingallery` VARCHAR (512) NOT NULL,
						`files` VARCHAR (512) NOT NULL,
						`address` VARCHAR (512) NOT NULL,
						`createdby` INT NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
post::setupTable()
?>