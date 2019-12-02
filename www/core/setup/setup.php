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
	function setup()
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

	The setup function's main responsibility
	is to create any basic pages and put default parts on them.
	============================================================*/
	function run(){
		//make sure we know of all themes
		themeLibrary::scanForThemes();

		//make sure we know of all the parts
		$partLibrary = & new partLibrary();
		$partLibrary->scanForNewParts();

		//setup a default site status
		$siteStatus = & new siteStatus();
		$siteStatus->load();
		if($siteStatus->id == "") {
			$siteStatus->defaultTheme = theme::getThemeIdBySystemName("default");
			$siteStatus->setup = 0;
			$siteStatus->saveNew();
			$siteStatus->load();

		}

		$siteStatus->defaultTheme->loadLayouts();

		//setup default user grups
		$usergroup = & new userGroup();
		$usergroup->name = "Visitor";
		$usergroup->visitor = 1;
		$usergroup->system = 0;
		$usergroup->saveNew();

		$usergroup = & new userGroup();
		$usergroup->name = "User";
		$usergroup->default = 1;
		$usergroup->system = 0;
		$usergroup->saveNew();

		$usergroup->name = "Admin";
		$usergroup->system = 1;
		$usergroup->saveNew();

		//setup a defualt account
		$admin = & new user();
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
		security::ensurePermission("Manage Parts","Site");
		security::ensurePermission("Manage Themes","Site");

		//create any baseline templates
		setup::createTemplates();

		//setup general pages and control panel
		setup::setupGeneralPages();

		//create a theme map
		$adminMap = & new themeMap();
		$adminMap->path = "/admin";
		$adminMap->theme = theme::getThemeIdBySystemName("control_panel");
		$adminMap->saveNew();

		//set flag that setup is complete.
		$siteStatus->setup = 1;
		$siteStatus->save();
	}

	/*===========================================================
	STATIC METHOD
	Sets up the basic site templates
	============================================================*/
	function createTemplates(){
		//create a master template
		$masterTemplate = & new virtualPage();
		$masterTemplate->url = "Templates/MasterTemplate";
		$masterTemplate->title = "Master Template";
		$masterTemplate->layout = layout::getLayoutIdByName("Columns2");
		$masterTemplate->isTemplate = 1;
		//create a default navigation part
		/*$navigation = setup::createPart("navigation");
		if($navigation!=0) {
			$navigation->border = 0;
			$navigation->title = "Navigation";
			$navigation->save();
			//setup the links that should be in the navigation
			$navigation->setContent(array(
											array("Home",""),
											array("Control Panel","admin","relativeToSite",permission::getPermissionIdByName("Control Panel"))
										));

			$masterTemplate->area1[] = $navigation->id;
		}
		//create a login part
		$login = setup::createPart("login");
		if($login!=0) {
			$login->border = 2;
			$login->setOption("Size","Small");
			$login->setOption("Grab Focus",0);
			$login->save();

			$masterTemplate->area1[] = $login;
		}*/
		$masterTemplate->saveNew();
	}

	/*===========================================================
	STATIC METHOD
	Sets up general pages, such as the home page
	============================================================*/
	function setupGeneralPages(){
		//create the home page
		$page = & new virtualPage();
		$page->url = "index";
		$page->title = "Home";
		$page->useTemplate = 1;
		$page->template = page::getTemplateId("MasterTemplate");
		//give it a welcome part
		/*$html = setup::createPart("html");
		if($html!=0) {
			$template = & new template();
			$content = $template->fetch("core/setup/welcome.tmpl.php");
			$html->title = "Welcome!";
			$html->border = 1;
			$html->setContent($content);
			$html->save();
			$page->area2[] = $html->id;
		}*/
		$page->saveNew();
	}

	/*===========================================================
	STATIC METHOD
	Creates a module with the given system name and returns
	the id of its newly created entry in the module instance database.
	============================================================*/
	function createPart($name,$border=-1){

		//create a new instance of the requested module
		$definition = & new partDefinition();
		$definition->loadFromDatabaseBySystemName($name);

		if($definition->id == 0 ) {
			return 0;
		}
		$part = $definition->createInstance();
		if($border!= -1){
			$module->border = $border;
		}
		$part->save();
		return $part;
	}
}
?>