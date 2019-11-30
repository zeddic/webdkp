<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("lib/wow/armory.php");

/*=================================================
Gives user instruction on setting up remote dkp
on their own website
=================================================*/
class pageArmory extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;
		$this->title = "Sync with Armory";
		$this->border = 1;

		return $this->fetch("armory.tmpl.php");
	}

	function eventSync(){

		//get data
		$guildname = util::getData("guild");
		$server = util::getData("server");
		$tableid = util::getData("table");
		$minlevel = util::getData("level");
		$wowserver = util::getData("wowserver");
		global $sql;
		if(!is_numeric($level))
			$level = 70;

		//create an array that will hold the tableids of
		//all tables we need to sync with
		$tables = array();
		if($tableid == 0) {
			foreach($this->tables as $table)
				$tables[] = $table->tableid;
		}
		else {
			$tables[] = $tableid;
		}

		//request a list of players from the armory
		$players = armory::GetPlayersInGuildByName($guildname, $server, $wowserver);
		$playersFound = sizeof($players);
		$playersAdded = 0;
		$playersSkipped = 0;

		//increment through each of the players, making sure that the system
		//both knows about it, and that it is in the tables we were requested to sync with
		foreach($players as $player) {
			//skip player if not in min level
			if($player->level < $minlevel ) {
				$playersSkipped++;
				continue;
			}

			if($this->updater->PlayerExists($player->name) == 0) {
				$realplayer = $this->updater->CreatePlayer($player->name, $player->class, $player->guild);
			}
			else {
				$realplayer = $this->updater->GetPlayer($player->name);
				// Reset the player's class so it shows up properly. Somewhere along the lines the class is getting messed up. An armory sync should fix it with this line.
				// Pull out the existing class info to see if it needs updated
				$existingclass = $sql->QueryRow("SELECT class FROM dkp_users WHERE name = '$player->name' AND server='$server' LIMIT 1");
				if ($existingclass[0] == ""){
					// The users class is nil so update it
					$sql->Query("UPDATE dkp_users SET class = '$player->class' WHERE name = '$player->name' AND server='$server'");
				}

			}

			//make sure the real players name is up to date
			if($realplayer->guild->name != $player->guild) {
				if(!isset($realguild))
					$realguild = dkpUtil::EnsureGuildExists($player->guild, $this->guild->server, $this->guild->faction);
				$realplayer->guild = $realguild->id;
				$realplayer->save();
				
			}

			//make sure the player is in the tables
			foreach($tables as $table) {
				if(!$this->updater->PlayerInTable($realplayer->id, $table)) {
					$this->updater->EnsurePlayerInTable($realplayer->id, $table);
					$playersAdded++;
				}
			}
		}

		//set result
		if($playersFound == 0 ) {
			$url = armory::GetArmoryUrlByName($guildname, $server, $wowserver);
			$this->setEventResult(false, "No players found on The Armory. Was the correct guild and server entered? The url used
										 was <a href='$url'>$url</a>. Please check the link to make sure it is valid.");
		}
		else {
			if($playersAdded == 0 )
				$this->setEventResult(true, "$playersFound Players Found - No New Information");
			else
				$this->setEventResult(true, "$playersFound Players Found - $playersAdded Entries Added!");
		}
	}
}
?>