<?php
include_once("lib/dkp/dkpUtil.php");
include_once("lib/stats/wowstats.php");
include_once("dkpmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageLoot extends pageDkpMain {


	var $layout = "Columns1";
	var $pageurl = "Loot";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;

		$this->pagetitle .= " - Loot ";
		$this->title = $this->guild->name." Loot";
		$this->border = 1;

		$this->LoadPageVars("loot");
		$completeAwards = dkpUtil::GetLoot($this->guild->id, $this->tableid, $count, $this->sort, $this->order, $this->page, $this->maxpage );

		$loot = array();
		foreach($completeAwards as $award) {
			// Added the itemid to try and pull from wowhead with itemid instead of item name
			$simple = new SimpleLoot($award->reason, $award->id, $award->points, $award->player,  $award->date, $award->itemid);
			$loot[] = $simple;
		}

		$this->set("canedit", $this->HasPermission("TableEditHistory", $this->tableid));
		$this->set("tabs",$this->GetTabs("loot"));
		$this->set("loot", $loot);
		return $this->fetch("loot.tmpl.php");
	}
}

class SimpleLoot {
	var $name;
	var $id;
	var $points;
	var $player;
	var $date;
	var $datestring;
	var $itemid;

	function __construct($name, $id, $points, $player, $date, $itemid){
		$this->name = $name;
		$this->id = $id;
		$this->points = $points;
		$this->points = str_replace(".00", "", $this->points);
		$this->player = $player;
		$this->date = date("U",strtotime($date));
		$this->datestring = date("M j, Y g:i A", strtotime($date));
		// Added to pull from wowhead with itemid
		$this->itemid = $itemid;
	}
}
?>