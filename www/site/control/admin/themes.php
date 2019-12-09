<?php
include_once("bin/lib/controlPanel.php");

/*===========================================================
Controller - Main Control Panel Page
Displays the main control panel with links to specific tasks
============================================================*/
class pageThemes extends page {
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
		$this->title = "Themes";
		global $siteStatus;

		//check security
		if(!security::hasAccess("Manage Themes")) {
			$this->border = 1;
			return "You do not have permissions to view this page.";
		}


		themeLibrary::scanForThemes();

		$themes = themeLibrary::getThemes();
		$scanErrors = themeLibrary::getScanErrors();

		$this->set("defaultTheme",$siteStatus->defaultTheme);
		$this->set("themes",$themes);
		$this->set("scanErrors",$scanErrors);

		return $this->fetch("themes.tmpl.php");
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
			$breadcrumbs[] = array("Themes");
		}
		else
			$breadcrumbs = $this->breadcrumbs;
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}


	/*===========================================================
	EVENT
	Sets a new active theme on the site
	============================================================*/
	function eventSetTheme(){
		if(!security::hasAccess("Manage Themes"))
			return;

		$themeid = $this->getData("themeid");
		if(!theme::idExists($themeid)) {
			$this->setEventResult(false,"Cannot switch, Theme no longer exists.");
			return;
		}

		$newTheme = new theme();
		$newTheme->loadFromDatabase($themeid);
		$newTheme->loadLayouts();

		//save the new theme
		$siteStatus = new siteStatus();
		$siteStatus->load();
		$siteStatus->theme = $newTheme;
		$siteStatus->save();

		//update the theme global
		global $siteStatus;
		$siteStatus->defaultTheme = $newTheme;

		//$GLOBALS["theme"] = $siteStatus->theme;

		$this->setEventResult(true,"Theme Selected");
	}

	/*===========================================================
	EVENT
	Reloads a theme, scanning for any new layouts that might have
	been added
	============================================================*/
	function eventReload(){
		if(!security::hasAccess("Manage Themes"))
			return;

		global $siteStatus;
		$theme = $siteStatus->defaultTheme;
		$theme->loadLayouts();
		$this->setEventResult(true,"Theme Reloaded");
	}
}

?>