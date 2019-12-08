<?php
include_once("util/pager.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageAddon extends page {

	var $layout = "Columns2Right";

	var $pagetitle = "Download Addon";

	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		$this->border = 1;
		$this->title = "World of Warcraft Addon";
		return $this->fetch("addon.tmpl.php");
	}

	function area1(){
		$this->border = 0;
		return $this->fetch("addonside.tmpl.php");
	}
}

?>