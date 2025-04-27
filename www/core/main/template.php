<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================

*/
class template {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $vars = array();		//variables to send to the template
	var $directory;				//the directory of the template file
	var $file;					//the template file
	var $depth = 0;				//Sets how many tabs to place in front of all
								//lines form the template. Use this to make
								//templates within templates look cleaner
								//in the source
	/*===========================================================
	Default Constructor
	============================================================*/
	function __construct($directory = "", $file= "") {
		if($directory!="")
			$this->directory = $directory;
		if($file!="")
			$this->file = $file;
	}

	/*===========================================================
	setDirectory
	Sets the directory where the template file can be found
	============================================================*/
	function setDirectory($directory){
		$this->directory = $directory;
	}
	/*===========================================================
	setFile
	Sets the file where the template can be found.
	============================================================*/
	function setFile($file){
		$this->file = $file;
	}

	/*===========================================================
	set
	Sets a variable that should be accessible in the tempalte with
	the given value. The template has access to this variable by using
	<?=$name?>	============================================================*/
	function set($name, $value) {
		$this->vars[$name] = $value;
	}

	/*===========================================================
	setVars
	Sets an array of variables at once to the template. The variables
	must be in an associative array, with the variable names as keys.
	An easy way to do this is with the compact() function in php.

	@param array $vars 	array of the variables and their values to set
	@param bool $clear 	If true any current variables that have been set will be
				       	erased
	============================================================*/
	function setVars($vars, $clear = false) {
		if($clear) {
			$this->vars = $vars;
		}
		else {
			if(is_array($vars)) $this->vars = array_merge($this->vars, $vars);
		}
	}

	/*===========================================================
	exists
	Returns true if the given template exists. (the given directory
	and file name is assumed. If a file name is passed it is used
	instead of the current $this->file instance.
	============================================================*/
	function exists($file = "") {
		$directory = $this->getDirectory();
		if($file!="")
			$filename = $directory.$file;
		else
			$filename = $directory.$this->file;
		return fileutil::file_exists_incpath($filename);
	}

	/*===========================================================
	getDirectory
	Returns the current directory that this template is pointing
	too.
	============================================================*/
	function getDirectory() {
		global $theme;
		$directory = $this->directory;
		if(empty($directory))
			$directory = $theme->getDirectory();
		return $directory;
	}


	/*===========================================================
	fetch
	Fetches the template. This will open up the template file,
	then pass all the variables to it. It will render the template
	than return the results as an html string.
	============================================================*/
	function fetch($page = "", $depth = null) {
		if($depth != null) {
			$this->depth = $depth;
		}
		//every template has a reference to the theme put in
		global $theme;
		if($page != "")
			$this->file = $page;
		if(is_a($theme,"theme"))
			$this->set("theme",$theme);
		$this->set("PHP_SELF",$_SERVER["PHP_SELF"]);
		$this->set("PHP_SELFDIR",$_SERVER["PHP_SELFDIR"]);
		$this->set("SiteRoot",$GLOBALS["SiteRoot"]);
		$this->set("siteRoot",$GLOBALS["SiteRoot"]);
		$this->set("siteUser",$GLOBALS["siteUser"]);
		$this->set("SiteUser",$GLOBALS["siteUser"]);
		$this->set("theme",$GLOBALS["theme"]);
		$file = $this->directory . $this->file;
		if(!fileutil::file_exists_incpath($file)) {
			return "The template engine could not locate the file $file";
		}
		extract($this->vars);          // Extract the vars to local namespace
		ob_start();                // Start output buffering
		include($file);  // Include the file
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();                // End buffering and discard


		$useIndents = $GLOBALS["Framework_UseTemplateIndents"];
		if($useIndents) {
			if($this->depth != 0) {
				$temp = "";
				for($i = 0 ; $i < $this->depth ; $i++)
					$temp.="\t";
				$contents = str_replace("\r\n","\r\n$temp",$contents);
			}
		}
		return $contents.="\r\n";              // Return the contents
	}
}
?>