<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
include_once("core/main/layout.php");
include_once("core/main/dispatcher.php");

class virtualPage {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;						//database id
	var $url;						//path + filename
	var $title;						//title for the page (will be displayed in browser window)
	var $layout = 0;				//the layout to use to arrange content

	var $isTemplate = 0;			//is this page a template? (other pages can inherit information from it)
	var $useTemplate = 0;			//use a template? (this page will inherit parts from a template)
	var $template = 0;				//id of a template to inherit parts from
	var $system = 0;				//is this a system page? System pages are protected and can not be edited
	var $actingAsTemplate = false; // unknown

	//The following arrays identify the content and order on the content on the page.
	//Each array entry represents either a part, or static content. The order of these
	//items in the array signals how the content will be ordered on the page.
	//This data is stored in the database as an array of ids, each id representing a
	//part instance.
	// The id of 0 is reserved to represent where parts from a
	// template should be placed.
	// The id of -1 is reserved to represent where real
	// content should be placed (if this class is extended by a control page class).
	var $area1= array();
	var $area2= array();
	var $area3= array();
	var $area4= array();
	var $area5= array();

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

	var $staticProperties = array();//An array of property names that have been hardcoded
									//in a control file page, overriding anything that was
									//in the database during a load. Set during LoadRow()
	var $isControlFile = 0;

	const tablename =  "site_pages";//defined above

	/*===========================================================
	Default Constructor
	============================================================*/
	function virtualPage() {
		$this->tablename = virtualPage::tablename;
		$this->startTime = util::timerStart();
	}

	/*===========================================================
	Given a url, returns an instance of a page class that matches
	that url. This will take into consideration control / codebehind
	files and instantiate them if they exist.
	============================================================*/
	static function loadPage($url, $actingAsTemplate = false){
		$controlFile = false;
		$page = dispatcher::getControlFilePageInstance($url);

		if($page == null) {
			$page = new virtualPage();
		}
		else {
			//for control pages, always use the control file as a basis
			//for looking it up in the database
			$url = $page->controlFile;
			fileUtil::trimLeftDir($url);
			$url = fileutil::stripExt($url);
			$controlFile = true;
		}

		$page->actingAsTemplate = $actingAsTemplate;
		$page->loadFromDatabaseByUrl($url);
		if($controlFile && $page->id == "") {
			$page->url = $url;
			$page->saveNew();
			$page->calculatePaths();
		}

		return $page;
	}
	/*===========================================================
	Given a pages database id, returns an instance of a page class that matches
	that url. This will take into consideration control / codebehind
	files and instantiate them if they exist.
	============================================================*/
	static function loadPageFromId($id, $actingAsTemplate = false){
		//first, get the url of the page with the given id
		global $sql;
		$id = sql::Escape($id);
		$tablename = virtualPage::tablename;
		$url = $sql->QueryItem("SELECT url FROM $tablename WHERE id='$id'");

		//now use load page
		$page =  virtualPage::loadPage($url, $actingAsTemplate);

		return $page;
	}

	/*===========================================================
	Loads the information for this class from the backend database
	using the passed $id;
	$id 			the id of this page in the database
	============================================================*/
	function loadFromDatabase($id)
	{
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'");
		$this->loadFromRow($row);
	}
	
	/*===========================================================
	Loads the information for a page using the given url.
	$url 			the url of this page in the database
	============================================================*/
	function loadFromDatabaseByUrl($url)
	{
		global $sql;

		//trim the last "/" on a url path if it is present
		if($url[strlen($url)-1] == "/")
			$url = substr($url,0,strlen($url));

		$url = sql::Escape($url);
		$tablename = virtualPage::tablename;
		$row = $sql->QueryRow("SELECT * FROM $tablename WHERE url='$url'");
		$this->loadFromRow($row);
	}

	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow(&$row) {

		//load easy values from the row
		$this->id=$row["id"];

		$this->url = strtolower($row["url"]);
		if($this->title == "")
			$this->title = $row["title"];
		else
			$this->staticProperties[]="title";
		if($this->isTemplate == "")
			$this->isTemplate = $row["isTemplate"];
		else
			$this->staticProperties[]="isTemplate";
		if($this->useTemplate == "")
			$this->useTemplate = $row["useTemplate"];
		else
			$this->staticProperties[]="useTemplate";
		if($this->system == "")
			$this->system = $row["system"];
		else
			$this->staticProperties[]="system";

		//load a template for this page (as needed)
		if($this->template == "")
			$this->template = $row["template"];
		else
			$this->staticProperties[]="template";

		if($this->useTemplate) {
			//if no template id specified, revert to master template
			if($this->template == "" || (is_numeric($this->template) && $this->template == 0)) {
				$this->template = virtualPage::loadPage("Templates/MasterTemplate",true);
			}
			else if ( is_numeric($this->template) ) {
				$this->template = virtualPage::loadPageFromId($this->template,true);
			}
			else {
				$this->template = virtualPage::loadPage($this->template,true);
			}

		}

		//load the layout for the page
		if($this->layout == "")
			$this->layout = $row["layout"];
		else
			$this->staticProperties[]="layout";


		//no layout specified
		if($this->layout == "" || (is_numeric($this->layout) && $this->layout == 0)) {
			//no layout specified, get it from the template
			if($this->useTemplate) {
				$this->layout = $this->template->layout;
				$this->layout->inherited = true;
			}
			//or assume a standard 2 column
			else {
				$layoutid = layout::getLayoutIdByName("Columns2");
				$this->layout = new layout();
				$this->layout->loadFromDatabase($layoutid);
			}
		}

		//layout id specified
		else if(is_numeric($this->layout)) {
			$layoutid = $this->layout;
			//layout id specified, load it
			$this->layout = new layout();
			$this->layout->loadFromDatabase($layoutid);
			$this->layout->inherited = false;
		}
		//layout name (string) specified
		else {
			$layoutid = layout::getLayoutIdByName($this->layout);
			$this->layout = new layout();
			$this->layout->loadFromDatabase($layoutid);
		}

		$this->area1 = explode(",", $row["area1"]);
		$this->area2 = explode(",", $row["area2"]);
		$this->area3 = explode(",", $row["area3"]);
		$this->area4 = explode(",", $row["area4"]);
		$this->area5 = explode(",", $row["area5"]);
	}

	/*===========================================================
	quickLoadFromRow($row)
	Does a very quick load from row, only getting limited data.
	Used for circumstances where all pages will be listed
	and it isn't neccessary instantiate child instances
	============================================================*/
	function quickLoadFromRow($row){
		$this->id=$row["id"];
		$this->url = $row["url"];
		$this->title = $row["title"];
		$this->isTemplate = $row["isTemplate"];
		$this->useTemplate = $row["useTemplate"];
		$this->system = $row["system"];
		$this->template = $row["template"];
		$this->checkIsControlFile();
	}

	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save($new = false)
	{
		global $sql;
		$url = sql::Escape($this->url);
		$title = sql::Escape($this->title);
		$isTemplate = sql::Escape($this->isTemplate);
		$useTemplate = sql::Escape($this->useTemplate);
		$system = sql::Escape($this->system);
		$area1 = sql::Escape(implode(",",$this->getPartIds($this->area1)));
		$area2 = sql::Escape(implode(",",$this->getPartIds($this->area2)));
		$area3 = sql::Escape(implode(",",$this->getPartIds($this->area3)));
		$area4 = sql::Escape(implode(",",$this->getPartIds($this->area4)));
		$area5 = sql::Escape(implode(",",$this->getPartIds($this->area5)));
		//Convert layout & template to ints
		if(is_a($this->layout,"layout"))
			$layout = $this->layout->id;
		else
			$layout = $this->layout;
		if(is_a($this->template,"virtualPage"))
			$template = $this->template->id;
		else
			$template = $this->template;

		$sql->Query("UPDATE $this->tablename SET
					url = '$url',
					title = '$title',
					isTemplate = '$isTemplate',
					useTemplate = '$useTemplate',
					system = '$system',
					template = '$template',
					layout = '$layout',
					area1 = '$area1',
					area2 = '$area2',
					area3 = '$area3',
					area4 = '$area4',
					area5 = '$area5'
					WHERE id='$this->id'");
	}
	/*===========================================================
	saveNew()
	Saves data into the backend database as a new row entry. After
	calling this method $id will be filled with a new value
	matching the new row for the data
	============================================================*/
	function saveNew()
	{
		global $sql;
		$url = sql::Escape($this->url);
		$title = sql::Escape($this->title);
		$isTemplate = sql::Escape($this->isTemplate);
		$useTemplate = sql::Escape($this->useTemplate);
		$system = sql::Escape($this->system);
		$area1 = sql::Escape(implode(",",$this->getPartIds($this->area1)));
		$area2 = sql::Escape(implode(",",$this->getPartIds($this->area2)));
		$area3 = sql::Escape(implode(",",$this->getPartIds($this->area3)));
		$area4 = sql::Escape(implode(",",$this->getPartIds($this->area4)));
		$area5 = sql::Escape(implode(",",$this->getPartIds($this->area5)));
		//Convert layout & template to ints
		if(is_a($this->layout,"layout"))
			$layout = $this->layout->id;
		else
			$layout = $this->layout;
		if(is_a($this->template,"virtualPage"))
			$template = $this->template->id;
		else
			$template = $this->template;

		$tablename = virtualPage::tablename;
		$sql->Query("INSERT INTO $tablename SET
					url = '$url',
					title = '$title',
					isTemplate = '$isTemplate',
					useTemplate = '$useTemplate',
					system = '$system',
					template = '$template',
					layout = '$layout',
					area1 = '$area1',
					area2 = '$area2',
					area3 = '$area3',
					area4 = '$area4',
					area5 = '$area5'");
		$this->id=$sql->GetLastId();
	}

	/**
	 * Returns the order of parts to be shown on the page.
	 * Shows inherited content, then current page content.
	 */
	function getPartIds() {
		// Note: this is a hack until parts are fully removed.
		return ["0", "-1"];
	}

	/*===========================================================
	delete()
	Deletes the row with the current id of this instance from the
	database
	============================================================*/
	function delete()
	{
		global $sql;
		$sql->Query("DELETE FROM $this->tablename WHERE id = '$this->id'");
	}
	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if a page exists with the given url
	============================================================*/
	function exists($url)
	{
		global $sql;
		$url = sql::escape($url);
		$tablename = virtualPage::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE pagename='$url'");
		return ($exists != "");
	}

	/*===========================================================
	existsWithId()
	STATIC METHOD
	Returns true if a page exists with the given database id
	============================================================*/
	function existsWithId($id)
	{
		global $sql;
		$id = sql::escape($id);
		$tablename = virtualPage::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$id'");
		return ($exists != "");
	}
	/*===========================================================
	getPageIdFromUrl()
	STATIC METHOD
	Returns the id of a page with the given url. If no page
	exists, returns an empty string
	============================================================*/
	function getPageIdFromUrl($url){
		global $sql;
		$tablename = virtualPage::tablename;
		$url = sql::Escape($url);
		$result = $sql->QueryItem("SELECT id FROM $tablename WHERE url='$url'");
		return $result;
	}
	/*===========================================================
	getTemplateId()
	STATIC METHOD
	Returns the id of a given template file.
	============================================================*/
	function getTemplateId($templatename){
		$url = "Templates/".$templatename;
		return virtualPage::getPageIdFromUrl($url);
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

		//get all the extra headers ready
		if($this->useTemplate)
			$this->extraHeaders = array_merge($this->extraHeaders,$this->template->extraHeaders);

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
		$template->set("pageid",$this->id);
		$template->set("system",$this->system);
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
		$template->set("editPageMode",$this->inEditPageMode());
		$template->set("pageid",$this->id);
		$template->set("system",$this->system);
		$template->set("directory",$theme->getAbsDirectory());
		$template->set("systemDirectory",$theme->getDirectory());

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
		$template->set("editPageMode",$this->inEditPageMode());
		$template->set("pageid",$this->id);
		$template->set("system",$this->system);
		$template->set("queryCount",$sql->queryCount);
		$template->set("processTime",$processTime);
		$template->set("directory",$theme->getAbsDirectory());
		$template->set("systemDirectory",$theme->getDirectory());
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

		// Render content from parent template.
		if ($this->useTemplate) {
			$fromTemplate = $this->template->renderAreaParts($area);
			if (sizeof($fromTemplate) > 0 ) {
				$toReturn = array_merge($toReturn, $fromTemplate);
				if($this->template->renderAlone != "")
					$this->renderAlone = $this->template->renderAlone;
			}
		}

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

	/*===========================================================
	inEditPageMode()
	Returns true if the page is currently in edit mode. While in
	edit mode the current user has the ability to rearrange current

	============================================================*/
	function inEditPageMode(){
		if($this->system==1 )
			return false;
		return (util::getData("editpage",false,true)==1);
	}

	/*===========================================================
	hardcodedProperty()
	Returns true if the given property / member variable name is
	using a hardcoded value from a control file.
	============================================================*/
	function hardcodedProperty($var){
		return in_array($var, $this->staticProperties);
	}
	/*===========================================================
	checkIsControlFile()
	Checks if this page has a control file. Stores true/false
	result in $this->isControlFile
	============================================================*/
	function checkIsControlFile(){
		$this->isControlFile = (dispatcher::getControlFile($this->url) !== false);
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(virtualPage::tablename)) {
			$tablename = virtualPage::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`url` VARCHAR (512) NOT NULL,
						`title` VARCHAR (512) NOT NULL,
						`useTemplate` INT DEFAULT '1' NOT NULL,
						`template` INT DEFAULT '1' NOT NULL,
						`isTemplate` INT DEFAULT '0' NOT NULL,
						`layout` INT DEFAULT '0' NOT NULL,
						`system` INT DEFAULT '0' NOT NULL,
						`area1` VARCHAR (512) NOT NULL,
						`area2` VARCHAR (512) NOT NULL,
						`area3` VARCHAR (512) NOT NULL,
						`area4` VARCHAR (512) NOT NULL,
						`area5` VARCHAR (512) NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
virtualPage::setupTable();
?>