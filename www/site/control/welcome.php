<?php
include_once("lib/dkp/dkpUtil.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("lib/wow/armory.php");
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

		//$this->addJavascriptHeader($this->binDirectory."js/welcome.js");

		$this->set("guildurl",$guildurl);
		return $this->fetch("join/welcome.tmpl.php");


	}
	/*===========================================================
	AJAX
	Callback initiates a sync with the WoW Armory. This will
	attempt to contact the armory and download the guilds roster
	MOVED TO SEPERATE PAGE IN CONTROL PANEL
	============================================================*/
	/*function ajaxLoadRoster(){
		global $siteUser;

		$updater = new dkpUpdater($siteUser->guild);

		$players = armory::GetPlayersInGuildByName($updater->guild->name,$updater->guild->server);
		$playersFound = sizeof($players);
		$playersAdded = 0;

		foreach($players as $player) {
			if(!$updater->PlayerExists($player->name)) {
				$realplayer = $updater->CreatePlayer($player->name, $player->class);
				$playersAdded++;
			}
			else {
				$realplayer = $updater->GetPlayer($player->name);
			}
			$updater->EnsurePlayerInTable($realplayer->id, 1);
		}

		if($playersFound == 0 )
			echo(util::json(array(false, $playersFound, $playersAdded)));
		else
			echo(util::json(array(true, $playersFound, $playersAdded)));
	}*/


}
?>