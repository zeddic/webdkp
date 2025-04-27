<?php
include_once("lib/dkp/dkpUtil.php");
include_once("lib/dkp/dkpUpdater.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageBrowse extends page {

	var $layout = "Columns2Right";
	var $pagetitle = "Browse Servers";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area1(){
		$this->border = 0;
		return $this->fetch("browseside.tmpl.php");
	}
	function area2()
	{
		global $sql;

		$this->title = "Browse Severs";
		$this->border = 1;

		$servers = dkpUtil::GetPopulatedServerList();

		$this->set("servers",$servers);
		return $this->fetch("browse.tmpl.php");
	}
}
?>