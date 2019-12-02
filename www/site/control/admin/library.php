<?php
include_once("bin/lib/controlPanel.php");

/*===========================================================
Controller - Main Control Panel Page
Displays the main control panel with links to specific tasks
============================================================*/
class pageLibrary extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = true;
	var $template = "admin/template";
	var $title = "Control Panel";
	var $system = 1;

	/*===========================================================
	Renders the center of the page
	============================================================*/
	function area2(){
		$this->title = "Part Library";

		//check security
		if(!security::hasAccess("Manage Parts")) {
			$this->border = 1;
			return "You do not have permissions to view this page.";
		}

		$parts = partLibrary::getPartDefinitions();
		$scanErrors = partLibrary::getScanErrors();

		$this->set("parts",$parts);
		$this->set("scanErrors",$scanErrors);

		return $this->fetch("library.tmpl.php");
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
			$breadcrumbs[] = array("Part Library");
		}
		else
			$breadcrumbs = $this->breadcrumbs;
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}

	/*===========================================================
	VIEW
	Reloads a given part, re-adding the entry as if it were just
	scanned.
	============================================================*/
	function eventReload(){
		if(security::hasAccess("Manage Parts"))
			return;

		$partid = $this->getData("part");
		if($partid=="") {
			$this->setEventResult(false,"No valid partid passed.");
			return;
		}

		$ok = partLibrary::reloadPart($partid);
		if(!$ok) {
			$this->setEventResult(false,"An error was encountered while reloading the part");
		}
		$this->setEventResult(true,"Part has been reloaded");
	}
}

?>