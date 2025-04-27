<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("adminmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pagePlayerAlts extends pageAdminMain {

	var $layout = "Columns1";
	var $pageurl = "Admin/PlayerAlts";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;
		global $siteRoot;

		$this->border = 1;

		$id = util::getData("player");
		$player = new dkpUser();
		$player->loadFromDatabase($id);

		if(empty($player->id))
			$this->title = "Invalid Player";
		else
			$this->title = "$player->name Alts";

		$player->loadAltAndMainData();

		//get a list of all players
		$players = $this->updater->GetPlayersInAllTables($this->tableid);

		$this->set("player", $player);
		$this->set("players", $players);
		$this->set("tabs",$this->GetTabs());
		return $this->fetch("playeralts.tmpl.php");
	}

	/*=================================================
	EVENT - Adds an alternative character to the current player
	=================================================*/
	function eventAddAlt(){

		//get post data
		$playerid = util::getData("player");
		$altid = util::getData("alt");

		//now for all the long permission checks...

		//first, do they have permission?
		if( !$this->HasPermission("TableEditPlayers",$this->tableid) )
			return $this->setEventResult(false, "You do not have permissions to perform this action.");

		//next, did they pass a valid playerid?
		$player = new dkpUser();
		$player->loadFromDatabase($playerid);
		if(empty($player->id))
			return $this->setEventResult(false, "Invalid Player '$playerid' Selected.");

		//did they pass a valid alt id?
		$alt = new dkpUser();
		$alt->loadFromDatabase($altid);
		if(empty($alt->id))
			return $this->setEventResult(false, "Invalid Alt '$altid' Selected.");

		//are the player and alt in this table?
		if(!$this->updater->PlayerInAnyTable($player->id) ||
		   !$this->updater->PlayerInAnyTable($alt->id, $this->tableid)) {
			return $this->setEventResult(false, "Player or Alt are not in your table. You cannot make this assignment.");
		}

		//whew, all checks done. Now for the  quick job of adding the alt..
		$player->addAlt($alt->id);

		$this->updater->CombineAltsWithMain();

		$this->setEventResult(true, "Alt Added");
	}
	/*=================================================
	EVENT - Deletes an alt from the current characters list
	=================================================*/
	function eventDeleteAlt(){
		$playerid = util::getData("player");
		$altid = util::getData("alt");

		//now for all the long permission checks...

		//first, do they have permission?
		if( !$this->HasPermission("TableEditPlayers",$this->tableid) )
			return $this->setEventResult(false, "You do not have permissions to perform this action.");

		//next, did they pass a valid playerid?
		$player = new dkpUser();
		$player->loadFromDatabase($playerid);
		if(empty($player->id))
			return $this->setEventResult(false, "Invalid Player Selected.");

		//did they pass a valid alt id?
		$alt = new dkpUser();
		$alt->loadFromDatabase($altid);
		if(empty($alt->id))
			return $this->setEventResult(false, "Invalid Alt Selected.");

		//are the player and alt in this table?
		//if(!$this->updater->PlayerInAnyTable($player->id, $this->tableid) ||
		//   !$this->updater->PlayerInAnyTable($alt->id, $this->tableid)) {
		//	return $this->setEventResult(false, "Player or Alt are not in your table. You cannot make this assignment.");
		//}

		//whew, all checks done. Now for the  quick job of deleting the alt
		$player->removeAlt($alt->id);

		$this->updater->CombineAltsWithMain();

		$this->setEventResult(true, "Alt Deleted");

	}

	/*=================================================
	EVENT - Makes this character an alt to someone else
	=================================================*/
	function eventMakeAlt(){
		$playerid = util::getData("player");
		$mainid = util::getData("main");

		//now for all the long permission checks...

		//first, do they have permission?
		if( !$this->HasPermission("TableEditPlayers",$this->tableid) )
			return $this->setEventResult(false, "You do not have permissions to perform this action.");

		//next, did they pass a valid playerid?
		$player = new dkpUser();
		$player->loadFromDatabase($playerid);
		if(empty($player->id))
			return $this->setEventResult(false, "Invalid Player Selected.");

		//did they pass a valid alt id?
		$main = new dkpUser();
		$main->loadFromDatabase($mainid);
		if(empty($main->id))
			return $this->setEventResult(false, "Invalid Main Selected.");

		//are the player and alt in this table?
		if(!$this->updater->PlayerInAnyTable($player->id, $this->tableid) ||
		   !$this->updater->PlayerInAnyTable($main->id, $this->tableid)) {
			return $this->setEventResult(false, "Player or Main are not in your table. You cannot make this assignment.");
		}

		//now to make the assignment
		$player->makeAltTo($main->id);

		$this->updater->CombineAltsWithMain();

		$this->setEventResult(true, "This player is now an alt to $alt->name");
	}

	/*=================================================
	EVENT - Unlinks this player - making them a main
	but leaving all other previous associations behind.
	=================================================*/
	function eventUnlink(){
		$playerid = util::getData("player");

		//now for all the long permission checks...

		//first, do they have permission?
		if( !$this->HasPermission("TableEditPlayers",$this->tableid) )
			return $this->setEventResult(false, "You do not have permissions to perform this action.");

		//next, did they pass a valid playerid?
		$player = new dkpUser();
		$player->loadFromDatabase($playerid);
		if(empty($player->id))
			return $this->setEventResult(false, "Invalid Player Selected.");

		//are the player in the table
		//if(!$this->updater->PlayerInAnyTable($player->id, $this->tableid)) {
		//	return $this->setEventResult(false, "Player or Main are not in your table. You cannot make this assignment.");
		//}

		//perform the update
		$player->main = 0;
		$player->save();

		//$this->updater->CombineAltsWithMain();

		$this->setEventResult(true, "This character has been unlinked. Is it now its own main.");
	}

	/*=================================================
	EVENT - Makes this character a main. Anyone who this
	person was an alt to, and everyone who was an alt to that
	person now points to this characteer as a main instead.
	=================================================*/
	function eventMakeMain(){
		$playerid = util::getData("player");

		//now for all the long permission checks...

		//first, do they have permission?
		if( !$this->HasPermission("TableEditPlayers",$this->tableid) )
			return $this->setEventResult(false, "You do not have permissions to perform this action.");

		//next, did they pass a valid playerid?
		$player = new dkpUser();
		$player->loadFromDatabase($playerid);
		if(empty($player->id))
			return $this->setEventResult(false, "Invalid Player Selected.");

		//are the player in the table
		if(!$this->updater->PlayerInAnyTable($player->id, $this->tableid)) {
			return $this->setEventResult(false, "Player or Main are not in your table. You cannot make this assignment.");
		}

		//perform the update
		$player->makeMain();

		$this->setEventResult(true, "$player->name is now the new main. All other alts now point to this player as their main.");

		$this->updater->CombineAltsWithMain();
	}
}
?>