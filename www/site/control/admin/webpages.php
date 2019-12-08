<?php
include_once("bin/lib/controlPanel.php");

/*===========================================================
Controller
Displays a list of web pages for the user to edit
============================================================*/
class pageWebPages extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = true;
	var $title = "Control Panel";
	var $system = 1;
	var $layout = "Columns2";

	/*===========================================================
	TOP LEFT - Displays links to the different security sections.
	============================================================*/
	function area1_1(){
		$this->title = "Pages";
		$this->border = 5;
		return $this->fetch("pages/sidebar.tmpl.php");
	}

	/*===========================================================
	Center Page - Displays list of all webpages
	============================================================*/
	function area2(){
		global $sql;

		//check security
		if(!security::hasAccess("Edit Page") && !security::hasAccess("Create Page")) {
			$this->border = 1;
			$this->title = "Pages";
			return "You do not have permissions to view this page.";
		}

		//determine the path that we are currently viewing
		$path = $this->getData("path");
		if($path != "" && $path[strlen($path)-1]=="/")
			$path = substr($path,0,strlen($path)-1);

		//create two escaped versions of the path. We will use these
		//for our query to get all pages & folders within that path
		$escapePath = sql::Escape($path);
		$escapePath2 = $escapePath;
		if($escapePath2 != "")
			$escapePath2 .= "/";

		//first step, get all the pages for this folder (will include subfolders' pages as well)
		$tablename = page::tablename;
		$result = $sql->Query("SELECT * FROM $tablename WHERE isTemplate = 0 AND ( url='$escapePath' OR url LIKE '$escapePath2%' ) ORDER BY url ASC");
		$webpages = array();
		$pages = array();
		while($row = mysqli_fetch_array($result)){
			$page = new page();
			$page->quickLoadFromRow($row);
			$pages[] = $page;
		}

		//second step, get all the subfolders for this folder
		$folders = array();
		$tablename = folder::tablename;
		$result = $sql->Query("SELECT * FROM $tablename WHERE name LIKE '$escapePath%' AND name!='$escapePath' ORDER BY name ASC ");
		while($row = mysqli_fetch_array($result)) {
			$folder = new folder();
			$folder->loadFromRow($row);
			$folder->isRealFolder = true;
			$folder->relativeUrl = $this->getRelativeUrl($path,$folder->name);
			$temp = fileutil::getLeftDir($folder->relativeUrl);
			if(!$this->seenFolder($temp,$folders))
				$folders[] = $folder;
		}

		//next step, we need to iterate through each of the pages
		//and calculate their relative path. We will use this to determine
		//if we need to show a virtual folder in our list as well. For example,
		//if a user created a page randomly at /this/is/a/really/long/path/page
		//the only entry is the database is for a single page. Each of the folders
		//of "this", "is", "a", etc. were not put in the folder table and are virtual.
		foreach($pages as $page) {
			/*if($page->url == $path ) {
				$seenIndex = true;
				$page->relativeUrl = "Index";
				continue;
			}*/
			//echo($page->url."<br />");
			//get the relative url for the page
			$page->relativeUrl = $this->getRelativeUrl($path,$page->url);
			if(strpos($page->relativeUrl,"/")!== false) {
				//if we havn't seen a virtual folder for this url yet, create oen
				$folder = fileutil::getLeftDir($page->relativeUrl);
				if(!$this->seenFolder($folder,$folders)) {
					$newFolder = new folder();
					$newFolder->name = $folder;
					$newFolder->relativeUrl = $folder;
					$folders[] = $newFolder;
				}
			}
		}

		//final step, iterate through all of the pages that we discovered
		//for this path and display only the ones that that are visible
		//at our currently level. Note that some of the pages in the pages
		//array may be pages from our subfolders

		foreach($pages as $page) {
			if(strpos($page->relativeUrl,"/")=== false) {
				if($page->url == $path )
					continue;
				//if(!$this->seenFolder($page->url,$folders)) {
					$isIndex = (strtolower($page->relativeUrl) == "index");
					if($isIndex && $seenIndex)
						continue;
					if($isIndex)
						$seenIndex = true;
					$webpages[] = $page;
				//}
			}
		}

		//determine a path that the user should go to if they go 'up' a level
		$uppath = fileutil::upPath($path);

		//determine the title to display
		$title = "Web Root";

		if($path != "")
			$title = fileutil::getRightDir($path);

		if($path != "" && $path[strlen($path)-1]!="/")
			$path .= "/";


		$this->generatePageBreadCrumbs($path);

		$this->addCSSHeader($this->binDirectory."css/webpages.css");
		$this->set("uppath",$uppath);
		$this->set("title",$title);
		$this->set("path",$path);
		$this->set("webpages",$webpages);
		$this->set("folders",$folders);
		$this->title = "Web Pages";
		return $this->fetch("pages/webpages.tmpl.php");
	}

	/*===========================================================
	HELPER
	Converts the url to a relative url to the given path.
	For example, if called with:
	path: hello/world/
	url: hello/world/pages/mypage

	it would return return: pages/mypage
	============================================================*/
	function getRelativeUrl($path, $url){
		$toReturn = $url;
		if(strpos($url,$path) === 0 ) {
			$toReturn = substr($url,strlen($path),strlen($url)-strlen($path));
			if($toReturn[0]=="/")
				$toReturn = substr($toReturn,1,strlen($toReturn)-1);
		}
		return $toReturn;
	}

	/*===========================================================
	HELPER
	Returns true if the given folder name has already been
	seen (helper for page view).
	$foldername - the foldername to check if we've seen so far
	$folders - the folders that have been discovered so far
	============================================================*/
	function seenFolder($foldername, &$folders ){
		foreach($folders as $folder) {
			if($folder->relativeUrl == $foldername) {
				$folder->hasChildren = true;
				return true;
			}
		}
		return false;
	}

	/*===========================================================
	HELPER
	Generates breadcrumbs for the page browser page. Note that these
	breadcrumbs will display the file path that the user has traveled.
	(helper for page view)
	============================================================*/
	function generatePageBreadCrumbs($path){
		global $SiteRoot;
		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Pages",$SiteRoot."admin/webpages");
		if($path == "")
			$this->breadcrumbs[] = array("Web Root");
		else
			$this->breadcrumbs[] = array("Web Root",$SiteRoot."admin/webpages");
		//to create the breadcrumbs we will progreissivly take folder names from the path,
		//popping them from the left. Each pop will be one breadcrumb.
		//As we pop data off it is appended to the end of the $trail var which will
		//be used to create the links to each relative page
		$tracer = $path;
		while($tracer != "") {
			$name = fileUtil::trimLeftDir($tracer);
			$trail .= $name."/";
			//last breadcrumb (no link)
			if($tracer == "")
				$this->breadcrumbs[] = array($name);
			else
				$this->breadcrumbs[] = array($name,$SiteRoot."admin/webpages?path=".$trail);
		}
	}

	/*===========================================================
	Center Page
	Shows a list of all available templates
	============================================================*/
	function area2Templates(){
		//check security
		if(!security::hasAccess("Edit Page") && !security::hasAccess("Create Page")) {
			$this->border = 1;
			$this->title = "Pages";
			return "You do not have permissions to view this page.";
		}


		global $sql;

		$tablename = page::tablename;
		$result = $sql->Query("SELECT * FROM $tablename WHERE isTemplate='1' ORDER BY url ASC");
		$templates = array();
		while($row = mysqli_fetch_array($result)){
			$page = new page();
			$page->loadFromRow($row);
			$templates[] = $page;
		}


		//set the breadcrumbs
		global $SiteRoot;
		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Templates");

		$this->set("templates",$templates);
		$this->title = "Site Templates";
		return $this->fetch("pages/templates.tmpl.php");
	}

	/*===========================================================
	Center Page
	Allows the settings behind a template to be edited
	============================================================*/
	function area2Template(){

		//check security
		if(!security::hasAccess("Edit Page")) {
			$this->border = 1;
			$this->title = "Edit Template";
			return "You do not have permissions to view this page.";
		}

		global $sql;

		//get the template information
		$id = $this->getData("templateid");
		$template = new page();
		$template->loadFromDatabase($id);

		if(!$template->isTemplate){
			return $this->area2();
		}


		//get a list of layouts
		global $sql;
		$table = layout::tablename;
		$result = $sql->Query("SELECT * FROM $table ORDER BY id ASC");
		$layouts =  array();
		$layoutNames = array();
		while($row = mysqli_fetch_array($result)){
			$layout = new layout();
			$layout->loadFromRow($row);
			$layouts[]=$layout;
			$layoutNames[]=$layout->name;
		}

		//get a list of available templates that this one can inherit from
		$table = page::tablename;
		$currentName = sql::Escape($template->displayName);
		$result = $sql->Query("SELECT id, url, title FROM $table WHERE isTemplate='1' AND url!='$currentName' ORDER BY title ASC");
		$templatenames = array();
		while($row = mysqli_fetch_array($result)){
			$tempPage = new page();
			$tempPage->id = $row["id"];
			$tempPage->url = $row["url"];
			$tempPage->title = $row["title"];
			if($tempPage->title == "")
				$tempPage->title = $tempPage->url;
			if($tempPage->url != $template->url) {
				$templatenames[]=$tempPage;
			}
		}

		//set the breadcrumbs
		global $SiteRoot;
		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Templates",$SiteRoot."admin/templates");
		$this->breadcrumbs[] = array($template->title);

		$this->set("template",$template);
		$this->set("layouts",$layouts);
		$this->set("templatenames",$templatenames);
		$this->title = "Editing Template";
		return $this->fetch("pages/template.tmpl.php");
	}


	/*===========================================================
	VIEW
	Breadcrumbs for the top of the page
	============================================================*/
	function area3(){
		global $SiteRoot;
		if($this->breadcrumbs == "") {
			$breadcrumbs = array();
			$breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
			$breadcrumbs[] = array("Pages");
		}
		else
			$breadcrumbs = $this->breadcrumbs;
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}

	/*===========================================================
	EVENT
	Creates a new empty template
	============================================================*/
	function eventCreateTemplate(){
		if(!security::hasAccess("Create Page")){
			$this->setEventResult(false,"Invalid Permissions");
			return;
		}

		$templateName = $this->getData("templatename");
		//make sure a name was passed
		if($templateName == ""){
			$this->setEventResult(false,"You must provide a template name");
			return;
		}

		//determine an appropriate file name for the template.
		//If another file with the same name exists, append a number at the end
		//it so this one can be differentiated
		$templateFileName = "Template".$templateName.".php";
		while(page::exists($templateFileName)) {
			$temp++;
			$templateFileName = "Template".$templateName.$temp.".php";
		}

		//create the template
		$page = new page();
		$page->pagename = $templateFileName;
		$page->displayName = $templateName;
		$page->layout = layout::getLayoutIdByName("Columns2");
		$page->inherit = 0;
		$page->isTemplate = 1;
		$page->createPageFile($page->pagename);
		$page->saveNew();

		$this->setEventResult(true, "New Template Created");
	}

	/*===========================================================
	EVENT
	Deletes a given template
	============================================================*/
	function eventDeleteTemplate(){
		if(!security::hasAccess("Create Page")){
			$this->setEventResult(false,"Invalid Permissions");
			return;
		}

		$this->setEventResult(false,"Error");
		$templateId = $this->getData("templateid");
		if(!page::existsWithId($templateId)){
			$this->setEventResult(false,"Invalid templateid");
			return;
		}
		$page = new page();
		$page->loadFromDatabase($templateId);
		if(!$page->isTemplate) {
			$this->setEventResult(false,"The passed id is a page and not a template. Page has not be deleted.");
			return;
		}

		//now delete the page
		$page->delete();

		$this->setEventResult(true,"Template Deleted");
	}

	/*===========================================================
	EVENT
	Updates changes made to a single template
	============================================================*/
	function eventUpdateTemplate(){
		if(!security::userHasAccess("Edit Page")) {
			$this->setEventResult(false,"Invalid Permissions");
			return;
		}

		$title = $this->getData("title");
		$useTemplate = ($this->getData("usetemplate")=="on");
		$templatePage = $this->getData("templatepage");
		$templateid = $this->getData("templateid");

		if($templateid == ""){
			$this->setEventResult(false,"A valid template id was not sent.");
			return;
		}
		if($title == ""){
			$this->setEventResult(false,"Please enter a name for the template.");
			return;
		}

		//load the template we want to edit
		$template = new page();
		$template->loadFromDatabase($templateid);

		//update the layout
		$layoutData = explode("|",$this->getData("layout"));
		$template->layout = $layoutData[0];

		//update the other page fields
		$template->title = $title;
		$template->useTemplate = $useTemplate;
		if($useTemplate==1)
			$template->template = $templatePage;

		$template->save();

		$this->setEventResult(true,"Saved");
	}

	/*===========================================================
	EVENT - Creates a new folder
	============================================================*/
	function eventCreateFolder(){
		if(!security::hasAccess("Create Page"))
			return;

		$folderName = $this->getData("newFolderName");
		$path = $this->getData("path");

		if(strpos($folderName,"/")!==false) {
			$this->setEventResult(false,"Folder names may not contain the '/' character.");
			return;
		}


		if($path != "" && $path[strlen($path)-1]!="/")
			$path.="/";
		$folderPath = $path.$folderName;

		if(folder::exists($folderPath)) {
			$this->setEventResult(false,"A folder with this name already exists.");
			return;
		}

		$folder = new folder();
		$folder->name = $folderPath;
		$folder->saveNew();
		$this->setEventResult(true,"New folder created.");

	}

	/*===========================================================
	EVENT - Deletes a folder
	============================================================*/
	function eventDeleteFolder(){
		if(!security::hasAccess("Create Page"))
			return;

		$folderid = $this->getData("folderid");
		if(!folder::existsId($folderid)) {
			$this->setEventResult(false, "Folder already exists");
			return;
		}

		$folder = new folder();
		$folder->id = $folderid;
		$folder->delete();

		$this->setEventResult(true,"Folder Deleted");
	}

	/*===========================================================
	EVENT - Deletes a webpage.
	============================================================*/
	function eventDeleteWebpage(){
		if(!security::hasAccess("Create Page"))
			return;

		$pageid = $this->getData("pageid");
		if(!page::existsWithId($pageid)) {
			$this->setEventResult(false, "Folder already exists");
			return;
		}

		//get the page
		$page = new page();
		$page->loadFromDatabase($pageid);
		$page->delete();

		$this->setEventResult(true, "Page Deleted");
	}
}

?>