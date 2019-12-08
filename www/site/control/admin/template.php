<?php
include_once("bin/lib/controlPanel.php");
/*===========================================================
Controller - ControlPanel Template
Defines a page with control panel navigation on the side using
the system template. All other control panel pages extend from
this one.
============================================================*/
class pageTemplate extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $isTemplate = 1;
	var $layout = "Columns1";

	/*===========================================================
	Perform initilization tasks
	============================================================*/
	function init(){
		$this->addCSSHeader($this->binDirectory."css/style.css");

		global $SiteRoot;

		$loginPage = $SiteRoot."admin/login";
		if(!security::hasAccess("Control Panel" && $_SERVER["PHP_SELF"]!= $loginPage) ) {
			//echo("here");
			//global $siteUser;
			//print_r($siteUser);
			//die();
			util::forward($loginPage);
		}
	}

}