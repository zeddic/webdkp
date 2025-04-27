<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("dkpmain.php");

class SimpleEntry {
	var $userid;
	var $dkp;
	var $lifetime;
	var $player;
	var $playerguild;
	var $playerclass;
	var $tier;

	function __construct($entry = null){
		if($entry != null) {
			$this->userid = $entry->user->id;
			$this->dkp = $entry->points;
			$this->lifetime = $entry->lifetime;
			$this->player = $entry->user->name;
			$this->playerguild = $entry->user->guild->name;
			$this->playerclass = $entry->user->class;
		}
	}
}

class pageIndex extends pageDkpMain {
	var $layout = "Columns1";
	var $pageurl = "";

	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2() {

		if (!$this->guild->id) {
			$this->border = 1;
			$this->pagetitle = 'Guild not found';
			$this->title = $this->pagetitle;
			return $this->fetch('unknown_guild.tmpl.php');
		}

		global $sql;
		$this->border = 1;
		$this->pagetitle .= " - DKP ";
		$this->title = $this->guild->name." DKP";

		$filters = $this->CombineDKPFilters("main");
		$this->LoadPageVars("main");
		$fulldata = dkpUtil::GetDKPTable($this->guild->id, $this->tableid, $count, $this->sort, $this->order, $this->page, $this->maxpage, $filters );


		$data = array();
		$useTiers = $this->settings->GetTiersEnabled();
		$tierSize = $this->settings->GetTierSize();
		foreach($fulldata as $entry) {
			$temp = new SimpleEntry($entry);
			if($tierSize == 0)
				$tierSize = 1;
			if($useTiers)
				$temp->tier = floor( ($temp->dkp - 1 ) / $tierSize )."";
			$data[] = $temp;
		}

		$this->set("tabs",$this->GetTabs());
		$this->set("filter",$this->GetDKPFilterUI("main"));
		$this->set("data", $data);
		return $this->fetch("dkp.tmpl.php");
	}

	function eventSetFilter() {
		$this->SetDKPFilter("main");
	}

	function eventClearFilter() {
		$this->ClearDKPFilter("main");
	}
}
?>