<?php
include_once("lib/dkp/dkpGuild.php");
/*=================================================
This page is intended to catch users using old
webdkp.com links and direct them to the new, correct
url which is based off of guild name / server
=================================================*/
class pageRemote extends page {

	function area2()
	{
		util::forward($this->GetGuildUrl()."RemotePreview");
	}

	function GetGuildUrl(){
		global $siteRoot;

		$guildid = util::getData("id");

		if(empty($guildid))
			util::forward($siteRoot);

		$guild = new dkpGuild();
		$guild->loadFromDatabase($guildid);

		$server = str_replace(" ","+",$guild->server);
		$name = str_replace(" ","+",$guild->name);

		$url = $siteRoot."dkp/$server/$name/";

		return $url;
	}
}

?>