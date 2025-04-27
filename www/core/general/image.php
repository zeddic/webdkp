<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================

*/

class image {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;				//The id of the image in the database
	var $title;				//The title of the image
	var $comment;			//A comment for the image
	var $originalname;		//The original file name that the file was uploaded with
	var $context;			//a context / free variable for programs to use. Some might fill this
							//in with a given string to group similar images. Example: NewPosts/Post5/
							//Internally it has no use
	var $path;				//path to the full / original image
	var $small;				//file path to the image in various sizes. NOT appropraite to be used as links
	var $medium;			// is available for file processing. If you wish to get url, use the methods getLarge,getSmall,getPath, etc.
	var $large;
	var $square;
	var $thumbnail;			//path to a thumbnail
	var $uploaddateDate;
	var $uploaddateTime;

	//width and height are only loaded after loadDetails() is called
	var $width;
	var $height;
	const tablename = "site_images";
	var $tablename;
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = image::tablename;
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
		$this->thumbnail = $row["thumbnail"] ?? null;
		$this->square = $row["square"] ?? null;
		$this->small = $row["small"] ?? null;
		$this->medium = $row["medium"] ?? null;
		$this->large = $row["large"] ?? null;
		$this->comment = $row["comment"] ?? null;
		if(($row["uploaddate"] ?? null) !="")
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
		$thumbnail = sql::Escape($this->thumbnail);
		$square = sql::Escape($this->square);
		$small = sql::Escape($this->small);
		$medium = sql::Escape($this->medium);
		$large = sql::Escape($this->large);
		$comment = sql::Escape($this->comment);

		$sql->Query("UPDATE $this->tablename SET
					title = '$title',
					originalname = '$originalname',
					context = '$context',
					path = '$path',
					thumbnail = '$thumbnail',
					square = '$square',
					small = '$small',
					medium = '$medium',
					large = '$large',
					comment = '$comment'
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
		$thumbnail = sql::Escape($this->thumbnail);
		$square = sql::Escape($this->square);
		$small = sql::Escape($this->small);
		$medium = sql::Escape($this->medium);
		$large = sql::Escape($this->large);
		$comment = sql::Escape($this->comment);

		$sql->Query("INSERT INTO $this->tablename SET
					title = '$title',
					originalname = '$originalname',
					context = '$context',
					path = '$path',
					thumbnail = '$thumbnail',
					square = '$square',
					small = '$small',
					medium = '$medium',
					large = '$large',
					comment = '$comment',
					uploaddate = NOW()
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

		if($this->path)
			@unlink($this->path);
		if($this->thumbnail)
			@unlink($this->thumbnail);
		if($this->square)
			@unlink($this->square);
		if($this->small)
			@unlink($this->small);
		if($this->medium)
			@unlink($this->medium);
		if($this->large)
			@unlink($this->large);

	}
	/*===========================================================
	existsId()
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function existsId($id)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = image::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'");
		return ($exists != "");
	}
	/*===========================================================
	Returns a complete url to the thumbnail.
	============================================================*/
	function getThumbnail(){
		return $GLOBALS["SiteRoot"]. $this->thumbnail;
	}

	/*===========================================================
	Returns url to the square image
	============================================================*/
	function getSquare(){
		return $GLOBALS["SiteRoot"]. $this->square;
	}
	/*===========================================================
	Returns url to the small image
	============================================================*/
	function getSmall(){
		return $GLOBALS["SiteRoot"]. $this->small;
	}

	/*===========================================================
	Returns url to the medium image
	============================================================*/
	function getMedium(){
		return $GLOBALS["SiteRoot"]. $this->medium;
	}
	/*===========================================================
	Returns url to the large image
	============================================================*/
	function getLarge(){
		return $GLOBALS["SiteRoot"]. $this->large;
	}

	/*===========================================================
	Returns a complete url to the image.
	============================================================*/
	function getOriginal(){
		return $GLOBALS["SiteRoot"]. $this->path;
	}
	/*===========================================================
	Given a letter / size string this will return the path to the
	appropriatly sized images. Example, passing "l" for large "m"
	for medium, etc.
	============================================================*/
	function getSizePath($size = ""){
		if($size == "")
			$size = "o";
		if($size == "l")
			$path = $this->getLarge();
		else if($size == "o")
			$path = $this->getOriginal();
		else if($size == "m")
			$path = $this->getMedium();
		else if($size == "s")
			$path = $this->getSmall();
		else if($size == "sq")
			$path = $this->getSquare();
		else if($size == "thumbnail")
			$path = $this->getThumbnail();
		else if($size == "t")
			$path = $this->getThumbnail();
		else
			$path = $this->getOriginal();
		return $path;
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
	function uploadImage($uploadPath, $formName = "Filedata"){

		$this->originalname = $_FILES[$formName]['name'];

		//make sure the upload path is formatted
		$uploadPath = fileutil::stripExt($uploadPath);
		if($uploadPath != "") {
			if($uploadPath[(strlen($uploadPath)-1)]!="/")
				$uploadPath .= "/";
			//make sure the upload path exists
			//mkdir($uploadPath,0777,true);
			@fileutil::mkdir($uploadPath);
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
	Creates a series of resized images, all of different sizes
	============================================================*/
	function createResizedImages(){
		$this->createLarge();
		$this->createMedium();
		$this->createSmall();
		$this->createThumbnail();
		$this->createSquare();
	}

	/*===========================================================
	Create a large resized image
	============================================================*/
	function createLarge($size = 800){
		$path = $this->resizeImage($size,"l");
		if($path)
			$this->large = $path;
	}

	/*===========================================================
	Create a medium resized image
	============================================================*/
	function createMedium($size = 500){
		$path = $this->resizeImage($size,"m");
		if($path)
			$this->medium = $path;
	}

	/*===========================================================
	Create a small resized image
	============================================================*/
	function createSmall($size = 240){
		$path = $this->resizeImage($size,"s");
		if($path)
			$this->small = $path;
	}

	/*===========================================================
	Create a thumbnail resized image
	============================================================*/
	function createThumbnail($size = 100){
		$path = $this->resizeImage($size,"t");
		if($path)
			$this->thumbnail = $path;
	}

	/*===========================================================
	Create a square resized image
	============================================================*/
	function createSquare($size = 75){
		$path = $this->resizeImageStrict($size,$size,"sq");
		if($path)
			$this->square = $path;
	}

	/*===========================================================
	resizeImage()
	Creates a resized image, keeping proportions. The width of the
	new image is only required. The new height will be determined
	automatically.

	Returns image path on success.
	Returns false on failure or if the new desired with is greater
	than the source width.

	Accepts parameters:
	$forceResize 	If true, the image will be resized to the
					desination size even if it is larger than
					the source image.

	Returns true if upload succedded. False if failure.
	============================================================*/
	function resizeImage($width = 100, $append = "t", $forceResize = false, $newPath = ""){

		$path = $this->getSmallestPath($width);

		//make sure the source image exists
		if(empty($path))
			return false;
		if(!fileutil::file_exists_incpath($path))
			return false;

		//create a source image object to work with
		$ext = fileutil::getExt($this->path);
		if ( preg_match("/jpg|jpeg/", $ext))
			$src_img=imagecreatefromjpeg($this->path);
		if ( preg_match("/png/", $ext))
			$src_img=imagecreatefrompng($this->path);

		//determine what the size of the image should be
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);

		//if we are trying to enlarge a smaller image, return the largest image
		//we already have instead
		if($old_x < $width && !$forceResize)
			return $this->path;

		//resize the image based on the new width
		$thumb_w = $width;
		$thumb_h = ($width * $old_y) / $old_x;


		//create the temporary thumbnail performing a resize (keeping proportions)
		$temp_img=ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecopyresampled(  $temp_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y );

		//determine where to save the file
		if($newPath == "") {
			$newPath = $this->trimAppended($path);
		}
		if($append!="")
			$newPath = fileutil::stripExt($newPath)."_$append".".".$ext;

		$filename = $newPath;

		if (preg_match("/png/",$ext) )
			$ok = imagepng($temp_img,$filename);
		else
			$ok = imagejpeg($temp_img,$filename);

		//clean up resources
		imagedestroy($temp_img);
		imagedestroy($src_img);

		if ($ok)
			return $filename;
		return false;
	}

	/*===========================================================
	resizeImage()
	Creates a resized image, strictly of the given size.

	This will: resize the image as close as it can without going
	under the given width / height. It will then stamp / cut out
	the given width /height out of the resized image, resulting
	in the final image that is returned.

	Returns the image path on success, false on failure.
	============================================================*/
	function resizeImageStrict($width, $height, $append = "t", $newPath = ""){

		$path = $this->getSmallestPath($width);
		//make sure the source image exists
		if($path == "")
			return false;
		if(!fileutil::file_exists_incpath($path))
			return false;

		//create a source image object to work with
		$ext = fileutil::getExt($this->path);
		if ( preg_match("/jpg|jpeg/", $ext))
			$src_img=imagecreatefromjpeg($this->path);
		if ( preg_match("/png/", $ext))
			$src_img=imagecreatefrompng($this->path);

		//create a temporary thumbnail that is resized to
		//have its shortests edge equal to the width / height value
		//and have it maintain proportions
		//We will then take a width/height (parameters) cutout from this temporary thumbnail
		//and use it as the final thumbnail image
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);
		if ($old_x >= $old_y) {
			$thumb_h = $height;
			$thumb_w = ( $height * $old_x ) / $old_y;
		}
		else if ($old_x < $old_y) {
			$thumb_w = $width;
			$thumb_h = ( $width * $old_y ) / $old_x;
		}

		//create the temporary thumbnail performing a resize (keeping proportions)
		$temp_img=ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecopyresampled(  $temp_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y );

		//create the final thumbnail, just taking the top 100,100 pixels from
		//the temporary thumbnail
		$dst_img=ImageCreateTrueColor($width,$height);
		imagecopyresampled(  $dst_img, $temp_img, 0, 0, 0, 0, $width, $height, $width, $height );

		//determine where to save the file
		if($newPath == "")
			$newPath = $this->trimAppended($path);
		if($append !="" )
			$newPath = fileutil::stripExt($newPath)."_$append".".".$ext;

		$filename = $newPath;

		//save the changes
		$filename = fileutil::stripExt($newPath).".".$ext;
		if (preg_match("/png/",$ext) )
			$ok = imagepng($dst_img,$filename);
		else
			$ok = imagejpeg($dst_img,$filename);

		//clean up resources
		imagedestroy($temp_img);
		imagedestroy($src_img);
		imagedestroy($dst_img);

		if ($ok)
			return $filename;
		return false;
	}

	/*===========================================================
	trimAppended()
	Given a path, this will return a version of the path with any
	"_size" appends removed. For example, if it were passed Waterfall_l.jpg
	it would return Waterfall.jpg.
	Note that it only picks up l,m,s,t, and sq.
	This is used to get the original name of a given file when trying to
	come up with a new name - specificly to address the resizing optimization
	where progressivly small images are used to create smaller images.
	This is done to avoid cases such as Waterfall_l_m_s_t_sq.jpg being genreated
	as a new name.
	============================================================*/
	function trimAppended($path){
		$ext = fileutil::getExt($path);
		$pathNoExt = fileutil::stripExt($path);
		$last = strrpos($pathNoExt,"_");
		if ($last===false || strlen($pathNoExt)-$last <= 0)
			return $path;
		$after = substr($pathNoExt,$last,strlen($pathNoExt)-$last);
		if ($after == "_l" || $after == "_m" || $after == "_s" || $after=="_t" || $after == "_sq") {
			return substr($pathNoExt,0,$last).".".$ext;
		}
		return $path;
	}

	/*===========================================================
	loadImageDetails()
	Loads the image widths and height. After making this call
	both properties will be filled with data
	============================================================*/
	function loadDetails(){
		$path = $this->path;
		//make sure the source image exists
		if($path == "")
			return false;
		if(!fileutil::file_exists_incpath($path))
			return false;

		//create a source image object to work with
		$ext = fileutil::getExt($this->path);
		if ( preg_match("/jpg|jpeg/", $ext))
			$src_img=imagecreatefromjpeg($this->path);
		if ( preg_match("/png/", $ext))
			$src_img=imagecreatefrompng($this->path);

		$this->width =imageSX($src_img);
		$this->height =imageSY($src_img);

		imagedestroy($src_img);
	}

	/*===========================================================
	getSmallestPath()
	Given a width, this will return a path to an image source
	that is at least as large. This helps with resizing - as
	the resized image can be created from the next larger image
	instead of the largest image, witch is an expensive resize.
	============================================================*/
	function getSmallestPath($width)
	{
		if(isset($this->square) && $width < 75)
			return $this->square;
		else if(isset($this->thumbnail) && $width < 100)
			return $this->thumbnail;
		else if(isset($this->small) && $width < 240)
			return $this->small;
		else if(isset($this->medium) && $width < 500)
			return $this->medium;
		else if(isset($this->large) && $width < 800)
			return $this->large;
		else
			return $this->path;

	}

	/*===========================================================
	getImagesWithContext($content)
	STATIC METHOD
	Returns an array of images that are in the database with the given
	context string.
	============================================================*/
	function getImagesWithContext($context)
	{
		global $sql;
		$table = image::tablename;
		$context = sql::Escape($context);
		$result = $sql->Query("SELECT * FROM $table WHERE context='$context'");
		$images = array();
		while($row = mysqli_fetch_array($result)) {
			$image = new image();
			$image->loadFromRow($row);
			$images[] = $image;
		}
		return $images;
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(image::tablename)) {
			$tablename = image::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`title` VARCHAR (256) NOT NULL,
						`comment` VARCHAR (512) NOT NULL,
						`originalname` VARCHAR (256) NOT NULL,
						`context` VARCHAR (256) NOT NULL,
						`path` VARCHAR (256) NOT NULL,
						`thumbnail` VARCHAR (256) NOT NULL,
						`square` VARCHAR (256) NOT NULL,
						`small` VARCHAR (256) NOT NULL,
						`medium` VARCHAR (256) NOT NULL,
						`large` VARCHAR (256) NOT NULL,
						`uploaddate` DATETIME NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
image::setupTable()


/*

	// Get the image and create a thumbnail
	$img = @imagecreatefromjpeg($_FILES["Filedata"]["tmp_name"]);

	imagejpeg($img, "../users/".$_SESSION["user_id"]."/".$_POST["albumName"]."/".$_FILES["Filedata"]["name"]);
	$data = AddPicture($_FILES["Filedata"]["name"], $_POST["albumId"], $_SESSION["user_id"]);
	// error checking.. eh
	if ($data[0] == true) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "image already exists";
		exit(0);
	}


	if (!$img) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "could not create image handle";
		exit(0);
	}

	$width = imageSX($img);
	$height = imageSY($img);

	if (!$width || !$height) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "Invalid width or height";
		exit(0);
	}

	// Build the thumbnail
	$target_width = 100;
	$target_height = 100;
	$target_ratio = $target_width / $target_height;

	$img_ratio = $width / $height;

	if ($target_ratio > $img_ratio) {
		$new_height = $target_height;
		$new_width = $img_ratio * $target_height;
	} else {
		$new_height = $target_width / $img_ratio;
		$new_width = $target_width;
	}

	if ($new_height > $target_height) {
		$new_height = $target_height;
	}
	if ($new_width > $target_width) {
		$new_height = $target_width;
	}

	$new_img = ImageCreateTrueColor(100, 100);
	if (!@imagefilledrectangle($new_img, 0, 0, $target_width-1, $target_height-1, 0)) {	// Fill the image black
		header("HTTP/1.0 500 Internal Server Error");
		echo "Could not fill new image";
		exit(0);
	}

	if (!@imagecopyresampled($new_img, $img, ($target_width-$new_width)/2, ($target_height-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height)) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "Could not resize image";
		exit(0);
	}

	if (!isset($_SESSION["file_info"])) {
		$_SESSION["file_info"] = array();
	}

	// Use a output buffering to load the image into a variable
	ob_start();
	imagejpeg($new_img, "../users/".$_SESSION["user_id"]."/".$_POST["albumName"]."/th_".$_FILES["Filedata"]["name"]);
	imagejpeg($new_img);
	$imagevariable = ob_get_contents();
	ob_end_clean();

	$file_id = md5($_FILES["Filedata"]["tmp_name"] + rand()*100000);

	*/

?>