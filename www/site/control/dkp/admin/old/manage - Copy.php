<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("lib/wow/armory.php");
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


	function SimpleEntry($entry = ""){

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


		$table = & new dkpPointsTable();
		$table->loadTable($this->guild->id, $this->tableid);

		$data = array();
		foreach($table->table as $entry) {
			$temp = new SimpleEntry($entry);
			$data[] = $temp;
		}

		$this->addJavascriptHeader($siteRoot."js/dkpAdmin.js");

		$this->set("canDelete",$this->HasPermission("TableDeletePlayer",$this->tableid));
		$this->set("canEditPlayer",$this->HasPermission("TableEditPlayers",$this->tableid));
		$this->set("canAddPlayer",$this->HasPermission("TableAddPlayer",$this->tableid));
		$this->set("canAddPoints",$this->HasPermission("TableAddPoints",$this->tableid));

		$this->set("tabs",$this->GetTabs());
		$this->set("table", $table);
		$this->set("data", $data);
		return $this->fetch("manage.tmpl.php");
	}

	function ajaxEditPlayer(){
		global $siteUser;

		$id = util::getData("id");
		$newname = util::getData("name");
		$newguild = util::getData("guild");
		$newplayerclass = util::getData("playerclass");
		$newdkp = util::getData("dkp");

		$player = new dkpUser();
		$player->loadFromDatabase($id);
		if($player->id == "")
			return $this->setAjaxResult(false, "Invalid User ID");

		if(!$this->updater->PlayerInTable($player->id,$this->tableid))
			return $this->setAjaxResult(false, "Invalid User ID");

		if( $player->name != $newname || $player->guild->name != $newguild || $player->class != $newplayerclass ) {
			//the player has been updated in some way
			//check for permissions first
			if(!$this->HasPermission("TableEditPlayers",$tableid))
				return $this->setAjaxResult(false, "You do not have permission to edit player details");

			$player->name = $newname;
			$player->class = $newplayerclass;
			$guild =  dkpUtil::EnsureGuildExists($newguild, $this->guild->server, $this->guild->faction);
			$player->guild = $guild->id;
			$player->save();
		}

		//check to see if their dkp has changed
		$currentdkp = dkpUtil::GetPlayerDKP($this->guild->id, $this->tableid, $id);
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

		return $this->setAjaxResult(true, "Changes Saved");

	}

	function ajaxAddPlayer(){
		global $siteUser;

		//get post data
		$name = util::getData("name");
		$playerclass = util::getData("playerclass");
		$playerguild = util::getData("playerguild");
		$dkp = util::getData("dkp");
		if($dkp == "")
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
			$this->updater->AddDKP($this->tableid, $player->id, $award);
		}

		//send confirmation
		$entry = new SimpleEntry();
		$entry->userid = $player->id;
		$entry->dkp = $dkp;
		$entry->lifetime = max(0,$dkp);
		$entry->player = $player->name;
		$entry->playerguild = $playerguild;
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
		$reason = util::getData("reason");
		if($reason == "")
			$reason = "Unknown";
		$cost = util::getData("cost");
		if($cost == "")
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
		$item = util::getData("item");
		if($item == "")
			$item = "Unknown";
		$cost = util::getData("cost");
		if($cost == "")
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

}
?>