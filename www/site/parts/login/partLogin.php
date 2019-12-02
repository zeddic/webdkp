<?php

/*====================================================
A part that allows the user to login to the system.
This is somewhat useful, but also intended as an example
for a simple part. This sample can also be used to see
how login/logout works so that it can be incorporated
directly into site themes.
=====================================================*/
class partLogin extends part {
	//the view that should be shown by default
	var $defaultView = "Content";

	/*===================================
	Views the html content. This is the view mode, and does
	not allow anything to be edited. it just echos the html
	to the page
	===================================*/
	function viewContent(){
		//login result is set by the framework when you rely on it to handle a signin
		global $loginResult;
		global $siteUser;

		//check to see if the user is signed in (in which case we don't display the signin form)
		if(!$siteUser->visitor) {
			return $this->viewLoggedIn();
		}

		//check to see if the user has already entered a username / password and failed
		if(isset($loginResult)){
			if($loginResult == user::LOGIN_BADUSER) {
				$loginError = "Incorrect username entered.";
			}
			else if($loginResult == user::LOGIN_BADPASSWORD) {
				$loginError = "Incorrect password entered";
			}
			$this->set("loginResult",$loginResult);
			$this->set("loginError",$loginError);
		}


		$this->set("grabFocus",$this->getOption("Grab Focus"));


		if($this->getOption("Size") == "Small")
			return $this->fetch("small/login.tmpl.php");
		else
			return $this->fetch("login.tmpl.php");
	}

	/*===================================
	View shown to the user when they are already logged in
	===================================*/
	function viewLoggedIn(){
		global $SiteUser;
		$this->title = "Welcome ";
		if($SiteUser->firstname != "")
			$this->title.=$SiteUser->firstname;
		else
			$this->title.=$SiteUser->lastname;
		$this->title.="!";

		$this->set("loginResult",$loginResult);

		if($this->getOption("Size") == "small")
			return $this->fetch("small/loggedin.tmpl.php");
		else
			return $this->fetch("loggedin.tmpl.php");
	}


}
?>