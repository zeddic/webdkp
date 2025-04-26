<?php
include_once("lib/dkp/dkpUtil.php");
include_once("lib/dkp/dkpUpdater.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageWelcome extends page {

	var $layout = "Columns1";
	var $pagetitle = "Welcome";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $siteUser;
		global $siteRoot;

		if($siteUser->visitor)
			util::forward($siteRoot."Login");

		$this->title = "Welcome ".$siteUser->username."!";
		$this->border = 1;

		$guildurl = dkpUtil::GetGuildUrl($siteUser->guild)."Admin";

		$this->set("guildurl",$guildurl);
		return $this->fetch("join/welcome.tmpl.php");
	}
}
?>