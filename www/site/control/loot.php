<?php
/*=================================================
This page is intended to catch users using old
webdkp.com links and direct them to the new, correct
url which is based off of guild name / server
=================================================*/
class pageLoot extends page {

	function area2()
	{
		util::forward($siteRoot);
	}

	function area2Loot(){
		util::forward($this->GetGuildUrl()."LootTable");
	}
	function GetGuildUrl(){
		global $siteRoot;

		$guildid = util::getData("id");

		if($guildid == "")
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