<?php

/*===========================================================
Controller - Main Control Panel Page
Displays the main control panel with links to specific tasks
============================================================*/
class pagePartLibrary extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = false;
	var $title = "Control Panel";
	var $system = 1;

	/*===========================================================
	Renders the center of the page
	============================================================*/
	function area2(){
		if(!security::hasAccess("Edit Page"))
			return;

		$pageid = $this->getData("pageid");
		$page = & new virtualPage();
		$page->loadFromDatabase($pageid, false);
		$partInfo = partLibrary::getPartDefinitions();

		$this->set("page",$page);
		$this->set("partInfo",$partInfo);
		echo $this->fetch("PartLibrary.tmpl.php");
		die();
	}
}

?>