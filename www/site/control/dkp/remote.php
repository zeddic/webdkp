<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpRemoteStyle.php");
include_once("lib/dkp/dkpCustomRemoteStyle.php");
include_once("lib/dkp/dkpUtil.php");
include_once("dkpmain.php");
/*=================================================
This page generates the neccessary javascript
and css files that allow the Remote DKP / DKP Syndication
to function. Clients will request different types of
javascript files at run time - these javascript files
are actually dynamically created via php to hold payloads
of table data.

In addition, this page can also return the css style
selected by the current guild.
=================================================*/
class pageRemote extends pageDkpMain {

	/*=================================================
	Default - return the starter javascript file. This file is loaded
	on the client page, generates a bare bones table,
	then starts requesting more data from webdkp
	=================================================*/
	function area2()
	{
		$styleid = util::getData("styleid");
		header("Content-Type: text/javascript");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		framework::useTemplateIndents(false);

		$this->set("styleid",$styleid);
		echo($this->fetch("/remote/remote.js"));
		die();
	}

	/*=================================================
	Returns a javascript file that contains dkp information
	for the specified table. The table is specified
	by the variable t in GET
	=================================================*/
	function area2TableData(){

		$tableid = util::getData("t");
		if(empty($tableid))
			$tableid = 1;

		//determine the type of data that has
		//been requested
		$type = util::getData("type");
		if(empty($type))
			$type = "dkp";

		//load the requested data
		if($type == "loot")
			$data = $this->loadLootData();
		else if($type == "awards")
			$data = $this->LoadAwardData();
		else
			$data = $this->loadDkpData();

		//pass it to the template
		$this->set("data", $data);
		$this->set("type", $type);

		//create the js file
		framework::useTemplateIndents(false);
		header("Content-Type: text/javascript");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		echo($this->fetch("/remote/tabledata.js"));
		die();
	}
	/*=================================================
	Loads dkp data for a guild
	=================================================*/
	function loadDkpData(){

		$fulldata = dkpUtil::GetDKPTable($this->guild->id, $this->tableid, $count, "dkp", "desc", "1", $maxpage, "", 100 );

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

		return $data;
	}
	/*=================================================
	Loads loot award data
	=================================================*/
	function loadLootData() {
		$completeAwards = dkpUtil::GetLoot($this->guild->id, $this->tableid, $count, "date", "desc", "1", $maxpage, "50");

		$loot = array();
		foreach($completeAwards as $award) {
			$simple = new SimpleLoot($award->reason, $award->id, $award->points, $award->player,  $award->date, $award->itemid);
			$loot[] = $simple;
		}
		return $loot;
	}

	/*=================================================
	Loads award data
	=================================================*/
	function loadAwardData(){
		$completeAwards = dkpUtil::GetAwards($this->guild->id, $this->tableid, $count, "date", "desc", "1", $maxpage, "50");
		$awards = array();
		foreach($completeAwards as $award) {
			$simple = new SimpleAward($award->reason, $award->id, $award->points, $award->playercount,  $award->date);
			$awards[] = $simple;
		}
		return $awards;
	}

	/*=================================================
	Returns a javascript file that contains a list of
	all the tables that this guild has. The client javascript
	file will use this to determine what group of data
	to request first.
	=================================================*/
	function area2Tables(){

		framework::useTemplateIndents(false);
		header("Content-Type: text/javascript");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		echo($this->fetch("/remote/tables.js"));
		die();
	}

	/*=================================================
	Returns a css file of the guilds currently selected
	remote dkp style.
	=================================================*/
	function area2Style(){

		$styleid = util::getData("styleid");
		if(!isset($styleid) || empty($styleid)) {
			$styleid = $this->settings->GetRemoteStyle();
			if(empty($styleid))
				$styleid = 1;
		}

		$style = new dkpRemoteStyle();
		$style->loadFromDatabase($styleid);
		if($style->file == "custom") {
			$custom = new dkpCustomRemoteStyle();
			$custom->loadFromGuild($this->guild->id);
			$content = $custom->content;
		}
		else {
			$content = $style->getContent();
		}

		framework::useTemplateIndents(false);
		header("Content-Type: text/css");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		echo($content);
		die();
	}
}

/*=======================================
Simple class used to wrap a single row used
in the table. Used cut out other un-needed
data so that the json object
in the javascript is smaller.
=======================================*/
class SimpleEntry {
	var $dkp;
	var $lifetime;
	var $player;
	var $playerguild;
	var $playerclass;

	function __construct(& $entry){

		$this->dkp = $entry->points;
		$this->lifetime = $entry->lifetime;
		$this->player = $entry->user->name;
		$this->playerguild = $entry->user->guild->name;
		$this->playerclass = $entry->user->class;
	}
}
/*=======================================
Simple class used to wrap a single row used
in the table. Used cut out other un-needed
data so that the json object
in the javascript is smaller.
=======================================*/
class SimpleAward {
	var $name;
	var $id;
	var $points;
	var $players;
	var $date;
	var $datestring;

	function __construct($name, $id, $points, $players, $date){
		$this->name = $name;
		$this->id = $id;
		$this->points = $points;
		$this->points = str_replace(".00", "", $this->points);
		$this->players = $players;
		$this->date = date("U",strtotime($date));
		$this->datestring = date("M j, Y g:i A", strtotime($date));
	}
}
/*=======================================
Simple class used to wrap a single row used
in the table. Used cut out other un-needed
data so that the json object
in the javascript is smaller.
=======================================*/
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
		$this->itemid = $itemid;
	}
}
?>