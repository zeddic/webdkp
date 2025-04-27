<?php
include_once("lib/dkp/dkpUtil.php");
include_once("lib/stats/wowstats.php");
include_once("dkpmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pagePlayer extends pageDkpMain {

	function __construct(){

		$playername = util::getData("player");
		$this->pageurl = "Player/".$playername;

		parent::__construct();
	}

	var $layout = "Columns1";
	var $pageurl = "Player";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;
		global $siteUser;

		$playername =   util::getData("player") ;
		$player = $this->updater->GetPlayer($playername);
		$dkp = dkpUtil::GetPlayerDKP($this->guild->id, $this->tableid, $player->id);

		$this->pagetitle .= " - $playername ";

		if($player->id == "") {
			$this->title = $this->guild->name." - Unknown Player";
			$this->border = 1;
			return "$playername is not in this guild's table.";
		}

		$this->title = $this->guild->name." - ".$player->name;
		$this->border = 1;


		$loot = dkpUtil::GetPlayerLootHistory($this->guild->id, $this->tableid, $player->id);
		$lootAwards = array();
		foreach($loot as $award) {
			$simple = new SimpleAward($award);
			$lootAwards[] = $simple;
		}

		$this->LoadPageVars("player");
		$completeAwards = dkpUtil::GetPlayerHistory($this->guild->id, $this->tableid, $player->id,
												   $this->sort, $this->order, $this->page,
												  $this->maxpage, 25, $dkp);
		$awards = array();
		foreach($completeAwards as $award) {
			$simple = new SimpleAward($award);
			$awards[] = $simple;
		}

		$this->set("canedit", $this->HasPermission("TableEditHistory", $this->tableid));
		$this->set("tabs",$this->GetTabs("dkp"));
		$this->set("loot", $lootAwards);
		$this->set("awards", $awards);
		$this->set("dkp", $dkp);
		$this->set("player", $player);
		return $this->fetch("player.tmpl.php");
	}
}

class SimpleAward {
	var $name;
	var $id;
	var $points;
	var $players;
	var $date;
	var $datestring;
	var $historyid;
	var $foritem;
	var $itemid;

	function __construct(&$award)
	{
		$this->name = $award->reason;
		$this->id = $award->id;
		$this->points = $award->points;
		$this->points = str_replace(".00", "", $this->points);
		$this->players = $award->players;
		$this->date = date("U",strtotime($award->date));
		$this->datestring = date("M j, Y g:i A", strtotime($award->date));
		$this->historyid = $award->historyid;
		$this->foritem = $award->foritem;
		$this->itemid = $award->itemid;
	}
}
?>