<?php
/*===========================================================
CSSCache is a utility class that will both compress and
cache css files. This shrinks the size of a css file
and allows it to retrieved quickly by web browsers.
It will automatically detect when one of the css files
has been updated and update its catche. It will also provide
headers to clients, notifying them when they must clear their
local css cache and download newer versions.

code based on comment by lucky760 in the php online
documentation.
============================================================*/
class cssCache {

	/*=======================================================
	MEMBER VARIABLES
	========================================================*/
	//the directory where cached files should be stored
	//relative to index.php in webroot
	const cacheDirectory = "site/cache/css/";
	//if true, css files will be compressed before being cached
	const compress = true;

	/*=======================================================
	DEFAULT CONSTRUCTOR
	========================================================*/
	function cssCache(){
	}

	/*=======================================================
	Gets a list of available
	========================================================*/
	function getCssFileList(){
		$themeid = util::getData("themeid");

		if ($themeid!="") {
			$theme = new theme();
			$theme->loadFromDatabase($themeid);
			if($theme->id == "")
				$theme = $GLOBALS["theme"];
		}
		else {
			$theme = $GLOBALS["theme"];
		}


		$css = $theme->getCssFiles();

		//get the common css files (framework files)
		$temp = array();
		$temp[] = "site/themes/common/reset.css";
		$temp[] = "site/themes/common/common.css";
		$temp[] = "site/themes/common/editpage.css";

		//combine the two (common css files go first, allowing theme
		//css files to override them)
		$css = array_merge($temp,$css);

		return $css;
	}

	function render(){
		$files = cssCache::getCssFileList();
		cssCache::renderFiles($files);
	}

	/*=======================================================
	Renders an array of css files.
	Contents will be echoed to output stream in gzipped format
	as one continuous css file.
	Appropriate headers will be set first.

	For each passed file it will perform the following:
	- Check if there is a cache file available
    - Make sure the cache file is recent (based on current file size)
    - If no cache is available compress the real file and store it in cache
    - echo contents

	NOTE: files must be relative to the site directory.
	So if these files were physical files in the web root, it
	would be:
	webroot\css\myfile.css
	While if they were a theme file they would be
	themes\common\common.css
	========================================================*/
	function renderFiles($files){
		cssCache::startHeaders($files);
		foreach($files as $file) {
			if(fileutil::file_exists_incpath($file)) {
				//include($file);
				cssCache::dumpfile($file);
			}
		}
	}

	/*=======================================================
	Renders a single css file. See renderFiles for more details
	========================================================*/
	function renderFile($file){
		cssCache::renderFiles(array($file));
	}

	/*=======================================================
	Starts the headers for the css dump.
	========================================================*/
	function startHeaders($filenames){
		//if($this->headersWritten == true)
		//	return;
		//$this->headersWritten = true;

		//if changes have been made, make sure that old cached
		//copies on users _local machine_ are cleared as well
		if(cssCache::cssChanged($filenames))
			$expire = -72000;
	    else
			$expire = 3200;

		header('Content-Type: text/css; charset: UTF-8');
		header('Cache-Control: must-revalidate');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT');
	}

	/*=======================================================
	Dumpts the contents of the given css file to the stream.
	This method handles the logic of determining when to load/store
	from cache and when to compress
	========================================================*/
	function dumpfile($filename){

		//expected name of the cache file (based on the size/editdate)
		$cachename = cssCache::getCacheName($filename);

		if(fileutil::file_exists_incpath($cachename)) {
			//cache exists - dump it)
			//echo("!");
			include($cachename);
			return;
		}

		//if cache doesn't exist, we will need to generate a new one.
		//first, delete all the old caches. Do this using a wildcard search
		$oldcaches = glob(cssCache::getCacheName($filename, true), GLOB_NOESCAPE );
		if($oldcaches)
			foreach($oldcaches as $oldcache)
				unlink($oldcache);

		//now generate the new cache
		if(cssCache::compress)
			$compressed = cssCache::compress(cssCache::getFileContents($filename));
		else
			$compressed = cssCache::getFileContents($filename);

		//save the compresed data in cache
		//(as long as the file doesn't have a php extension - php files
		// may change every run)
		$ext = fileutil::getExt($filename);

		if(strtolower($ext)!="php") {
			$path = fileutil::stripFile($cachename);
			fileutil::mkdir($path);

			$handle = fopen($cachename,'w+');
			fwrite($handle, $compressed);
			fclose($handle);
		}
		echo $compressed;
	}


	/*=======================================================
	Gets the contents of a given css file name
	========================================================*/
	function getFileContents($filename){

		//to each file we need to pass the special php variable:
		//$directory
		//The files can use this to provide absolute paths to their images
		$path = fileutil::stripFile($filename);
		global $SiteRoot;
		$directory = $SiteRoot.$path."/";
		//echo("About to set directory $directory <br />");

		ob_start();                    // Start output buffering
		include($filename);  // Include the file
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();                // End buffering and discard
		return $contents;
	}

	/*=======================================================
	Compresses the given string. Returns string in compressed
	format. This will remove comments and white space
	========================================================*/
	function compress($buffer){
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
	    $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  '), '', $buffer);
	    $buffer = str_replace('{ ', '{', $buffer);
	    $buffer = str_replace(' }', '}', $buffer);
	    $buffer = str_replace('; ', ';', $buffer);
	    $buffer = str_replace(', ', ',', $buffer);
	    $buffer = str_replace(' {', '{', $buffer);
	    $buffer = str_replace('} ', '}', $buffer);
	    $buffer = str_replace(': ', ':', $buffer);
	    $buffer = str_replace(' ,', ',', $buffer);
	    $buffer = str_replace(' ;', ';', $buffer);
	    return $buffer;
	}

	/*=======================================================
	Returns true if any of the given css file names has changed,
	resulting in an out of date cache
	========================================================*/
	function cssChanged($filenames){
		return false;
		foreach ($filenames as $filename) {
		    if (!fileutil::file_exists_incpath(cssCache::getCacheName($filename))) {
		        return true;
		    }
		}
	    return false;
	}

	/*=======================================================
	Given a file name will return an appropriate name
	for its cache file. This will take into consideration
	its size and last edit date.
	Accepts an optional 2nd parameter: wildcard. When set to
	true this method will return a wildcard string that will
	match any of the cache files for the given filename that
	might have been generated in the past.
	========================================================*/
	function getCacheName($filename, $wildcard = false){
		//filename may be a full out directory.
		//Use its parent directory and file name to create its cache name
		//Would use full directoy name, but will become too long
		$temp = explode("/",$filename);
		if(sizeof($temp > 1))
			$cachefilename = $temp[sizeof($temp)-2] . "_".$temp[sizeof($temp)-1];
		else
			$cachefilename = $temp[0];
		$stat = stat($filename);
		if($wildcard)
			return cssCache::cacheDirectory . $cachefilename.".*.cache";
		else
			return cssCache::cacheDirectory . $cachefilename.".".$stat['size'].".".$stat['mtime'].".cache";
	}




		/*function createCssCache(){

		cssCache::getCssFileList();
		global $theme;

		$cssPath = "site/webroot/css/";
		fileutil::mkdir($cssPath);
		$cssFile = $cssPath."theme".$theme->id.".css";

		$data = "";

		$files = cssCache::getCssFileList();

		foreach($files as $file) {
			if(fileutil::file_exists_incpath($file)) {
				$data .= cssCache::getFile($file);
			}
		}

		if(fileutil::file_exists_incpath($cssFile)){
			unlink($cssFile);
		}

		$handle = fopen($cssFile,'w+');
		fwrite($handle, $data);
		fclose($handle);
	}

	function getFile($filename){
		//to each file we need to pass the special php variable:
		//$directory
		//The files can use this to provide absolute paths to their images
		$path = fileutil::stripFile($filename);
		global $SiteRoot;
		$directory = $SiteRoot.$path."/";
		//echo("About to set directory $directory <br />");

		ob_start();                    // Start output buffering
		include($filename);  // Include the file
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();                // End buffering and discard
		return $contents;
	}*/

	/*function needToUpdateCache(){
		return false;
		$cssFiles = cssCache::getCssFileList();
		return cssCache::cssChanged($cssFiles);
	}*/
}
?>