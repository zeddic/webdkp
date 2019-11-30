<?php

/*=================================================
The news page displays news to the user.
=================================================*/
class pageForSale extends page {

	var $layout = "Columns2Right";
	var $pagetitle = "WebDKP For Sale";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $siteUser;
		global $siteRoot;

		$this->border = 1;
		$this->title = "WebDKP For Sale";

		return $this->fetch("forsale.tmpl.php");
	}

	function area1(){
		$this->border = 0;
		return $this->fetch("forsaleside.tmpl.php");
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