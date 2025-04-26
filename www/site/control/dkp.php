<?php
/*=================================================
This page is intended to catch users using old
webdkp.com links and direct them to the new, correct
url which is based off of guild name / server
=================================================*/
include_once("lib/dkp/dkpUtil.php");
class pageDkp extends page {

	function area2() {
		util::forward($this->GetGuildUrl());
	}
	function area2Guild(){
		util::forward($this->GetGuildUrl());
	}
	function area2Guilds(){
		global $siteRoot;
		util::forward($siteRoot."Browse");
	}
	function area2LootHistory(){
		util::forward($this->GetGuildUrl()."Loot");
	}
	function area2Awards(){
		util::forward($this->GetGuildUrl()."Awards");
	}
	function area2RawData(){
		//include_once("client/download.php");
	}
	function GetGuildUrl(){
		global $siteRoot;

		$guildid = util::getData("id");

		if(empty($guildid))
			util::forward($siteRoot);

		$url = dkpUtil::GetGuildUrl($guildid);

		return $url;
	}
}

?>