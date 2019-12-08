<?php
include_once("util/pager.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageIndex extends page {

	var $layout = "Columns2Right";
	var $pagetitle = "A DKP Addon for World of Warcraft";

	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		$this->border = 1;
		$this->title = "Welcome";
		return $this->fetch("index.tmpl.php");
	}

	function area2addon(){
		global $siteRoot;
		util::forward($siteRoot."Addon");
	}
	function area2screen(){
		global $siteRoot;
		util::forward($siteRoot."Screenshots");
	}

	function area1(){
		$this->border = 0;
		return $this->fetch("welcomeside.tmpl.php");
	}
}

?>