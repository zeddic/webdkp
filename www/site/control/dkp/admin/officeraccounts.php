<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpAccountUtil.php");
/*=================================================
Page displays a list of officer accounts. These are
accounts that also have access to a guilds dkp
table in addition to the main administrator.

This page allows users to:
- see officer accounts
- create officer accounts
- delete officer accounts
- links to a seperate page to update a particular officer account
=================================================*/
class pageOfficerAccounts extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		$this->title = "Officer Accounts";
		$this->border = 1;

		//get a list of current accounts
		$accounts = dkpAccountUtil::GetOfficerAccounts($this->guild->id);

		//call the template
		$this->set("accounts",$accounts);
		return $this->fetch("officeraccounts.tmpl.php");
	}

	/*=================================================
	EVENT - New officer account
	=================================================*/
	function eventCreateAccount(){
		$username = util::getData("username");
		$password1 = util::getData("password1");
		$password2 = util::getData("password2");
		$email = util::getData("email");

		$result = dkpAccountUtil::CreateSecondaryAccount($this->guild->id, $username, $password1, $password2, $email);

		if($result != dkpAccountUtil::UPDATE_OK)
			$this->setEventResult(false, dkpAccountUtil::GetErrorString($result));
		else
			$this->setEventResult(true,"Officer Account Created!");
	}

	/*=================================================
	EVENT - Delete Officer Account
	=================================================*/
	function eventDeleteAccount(){
		$userid = util::getData("id");

		$result = dkpAccountUtil::DeleteSecondaryAccount($this->guild->id, $userid);

		if($result != dkpAccountUtil::UPDATE_OK)
			$this->setEventResult(false, dkpAccountUtil::GetErrorString($result));
		else
			$this->setEventResult(true,"Officer Account Deleted!");
	}
}
?>