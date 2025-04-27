<?php
include_once("bin/lib/controlPanel.php");
include_once("util/pager.php"); //adds support for creating pages of data
include_once("lib/dkp/dkpUtil.php");
/*===========================================================
Controller - Main Control Panel Page
Displays the main control panel with links to specific tasks
============================================================*/
class pageSecurity extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $title = "Control Panel";
	var $layout = "Columns2";

	/*===========================================================
	Center page. Default view is a page of all users and a
	searching input box.
	============================================================*/
	function area2(){
		if(!security::hasAccess("Edit Users")) {
			$this->border = 1;
			$this->title = "Users";
			return "You do not have permissions to edit site users";
		}

		global $sql;
		$pager = new pager("users_page",20);
		$pager->pageUrl = $_SERVER["PHP_SELFDIR"]."users";
		$pager->useDirectoryLinks = true;
		$pager->carryQueryStrings = false;

		//set the breadcrumbs
		global $SiteRoot;
		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Security",$SiteRoot."admin/security");
		$this->breadcrumbs[] = array("Users");

		//Check to see if the user entered any search terms
		if($this->getData("users_clearsearch")){
			//user requested to clear their previous search
			util::clearFromSession("users_search");
		}
		$search = $this->getData("users_search");
		$searchString = $this->getSearchString($search);

		if($searchString != "") {
			$searchString = " AND ".$searchString;
		}
		//get any sort requests
		$sort = $this->getData("users_sort","username");
		$sortType = $this->getData("users_sorttype","asc");
		if(!in_array($sort,array("username","email","usergroup","gname","gserver")))
			$sort = "username";
		if(!in_array($sortType,array("asc","desc")))
			$sortType = "asc";
		util::saveInSession("users_sort",$sort);
		util::saveInSession("users_sorttype",$sortType);
		$sortString = "ORDER BY $sort $sortType";

		//update the pager
		$pager->appendAfterPage = "/$sort/$sortType";

		//get the current page. Put this in a loop in case we need to backtrack
		//to a previous page because the current requested page is empty.
		//This might occur if a user deleted the last user on a given page
		$table = user::tablename;
		do {
			$result = $sql->Query("SELECT *, security_users.id as userid FROM security_users, dkp_guilds
								   WHERE security_users.guild = dkp_guilds.id
							  	   $searchString $sortString $pager->pageQueryString");
			if($sql->a_rows > 0 || $pager->page ==1)
				break;
			//our request got no data...
			//lets try moving back a page
			$pager->page--;
			$pager->handlePages($pager->page);
		}while(true);

		//we have user data now, parse it into an array
		$users = array();
		while($row = mysqli_fetch_array($result)){
			$user = new user();
			$user->loadFromRow($row);
			$user->id = $row["userid"] ?? null;
			$user->guildname = $row["gname"] ?? null;
			$user->servername = $row["gserver"] ?? null;
			$user->guildurl = dkpUtil::GetGuildUrlByName($user->guildname, $user->servername);
			$users[]=$user;
		}

		//get some final data for the template
		$numRows = $sql->QueryItem("SELECT count(security_users.id) FROM security_users , dkp_guilds
									WHERE security_users.guild = dkp_guilds.id
									 $searchString");
		$pageLinks = $pager->getRawHtmlPageLinks($numRows);

		//$showNewUser = isset($this->newuserResult) && $this->newuserResult!==0;

		$page = $pager->page;

		//fetch the template
		$this->setVars(compact("users","pageLinks","search","sort","sortType","userGroups",
								"showNewUser","fillInUserData","newUserString","newUserOk","page"));
		$this->title = "Site Security";

		return $this->fetch("security/users.tmpl.php");
	}
	/*===========================================================
	Utility method - given a term to search for this will return
	a query clause that can be appended to the end of a sql query.
	============================================================*/
	function getSearchString($searchFor){
		if($searchFor!=""){
			//user entered a search. Determine if it is a group search..
			if(stripos($searchFor,"group:")!==false) {
				$temp = explode(":",$search);
				if (count($temp>1)) {
					//yes, its a group search, append a clause to only display
					//users in the given group
					$id = userGroup::getUserGroupIdByName($temp[1]);
					$groupSearch = "OR usergroup='$id'";
				}
			}
			//no group search. Search for username or email
			$searchString = "( username LIKE '%$searchFor%' OR gname LIKE '%$searchFor%' OR email LIKE '%$searchFor%' $groupSearch )";
			//save search in session
			util::saveInSession("users_search",$searchFor);
		}
		return $searchString;
	}

	/*===========================================================
	TOP LEFT - Displays links to the different security sections.
	============================================================*/
	function area1_1(){
		$this->title = "Security";
		$this->border = 5;
		return $this->fetch("security/sidebar.tmpl.php");
	}

	/*===========================================================
	LEFT HAND SIDE - Displays a search box that can be used to
	search the user table.
	============================================================*/
	function area1_2(){
		if(!security::hasAccess("Edit Users"))
			return "";

		global $sql;
		//Check to see if the user entered any search terms
		if($this->getData("users_clearsearch")){
			//user requested to clear their previous search
			util::clearFromSession("users_search");
		}
		$search = $this->getData("users_search");
		$searchString = $this->getSearchString($search);
		if($searchString != "")
			$searchString = " AND ".$searchString;
		$table = user::tablename;

		$hits = $sql->QueryItem("SELECT count(security_users.id) FROM security_users, dkp_guilds
								 WHERE security_users.guild = dkp_guilds.id
								 $searchString");


		$this->title = "User Search";
		$this->border = 4;
		$this->set("hits",$hits);
		$this->set("search",$search);
		return $this->fetch("security/usersSearch.tmpl.php");
	}

	/*===========================================================
	Left hand side - shows a window that allows a new user to be
	created.
	============================================================*/
	function area1_3(){
		if(!security::hasAccess("Edit Users"))
			return "";

		$userGroups = security::getUserGroups();
		$fillInUserData = (isset($this->newuserResult) && $this->newuserResult!=0);
		$newUserString = user::getRegisterErrorString($this->newuserResult);
		$newUserOk = ($this->newuserResult == user::REGISTER_OK);

		$this->title = "New User";
		$this->border = 4;
		$this->set("userGroups",$userGroups);
		$this->set("newUserResult",$this->newuserResult);
		$this->set("newUserOk",$newUserOk);
		$this->set("newUserString",$newUserString);
		$this->set("fillInUserData",$fillInUserData);
		return $this->fetch("security/newUser.tmpl.php");
	}

	/*===========================================================
	VIEW
	Displays a view where the settings for an individual user can be
	modified
	============================================================*/
	function area2User(){

		if(!security::hasAccess("Edit Users"))
			util::forward($_GLOBALS["SiteRoot"]."admin");

		global $sql;

		$userid = $this->getData("userid");
		$user = new user();
		$user->loadFromDatabase($userid);

		$userGroups = security::getUserGroups();

		//set the breadcrumbs
		global $SiteRoot;
		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Security",$SiteRoot."admin/security");
		$this->breadcrumbs[] = array("Users",$SiteRoot."admin/users");
		$this->breadcrumbs[] = array($user->username);

		$this->set("user",$user);
		$this->set("userGroups",$userGroups);
		$this->set("message",$this->message);
		$this->title = "Site Security";
		return $this->fetch("security/user.tmpl.php");
	}

	/*===========================================================
	VIEW
	Views a list of groups in the system. Allows the user to create
	or remove groups.
	============================================================*/
	function area2Groups(){

		if(!security::hasAccess("Edit User Groups"))
			util::forward($_GLOBALS["SiteRoot"]."admin/security");

		global $sql;

		$table = userGroup::tablename;
		$result = $sql->Query("SELECT * FROM $table ORDER BY name ASC");
		$userGroups = array();
		while($row = mysqli_fetch_array($result)){
			$userGroup = new userGroup();
			$userGroup->loadFromRow($row);
			$userGroups[]=$userGroup;
		}
		//set the breadcrumbs
		global $SiteRoot;
		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Security",$SiteRoot."admin/security");
		$this->breadcrumbs[] = array("User Groups");

		$this->title = "Site Security";
		$this->set("userGroups",$userGroups);
		$this->set("message",$this->message);
		return $this->fetch("security/userGroups.tmpl.php");
	}

	/*===========================================================
	VIEW
	Displays a a specific user group
	============================================================*/
	function area2UserGroup(){
		if(!security::hasAccess("Edit User Groups"))
			util::forward($_GLOBALS["SiteRoot"]."admin/security");

		global $sql;

		$groupid = $this->getData("groupid");

		$userGroup = new userGroup();
		$userGroup->loadFromDatabase($groupid);

		global $SiteRoot;
		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Security",$SiteRoot."admin/security");
		$this->breadcrumbs[] = array("User Groups",$SiteRoot."admin/usergroups");
		$this->breadcrumbs[] = array($userGroup->name);

		$this->title = "Site Security";
		$this->set("message",$this->message);
		$this->set("userGroup",$userGroup);
		return $this->fetch("security/userGroup.tmpl.php");
	}

	/*===========================================================
	VIEW
	Displays group permissions and the groups they are assigned to
	============================================================*/
	function area2Permissions(){
		if(!security::hasAccess("Edit Permissions"))
			util::forward($_GLOBALS["SiteRoot"]."admin/security");

		$this->title = "Security - Permissions";

		global $sql;

		$table = permission::tablename;
		$result = $sql->Query("SELECT * FROM $table ORDER BY category, name");
		$permissions = array();
		while($row = mysqli_fetch_array($result)) {
			$permission = new permission();
			$permission->loadFromRow($row);
			if($permissions[$permission->category]=="") {
				$permissions[$permission->category]= array();
			}
			$permissions[$permission->category][]=$permission;
		}

		$userGroups = security::getUserGroups();

		global $SiteRoot;
		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Security",$SiteRoot."admin/security");
		$this->breadcrumbs[] = array("Permissions");

		$this->set("userGroups",$userGroups);
		$this->set("permissions",$permissions);
		return $this->fetch("security/permissions.tmpl.php");
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
			$breadcrumbs[] = array("Security");
		}
		else
			$breadcrumbs = $this->breadcrumbs;
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}


	/*===========================================================
	EVENT - DELETES A USER
	============================================================*/
	function eventDeleteUser(){

		if(!security::hasAccess("Edit Users"))
			return;

		$userid = $this->getData("userid");
		$user = new user();
		$user->loadFromDatabase($userid);

		if(empty($user->id))
			return;

		$user->delete();

		//see if there are any others users with this guild
		global $sql;
		$guildid = $user->guild;

		if(empty($guildid))) {
			return $this->setEventResult(true, "User Deleted, but it was not tied to any guild.");
		}

		$id = $sql->QueryItem("SELECT id FROM security_users WHERE guild='$guildid'");
		if(empty($id)) {
			//no more guilds
			$this->setEventResult(true, "Last User For Guild - Deleting Guild");
			dkpUtil::DeleteGuild($guildid);
		}
		else {
			$this->setEventResult(true, "User Deleted, but other user ($id) still use the guild");
		}
	}


	/*===========================================================
	EVENT - SAVES EDITS TO A USER
	============================================================*/
	function eventEditUser(){
		if(!security::hasAccess("Edit Users"))
			return;

		$userid = $this->getData("userid");
		$user = new user();
		$user->loadFromDatabase($userid);
		$user->username = $this->getData("username");
		$user->email =$this->getData("email");
		$user->firstname = $this->getData("firstname");
		$user->lastname = $this->getData("lastname");
		$user->usergroup = $this->getData("usergroup");
		$user->save();
		$this->setEventResult(true, "Saved");
	}

	/*===========================================================
	EVENT - RESETS A USERS PASSWORD
	============================================================*/
	function eventResetUserPassword(){
		if(!security::hasAccess("Edit Users"))
			return;

		$newpassword = $this->getData("password");
		$newpassword2 = $this->getData("password2");

		if($newpassword != $newpassword2){
			$this->setEventResult(false, "Passwords did not match - password not reset");
			return;
		}

		$userid = $this->getData("userid");
		$user = new user();
		$user->loadFromDatabase($userid);
		$user->setPassword($newpassword);
		$user->save();
		$this->setEventResult(true, "Password Reset");
	}

	/*===========================================================
	EVENT - ADDS A NEW USER
	============================================================*/
	function eventAddUser(){
		if(!security::hasAccess("Edit Users"))
			return;

		$password1 = $this->getData("password");
		$password2 = $this->getData("password2");
		$username = $this->getData("username");

		$user = new user();
		$result = $user->register($username, $password1, $password2);
		$this->newuserResult = $result;
		//echo("here! $this->newuserResult <br />");
		if($result == user::REGISTER_OK) {
			$user->email = $this->getData("email");
			$user->usergroup = $this->getData("usergroup");
			$user->save();
		}
	}

	/*===========================================================
	EVENT HANDLER
	Adds a new user group
	============================================================*/
	function eventAddUserGroup(){
		if(!security::hasAccess("Edit User Groups"))
			return;

		$groupname = $this->getData("newGroup");

		$existingId = userGroup::getUserGroupIdByName($groupname);
		if(isset($existingId)){
			$this->setEventResult(false, "Group with same name already exists");
			return;
		}
		$group = new userGroup();
		$group->name = $groupname;
		$group->saveNew();
		$this->setEventResult(true, "New Usergroup Created");
	}

	/*===========================================================
	EVENT HANDLER
	Deletes a user group
	============================================================*/
	function eventDeleteUserGroup(){
		if(!security::hasAccess("Edit User Groups"))
			return;

		$groupid = $this->getData("groupid");
		$group = new userGroup();
		$group->loadFromDatabase($groupid);

		if($group->system == 1){
			$this->setEventResult(false, "Cannot delete system user groups");
			return;
		}
		if($group->default == 1) {
			$this->setEventResult(false, "Cannot delete the new user default group");
			return;
		}

		if($group->visitor == 1) {
			$this->setEventResult(false, "Cannot delete the visitor user group");
			return;
		}
		$group->delete();
		$this->setEventResult(true, "Usergroup Deleted");
	}

	/*===========================================================
	EVENT HANDLER
	Updates data for as ingle user group
	============================================================*/
	function eventEditUserGroup(){
		if(!security::hasAccess("Edit User Groups"))
			return;

		$groupid = $this->getData("groupid");
		$group = new userGroup();
		$group->loadFromDatabase($groupid);
		$group->name = $this->getData("groupname");
		$group->save();

		if($this->getData("visitor")) {
			userGroup::setVisitorUserGroup($groupid);
		}
		if($this->getData("default")) {
			userGroup::setDefaultUserGroup($groupid);
		}

		if(!$this->getData("visitor") && $group->visitor ||
		   !$this->getData("default") && $group->default ) {
		    $this->setEventResult(false, "Assign visitor / default to another group to uncheck" );
			return;
		}

		$this->setEventResult(true, "Saved" );
	}

	/*===========================================================
	EVENT HANDLER
	Updates the visitor and default user group
	============================================================*/
	function eventUpdateGroups(){
		if(!security::hasAccess("Edit User Groups"))
			return;

		$visitor = $this->getData("visitor");
		$defaultuser = $this->getData("default");

		userGroup::setVisitorUserGroup($visitor);
		userGroup::setDefaultUserGroup($defaultuser);

		$this->setEventResult(true,"Groups Updated");
	}

	/*===========================================================
	EVENT HANDLER
	Updates the permissions assigned to each group.
	============================================================*/
	function eventUpdatePermissions(){
		if(!security::hasAccess("Edit Permissions"))
			return;

		global $sql;
		//iterate through all the groups, finding which permissions where
		//checked of for each
		$userGroups = security::getUserGroups();
		foreach($userGroups as $userGroup){
			//permissions for this group (array of permission ids)
			$permissions = $this->getData("permissions_".$userGroup->id);
			if(!is_array($permissions)) {
				$permissions = array();
			}
			//update the group in the database
			$userGroup->permissions = implode(",",$permissions);
			$userGroup->save();

		}
		$this->setEventResult(true, "Updated" );
	}


	/*===========================================================
	EVENT - DELETES A USER & ALL ASSOCIATED DATA!
	============================================================*/
	function eventDeleteUserAll(){

		if(!security::hasAccess("Edit Users"))
			return;

		$userid = $this->getData("userid");
		$user = new user();
		$user->loadFromDatabase($userid);
		
		// If the user is not found in the database end
		if(empty($user->id))
			return;

		// Delete the user
		$user->delete();


		global $sql;
		$guildid = $user->guild;

		if( $guildid == "" ) {
			return $this->setEventResult(true, "User Deleted, but it was not tied to any guild.");
		}
		
		// Delete all guild information
		dkpUtil::DeleteGuild($guildid);

		$this->setEventResult(true, "User Information Succesfully Purged");


	}




}

?>