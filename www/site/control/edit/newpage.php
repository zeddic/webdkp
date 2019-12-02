<?php
/*===========================================================
Controller

============================================================*/
class pageNewPage extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = false;
	var $title = "Create Page";
	var $system = 1;
	var $layout = "Columns1";

	function init(){
		//$this->addJavascriptHeader($this->binDirectory."js/webpages.css");
		//$this->addCSSHeader($this->binDirectory."css/webpages.css");
	}

	/*===========================================================
	Center Page - Displays list of all webpages
	============================================================*/
	function area2(){
		if(!security::hasAccess("Create Page")) {
			$this->border = 1;
			$this->title = "Access Denined";
			return "You're account is unable to create new pages";
		}


		global $sql;
		global $SiteRoot;

		//add extra headers
		$this->addJavascriptHeader($this->binDirectory."js/newPage.js");
		$this->addCSSHeader($this->binDirectory."css/pageSettings.css");
		//$this->addJavascriptHeader($SiteRoot."js/glider.js");

		//get a list of layouts
		$table = layout::tablename;
		$result = $sql->Query("SELECT * FROM $table ORDER BY id ASC");
		$layouts =  array();
		$layoutNames = array();
		while($row = mysql_fetch_array($result)){
			$layout = & new layout();
			$layout->loadFromRow($row);
			$layouts[]=$layout;
			$layoutNames[]=$layout->name;
		}

		//get a layout if it has already been selected (user hit next then back,
		//so they already entered in the data)
		//$currentLayout = & new layout();
		//$currentLayout->loadFromDatabase($this->getData("layout"));

		//get a list of user groups
		$table = userGroup::tablename;
		$result = $sql->Query("SELECT * FROM $table ");
		$userGroups = array();
		while($row = mysql_fetch_array($result)){
			$userGroup = & new userGroup();
			$userGroup->loadFromRow($row);
			$userGroups[]=$userGroup;
		}

		//get a list of templates
		$table = page::tablename;
		$result = $sql->Query("SELECT id, url, title FROM $table WHERE isTemplate='1' ORDER BY title ASC");
		$templates = array();
		while($row = mysql_fetch_array($result)){
			$tempPage = & new page();
			$tempPage->id = $row["id"];
			$tempPage->url = $row["url"];
			$tempPage->title = $row["title"];
			if($tempPage->title == "")
				$tempPage->title = $tempPage->url;
			$templates[]=$tempPage;
		}

		//pass all data to the template
		$this->border = 1;
		$this->title = "Create New Page";
		$this->set("layouts",$layouts);
		$this->set("layoutNames",$layoutNames);
		$this->set("currentLayout",$currentLayout);
		$this->set("userGroups",$userGroups);
		$this->set("templates",$templates);
		$this->setExistingData();

		return $this->fetch("create.tmpl.php");
	}


	/*===========================================================
	HELPER METHOD
	Helper method for the create page views. This will carray data,
	over from wizard step to wizard step. Ie, if they have already
	entered data in on page/step 1, that data has to be reset
	as they progress through the wizard
	============================================================*/
	function setExistingData()
	{
		$this->set("pagetitle",stripslashes($this->getData("pagetitle")));
		$this->set("pagename",stripslashes($this->getData("pagename")));
		$this->set("path",stripslashes($this->getData("path")));
		$this->set("from",stripslashes($this->getData("from")));
		$this->set("useTemplate",stripslashes($this->getData("useTemplate")));
		$this->set("currentTemplate",stripslashes($this->getData("template")));
		$this->set("layoutid",$this->getData("layoutid"));

		//did this request originate from the webpages control panel?
		if($this->getData("newFileName") != "") {
			//get the name of the file and the path that they were
			//in when they tried to create the page
			$name = $this->getData("newFileName");
			$path = $this->getData("path");
			$this->set("pagetitle",$name);
			$this->set("pagename",$path.$name); //pagename = url
		}

		//set the return to url
		$from = $this->getData("from");
		if($from == "webpages")
			$returnTo = $GLOBALS["SiteRoot"]."ControlPanel/Webpages?path=".$this->getData("path");
		//else
		//	$returnTo = $GLOBALS["SiteRoot"];
		$this->set("returnTo",$returnTo);


		//grap the permissions. On first submit it is an array.
		$permissions = $this->getData("permissions");
		if(!is_array($permissions)) {
			//if not set at all, give it default values
			if ($permissions == "") {
				$permissions = array("everyone");
			}
			//already set, convert it from text form back into its array
			else {
				$permissions = explode(",",$permissions);
			}
		}
		$this->set("permissions",$permissions);
	}

	/*===========================================================
	EVENT HANDLER - Creates a new page for the page. Triggered
	by the last step of the wizard
	============================================================*/
	function ajaxCreateNewPage(){
		if(!security::hasAccess("Create Page"))
			return;

		$result = array();

		$title = $this->getData("pagetitle");
		$url = $this->getData("pagename");
		$layout = $this->getData("layoutid");
		$useTemplate = ($this->getData("useTemplate") == "on");
		$template = $this->getData("template");


		//grap the permissions. On first submit it is an array.
		$permissions = $this->getData("permissions");
		if(!is_array($permissions)) {
			$permissions = array();
		}

		//now to create the new page
		$page = & new page();
		$page->loadFromDatabaseByUrl($url,false);

		//does the page already exist?
		if($page->id == "") {
			$page->title = $title;
			$page->url = $url;
			$page->layout = $layout;
			$page->useTemplate = $useTemplate;
			$page->template = $template;
			$page->area1 = 0;
			$page->area2 = 0;
			$page->area3 = 0;
			$page->area4 = 0;
			$page->area5 = 0;
			$page->saveNew();

			if (!in_array("everyone",$permissions) && count($permissions) > 0 && $page->id != "") {
				$securePage = & new securePage();
				$securePage->pageid = $page->id;
				$securePage->allowedGroups = $permissions;
				$securePage->saveNew();
			}

			//$this->setEventResult(true,"New Page Created");
			$result[0] = 1;
			$result[1] = $page->url;
			if($page->url == "false")
				$result[1] = "";
		}
		else {
			//$this->setEventResult(false,"Page already exists, new page not created");
			$result[0] = 0;
			$result[1] = "A page with the requested path already exists. Please try a different path.";
		}
		//$this->createdPageUrl = $page->url;


		//$json = new Services_JSON();
		$result = util::json($result);
		echo($result);

	}
	/*===========================================================
	AJAX HANDLER - Returns 1 if the given path is already in use,
	0 otherwise
	============================================================*/
	function ajaxPathInUse(){
		if(!security::hasAccess("Create Page"))
			return "0";

		$path = util::getData("path");

		$page = & new page();
		$page->loadFromDatabaseByUrl($path, false);
		if($page->id != "")
			echo("1");
		else
			echo("0");
	}
}

?>