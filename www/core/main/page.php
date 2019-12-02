<?php
/*===========================================================
Page Class
The page class reprents a physical page that is shown to the user.
It is intended to be extended by actual page classes in the
control directory. Most functionality for the page class
is implemented in the class virtualPage, which represents
a page stored in the database with parts placed on it.
============================================================*/
include_once("core/main/virtualPage.php");
include_once("core/main/dispatcher.php");
class page extends virtualPage
{
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $defaultView = "";			//Defined by control files that extend the page class.
									//Used to identify a default view when the render
									//method is called
	var $pageTemplate;
	var $controlPath = "";			//The path (dir + filename) of the control file
	var $directory = "";			//The directory of the file as seen from the outside world
	var $templateDirectory = "";	//The directory of templates for this page (Seen from the control files perspective)
									//Templates arn't directly accessible from the outside world
	var $binDirectory = "";			//The directory where page resources may be accessed by external visitors
									//This is where users may access images, css, js, or files that are included
									//in the control path.
									//Users will see this as: http://www.site.com/somepath/bin/(images|css|js|files)
									//It will actually map to the bin folder where the page class instance is located
									//such as http://www.site.com/site/control/somepath/bin/images
									//The control directory is protected to only allow access to the bin folders
									//and not code behind files.
	var $eventResult;				//Boolean. Set by a event callback to signal whether it worked correctly or not. True = success.
	var $eventMessage;				//String. Set by a event callback to tell what the event failed or succedded.
	var $isControlFile = 1;			//Internal flag to signal that extending classes are based control / code behind files
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function page(){
		parent::virtualPage();
	}

	/*===========================================================
	loadFromRow()
	Loads information about the page from the given row (obtained
	via a database query).
	Actual code is implemented in base class. Overridden here
	so that extra steps can be taken that are special for a
	real page.
	============================================================*/
	function loadFromRow(&$row, $loadParts = true){

		parent::loadFromRow($row, $loadParts);

		$this->calculatePaths();

		$this->init();
	}
	/*===========================================================
	calculatePaths()
	Calculates the paths for a given pages resource and control directories
	============================================================*/
	function calculatePaths(){
		global $SiteRoot;
		//$this->controlFile = dispatcher::getControlFile($this->url);  //Now set by director
		$this->controlPath = fileUtil::stripExt(fileUtil::stripFile($this->controlFile));
		$this->directory = fileutil::upPath($this->url);
		$this->templateDirectory = $this->controlPath . "/bin/templates/";
		//$this->binDirectory = $this->directory."/bin/";

		$this->binDirectory = $SiteRoot . $this->directory;
		if($this->directory == "")
			$this->binDirectory .= "bin/";
		else
			$this->binDirectory .= "/bin/";

	}

	/*===========================================================
	init()
	Handles any initalization that needs to be done for the page
	Implemented in extending classes
	============================================================*/
	function init(){

	}

	/*===========================================================
	renderControlArea()
	Renders the control/codebehind content for this area. This
	is content that is hardcoded by a page that has a physical
	codebehind file located in the control/ directory.
	============================================================*/
	function renderControlArea($area){

		$toReturn = array();

		$content = $this->renderArea($area,"");
		if(isset($this->pagetitle))
			$this->title = $this->pagetitle;
		if($content != "")
			$toReturn[] = $content;

		$foundMore = false;
		$count = 0;
		do{
			$foundMore = false;
			$count++;
			$postfix = "_".$count;
			$content = $this->renderArea($area,$postfix);
			if($content != null ) {
				$foundMore = true;
				$toReturn[] = $content;
			}

		}while($foundMore || $count < 2);

		return $toReturn;
	}

	/*===========================================================
	renderArea()
	Attempts to render the current area with the page, looking
	for the area name, appeneded with a given prefix.
	Checks three posibilities for an area name in an extending class
	$areaVIEW$postfix
	$areaDEFAULTVIEW$postfix
	$area$postfix

	Example: area = "Area1", postfix = "_1". View="Members
	Area1_Members_1
	Area1_Default_1
	Area1_1

	Postfix is optional and may be left blank
	============================================================*/
	function renderArea($area, $postfix = ""){
		//temporarily save current page title (reset it after this method)
		$pagetitle = $this->title;

		$view = util::getData("view");

		$method = null;

		$foundMatch = false;
		if(method_exists($this, $area.$view.$postfix)) {
			$method = $area.$view.$postfix;
			$foundMatch = true;
		}
		//second, look for a default view
		else if(method_exists($this, $area.$this->defaultView.$postfix)) {
			$method = $area.$this->defaultView.$postfix;
			$foundMatch = true;
		}
		//third, look for a method without a view attached
		else if(method_exists($this, $area.$postfix)) {
			$method = $area.$postfix;
			$foundMatch = true;
		}

		if(!$foundMatch)
			return null;

		//border might be set by page to tell how to border the content
		unset($this->border);
		unset($this->title);
		$this->pageTemplate = & new template();
		//$this->pageTemplate->directory = $this->templateDirectory;

		$content = $this->$method();

		//now wrap the contents in the requested border
		$this->fetchBorder($content);

		if( $this->inEditPageMode() ) {
			$this->fetchEditPageBorder($content);
		}

		//reset original page title
		$this->title = $pagetitle;
		return $content;

	}

	/*===========================================================
	wrapContentInBorder()
	Wraps the given content in a border
	============================================================*/
	function fetchBorder(&$content){
		global $theme;
		$template = & new template();
		$template->set("content",$content);
		$template->set("title",$this->title);
		$template->set("border",$this->border);
		$template->directory = $theme->getDirectory()."borders/";
		$template->setFile("border".$this->border.".tmpl.php");
		$template->depth = 1;
		if(!$template->exists()) {
			$template->setFile("border0.tmpl.php");
		}
		$content = $template->fetch();
	}

	/*===========================================================
	fetchEditPageBorder()
	Wraps the given module content in a 'edit page' border.
	This border, unlike the regular border, provides methods for
	moving the modules as well as changing title / border color / etc.
	============================================================*/
	function fetchEditPageBorder(&$content){
		$template = & new template();
		$title = "Page ";
		$iid = "page_".$this->id;

		//we only display an 'edit' link on the border if the module specifies
		//a edit section / view AND there is not already another module
		//that is being rendered alone (ie, being edited on)
		$displayEditLink = false;
		//determine if we are already in edit view (so we can add links to the border to leave it)
		$isEditView = $this->inEditPageMode();
		$template->set("iid",$iid);
		$template->set("content",$content);
		$template->set("displayEditLink",false);
		$template->set("isEditView",false);
		$template->set("defaultView",$this->defaultView);
		$template->set("options",$this->fetchEditPageOptions());  //any custom options that they want to appear in the options drop down
		$template->set("title","Code Behind");
		$template->set("border",$this->border);
		$template->set("fromTemplate",$this->actingAsTemplate);
		$template->set("icon",theme::getAbsCommonDirectory()."images/editpage/staticIcon.png");
		$template->directory = theme::getCommonDirectory();
		$content = $template->fetch("editpage/static.tmpl.php");
	}

/*===========================================================
	fetchEditPageOptions()
	Returns an html string of options for a given module. These
	are the options that will be shown for the module when a
	user clicks on the options link. It is composed for 3
	different components:
	- Standard options (title + border )
	- Custom options defined by module (drop down, text file, etc.)
	- Custom options defined by module in a options.tmpl.php template
	============================================================*/
	function fetchEditPageOptions(){
		global $theme;

		$template = & new template();
		$template->set("id",$this->id);
		$template->set("iid",$this->id);
		$template->set("title",$this->title);
		$template->set("numberOfBorders",$theme->numberOfBorderTypes());
		$template->set("border",$this->border);
		$template->directory = theme::getCommonDirectory();
		return $template->fetch("editpage/options.tmpl.php");
	}


	/*===========================================================
	set()
	Sets a variable in the template file. This variable can then be
	accesses like a regular php variable in the template.
	============================================================*/
	function set($name, $value){
		$this->pageTemplate->set($name,$value);
	}

	/*===========================================================
	setVars()
	Sends an array of variables to the template file at once. The
	arrays must be in an associative array, with the variable name
	as a key and the value as the array entry or that key.
	An easy way to do this is using the compact() method in php.
	Optioanl second variable will clear all currently set variables
	in the template
	============================================================*/
	function setVars($vars, $clear = false){
		$this->pageTemplate->setVars($vars,$clear);
	}

	/*===========================================================
	fetch()
	Fetches / renders the content for the current template. This
	will return the rendered content as a string.
	============================================================*/
	function fetch($templateFile){
		global $siteUser;

		$this->pageTemplate->setDirectory($this->templateDirectory);
		$this->pageTemplate->setFile($templateFile);
		$this->pageTemplate->depth = 1;
		$this->pageTemplate->set("directory",$this->binDirectory);
		$this->pageTemplate->set("baseDirectory",$this->directory);
		$this->pageTemplate->set("PHP_SELF",$_SERVER["PHP_SELF"]);
		$this->pageTemplate->set("siteUser",$siteUser);
		$this->pageTemplate->set("eventResult",$this->eventResult);
		$this->pageTemplate->set("eventMessage",$this->eventMessage);
		$this->pageTemplate->set("eventResultString",($this->eventResult?"1":"0"));


		$content =  $this->pageTemplate->fetch();

		//if we are set to use a border, wrap our content
		//in it.
		/*if($this->useBorder && !$this->editPageMode){
			$this->fetchBorder($content);
		}*/

		//if we are in the edit page mode we need to
		//call the module template. This will provide an interactive way
		//for the user to edit and modify the module color, border, title, position
		/*if($this->editPageMode && !$this->inEditView()) {
			$this->fetchEditPageBorder($content);
		}*/

		return $content;
	}


	/*===========================================================
	addHeader()
	Adds a given string of html to the head of the page.
	Use this to import another javascript or css file that might
	be needed for the current module
	============================================================*/
	function addHeader($htmlString){
		$this->extraHeaders[]=$htmlString;
	}
	/*===========================================================
	addJavascriptToHeader()
	Adds a reference to the given javascript file to the <head>
	tag of the current page.
	============================================================*/
	function addJavascriptHeader($path){
		$this->addHeader("<script src=\"$path\" type=\"text/javascript\"></script>");
	}
	/*===========================================================
	addCSSToHeader()
	Adds a reference to the given css file to the <head>
	tag of the current page.
	============================================================*/
	function addCSSHeader($path){
		$this->addHeader("<link rel=\"stylesheet\" type=\"text/css\" href=\"$path\" />");
	}

	/*===========================================================
	getData()
	Attempts to retrieve data from a combination of get, post
	and a session (in that order). Please see definition
	in module class for complete description
	============================================================*/
	function getData($var, $default=false, $storeInSession=false){
		return util::getData($var, $default, $storeInSession);
	}
	/*===========================================================
	getIData()
	Same as getData, except it always appends the modules current
	instance id to the passed parameter, garanteeing that it will
	be unique within a page.
	============================================================*/
	function getIData($var, $default=false, $storeInSession=false){
		return util::getData($var.$this->id, $default, $storeInSession);
	}

	/*===========================================================
	setEventResult()
	Sets the result of a event callback. These values will be available
	in any template that is invoked on the same page rendering as the
	event.
	$ok =  Whether the event task succedded
	$message = Any message to go along with the result
	============================================================*/
	function setEventResult($ok = true, $message = ""){
		$this->eventResult = $ok;
		$this->eventMessage = $message;
	}
	/*===========================================================
	setEventResult()
	Sets the result of a ajax callback. This will echo the
	results as a json object so that it can be parsed by the
	calling page.
	$ok =  Whether the event task succedded
	$message = Any message to go along with the result
	============================================================*/
	function setAjaxResult($ok = true, $message = "", $others = null){
		$this->setEventResult($ok, $message);

		if($others == null)
			echo(util::json(array($this->eventResult, $this->eventMessage)));
		else
			echo(util::json(array($this->eventResult, $this->eventMessage, $others), true));
	}

	/*===========================================================
	changeLayout()
	Temporarily changes the layout at runtime. Will accept either:
	the name of the layout or its id in the database.
	============================================================*/
	function changeLayout($layout){
		if($layout == "")
			return;

		//layout id specified
		if(is_numeric($this->layout)) {
			$layoutid = $this->layout;
			//layout id specified, load it
			$this->layout = & new layout();
			$this->layout->loadFromDatabase($layoutid);
			$this->layout->inherited = false;
		}
		//layout name (string) specified
		else {
			$layoutid = layout::getLayoutIdByName($layout);
			if($layoutid == "")
				return;
			$this->layout = & new layout();
			$this->layout->loadFromDatabase($layoutid);
		}
	}

}

?>