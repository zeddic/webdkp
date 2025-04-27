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
	//const cacheDirectory = "site/cache/css/";
	//if true, css files will be compressed before being cached
	const compress = false;

	/*=======================================================
	DEFAULT CONSTRUCTOR
	========================================================*/
	function __construct(){
	}

	/*=======================================================
	Gets a list of available
	========================================================*/
	static function getCssFileList(){
		$themeid = util::getData("themeid");

		if (!empty($themeid)) {
			$theme = new theme();
			$theme->loadFromDatabase($themeid);
			if(empty($theme->id))
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

		//combine the two (common css files go first, allowing theme
		//css files to override them)
		$css = array_merge($temp,$css);

		return $css;
	}

	/*=======================================================
	Renders the css for either the current theme, or the
	theme identified by the id "themeid". Echoes the generated
	css contents to screen. This will also add the neccessary
	http headers to identify a css file type.
	========================================================*/
	static function render(){
		$files = cssCache::getCssFileList();
		cssCache::renderFiles($files);
	}

	/*=======================================================
	Renders an array of css files.
	Contents will be echoed to output stream in gzipped format
	as one continuous css file.
	Appropriate headers will be set first.


	NOTE: files must be relative to the site directory.
	So if these files were physical files in the web root, it
	would be:
	webroot\css\myfile.css
	While if they were a theme file they would be
	themes\common\common.css
	========================================================*/
	static function renderFiles($files){
		cssCache::startHeaders($files);
		foreach($files as $file) {
			if(fileutil::file_exists_incpath($file)) {
				cssCache::dumpfile($file);
			}
		}
	}

	/*=======================================================
	Renders a single css file. See renderFiles for more details
	========================================================*/
	static function renderFile($file){
		cssCache::renderFiles(array($file));
	}

	/*=======================================================
	Starts the headers for the css dump.
	========================================================*/
	static function startHeaders($filenames){
		$expire = 3200;
		header('Content-Type: text/css; charset: UTF-8');
		header('Cache-Control: must-revalidate');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT');
	}

	/*=======================================================
	Dumps the contents of the given css file to the stream.
	This method handles the logic of determining when to load/store
	from cache and when to compress
	========================================================*/
	static function dumpfile($filename){

		//now generate the new cache
		if(cssCache::compress)
			$compressed = cssCache::compress(cssCache::getFileContents($filename));
		else
			$compressed = cssCache::getFileContents($filename);

		echo $compressed;
	}

	/*=======================================================
	Gets the contents of a given css file name
	========================================================*/
	static function getFileContents($filename){

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
	static function compress($buffer){
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
}
?>