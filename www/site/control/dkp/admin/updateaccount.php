<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpAccountUtil.php");
include_once("lib/dkp/dkpUtil.php");

class pageUpdateAccount extends pageAdminMain {

	function area2()
	{
		global $sql;

		
		$this->title = "Update Account Settings";
		$this->border = 1;

		$this->set("tabs",$this->GetTabs("admin"));
		return $this->fetch("updateaccount.tmpl.php");
	}

	function eventUpdateAccount(){
		//todo: check permissions

		//get post data
		$name = util::getData("username");
		$email = util::getData("email");
		$password = util::getData("password");

		//update username
		$result = dkpAccountUtil::UpdateUsername($password, $name);
		if($result != dkpAccountUtil::UPDATE_OK) {
			$this->setEventResult(false, dkpAccountUtil::GetErrorString($result));
			return;
		}

		//update email
		$result = dkpAccountUtil::UpdateEmail($password, $email);
		if($result != dkpAccountUtil::UPDATE_OK) {
			$this->setEventResult(false, dkpAccountUtil::GetErrorString($result));
			return;
		}

		$this->setEventResult(true,"Account Updated!");
	}

	function eventUpdatePassword(){
		$currentpassword = util::getData("currentPassword");
		$password1 = util::getData("password1");
		$password2 = util::getData("password2");

		$result = dkpAccountUtil::UpdatePassword($currentpassword, $password1, $password2);
		if($result != dkpAccountUtil::UPDATE_OK) {
			$this->setEventResult(false, dkpAccountUtil::GetErrorString($result));
			return;
		}

		$this->setEventResult(true,"Password Updated!");
	}

	function eventDeleteAccount(){
		
		global $siteUser;
		$userid = $siteUser->id;

		$user = new user();
		$user->loadFromDatabase($userid);
		
		// If the user is not found in the database end
		if(empty($user->id))
			return;
		
		util::forward("http://www.webdkp.com/login?siteUserEvent=logout");

		// Delete the user
		$user->delete();

		global $sql;
		$guildid = $user->guild;


		if(empty($guildid)) {
			return;
		}
		
		// Delete all guild information
		dkpUtil::DeleteGuild($guildid);

	}


}
?>