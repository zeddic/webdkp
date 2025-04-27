<?php
include_once("core/sql/sql.php");
director::connectToSQL();
include_once("core/main/dispatcher.php");
include_once("core/main/template.php");
include_once("core/main/theme.php");
include_once("core/main/page.php");
include_once("core/util/util.php");
include_once("core/main/site.php");
include_once("core/security/user.php");
include_once("core/main/actionHandler.php");
include_once("core/main/themeLibrary.php");
include_once("core/setup/setup.php");

class director
{
	/*===========================================================
	MEMBER VARAIBLES
	============================================================*/
	var $site;
	var $siteUser;
	var $siteStatus;
	var $url;
	var $start;
	var $status;
	/*===========================================================
	Default Constructor
	============================================================*/
	function __construct(){
		$this->start = util::timerStart();

		//allow sessions
		session_start();

		//get the url that we need to route
		$this->url = dispatcher::getUrl();

		//load the current site status
		$this->loadSiteStatus();

		//run setup if this is the first time the site has been used
		if($this->status->setup == 0) {
			setup::run();
			$this->loadSiteStatus();
		}

		//load the site
		$this->site = new site($this->url);

		//get the current user and handle any appropriate events
		$this->handleUser();

		//register globals
		$this->registerGlobals();
	}

	/*===========================================================
	Makes an initial connection to the sql database and stores
	it in the global $sql;
	============================================================*/
	static function connectToSQL(){
		$sql = new sql();
		//globals are defined in the config file.
		$sql->host = $GLOBALS["DatabaseHost"] ?? null;
		$sql->Setup($GLOBALS["DatabaseUsername"], $GLOBALS["DatabasePassword"], $GLOBALS["DatabaseName"], true);
		$GLOBALS["sql"]=$sql;
	}

	/*===========================================================
	Loads the current site status. This conatins information about
	how the site is configured, including the currently active theme.
	============================================================*/
	function loadSiteStatus(){
		$this->status = new siteStatus();
		$this->status->load($this->url);
	}

	/*===========================================================
	Register's global variables for access elsewhere in the system
	============================================================*/
	function registerGlobals(){
		//$GLOBALS["hourOffset"]=$this->hourOffset;
		$GLOBALS["siteUser"] = $this->siteUser;
		$GLOBALS["SiteUser"] = $this->siteUser;
		$GLOBALS["theme"] = $this->status->theme;
		$GLOBALS["siteStatus"] = $this->status;
	}

	/*===========================================================
	Runs the director, choosing the appropriate page to display.
	This must look at the url that the user requested (
	that could not be found in a physical file).
	It will do the following:
	1: Look for a corresponding control file. If one is found, it
	   is included. If a page class is found inside it is passed
	   off to the site to render
	2: Look at the extension. If an invalid extension was passed
	   it will return a 404 error. (For example, the framework
	   cannot have a .gif path pretending to be a virtual page
	3: If 1 and 2 pass, assumes the path to be a virtual page
	   and hands it off to the site class to handle.
	============================================================*/
	function run(){

		$page = null;

		$url = $this->url;

		$_SERVER["PHP_SELF"] = $GLOBALS["SiteRoot"].$url;
		$_SERVER["PHP_SELF_NOEXT"] = fileutil::stripExt($GLOBALS["SiteRoot"].$url);
		$_SERVER["PHP_SELFDIR"] = fileutil::stripFile($_SERVER["PHP_SELF"])."/";

		//1 - check if there is a control file
		$controlFile = dispatcher::getControlFile($url);

		//echo("looking for url $url - $controlFile <br />");
		//$controlFile = $this->getControlFile($url);
		if($controlFile) {
			//a - include the control file
			//set up a few variables that might be used by the control file
			//These might be used if the control file is a raw php file and not
			//implementing a page class

			$directory = $GLOBALS["SiteRoot"].fileUtil::stripFile($url)."/bin/";
			$baseDirectory = $GLOBALS["SiteRoot"].fileUtil::stripFile($url)."/";
			$bin = fileUtil::stripFile($controlFile)."/bin/";
			$templateDirectory = fileUtil::stripFile($controlFile)."/bin/templates/";
			$template = new template($templateDirectory);
			$template->set("directory",$directory);
			$template->set("baseDirectory",$baseDirectory);
			$template->set("PHP_SELF",$_SERVER["PHP_SELF"]);
			include_once($controlFile);
			//b - check if there is a page class in the control file
			//if there is, create it and pass it to the site.
			//If there is no page class, the control file must have
			//been running on its own. We can die now.
			$page = dispatcher::getControlFilePageInstance($url);

			if($page == null)
				die();
		}

		//2 - no control file. Check for a valid extension
		//    before doing moving on

		$ext = fileutil::getExt($url);
		if($ext != "") {
			if(!in_array($ext,$GLOBALS["SupportedExtensions"])) {
				//throw a 404 error
				header("HTTP/1.0 404 Not Found");
				die();
			}
		}

		//3 - If we are here either we have a control file that defined a page
		//	  class instance or we have a completly virtual page. Start up the
		//	  site to display the page.
		$url = fileUtil::stripExt($url);
		//update the sites url
		$this->site->url = $url;
		//$site = new site($url);

		$this->site->render($page);

		//$result = util::timerEnd($this->start);
		//echo("<br /><br /><br /><br /><br /><br /><br /><br /><br />Time: ".$result);
	}

	/*===========================================================
	Checks to see if there is a control / codebehind file for
	the given url. This will check in the site/control directory.
	If a valid control file is found, its path is returned. If
	no control file is found, false is returned.
	============================================================*/
	function getControlFile($url){

		$ext = fileutil::getExt($url);
		$url = fileutil::stripExt($url);
		if ($ext == "") {
			$path = "control/$url/index.php";
			if(fileutil::file_exists_incpath($path)) {
				return $path;
			}
		}
		$path = "control/$url.php";
		if(fileutil::file_exists_incpath($path)){
			return $path;
		}
		return false;
	}

	/*===========================================================
	Attempts to identify the current user and populate the
	$this->siteUser member variable. This will handle any of the following
	conditions:
	- User logging in
	- User registering
	- User logging out
	- User alreayd logged in (data loaded from cookie).
	If all of these conditions fail, then there is no user that we
	know about using the site. $siteUser will still be populated
	in this case, it will just have a isValid flag = to false.

	Results from either a register or a login are stored in a global
	variable loginResult and registerResult. These will contain
	an int corresponding to a static variable in user that can be
	reached via user::LOGIN_OK, etc.
	============================================================*/
	function handleUser(){

		$siteUser  = new user();

		$siteUserEvent = util::getData("siteUserEvent");
		//user logging in
		if($siteUserEvent == "login") {
			$username = $_POST["username"];
			$password = $_POST["password"];
			$loginResult = $siteUser->login($username,$password);
			$GLOBALS["loginResult"] = $loginResult;

		}
		//user registering
		else if($siteUserEvent == "register") {
			$username = $_POST["username"];
			$password1 = $_POST["password1"];
			$password2 = $_POST["password2"];

			$siteUser->firstname = $_POST["firstname"];
			$siteUser->lastname = $_POST["lastname"];
			$siteUser->email = $_POST["email"];
			$registerResult = $siteUser->register($username, $password1, $password2);
			$GLOBALS["registerResult"] = $registerResult;
		}
		//user logging out
		else if($siteUserEvent == "logout") {
			$siteUser->loadFromCookie();
			$siteUser->logout();
		}
		//user is already logged in / check to see if they are logged in
		else {
			$siteUser->loadFromCookie();
		}
		$this->siteUser	 = $siteUser;
	}
}

?>