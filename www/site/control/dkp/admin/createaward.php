<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("adminmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/


class pageCreateAward extends pageAdminMain {

	var $layout = "Columns1";
	var $pageurl = "Admin/CreateAward";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;
		global $siteRoot;

		$this->title = "Create Award";
		$this->border = 1;

		/*$data = array();
		foreach($fulldata as $entry) {
			$temp = new SimpleEntry($entry);
			$data[] = $temp;
		}*/

		$table = new dkpPointsTable();
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
		return $this->fetch("createaward.tmpl.php");
	}

	function ajaxCreateAward(){
		$playerids = util::getData("playerids");
		$reason = util::getData("reason");
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

		//if enabled, transfer dkp from alts to main
		if($this->settings->GetCombineAltsEnabled())
			$this->updater->CombineAltsWithMain();

		return $this->setAjaxResult(true, "Award Created");
	}

	function ajaxCreateItemAward(){
		//get the post data
		$playerid = util::getData("playerid");
		$item = util::getData("item");
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

		//if enabled, transfer dkp from alts to main
		if($this->settings->GetCombineAltsEnabled())
			$this->updater->CombineAltsWithMain();

		return $this->setAjaxResult(true, "Award Created");
	}
}


class SimpleEntry {
	var $userid;
	var $player;
	var $playerguild;
	var $playerclass;

	function __construct($entry = ""){

		if(!empty($entry)) {
			$this->userid = $entry->user->id;
			$this->player = $entry->user->name;
			$this->playerguild = $entry->user->guild->name;
			$this->playerclass = $entry->user->class;
		}
	}
}
?>