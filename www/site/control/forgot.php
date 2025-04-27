<?php
//include_once("lib/dkp/dkpServer.php");
//include_once("lib/dkp/dkpUserPermissions.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageForgot extends page {

	var $layout = "Columns1";
	var $pagetitle = "Forgot Password";
	/*=================================================
	Gives the user a form where they can list the server
	that is missing.
	=================================================*/
	function area2()
	{
		$this->title = "Forgot Password";
		$this->border = 1;

		return $this->fetch("join/forgot.tmpl.php");
	}

	/*=================================================
	event handler for the missing server form.
	Detects what server is missing and sends an email
	to the admin, prompting them to add it
	=================================================*/
	function eventRequestReset(){
		//get post data
		$username = util::getData("username");

		if(empty($username))
			return $this->setEventResult(false, "You must enter a username!");

		//perform a sanity check
		$user = new user();
		$user->loadFromDatabaseByUser($username);
		if(empty($user->id))
			return $this->setEventResult(false, "A account with the requested username does not exist.");
		if(empty($user->email))
			return $this->setEventResult(false, "The requested account does not have a registered email. As a result,
												 WebDKP will not be able to do an automatted password reset.");

		//ok, sanity checks passed, lets give it a try
		$ok = $user->requestReset();
		if($ok != user::RESET_OK)
			return $this->setEventResult(false, $user->getResetErrorString($ok));

		return $this->setEventResult(true, "Thank you! You should recieve an email shortly with instructions on how to reset your password!");
	}
}
?>