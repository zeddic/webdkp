<?php
include_once("bin/lib/controlPanel.php");

/*===========================================================
Controller - Main Control Panel Page
Displays the main control panel with links to specific tasks
============================================================*/
class pageLogin extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $title = "Control Panel";
	//var $layout = "3Column";
	var $layout = "Columns1";


	/*===========================================================
	Renders the center of the page
	============================================================*/
	function area2(){
		$this->title = "Control Panel Login";
		$this->border = 1;

		//login result is set by the framework when you rely on it to handle a signin
		global $loginResult;
		global $siteUser;

		//check to see if the user is signed in (in which case we don't display the signin form)
		if(!$siteUser->visitor) {
			$this->set("loggedin",true);
			$this->title = "Welcome ";
			if($siteUser->firstname != "")
				$this->title.=$siteUser->firstname;
			else
				$this->title.=$siteUser->username;
			$this->title.="!";
			return $this->fetch("login.tmpl.php");
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




		return $this->fetch("login.tmpl.php");
	}

	/*===========================================================
	Renders the breadcrumbs at the top
	============================================================*/
	function area3(){
		global $SiteRoot;
		$breadcrumbs = array();
		$breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$breadcrumbs[] = array("Login");
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}
}

?>