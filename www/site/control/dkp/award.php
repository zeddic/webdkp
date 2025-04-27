<?php
include_once("lib/dkp/dkpUtil.php");
include_once("lib/stats/wowstats.php");
include_once("dkpmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageAward extends pageDkpMain {

	/*function pagePlayer(){

		$playername = util::getData("player");
		$this->pageurl = "Player/".$playername;

		pageDkpMain::pageDkpMain();
	}*/

	var $layout = "Columns1";
	var $pageurl = "Award";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;
		global $siteUser;

		//get the award
		$awardid = util::getData("id");
		$award = new dkpAward();
		$award->loadFromDatabase($awardid);

		//get a list of all players in this table
		//$allPlayers = $this->updater->GetPlayersInTable($award->tableid);

		//if the award is linked, we will need to load up the
		//other related awards
		if( $award->linked != 0 && $award->linked != $award->id ) {
			//if this is a transfer award, load the other one
			if( $award->transfer ) {
				//get the other transfer award
				$other = $this->updater->GetTransferAward($award);
				//load the players for both
				$award->loadPlayer();
				$other->loadPlayer();
				//determine who the points were transfered from and to
				if($other->points > $award->points ) {
					$this->set("fromAward",$award);
					$this->set("toAward", $other);
				}
				else {
					$this->set("fromAward", $other);
					$this->set("toAward", $award);
				}
				//set the transfer flag
				$type = "transfer";
				$this->title = "Transfer Details";
			}
			//if this is a zerosum award, load both the root
			//and auto award. Since the two are so tightly linked
			//its best to edit them both at the same time.
			else if( $this->updater->IsZerosumAutoAward($award) ||
					 $this->updater->IsZerosumRootAward($award) ) {
				//load both the root and auto award
				$root = $this->updater->GetZerosumRootAward($award);
				$auto = $this->updater->GetZerosumAutoAward($award);

				//get a list of the players who recieved the auto award
				$auto->loadPlayers();

				//load the player who recieved the root award
				$root->loadPlayer();
				//pass to template
				$this->set("root", $root);
				$this->set("auto", $auto);
				$type = "zerosum";
				$this->title = "Zerosum Award Details";

			}
		}
		//otherwise, this is just a regular old award - none of that fancy
		//linking stuff
		else {
			//load the recieving player(s)
			if( $award->foritem  == 1)
				$player = $award->loadPlayer();
			$award->loadPlayers();

			if (!empty($player)) 
				$this->set("player", $player);
			$this->set("award", $award);
			$type = "normal";
			$this->title = "Award Details";
		}

		//set the title
		$this->border = 1;
		if(empty($award->id))
			$this->title = "Invalid Award";

		//pass variables to the template
		$this->set("canedit", $this->HasPermission("TableEditHistory", $this->tableid));
		$this->set("tabs",$this->GetTabs("awards"));

		//render
		//choose are template based on the type of the award we wish to edit
		//This keeps the templates at least a bit more cleaner :)
		$this->set("type", $type);
		if($type == "zerosum")
			return $this->fetch("zerosumaward.tmpl.php");
		else if($type == "transfer")
			return $this->fetch("transferaward.tmpl.php");
		else
			return $this->fetch("award.tmpl.php");
	}
}
?>