<?php
include_once("core/main/dispatcher.php");
include_once("core/main/layout.php");

/**
 * The page class reprents a page that is shown to the user.
 * It is intended to be extended by page classes defined in the
 * control directory. 
 */
class Page {
	/**
	 * Defined by control files that extend the page class.
	 * Used to identify a default view when the render
	 * method is called
	 */
	var $defaultView = "";

	var $pageTemplate;

	/**
	 * The path (dir + filename) of the control file
	 */
	var $controlPath = "";	

	/**
	 * The directory of the file as seen from the outside world
	 */
	var $directory = "";

	/**
	 * The directory of templates for this page (Seen from the control files perspective) 
	 * Templates arn't directly accessible from the outside world
	 */
	var $templateDirectory = "";

	/**
	 * The directory where page resources may be accessed by external visitors
	 * This is where users may access images, css, js, or files that are included
	 * in the control path.
	 * Users will see this as: http://www.site.com/somepath/bin/(images|css|js|files)
	 * It will actually map to the bin folder where the page class instance is located
	 * such as http://www.site.com/site/control/somepath/bin/images
 	 * The control directory is protected to only allow access to the bin folders
	 * and not code behind files.
	 */
	var $binDirectory = "";

	/**
	 * Boolean. Set by a event callback to signal whether it worked correctly or not. True = success.
	 */
	var $eventResult;

	/**
	 * String. Set by a event callback to tell what the event failed or succedded.
	 */
	var $eventMessage;

	/**
	 * path + filename
	 */
	var $url;

	/**
	 * title for the page (will be displayed in browser window)
	 */
	var $title = "";
	var $pagetitle = "";

	/**
	 * the layout to use to arrange content.
	 * This may be defined as either a # or string. At runtime it will be
	 * replaced with an instance of Layout
	 * TODO: Stop this behavior, it is error prone.
	 */
	var $layout = 0;
	var $border;

	/**
	 * additional css and js includes that should be placed in the
	 * in the <head> tag of the final page. Each of the array entries
	 * should be an entire html tag such as "<script .... />"
 	 * Set at run time based on extending classes and the parts
 	 * that are present
	 */
	var $extraHeaders = array();

	var $startTime;

	var $controlFile;

	function __construct(){
		$this->startTime = util::timerStart();
	}

	/**
	 * Loads the layout for the page. This inspects the 'layout' variable and
	 * replaces it with an instance of Layout.
	 */
	function loadLayout() {
		//no layout specified: assume a standard 2 column
		if(empty($this->layout) || (is_numeric($this->layout) && $this->layout == 0)) {
			$layoutid = layout::getLayoutIdByName("Columns2");
			$this->layout = new layout();
			$this->layout->loadFromDatabase($layoutid);
		}
		//layout id specified
		else if(is_numeric($this->layout)) {
			$layoutid = $this->layout;
			//layout id specified, load it
			$this->layout = new layout();
			$this->layout->loadFromDatabase($layoutid);
		}
		//layout name (string) specified
		else {
			$layoutid = layout::getLayoutIdByName($this->layout);
			$this->layout = new layout();
			$this->layout->loadFromDatabase($layoutid);
		}
	}

	/**
	 * Calculates the paths for a given pages resource and control directories
	 */
	function calculatePaths(){
		global $SiteRoot;
		$this->controlPath = fileUtil::stripExt(fileUtil::stripFile($this->controlFile));
		$this->directory = fileutil::upPath($this->url);
		$this->templateDirectory = $this->controlPath . "/bin/templates/";

		$this->binDirectory = $SiteRoot . $this->directory;
		if(empty($this->directory))
			$this->binDirectory .= "bin/";
		else
			$this->binDirectory .= "/bin/";
	}

	/**
	 * Iterates through all parts on the pages and calls their
	 * event handlers.
	 */
	function handleEvents(){
		//see if this page has any event handlers (implemented by extending
		//classes)
		$event = util::getData("event");
		if($event) {
			$eventHandler = "event" . $event;
			if(method_exists($this, $eventHandler)) {
				$this->$eventHandler();
			}
		}
	}

	/**
	 * Iterates through all parts on the pages and calls their
	 * ajax handlers
	 */
	function handleAjax(){
		//see if this page has any event handlers (implemented by extending
		//classes)
		$ajax = util::getData("a");
		if(empty($ajax))
			$ajax = util::getData("ajax");
		if($ajax) {
			$ajaxHandler = "ajax" . $ajax;
			if(method_exists($this, $ajaxHandler)) {
				$this->pageTemplate = new template();
				$this->$ajaxHandler();
				die();
			}
		}
	}

	/**
	 * Renders the page, returning the generated page as an html string.
	 * This will in change call the render methods of each of the parts on
	 * the page. These render parts will then be organized based on
	 * the layout for the page.
	 */
	function render(){
		//render all the areas
		$area1 = $this->renderAreaParts("area1");
		$area2 = $this->renderAreaParts("area2");
		$area3 = $this->renderAreaParts("area3");
		$area4 = $this->renderAreaParts("area4");
		$area5 = $this->renderAreaParts("area5");

		$header = $this->renderHeader();
		$footer = $this->renderFooter();
		$links = $this->renderLinks();

		//figure out what layout we are using and pass all
		//data to the layout to arrange
		global $theme;
		$template = new template();
		$template->setDirectory($this->layout->getDirectory());
		$template->setFile($this->layout->filename.".tmpl.php");
		if(empty($this->layout->filename))
			$template->setFile("Columns1.tmpl.php");

		//if a part requested to be rendered alone, send it to the
		//special editAlone layout
		if (isset($this->renderAlone)) {
			$area2 = $this->renderAlone;
			$layout = new layout();
			$layout->loadFromDatabaseByName("EditAlone");

			$template->setDirectory($layout->getDirectory());
			$template->setFile($layout->filename.".tmpl.php");
		}

		//pass all data to the layout and render
		$template->setVars(compact("area1","area2","area3","area4","area5","header","footer","links"));
		$template->depth = 1;
		$renderedPage = $template->fetch();

		return $renderedPage;
	}

	/**
	 * Renders the header - returning the contents as an html string
	 */
	function renderHeader(){
		global $theme;
		$template = new template();
		$template->setDirectory($theme->getDirectory());
		$template->set("directory",$theme->getAbsDirectory());

		$template->setFile("header.tmpl.php");
		$template->depth = 2;
		return $template->fetch();
	}

	/**
	 * Renders the footer - returning the contents as an html string
   */
	function renderFooter(){
		global $sql;
		global $theme;
		$processTime = util::timerEnd($this->startTime);

		$template = new template();
		$template->setDirectory($theme->getDirectory());
		$template->set("queryCount",$sql->queryCount);
		$template->set("processTime",$processTime);
		$template->set("directory",$theme->getAbsDirectory());
		$template->setFile("footer.tmpl.php");
		$template->depth = 2;
		return $template->fetch();
	}

	/**
	 * Renders the links that provide access to the edit page mode, page
	 * settings, etc.
	 */
	function renderLinks(){
		global $theme;

		//call the links template
		$template = new template();

		//we will look for this template in two places:
		//1 - the current themes directory
		//2 - the common directory.
		//This allows the current theme to override the links if they so wish
		$path = $theme->getDirectory()."links.tmpl.php";
		if(fileutil::file_exists_incpath($path))
			$template->setDirectory($theme->getDirectory());
		else
			$template->setDirectory($theme->getCommonDirectory());
			
		$template->setFile("links.tmpl.php");
		return $template->fetch();
	}

	/**
	 * Renders all parts of the given areay, returning their
	 * generated content as an array of html strings.
	 */
	function renderAreaParts($area){

		$toReturn = array();	//array of rendered content

		// Render content from code-behind.
		$fromCodeBehind = $this->renderControlArea($area);
		if (sizeof($fromCodeBehind) > 0 ) {
			$toReturn = array_merge($toReturn,$fromCodeBehind);
		}

		return $toReturn;
	}

	/**
	 * Renders the control/codebehind content for this area. This
	 * is content that is hardcoded by a page that has a physical
	 * codebehind file located in the control/ directory.
	 */
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

	/**
	 * Attempts to render the current area with the page, looking
	 * for the area name, appeneded with a given prefix.
	 * Checks three posibilities for an area name in an extending class
	 * $areaVIEW$postfix
	 * $areaDEFAULTVIEW$postfix
	 * $area$postfix
   * 
	 * Example: area = "Area1", postfix = "_1". View="Members
	 * Area1_Members_1
	 * Area1_Default_1
	 * Area1_1
   * 
	 * Postfix is optional and may be left blank
	 */
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
		$this->pageTemplate = new template();

		$content = $this->$method();

		//now wrap the contents in the requested border
		$this->fetchBorder($content);

		//reset original page title
		$this->title = $pagetitle;
		return $content;
	}

	/**
	 * Wraps the given content in a border
	 */
	function fetchBorder(&$content){
		$title = isset($this->title) ? $this->title : "";

		global $theme;
		$template = new template();
		$template->set("content",$content);
		$template->set("title", $title);
		$template->set("border",$this->border);
		$template->directory = $theme->getDirectory()."borders/";
		$template->setFile("border".$this->border.".tmpl.php");
		$template->depth = 1;
		if(!$template->exists()) {
			$template->setFile("border0.tmpl.php");
		}
		$content = $template->fetch();
	}

	/**
	 * Sets a variable in the template file. This variable can then be
	 * accesses like a regular php variable in the template.
	 */
	function set($name, $value){
		$this->pageTemplate->set($name,$value);
	}

	/**
	 * Sends an array of variables to the template file at once. The
	 * arrays must be in an associative array, with the variable name
	 * as a key and the value as the array entry or that key.
	 * An easy way to do this is using the compact() method in php.
	 * Optioanl second variable will clear all currently set variables
	 * in the template
	 */
	function setVars($vars, $clear = false){
		$this->pageTemplate->setVars($vars,$clear);
	}

	/**
	 * Fetches / renders the content for the current template. This
	 * will return the rendered content as a string.
	 */
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

		return $content;
	}


	/**
	 * Adds a given string of html to the head of the page.
	 * Use this to import another javascript or css file that might
	 * be needed for the current module
	 */
	function addHeader($htmlString){
		$this->extraHeaders[]=$htmlString;
	}

	/**
	 * Adds a reference to the given javascript file to the <head>
	 * tag of the current page.
	 */
	function addJavascriptHeader($path){
		$this->addHeader("<script src=\"$path\" type=\"text/javascript\"></script>");
	}

	/**
	 * Adds a reference to the given css file to the <head>
	 * tag of the current page.
	 */
	function addCSSHeader($path){
		$this->addHeader("<link rel=\"stylesheet\" type=\"text/css\" href=\"$path\" />");
	}

	/**
	 * Attempts to retrieve data from a combination of get, post
	 * and a session (in that order). Please see definition
	 * in module class for complete description
	 */
	function getData($var, $default=false, $storeInSession=false){
		return util::getData($var, $default, $storeInSession);
	}

	/**
	 * Sets the result of a event callback. These values will be available
	 * in any template that is invoked on the same page rendering as the
	 * event.
	 * $ok =  Whether the event task succedded
	 * $message = Any message to go along with the result
	 */
	function setEventResult($ok = true, $message = ""){
		$this->eventResult = $ok;
		$this->eventMessage = $message;
	}

	/**
	 * Sets the result of a ajax callback. This will echo the
	 * results as a json object so that it can be parsed by the
	 * calling page.
	 * $ok =  Whether the event task succedded
	 * $message = Any message to go along with the result
	 */
	function setAjaxResult($ok = true, $message = "", $others = null){
		$this->setEventResult($ok, $message);

		if($others == null)
			echo(util::json(array($this->eventResult, $this->eventMessage)));
		else
			echo(util::json(array($this->eventResult, $this->eventMessage, $others), true));
	}

	/**
	 * Given a url, returns an instance of a page class that matches
	 * that url. This will take into consideration control / codebehind
	 * files and instantiate them if they exist.
	 */
	static function loadPage($url){
		$controlFile = false;
		$page = dispatcher::getControlFilePageInstance($url);

		if (empty($page)) {
			echo("Error finding page $url!");
			die();
		}

		$page->loadLayout();
		$page->calculatePaths();
		return $page;
	}
}
?>