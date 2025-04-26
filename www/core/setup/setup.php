<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Performs setup operations for the site on its first installation.
*/

class setup
{
	/*===========================================================
	MEMBER VARAIBLES
	============================================================*/

	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{

	}

	/*===========================================================
	STATIC METHOD
	Runs first time setup procedures for the site. This is to be
	called when the site has first been installed

	Note: Table structures would have already been created by this
	point and filled with some default values. This is always done
	in the class that wraps that particular table. For example,
	the page class creates the page table, the siteStatus class creates
	the site_status table, etc.

	The setup function's main responsibility is to create any basic pages.
	============================================================*/
	function run(){
		//make sure we know of all themes
		themeLibrary::scanForThemes();

		//setup a default site status
		$siteStatus = new siteStatus();
		$siteStatus->load();
		if(empty($siteStatus->id)) {
			$siteStatus->defaultTheme = theme::getThemeIdBySystemName("default");
			$siteStatus->setup = 0;
			$siteStatus->saveNew();
			$siteStatus->load();

		}

		$siteStatus->defaultTheme->loadLayouts();

		//setup default user grups
		$usergroup = new userGroup();
		$usergroup->name = "Visitor";
		$usergroup->visitor = 1;
		$usergroup->system = 0;
		$usergroup->saveNew();

		$usergroup = new userGroup();
		$usergroup->name = "User";
		$usergroup->default = 1;
		$usergroup->system = 0;
		$usergroup->saveNew();

		$usergroup->name = "Admin";
		$usergroup->system = 1;
		$usergroup->saveNew();

		//setup a defualt account
		$admin = new user();
		$admin->register("Admin","Titan5879","Titan5879");
		$admin->usergroup = userGroup::getUserGroupIdByName("Admin");
		$admin->save();

		//setup some default permissions
		security::ensurePermission("Control Panel","Site");
		security::ensurePermission("Create Page","Site");
		security::ensurePermission("Edit Page","Site");
		security::ensurePermission("Edit Permissions","Site Security");
		security::ensurePermission("Edit User Groups","Site Security");
		security::ensurePermission("Edit Users","Site Security");
		security::ensurePermission("Manage Themes","Site");

		//create a theme map
		$adminMap = new themeMap();
		$adminMap->path = "/admin";
		$adminMap->theme = theme::getThemeIdBySystemName("control_panel");
		$adminMap->saveNew();

		//set flag that setup is complete.
		$siteStatus->setup = 1;
		$siteStatus->save();
	}
}
?>