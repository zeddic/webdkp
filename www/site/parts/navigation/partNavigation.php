<?php
include_once("navigationList.php");
/*================================================
The navigation part displays a list of links to
different parts of the site. It provides an editor
where the user can easily edit and arrange the links.

3 different different types of links can be created:
absolute
relative to site
relative to page
The visual look and feel of the navigation part
is defined in the style sheet for the theme so that
the navigation will change from theme to theme.
The list will always be wrapped in a div of class: "navigationContainer"
and css can be used to style. The following is one possible example:

.navigationContainer {
	border-left:1px solid #E2E2E2;
	border-right:1px solid #E2E2E2;
	border-top:1px solid #E2E2E2;
	}

.navigationContainer ul{
	margin-left: 0;
	padding-left: 0;
	list-style-type: none;
	font-family: Verdana, sans-serif;
	font-size: 12pt;
	}

.navigationContainer a {
	display: block;
	padding: 4px;
	background: url(<?=$directory?>images/navback.gif);
	color: #4B4B4B;
	border-bottom: 1px solid #E2E2E2;

	}
.navigationContainer ul .active a{
	background: #F6FFD2;
	}

.navigationContainer a:link, .navigationContainer a:visited{
	text-decoration: none;
	}

.navigationContainer a:hover{
	background: #F6FFD2;
	}

================================================*/
class partNavigation extends part {

	var $defaultView = "Content";
	var $editView = "edit";

	/*==============================================
	VIEW
	Shows the navigation link
	==============================================*/
	function viewContent(){

		//$htmlContent = & new htmlContent();
		//$htmlContent->loadFromDatabaseByInstance($this->id);
		$navigation = & new navigationList();
		$navigation->loadFromDatabasePartId($this->id);

		//determine which link should be highlighted / actived.
		//This should be a link that is part of the current url
		//If two links say they should be active, preference is given
		//to the link that has a longer url.
		$currentActiveIndex = -1;		//the current longest active link found
		$currentActiveLinkLength = 0;	//the length of the logner active link found
		foreach($navigation->list as $index => $link){
			if($link->canView() && $link->isHere()) {
				$linkLen = strlen($link->getLink());
				//found a new active longest link?
				if($currentActiveIndex == -1 || $linkLen > $currentActiveLinkLength) {
					$currentActiveIndex = $index;
					$currentActiveLinkLength = $linkLen;
				}
			}
		}
		//set the 'ishere' flag to true for the longest active link
		if($currentActiveIndex != -1)
			$navigation->list[$currentActiveIndex]->isHere = true;

		$this->set("nav",$navigation);
		$this->set("editPageMode",$this->editPageMode);
		return $this->fetch("content.tmpl.php");
	}

	/*==============================================
	VIEW
	Shows an editor
	==============================================*/
	function viewEdit() {
		$this->renderAlone = 1;
		$this->useBorder = 1;
		$this->border = 1;
		$this->title = "Edit Navigation";
		//$htmlContent = & new htmlContent();
		//$htmlContent->loadFromDatabaseByInstance($this->id);
		$this->addCSSHeader($this->binDirectory."css/style.css");
		$this->addJavascriptHeader($this->binDirectory."js/nav.js");
		//$this->addJavascriptHeader("js/glider.js");
		$navigation = & new navigationList();
		$navigation->loadFromDatabasePartId($this->id);

		//get a list of available permission that we can bind this
		//link to
		$permissions = security::getPermissions();
		$userGroups = security::getUserGroups();

		$this->set("permissions",$permissions);
		$this->set("userGroups",$userGroups);
		$this->set("nav",$navigation);

		return $this->fetch("edit.tmpl.php");
	}

	/*==============================================
	AJAX CALL
	Returns a link object via json
	==============================================*/
	function ajaxGetLink(){
		$linkid = util::getData("linkid");

		$link = & new navigationEntry();
		$link->loadFromDatabase($linkid);

		//$json = new Services_JSON();
		$test = util::json($link);
		echo($test);
	}

	/*==============================================
	EVENT
	Edits the content of a link
	==============================================*/
	function eventEditLink(){
		//get the new link data
		$linkTitle = util::getData("linkTitle");
		$linkUrl = util::getData("linkUrl");
		$linkType = util::getData("linkType");
		$linkid = util::getData("linkid");
		$permission = util::getData("editLinkPermission");
		$userGroups = util::getData("editLinkUserGroups");


		//if the user checked off 'everyone', ignore all other checks for usergroups
		if (sizeof($userGroups) == 0 ||  in_array("everyone",$userGroups)) {
			$userGroups = array("everyone");
		}

		//echo("usergroups: $userGroups");
		//echo("<br /><br />");
		//echo("permission: $permission <br />");

		//die();
		//do error checking
		if($linkTitle == "") {
			echo("0|You must enter a valid link title.");
			die();
		}

		//add the new link
		$link = & new navigationEntry();
		$link->loadFromDatabase($linkid);
		$link->setValues($linkTitle,$linkUrl,$linkType,$this->id);
		$link->permission = $permission;
		$link->userGroups = $userGroups;
		$link->save();

		echo ("1|Changes Saved.");
		die();
	}

	/*==============================================
	EVENT
	Callback send when the order of links changes
	in the edit page preview
	==============================================*/
	function eventEditOrder(){
		$data = util::getData("navPreviewList");
		if(sizeof($data)==0) {
			echo("1");
			die();
		}
		$navList = & new navigationList();
		$navList->loadFromDatabasePartId($this->id);
		$navList->list = $data;
		$navList->save();
		echo("1");
		die();
	}

	/*==============================================
	EVENT
	Creates a new link. (Called via ajax)
	==============================================*/
	function eventNewLink(){
		//TODO: CHECK SECURITY

		//get the new link data
		$linkTitle = util::getData("linkTitle");
		$linkUrl = util::getData("linkUrl");
		$linkType = util::getData("linkType");
		$linkPermission = util::getData("linkPermission");
		$linkUserGroups = util::getData("linkUserGroups");
		$partid = $this->id;

		//if the user checked off 'everyone', ignore all other checks for usergroups
		if (sizeof($linkUserGroups) == 0 || in_array("everyone",$linkUserGroups)) {
			$linkUserGroups = array("everyone");
		}

		//echo($linkPermission);
		//print_r($linkUserGroups);
		//die();

		//do error checking
		if($linkTitle == "") {
			echo("0|You must enter a valid link title.");
			die();
		}

		//add the new link
		$navList = & new navigationList();
		$navList->loadFromDatabasePartId($partid);
		$navList->partid = $partid;

		//add the new link
		$linkid = $navList->addNewLink($linkTitle, $linkUrl, $linkType, $linkPermission, $linkUserGroups);
		if($navList->id == "")
			$navList->saveNew();
		else
			$navList->save();

		echo ("1|".$linkid);
		die();
	}

	/*==============================================
	EVENT
	Deletes a given link. Called via ajax
	==============================================*/
	function eventDeleteLink(){
		$linkid = util::getData("linkid");
		if($linkid == 0) {
			echo("0");
			die();
		}

		$link = & new navigationEntry();
		$link->loadFromDatabase($linkid);
		if($link->id == 0){
			echo("0");
			die();
		}
		$link->delete();
		echo("1");
		die();
	}
	/*==============================================
	UTILITY
	Helper method for the setup program. Allows
	a new part instance to be built programatically.
	Accepts data in the form of an array of arrays, with
	each array entry being another link.
	[0] = array(title, url, linkType, permissionid, userGroups)
	[0] = array(title, url, linkType)

	link type / permissionid, and usergroups are optional.

	Link type is assumed to be 'relativeSite' if
	mising. Other options are 'relativePage' or '' for absolute
	==============================================*/
	function setContent($links){
		//add the new link
		$navList = & new navigationList();
		$navList->loadFromDatabasePartId($this->id);
		$navList->partid = $this->id;

		foreach($links as $link) {
			$linkTitle = $link[0];
			$linkUrl = $link[1];
			//get link type
			$linkType = "relativeSite";
			if(sizeof($link)>2)
				$linkType = $link[2];
			if($linkType != "" && $linkType != "relativePage")
				$linkType = "relativeSite";
			//get required link permission
			if(sizeof($link)>3) {
				$linkPermission = $link[3];
			}
			else {
				$linkPermission = 0;
			}
			//get link user groups
			if(sizeof($link)>4) {
				$linkUserGroups = $link[4];
			}
			else {
				$linkUserGroups = array("everyone");
			}
			$navList->addNewLink($linkTitle, $linkUrl, $linkType, $linkPermission, $linkUserGroups);
		}

		if($navList->id == "") {
			$navList->saveNew();
		}
		else
			$navList->save();

	}
}
?>