<?php
/*===========================================================
A utility class that provides various static methods
and helper functions related to files
============================================================*/
class fileutil{

	/*===========================================================
	Returns true if the given file exists, taking into consideration
	the php include path. This is different from file_exists which
	considers the query based on the path from the current file.
	============================================================*/
	static function file_exists_incpath($file)
	{
	    $paths = explode(PATH_SEPARATOR, get_include_path());

		if(file_exists($file))
			return true;

	    foreach ($paths as $path) {
	        // Formulate the absolute path
	        if (strlen($path) > 0 && $path[strlen($path)-1] == DIRECTORY_SEPARATOR ) {
				$fullpath = $path . $file;
	        }
	        else {
				$fullpath = $path . DIRECTORY_SEPARATOR . $file;
			}

	        // Check it
	        if (file_exists($fullpath)) {
	            return $fullpath;
	        }
	    }

	    return false;
	}

	/*===========================================================
	Recursivly makes a given directory. Used in place of phps' native
	============================================================*/
	static function mkdir($path, $mode = 0777){
		//make sure the path is using the correct directory seperator (windows check)
		$path = str_replace("/",DIRECTORY_SEPARATOR,$path);
	    $dirs = explode(DIRECTORY_SEPARATOR , $path);
	    $count = count($dirs);
	    $path = '.';
	    for ($i = 0; $i < $count; ++$i) {
	        $path .= DIRECTORY_SEPARATOR . $dirs[$i];
	        if (!is_dir($path) && !mkdir($path, $mode)) {
	            return false;
	        }
	    }
	    return true;
	}

	/*===========================================================
	Recursivly deletes a given directory and all files/folders/data contained within.
	Used in place of phps' native delete method in order to support
	this recursive delete
	============================================================*/
	static function rmdir($path){
		//make sure the path is using the correct directory seperator (windows check)
		$path = str_replace("/",DIRECTORY_SEPARATOR,$path);
	    if ( is_dir ( $path ) ) {
	        foreach ( scandir ( $path )  as $value ) {
	            if ( $value != "." && $value != ".." ) {
	                $value = $path . "/" . $value;

	                if ( is_dir ( $value ) ) {
	                    fileutil::rmdir ( $path );
	                }
	                elseif ( is_file ( $value ) ) {
	                    @unlink ( $value );
	                }
	            }
	        }

	        return rmdir ( $path );
	    }
	    else
	    {
	        return false;
		}
	}

	/*===========================================================
	Strips the extension from the given file. Returns a new string
	with the extension removed.
	Example:
	stripExt("hello.txt") returns "hello"
	============================================================*/
	static function stripExt($file){
		$ext = strrchr($file, '.');
		if($ext !== false)
		{
			$file = substr($file, 0, -strlen($ext));
		}
		return $file;
	}

	/*===========================================================
	Returns the extension of the given file.
	============================================================*/
	static function getExt($file){
		$parts = explode('.',$file);
		if(sizeof($parts) == 1) {
			return "";
		}
		return strtolower(end($parts));
	}
	
	/*===========================================================
	Strips the path from a given filepath string, returning
	just the file. For example, if called with:
	"hello/world/news.php" it would return "news.php"
	============================================================*/
	static function stripPath($filepath){
		return end(explode('/',$filepath));
	}

	/*===========================================================
	Strips the file name off of a path
	============================================================*/
	static function stripFile($filepath){
		$file = strrchr($filepath, '/');
		if ($file === false) {
			return $filepath;
		}
		$filepath = substr($filepath, 0, -strlen($file));
		return $filepath;
	}

	/*===========================================================
	Gets the file name at the end of the given path
	============================================================*/
	static function getFile($filepath){
		$parts = explode('/',$filepath);
		if(sizeof($parts) == 1) {
			return $filepath;
		}
		return end($parts);
	}

	/*===========================================================
	Given a path, will return the the name of the left most directory
	identified by that path. Example:
	Hello/World
	would return "Hello"
	If the path does not contain any "/" character, the entire path
	is returned back
	============================================================*/
	static function getLeftDir($path){
		$parts = explode("/",$path);
		return $parts[0];
	}

	/*===========================================================
	Given a path, will return the the name of the right most directory
	identified by that path. Example:
	Hello/World
	would return "Hello"
	If the path does not contain any "/" character, the entire path
	is returned back
	============================================================*/
	static function getRightDir($path){
		$parts = explode("/",$path);
		return $parts[count($parts)-1];
	}

	/*===========================================================
	Given a path, will return TRIM the name of the left most directory
	identified by that path, modifying the passed variable by reference.
	In addition it returns the the trimmed part.
	Hello/World
	would return "Hello"
	If the path does not contain any "/" character, the entire path
	is returned back
	============================================================*/
	static function trimLeftDir(& $path){
		$parts = explode("/",$path);
		$toReturn = $parts[0];
		$newPath = array();
		for($i = 1 ; $i < count($parts) ; $i++)
			$newPath[]=$parts[$i];
		$newPath = implode("/",$newPath);
		$path = $newPath;

		return $toReturn;
	}

	/*===========================================================
	Given a path, returns the directy immediatly above it.
	Example:
	Hello/World/Test
	Returns
	Hello/World
	============================================================*/
	static function upPath($path){
		if($path == "")
			return $path;
		if($path[strlen($path)-1]=="/")
			$path = substr($path,0,strlen($path)-1);
		$parts = explode("/",$path);
		unset($parts[count($parts)-1]);
		return implode("/",$parts);
	}

	/*===========================================================
	Returns true if one path is part of another.
	============================================================*/
	static function containsPath($haystack, $needle){
		//first, check to see if the needle is in the haystack
		$pos = stripos($haystack, $needle);
		if ($pos === false) {
			return false;
		}

		//next, we need to make sure we found a path substring versus just
		//a random substring in the path. For example, we want to make sure that
		//we didn't find:
		//User/Name inside SiteUser/Name . User/Name meets the substring requirement, but it
		//is not a true subpath.
		//To do this we check before and after the discovered substring. It
		//must either be at the start / end of the haystack or have the "/" seperator
		//present

		//check before
		$validBefore = false;
		$validAfter = false;
		if($pos == 0 || $needle[0]=="/" ||$pos > 0 && $haystack[$pos-1] == "/") {
			$validBefore = true;
		}

		//check after
		$needleLen = strlen($needle);
		$haystackLen = strlen($haystack);
		if($pos+$needleLen == $haystackLen ||
		   $needle[$needleLen-1] == "/" ||
		   $pos+$needleLen < $haystackLen && $haystack[$pos+$needleLen] == "/") {
			$validAfter = true;
		}

		//both good?
		if($validBefore && $validAfter) {
			return true;
		}

		return false;
	}

	/*===========================================================
	Given a string, this will return a version of the string
	that is acceptable for using as a folder name
	============================================================*/
	static function convertToFolderName($string){
		$string = strtolower($string);
		$string = str_replace(' ', '_', $string);
		$string = preg_replace('/[^a-z0-9_]/i', '', $string);
		return $string;
	}

	/*===========================================================
	Returns the names of all files in a given directory
	============================================================*/
	static function getFilesInDir($directory){
		$files = array();
		$dir = @opendir($directory);
		while($filename = readdir($dir)) {
			//if its not a part directory, skip to the next one
			if(is_dir($directory.$filename) || $filename == "." || $filename=="..")
				continue;

			$files[] = $filename;
		}
		@closedir($dir);

		return $files;
	}


	/*===========================================================
	Returns a string that represents the type of document that the
	extension represents. Used to map extensions to icons.

	This can be called statically or
	============================================================*/
	static function getExtType($ext){
		if($ext == "")
			return "None";

		//need to use a global in place of a static variable to
		//allow support for php4 :(

		//do late loading of the hash map, so we only define it
		//as needed
		$extMap = $GLOBALS["FrameworkExtMap"];
		if($extMap == "") {
			fileutil::loadExtTypeMap();
			$extMap = $GLOBALS["FrameworkExtMap"];
		}

		//remove the "." if it was added in front of the ext
		if($ext[0] == ".")
			$ext = substr($ext,1,strlen($ext)-1);

		$ext = strtolower($ext);

		$type = $extMap[$ext];
		if($type == "")
			$type = "Other";
		return $type;

	}
	/*===========================================================
	Loads an extension map of file types. Loaded on demand
	to save resources
	============================================================*/
	static function loadExtTypeMap(){
		$map = array();

		$map["xlw"] = "excel";
		$map["xls"] = "excel";
		$map["csv"] = "excel";
		$map["xlt"] = "excel";
		$map["xlsx"] = "excel";
		$map["xlsm"] = "excel";

		$map["doc"] = "word";
		$map["wps"] = "word";
		$map["docx"] = "word";
		$map["docm"] = "word";

		$map["txt"] = "text";
		$map["rtf"] = "text";

		$map["ppt"] = "powerpoint";
		$map["pps"] = "powerpoint";
		$map["pptx"] = "powerpoint";
		$map["pptm"] = "powerpoint";
		$map["ppsx"] = "powerpoint";
		$map["ppsxm"] = "powerpoint";

		$map["zip"] = "zip";
		$map["rar"] = "zip";
		$map["tar"] = "zip";
		$map["gzip"] = "zip";

		$map["pdf"] = "pdf";

		$map["gif"] = "image";
		$map["jpeg"] = "image";
		$map["png"] = "image";
		$map["jpg"] = "image";

		$map["cs"] = "code";
		$map["c"] = "code";
		$map["cpp"] = "code";
		$map["h"] = "code";

		$map["html"] = "html";
		$map["htm"] = "html";

		$map["divx"] = "movie";
		$map["avi"] = "movie";
		$map["rm"] = "movie";
		$map["swf"] = "movie";
		$map["wmv"] = "movie";
		$map["ogm"] = "movie";

		$map["mp3"] = "music";
		$map["m4p"] = "music";
		$map["ogg"] = "music";
		$map["wav"] = "music";
		$map["flac"] = "music";
		$map["raw"] = "music";
		$map["wma"] = "music";

		$GLOBALS["FrameworkExtMap"] = $map;
	}
}
