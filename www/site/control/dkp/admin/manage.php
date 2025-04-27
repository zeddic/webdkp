<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("adminmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/

class SimpleEntry {
	var $userid;
	var $dkp;
	var $lifetime;
	var $player;
	var $playerguild;
	var $playerclass;


	function __construct($entry = ""){

		if($entry != "") {
			$this->userid = $entry->user->id;
			$this->dkp = $entry->points;
			$this->lifetime = $entry->lifetime;
			$this->player = $entry->user->name;
			$this->playerguild = $entry->user->guild->name;
			$this->playerclass = $entry->user->class;
		}
	}
}

class pageManage extends pageAdminMain {

	var $layout = "Columns1";
	var $pageurl = "Admin/Manage";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;
		global $siteRoot;

		$this->title = "Manage DKP";
		$this->border = 1;


		$filters = $this->CombineDKPFilters("admin");

		$this->LoadPageVars("manage");
		$fulldata = dkpUtil::GetDKPTable($this->guild->id, $this->tableid, $count, $this->sort, $this->order, $this->page, $this->maxpage, $filters );

		$data = array();
		foreach($fulldata as $entry) {
			$temp = new SimpleEntry($entry);
			$data[] = $temp;
		}


		$this->addJavascriptHeader($siteRoot."js/dkpAdmin.js");

		$this->set("canDelete",$this->HasPermission("TableDeletePlayer",$this->tableid));
		$this->set("canEditPlayer",$this->HasPermission("TableEditPlayers",$this->tableid));
		$this->set("canAddPlayer",$this->HasPermission("TableAddPlayer",$this->tableid));
		$this->set("canAddPoints",$this->HasPermission("TableAddPoints",$this->tableid));

		$this->set("tabs",$this->GetTabs());
		$this->set("data", $data);
		$this->set("filter",$this->GetDKPFilterUI("admin"));
		return $this->fetch("manage.tmpl.php");
	}

	function ajaxEditPlayer(){
		global $siteUser;
		global $sql;

		//get post data
		$id = util::getData("id");
		$newname = trim(util::getData("name"));
		$newguild = trim(util::getData("guild"));
		$newplayerclass = util::getData("playerclass");
		$newdkp = util::getData("dkp");

		//load the player
		$player = new dkpUser();
		$player->loadFromDatabase($id);
		if(empty($player->id))
			return $this->setAjaxResult(false, "Invalid User ID");
		$currentdkp = dkpUtil::GetPlayerDKP($this->guild->id, $this->tableid, $id);

		//check if the player is in the table
		if(!$this->updater->PlayerInTable($player->id,$this->tableid))
			return $this->setAjaxResult(false, "Invalid User ID");

		//check to see if any of the names or classes had changed
		if( $player->name != $newname || $player->guild->name != $newguild || $player->class != $newplayerclass ) {

			//the player has been updated in some way
			//check for permissions first
			if(!$this->HasPermission("TableEditPlayers",$this->tableid)) {
				//error, return the original entry back to the javascript so it can update the table
				$entry = new SimpleEntry();
				$entry->userid = $player->id;
				$entry->dkp = $currentdkp;
				$entry->player = $player->name;
				$entry->playerguild = $player->guild->name;
				$entry->playerclass = $player->class;
				return $this->setAjaxResult(false, "You do not have permission to edit player details", $entry);
			}

			//if the player name has changed, we need to check for the condition where
			//another player with the same name already exists on the server
			if($player->name != $newname && !$sameDbName) {

				//does one exists?
				$tempServer = sql::Escape($this->guild->server);
				$tempName = sql::Escape($newname);
				$exists = $sql->QueryItem("SELECT id FROM dkp_users WHERE name='$tempName' AND server='$tempServer'");

				//We can skip this step if the player names only differ by capitilization...
				//In that case they are still functionally the same in the eyes of the database
				//and we'll just write the new capitlization changes.
				$sameDbName = (strtoupper($player->name) == strtoupper($newname));

				if($exists && !$sameDbName) {
					$guildid = sql::Escape($this->guild->id);
					$tableid = sql::Escape($this->tableid);

					//see if they are already in our table...
					$intable = $sql->QueryItem("SELECT id FROM dkp_points WHERE user='$exists' AND guild='$guildid' AND tableid='$tableid'");
					if($intable) {
						//error, return the original entry back to the javascript so it can update the table
						$entry = new SimpleEntry();
						$entry->userid = $player->id;
						$entry->dkp = $currentdkp;
						$entry->player = $player->name;
						$entry->playerguild = $player->guild->name;
						$entry->playerclass = $player->class;
						return $this->setAjaxResult(false, "A player with this name is already in your table.", $entry);
					}

					//yes... this gets tricky. First load the player so we can apply our changes to him
					$oldid = $player->id;
					$player->loadFromDatabase($exists);

					//next, we need to update all records that pointed to the old playerid
					//in our table to point to the new playerid
					$newid = $player->id;

					//update thier points
					$sql->Query("UPDATE dkp_points SET user='$newid' WHERE user='$oldid' AND guild='$guildid' AND tableid='$tableid'");

					//update their history
					$sql->Query("UPDATE dkp_pointhistory, dkp_awards
								 SET dkp_pointhistory.user='$newid'
								 WHERE dkp_pointhistory.user='$oldid'
								 AND dkp_pointhistory.award = dkp_awards.id
								 AND dkp_awards.guild='$guildid'
								 AND dkp_awards.tableid='$tableid'");
				}
				else {
					//no, other player with the same name exists. We should able to just rename them.
					//However... we don't want to do this if this player is shared with another guild
					$id = $player->id;
					$guildid = sql::Escape($this->guild->id);
					$tableid = sql::Escape($this->tableid);
					$shared = $sql->Query("SELECT id FROM dkp_points WHERE user='$id' AND guild != '$guildid'");
					if(empty($shared) || $sameDbName) {
						//player is only used by this guild... just update it
						$player->name = $newname;
					}
					else {
						//we have the case where: the new player name is open, but
						//the player we want to rename is shared with another guild.
						//Go ahead and create a new user on the same server
						$oldid = $player->id;
						$player->name = $newname;
						$player->saveNew();
						$newid = sql::Escape($player->id);
						//update thier points
						$sql->Query("UPDATE dkp_points SET user='$newid' WHERE user='$oldid' AND guild='$guildid' AND tableid='$tableid'");
						//update their history
						$sql->Query("UPDATE dkp_pointhistory, dkp_awards
								 SET dkp_pointhistory.user='$newid'
								 WHERE dkp_pointhistory.user='$oldid'
								 AND dkp_pointhistory.award = dkp_awards.id
								 AND dkp_awards.guild='$guildid'
								 AND dkp_awards.tableid='$tableid'");
					}
				}
			}

			$player->class = $newplayerclass;
			$guild = dkpUtil::EnsureGuildExists($newguild, $this->guild->server, $this->guild->faction);
			$player->guild = $guild->id;
			$player->save();
		}

		//check to see if their dkp has changed
		if($currentdkp != $newdkp) {

			//if(!$this->HasPermission("TableEditHistory", $this->tableid))
			//	return $this->setAjaxResult(false, "You do not haver permission to edit DKP");
			//dkp value has changed
			//figure out by what amount
			$delta = $newdkp - $currentdkp;

			$award = new dkpAward();
			$award->guild = $this->guild->id;
			$award->points = $delta;
			$award->reason = "Adjustment";
			$award->location = "WebDKP";
			$award->awardedby = $siteUser->username;
			$award->foritem = 0;
			$this->updater->AddDkp( $id, $this->tableid, $award);
		}

		return $this->setAjaxResult(true, "Changes Saved", $player->id);

	}

	function ajaxAddPlayer(){
		global $siteUser;

		//get post data
		$name = trim(strip_tags(util::getData("name")));
		$playerclass = util::getData("playerclass");
		$playerguild = trim(strip_tags(util::getData("playerguild")));
		$dkp = util::getData("dkp");
		if(empty($dkp))
			$dkp = 0;

		//make sure we have permissions to add players
		if(!$this->HasPermission("TableAddPlayer",$this->tableid))
			return $this->setAjaxResult(false, "You do not have permission to create a new user");

		//create the player
		$player = $this->updater->EnsurePlayerExists($name, $playerclass, $playerguild);

		//check to see if the player is already in the table, if so, nothing to do
		if($this->updater->PlayerInTable($player->id, $this->tableid))
			return $this->setAjaxResult(false, "Player already in table", 2);

		//add the player to the table
		$this->updater->EnsurePlayerInTable($player->id, $this->tableid);

		//award them any starting dkp
		if($dkp != 0 ) {
			$award = new dkpAward();
			$award->guild = $this->guild->id;
			$award->points = $dkp;
			$award->reason = "Player Created";
			$award->location = "WebDKP";
			$award->awardedby = $siteUser->username;
			$award->foritem = 0;
			$this->updater->AddDKP($player->id, $this->tableid, $award);
		}

		//send confirmation
		$entry = new SimpleEntry();
		$entry->userid = $player->id;
		$entry->dkp = $dkp;
		$entry->lifetime = max(0,$dkp);
		$entry->player = $player->name;
		$entry->playerguild = stripslashes($playerguild);
		$entry->playerclass = $player->class;


		$this->setAjaxResult(true,"Changes Saved", $entry);
	}

	function ajaxDeletePlayer(){
		//get player
		$id = util::getData("id");

		//security check
		if( !$this->HasPermission("TableDeletePlayer", $this->tableid) ) {
			return $this->setAjaxResult(false, "You do not have permission to remove players from this table");
		}

		//remove player from table
		$this->updater->RemovePlayerFromTable($id, $this->tableid);

		//send confirmation
		return $this->setAjaxResult(true, "Player Deleted");
	}

	function ajaxCreateAward(){
		$playerids = util::getData("playerids");
		$reason = strip_tags(util::getData("reason"));
		if(empty($reason))
			$reason = "Unknown";
		$cost = util::getData("cost");
		if(empty($cost))
			$cost = 0;
		$location = util::getData("location");
		$awardedby = util::getData("awardedby");

		if( !$this->HasPermission("TableAddPoints", $this->tableid) ) {
			return $this->setAjaxResult(false, "You do not have permission to create awards on this table.");
		}

		$playerids = explode(",", $playerids);

		$award = new dkpAward();
		$award->guild = $this->guild->id;
		$award->points = $cost;
		$award->reason = $reason;
		$award->location = $location;
		$award->awardedby = $awardedby;
		$award->foritem = 0;

		$this->updater->AddDkpToPlayers($playerids, $this->tableid, $award);

		return $this->setAjaxResult(true, "Award Created");
	}

	function ajaxCreateItemAward(){
		//get the post data
		$playerid = util::getData("playerid");
		$item = strip_tags(util::getData("item"));
		if(empty($item))
			$item = "Unknown";
		$cost = util::getData("cost");
		if(empty($cost))
			$cost = 0;
		$cost *= -1;
		$location = util::getData("location");
		$awardedby = util::getData("awardedby");
		$zerosum = util::getData("zerosum");
		$zerosum = explode(",", $zerosum);

		//check permissions
		if( !$this->HasPermission("TableAddPoints", $this->tableid) ) {
			return $this->setAjaxResult(false, "You do not have permission to create awards on this table.");
		}

		//create the award
		$award = new dkpAward();
		$award->guild = $this->guild->id;
		$award->points = $cost;
		$award->reason = $item;
		$award->location = $location;
		$award->awardedby = $awardedby;
		$award->foritem = 1;

		//pass the award to the updater class to handle
		if($this->settings->GetZerosumEnabled()) {
			$this->updater->AddDkp($playerid, $this->tableid, $award, $zerosum);
		}
		else {
			$this->updater->AddDkp($playerid, $this->tableid, $award);
		}


		return $this->setAjaxResult(true, "Award Created");
	}

	function eventSetFilter()
	{
		$this->SetDKPFilter("admin");
	}

	function eventClearFilter()
	{
		$this->ClearDKPFilter("admin");
	}

}
?>