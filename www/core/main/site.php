<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
//setcookie ("DBGSESSID", "1234@clienthost:7869;d=1,p=0",time()-60000);

include_once("main/framework.php");
include_once("main/siteStatus.php");
include_once("main/virtualPage.php");
include_once("main/page.php");
include_once("main/folder.php");
include_once("main/theme.php");
include_once("util/util.php");
include_once("util/xmlutil.php");
include_once("lib/dkp/dkpUtil.php");

class site
{
	/*===========================================================
	MEMBER VARAIBLES
	============================================================*/
	var $url;
	//var $page;
	//var $partLibrary;
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function site($url)
	{
		$this->url = $url;
	}



	/*===========================================================
	Renders the site and the current page.
	Accepts two parameters:
	$url - the url that the user requested. This will be used
		   to load information from the backend database, such
		   as parts that have been placed on it
	$page  A page instance that was defined in a codebehind file.
		   The data in this instance will be combined with data
		   from the database.
	============================================================*/
	function render($page){
		$url = $this->url;
		if($page != null) {
			//for a code behind page, use its control file
			//to determine what we load from the database
			$toLoad = $page->controlFile;
			fileUtil::trimLeftDir($toLoad);
			$toLoad = fileutil::stripExt($toLoad);

			//update the globals to reflect our actual page
			$_SERVER["PHP_SELF"] = $GLOBALS["SiteRoot"].$toLoad;
			$_SERVER["PHP_SELF_NOEXT"] = fileutil::stripExt($GLOBALS["SiteRoot"].$toLoad);
			$_SERVER["PHP_SELFDIR"] = fileutil::stripFile($_SERVER["PHP_SELF"])."/";

			//page defined in control / codebehind.
			//Check from the database to see if there is more
			//information to go along with this page
			$page->loadFromDatabaseByUrl($toLoad);

			if($page->id == "") {

				//not in database yet, add it
				$page->url = $toLoad;
				$page->calculatePaths();
				$page->saveNew();
			}
		}
		else {
			//purly virtual page, attempt to load from database
			$page = & new virtualPage();
			$page->loadFromDatabaseByUrl($url);

			//no virtual page... lets try again
			if($page->id == "") {
				$page = & new virtualPage();
				//If we tried using a regular url before, try again assuming is a directory name
				//If we tried using a directory before, try again assumings is a file name
				$lastItem = fileutil::getRightDir($url);
				if(strtolower($lastItem) == "index")
				{
					//we tried a directory lookup before, lets try a page lookup now
					$newUrl = fileutil::upPath($url);
					$page->loadFromDatabaseByUrl($newUrl);
				}
				else
				{
					//we tried a page looking before, lets try a directory/index lookup now
					$newUrl = $url . "/Index";
					$page->loadFromDatabaseByUrl($newUrl);
				}

				//could we load a page now?
				if($page->id == "") {
					$page = virtualPage::loadPage("Errors/404");
				}
				else {
					$_GET["url"] = $newUrl;
					$url = $newUrl;
					//update the globals to reflect our new page
					$_SERVER["PHP_SELF"] = $GLOBALS["SiteRoot"].$url;
					$_SERVER["PHP_SELF_NOEXT"] = fileutil::stripExt($GLOBALS["SiteRoot"].$url);
					$_SERVER["PHP_SELFDIR"] = fileutil::stripFile($_SERVER["PHP_SELF"])."/";
				}
			}
		}

		//do a security check
		if( !security::hasPageAccess($page) ) {

			echo("Permission Denied for $page->url!");
			die();
			//TODO:
			//load a permission denied page from database. Check for control too?
		}

		//trigger any page events
		$page->handleEvents();

		//handle any ajax get requests
		$page->handleAjax();

		//if this is an ajax post, we don't need to render.
		//It was just interested in executing events
		if(util::getData("ajaxpost")!="")
			die();

		$this->callTemplate($page);

	}

	/*===========================================================
	Calls the template for the site, placing the page in
	the master 'linker' template.
	============================================================*/
	function callTemplate(&$page){
		global $theme;



		//get global config values
		$title = $GLOBALS["SiteTitle"];
		$description = $GLOBALS["SiteDescription"];
		$keywords = $GLOBALS["SiteKeywords"];

		//render the page
		$content = $page->render();

		//append page name to global config site name
		if($page->title != "") {
			if($title != "")
				$title .= " - ";
			$title .= $page->title;
		}

		//check if the page requested extra headers
		//(CSS or JS includes)
		$extraHeaders = array_merge($this->getExtraHeaders($page),$page->extraHeaders);

		//send all the data to the main linker template
		//The location of this file is based on the current theme
		$template = & new template();

		//we will look for this template in two places:
		//1 - the current themes directory
		//2 - the common directory.
		//This allows the current theme to override the linker if needed

		$path = $theme->getDirectory()."linker.tmpl.php";
		if(fileutil::file_exists_incpath($path))
			$template->setDirectory($theme->getDirectory());
		else
			$template->setDirectory($theme->getCommonDirectory());
		$template->setFile("linker.tmpl.php");
		$template->set("title",$title);
		$template->set("keywords",$this->keywords);
		$template->set("description",$this->description);
		$template->set("content",$content);
		//$template->set("editpage", (util::getData("editpage")==1));
		$template->set("extraHeaders",implode("\r\n\t",$extraHeaders)."\r\n");


		//before displaying, make sure this isn't a part request
		//that wasn't handled. If it is, don't display anything.
		if(isset($_GET["getPart"]) || isset($_POST["getPart"]))
			die();


		//display the page to the user
		echo($template->fetch("",0));





		//$result = util::timerEnd($this->start);
		//echo("Time result: $result <br />");

		//DONE!
	}
	/*===========================================================
	Returns an array of extra headers defined at the site level.
	These are extra js or css files handled by the site.
	============================================================*/
	function getExtraHeaders(&$page){
		global $SiteRoot;
		$toReturn = array();

		//include the core framework js
		$toReturn[] = "<script src=\"".$SiteRoot."js/core.js\" type=\"text/javascript\"></script>";


		//include inline js to init the core js
		$url = dispatcher::getUrl();

		$toReturn[] = "<script type=\"text/javascript\">Site.Init(\"$SiteRoot\",\"$url\",\"$page->id\");</script>";

		$server = util::getData("pserver");
		$guild = util::getData("pguild");

		$toReturn[] = "<script type=\"text/javascript\">DKP.Init(\"$server\",\"$guild\");</script>";




		//dynamically created css file code goes here? no , just the link to
		//the file that generates itself automattically.

		return $toReturn;
	}
}

?>