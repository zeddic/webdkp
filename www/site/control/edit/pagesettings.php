<?php
//include_once("bin/lib/controlPanel.php");

/*===========================================================
Controller -
Shows a page that allows the user to manage page settings
for a specific page in the database.
============================================================*/
class pagePageSettings extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = false;
	var $layout = "Columns1";
	var $title = "Page Settings";
	var $system = 1;

	/*===========================================================
	Renders the center of the page
	============================================================*/
	function area2(){
		global $SiteRoot;
		if(!security::hasAccess("Edit Page")) {
			$this->border = 1;
			$this->title = "Access Denined";
			return "You're account is unable to edit page settings.";
		}

		$returnto = $this->getData("returnto");
		$pageid = $this->getData("pageid");

		$page = page::loadPageFromId($pageid);

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

		//get a list of available page names
		$table = page::tablename;
		$result = $sql->Query("SELECT * FROM $table WHERE isTemplate='1' ORDER BY title ASC");
		$templates = array();
		while($row = mysqli_fetch_array($result)){
			$tempTemplate = new page();
			$tempTemplate->loadFromRow($row,false);
			if($tempTemplate->title != $page->title) {
				$templates[]=$tempTemplate;
			}
		}

		//get a list of user groups
		$table = userGroup::tablename;
		$result = $sql->Query("SELECT * FROM $table ");
		$userGroup = array();
		while($row = mysqli_fetch_array($result)){
			$userGroup = new userGroup();
			$userGroup->loadFromRow($row);
			$userGroups[]=$userGroup;
		}

		//load up the secure page settings for this page
		//(ie - what usergroups have permission)
		$securePage = new securePage();
		$securePage->loadFromDatabaseByPageid($page->id);

		//determine where we should return to after edits are complete.
		//Either back to the page, or back to the web pages lists
		$returnto = $this->getData("returnto");
		if($returnto == "")
			$returntoUrl = $SiteRoot.$page->url."?editpage=1";
		else if($returnto == "webpages")
			$returntoUrl = $SiteRoot."admin/webpages?path=".$this->getData("path");

		$this->border = 1;

		$this->addJavascriptHeader($this->binDirectory."js/pageSettings.js");
		$this->addCSSHeader($this->binDirectory."css/pageSettings.css");
		$this->title = "Page Settings";
		$this->set("layouts",$layouts);
		$this->set("layoutNames",$layoutNames);
		$this->set("page",$page);
		$this->set("templates",$templates);
		$this->set("userGroups",$userGroups);
		$this->set("securePage",$securePage);
		$this->set("returnto",$returnto);
		$this->set("returntoUrl",$returntoUrl);
		return $this->fetch("pageSettings.tmpl.php");
	}

	/*===========================================================
	EVENT HANDLER - Updates
	============================================================*/
	function ajaxUpdatePageSettings(){
		if(!security::hasAccess("Edit Page"))
			return;

		$title = $this->getData("pagetitle");
		$useTemplate = ($this->getData("useTemplate")=="on");
		$templatePage = $this->getData("templatepage");
		$permissions = $this->getData("permissions");
		$pageid = $this->getData("pageid");

		//load the page we are trying to edit
		$page = new page();
		$page->loadFromDatabase($pageid);

		//update the layout
		//$layoutData = explode("|",$this->getData("layout"));
		$page->layout = util::getData("layoutid");//$layoutData[0];

		//update the other page fields
		$page->title = $title;
		$page->useTemplate = $useTemplate;
		if($useTemplate==1)
			$page->template = $templatePage;
		$page->save();

		//update the permissions
		$everyone = $this->getData("permissions_everyone");
		//if everyone is checked, delete the secure page entry
		if($everyone!="") {
			if(securePage::exists($page)){
				securePage::deleteSecurePage($page);
			}
		}
		else {
			$permissions = $this->getData("permissions");
			if(!is_array($permissions)) {
				$permissions = array();
			}
			$securePage = new securePage();
			$securePage->loadFromDatabaseByPageid($page->id);
			$securePage->allowedGroups = $permissions;
			if($securePage->id == "") {
				$securePage->pageid = $page->id;
				$securePage->saveNew();
			}
			else {
				$securePage->save();
			}
		}

		$this->setEventResult(true,"Saved");
	}

}

?>