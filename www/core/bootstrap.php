<?php
/*======================
The bootstrap file sets up the environment for the framework.
It sets up needed global varaibles, includes the configuration file,
fixes the current working directory, then kicks off the
framework directory.
=======================*/


//set some globals
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)).DS);
define('CORE', dirname(__FILE__).DS);
define('SITE', dirname(dirname(__FILE__).DS). DS ."site".DS);
define('WEBROOT', SITE . "webroot" . DS );

//set the include path
$result = ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR .
		CORE . PATH_SEPARATOR .
		ROOT . PATH_SEPARATOR .
		SITE . PATH_SEPARATOR );

//import the configuration settings
include("config/config.php");

//do some config checks
define("DB_PREFIX", $GLOBALS["DatabasePrefix"]);

date_default_timezone_set($GLOBALS["TimeZone"]);

//fix our current working directory
fixWorkingDirectory();

//determine our site root
createSiteRoot();

//kick of the framework
include("main/director.php");
$director = new director();
$director->run();


/*======================
Helper function. Makes sure the current working directly
is always set to the site directory instead of the directory
of the file that started bootstrap.
=======================*/
function fixWorkingDirectory(){

	$dir = getcwd();
	$path = explode(DS, $dir);

	while(sizeof($path) > 0 && strtolower(end($path)) != "site") {
		array_pop($path);
	}

	array_pop($path);
	$newdir = implode(DS, $path);

	chdir($newdir);

}
/*======================
Generates the SiteRoot global variable. This global variable
is used everywhere in the site to provide site relative links
to files. The SiteRoot variable can either be set in the configuration
file, or will be generated automattically for the user (default)
=======================*/
function createSiteRoot(){

	//if the user didn't set a site root in their configuration file,
	//create one for them automattically
	if(!isset($GLOBALS["SiteRoot"])) {

		//the difference between the current working directory (which should
		//be at the engines very root) and the document root of the server
		//will give us our site root. This is the path that can be used to
		//make site relative links for the engine

		//get our current working directory
		$dir = getcwd();
		$path = explode(DS,$dir);

		//get our document root (for the web server)
		$docroot = $_SERVER["DOCUMENT_ROOT"];
		$docroot = str_replace(DS,"/",$docroot);
		$docroot = explode("/",$docroot);

		//determine a 'flag'. The flag represents the last folder in the
		//document roots path.
		$flag = end($docroot);
		if(empty($flag) && sizeof($docroot)>1)
			$flag = $docroot[sizeof($docroot)-2];

		//keep popping folders off of the current working directory path until
		//we reach the document root folder. This gives us the path to our site
		//relative to the document root (in reverse order)
		$siteRootDirs = array();
		while(sizeof($path) > 0 && strtolower(end($path)) != strtolower($flag)) {
			$siteRootDirs[] = array_pop($path);
		}
		//reverse
		$siteRootDirs = array_reverse($siteRootDirs);
		//convert back to string
		$siteRoot = "/".implode("/",$siteRootDirs);

		//store in globals
		$GLOBALS["SiteRoot"] = $siteRoot;
	}

	//make sure the site root has a trailing "/"
	$trailingSlash = (strrpos($GLOBALS["SiteRoot"],"/") == strlen($GLOBALS["SiteRoot"])-1);
	if(!$trailingSlash)
		$GLOBALS["SiteRoot"].="/";

	$GLOBALS["siteRoot"] = $GLOBALS["SiteRoot"];

}


?>