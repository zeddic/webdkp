<?php
include_once("bin/lib/controlPanel.php");

/*===========================================================
Controller - Main Control Panel Page
Displays the main control panel with links to specific tasks
============================================================*/
class pageIndex extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $title = "Control Panel";
	var $layout = "Columns1";

	/*===========================================================
	Renders the center of the page
	============================================================*/
	function area2(){
		$controlPanel = new controlPanel();
		$controlPanel->load();
		$this->set("categories",$controlPanel->categories);

		return $this->fetch("main.tmpl.php");
	}

	function area3(){
		global $SiteRoot;
		$breadcrumbs = array();
		$breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$breadcrumbs[] = array("Main");
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}
}
?>