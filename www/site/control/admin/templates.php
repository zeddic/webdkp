<?php
include_once("bin/lib/controlPanel.php");

/*===========================================================
Controller - Main Control Panel Page
Displays the main control panel with links to specific tasks
============================================================*/
class pageTemplates extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = true;
	var $title = "Control Panel";
	var $system = 1;
	var $layout = "Columns1";

	/*===========================================================
	Init
	============================================================*/
	function init(){
		//$this->addJavascriptHeader($this->binDirectory."js/webpages.css");
		$this->addCSSHeader($this->binDirectory."css/webpages.css");
	}

	/*===========================================================
	Center Page
	Shows a list of all available templates
	============================================================*/
	function area2(){
		global $sql;

		$tablename = page::tablename;
		$result = $sql->Query("SELECT * FROM $tablename WHERE isTemplate='1' ORDER BY url ASC");
		$templates = array();
		while($row = mysqli_fetch_array($result)){
			$page = new page();
			$page->loadFromRow($row);
			$templates[] = $page;
		}
		//themeLibrary::scanForThemes();

		$this->set("templates",$templates);
		$this->title = "Site Templates";
		return $this->fetch("templates.tmpl.php");
	}

	/*===========================================================
	Center Page
	Allows the settings behind a template to be edited
	============================================================*/
	function area2Template(){
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
		return $this->fetch("template.tmpl.php");
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
			$breadcrumbs[] = array("Templates");
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
}

?>