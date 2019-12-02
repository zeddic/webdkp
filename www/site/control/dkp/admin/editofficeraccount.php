<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpAccountUtil.php");
/*=================================================
Page displays a single officer account to edit. These are
accounts that also have access to a guilds dkp
table in addition to the main administrator.

This page allows users to:
- edit a single officer account

Officer accounts are expected to be identified
by the variable 'id' in the query string
=================================================*/
class pageEditOfficerAccount extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		$this->title = "Edit Officer Account";
		$this->border = 1;

		//get the user to edit
		$userid = util::getData("id");

		//make sure this is a valid user
		if(!$this->canUpdateUser($userid))
			return $this->PermissionError();

		//load the user's permissions
		$permissions = new dkpUserPermissions();
		$permissions->loadUserPermissions($userid);

		//load the user
		$account = new user();
		$account->loadFromDatabase($userid);

		//call the template
		$this->set("permissions",$permissions);
		$this->set("tables", $this->updater->GetTables());
		$this->set("account", $account);
		return $this->fetch("editofficeraccount.tmpl.php");
	}

	/*=================================================
	EVENT - New officer account
	=================================================*/
	function eventUpdateAccount(){
		$userid = util::getData("id");
		$selectedPermissions = util::getData("customPermissions");
		$selectedTables = util::getData("selectedTables");
		$tableAccess = util::getData("tableAccess");
		$accountType = util::getData("accountType");

		//make sure we can modify this account
		if(!$this->canUpdateUser($userid))
			return $this->setEventResult(false, "You cannot update this account");

		$result = dkpAccountUtil::UpdateSecondaryAccount($this->guild->id, $userid, $selectedPermissions, $selectedTables, $tableAccess, $accountType);

		if($result != dkpAccountUtil::UPDATE_OK)
			$this->setEventResult(false, dkpAccountUtil::GetErrorString($result));
		else
			$this->setEventResult(true,"Account Updated!");
	}

	/*=================================================
	EVENT - Sets the password for the given user
	=================================================*/
	function eventUpdatePassword(){
		$userid = util::getData("id");
		$password1 = util::getData("password1");
		$password2 = util::getData("password2");

		if(!$this->canUpdateUser($userid))
			return $this->setEventResult(false, "You cannot update this account");

		$result = dkpAccountUtil::SetOfficerAccountPassword($this->guild->id, $userid, $password1, $password2);

		if($result != dkpAccountUtil::UPDATE_OK)
			$this->setEventResult(false, dkpAccountUtil::GetErrorString($result));
		else
			$this->setEventResult(true,"Password Changed!");

	}

	/*=================================================
	Helper method - returns true if you can update
	the permissions for the selected user.
	=================================================*/
	function canUpdateUser($userid){
		$permissions = new dkpUserPermissions();
		$permissions->loadUserPermissions($userid);

		//make sure we are trying to edit a user who really belongs to our guild
		if($permissions->guildid != $this->guild->id || !$this->HasPermission("AccountSecondaryUsers"))
			return false;
		return true;
	}
}
?>