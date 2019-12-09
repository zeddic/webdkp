<?php
include_once("core/main/layout.php");
include_once("core/main/dispatcher.php");

// TODO(scott): merge this with the page class.
class virtualPage {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;						//database id
	var $url;						//path + filename
	var $title;						//title for the page (will be displayed in browser window)
	var $layout = 0;				//the layout to use to arrange content

	/*===========================================================
	RUNTIME MEMBER VARIABLES
	These values are not saved in the database. They are intended
	to be set at run time, either in extending classes or as needed.
	============================================================*/
	var $extraHeaders = array();	//additional css and js includes that should be placed in the
									//in the <head> tag of the final page. Each of the array entries
									//should be an entire html tag such as "<script .... />"
									//Set at run time based on extending classes and the parts
									//that are present

	/*===========================================================
	Default Constructor
	============================================================*/
	function virtualPage() {
		$this->startTime = util::timerStart();
	}

	/*===========================================================
	Given a url, returns an instance of a page class that matches
	that url. This will take into consideration control / codebehind
	files and instantiate them if they exist.
	============================================================*/
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
	
	function loadLayout() {
		//no layout specified: assume a standard 2 column
		if($this->layout == "" || (is_numeric($this->layout) && $this->layout == 0)) {
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

	/*===========================================================
	handleEvents()
	Iterates through all parts on the pages and calls their
	event handlers.
	============================================================*/
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

	/*===========================================================
	handleEvents()
	Iterates through all parts on the pages and calls their
	ajax handlers
	============================================================*/
	function handleAjax(){
		//see if this page has any event handlers (implemented by extending
		//classes)
		$ajax = util::getData("a");
		if($ajax == "")
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

	/*===========================================================
	render()
	Renders the page, returning the generated page as an html string.
	This will in change call the render methods of each of the parts on
	the page. These render parts will then be organized based on
	the layout for the page.
	============================================================*/
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
		if($this->layout->filename == "")
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
	/*===========================================================
	renderHeader()
	Renders the header - returning the contents as an html string
	============================================================*/
	function renderHeader(){
		global $theme;
		$template = new template();
		$template->setDirectory($theme->getDirectory());
		$template->set("directory",$theme->getAbsDirectory());

		$template->setFile("header.tmpl.php");
		$template->depth = 2;
		return $template->fetch();
	}
	/*===========================================================
	renderFooter()
	Renders the footer - returning the contents as an html string
	============================================================*/
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

	/*===========================================================
	renderLinks()
	Renders the links that provide access to the edit page mode, page
	settings, etc.
	============================================================*/
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
	/*===========================================================
	Renders all parts of the given areay, returning their
	generated content as an array of html strings.
	============================================================*/
	function renderAreaParts($area){

		$toReturn = array();	//array of rendered content

		// Render content from code-behind.
		$fromCodeBehind = $this->renderControlArea($area);
		if (sizeof($fromCodeBehind) > 0 ) {
			$toReturn = array_merge($toReturn,$fromCodeBehind);
		}

		return $toReturn;
	}

	/*===========================================================
	renderControlArea()
	Renders the content from a given area from a physical code
	behind page. This function is implemented in extending classes.
	============================================================*/
	function renderControlArea($area){
		return null;
	}
}
?>