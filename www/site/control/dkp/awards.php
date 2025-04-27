<?php
include_once("lib/dkp/dkpUtil.php");
include_once("dkpmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageAwards extends pageDkpMain {


	var $layout = "Columns1";
	var $pageurl = "Awards";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;
		global $siteUser;

		$this->pagetitle .= " - Awards ";
		$this->title = $this->guild->name." Awards";
		$this->border = 1;

		$this->LoadPageVars("all");

		$completeAwards = dkpUtil::GetAwards($this->guild->id, $this->tableid, $count, $this->sort, $this->order, $this->page, $this->maxpage );

		$awards = array();
		foreach($completeAwards as $award) {
			// if(security::hasAccess("Control Panel")) {
			// 	var_dump($award);
			// 	echo("<br />");
			// }
			$simple = new SimpleAward($award->reason, $award->id, $award->points, $award->playercount,  $award->date);
			$awards[] = $simple;
		}

		$this->set("canedit", $this->HasPermission("TableEditHistory", $this->tableid));
		$this->set("tabs",$this->GetTabs("awards"));
		$this->set("awards", $awards);
		return $this->fetch("awards.tmpl.php");
	}
}


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
?>