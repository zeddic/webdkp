<?php
//include_once("lib/dkp/dkpServer.php");
//include_once("lib/dkp/dkpUserPermissions.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageReset extends page {

	var $layout = "Columns1";
	var $pagetitle = "Reset Password";
	/*=================================================
	Gives the user a form where they can list the server
	that is missing.
	=================================================*/
	function area2()
	{
		global $siteRoot;

		$this->title = "Reset Password";
		$this->border = 1;
		$resetok = util::getData("resetok");
		if( $resetok ) {
			$this->set("resetok", true);
			return $this->fetch("join/reset.tmpl.php");
		}

		//do some sanity checks
		$userid = util::getData("uid");
		$key = util::getData("key");

		$user = new user();
		$user->loadFromDatabase($userid);

		if(empty($userid) || empty($user->id)) {
			return "No valid userid was passed. Unable to reset password.";
		}
		if(!passwordReset::exists($userid)) {
			return "This account has not requested to reset their password. You can request your password to
				    be reset <a href='$siteRoot/Forgot'>Here</a>.";
		}

		$reset = new passwordReset();
		$reset->loadFromDatabaseByUser($user->id);

		if($reset->key != $key) {
			return "An invalid reset key was passed. Please try to reset your password again <a href='$siteRoot/Forgot'>Here</a>.";
		}

		$this->set("resetok", false);
		$this->set("user",$user);
		$this->set("key", $key);
		return $this->fetch("join/reset.tmpl.php");
	}

	/*=================================================
	event handler for the missing server form.
	Detects what server is missing and sends an email
	to the admin, prompting them to add it
	=================================================*/
	function eventReset(){
		//get post data
		$password = util::getData("password");
		$password2 = util::getData("password2");
		$userid = util::getData("uid");
		$key = util::getData("key");

		//do some sanity checks
		$user = new user();
		$user->loadFromDatabase($userid);
		if(empty($userid) || empty($user->id)) {
			return $this->setEventResult(false, "An invalid userid was passed.");
		}

		if(!passwordReset::exists($user->id)) {
			return $this->setEventResult(false, "This user has not requested to reset their password");
		}

		if(empty($password))
			return $this->setEventResult(false, "You must enter a password. A blank password is too easy for people to guess!");
		if($password != $password2)
			return $this->setEventResult(false, "You typed in two seperate passwords :(");

		//now try to reset the password. This call will
		//perform more error checking for us. Check the return value
		//to make sure everything went well.
		$ok = $user->resetPassword($key, $password);
		if($ok != user::RESET_OK)
			return $this->setEventResult(false, $user->getResetErrorString($ok));

		$_GET["resetok"] = true;
		return $this->setEventResult(true, "Thank you! Your password has now been reset!");
	}
}
?>