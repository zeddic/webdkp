<?php
include_once("lib/dkp/dkpUtil.php");
include_once("lib/stats/wowstats.php");
include_once("adminmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageEditAward extends pageAdminMain {


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
		$allPlayers = $this->updater->GetPlayersInTable($award->tableid);

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
				$players = $this->getSimplePlayers($allPlayers, $award);
				$this->set("players", $players);
				//set the transfer flag
				$edittype = "transfer";
				$this->title = "Edit Transfer";
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
				$players = $this->getSimplePlayers($allPlayers, $auto);
				//load the player who recieved the root award
				$root->loadPlayer();
				//pass to template
				$this->set("players", $players);
				$this->set("root", $root);
				$this->set("auto", $auto);
				$edittype = "zerosum";
				$this->title = "Edit Zerosum Award";

			}
		}
		//otherwise, this is just a regular old award - none of that fancy
		//linking stuff
		else {
			//load the recieving player(s)
			if( $award->foritem  == 1)
				$player = $award->loadPlayer();
			$players = $this->getSimplePlayers($allPlayers, $award);

			if (!empty($player))
				$this->set("player", $player);
			$this->set("players", $players);
			$this->set("award", $award);
			$edittype = "normal";
			$this->title = "Edit Award";
		}

		//set the title
		$this->border = 1;
		if(empty($award->id))
			$this->title = "Invalid Award";


		//determine where the back url should take us
		//if it is already in post, get it from there.
		//Otherwise, construct it from the query string
		$backurl = util::getData("backurl");
		if(empty($backurl))
			$backurl = $this->getBackUrl();

		//pass variables to the template
		$this->set("canedit", $this->HasPermission("TableEditHistory", $award->tableid));
		$this->set("tabs",$this->GetTabs("awards"));
		$this->set("backurl", $backurl);

		//get the tables they can switch awards between
		$tables = $this->updater->GetTables();
		$this->set("awardtables", $tables);

		//render
		//choose are template based on the type of the award we wish to edit
		//This keeps the templates at least a bit more cleaner :)
		$this->set("edittype", $edittype);
		if($edittype == "zerosum")
			return $this->fetch("editzerosum.tmpl.php");
		else if($edittype == "transfer")
			return $this->fetch("edittransfer.tmpl.php");
		else
			return $this->fetch("editaward.tmpl.php");
	}

	function getSimplePlayers(&$players, &$award){

		$awardplayers = $award->getPlayerids();

		$simpleplayers = array();

		foreach($players as $player) {
			$simpleplayer = array();
			$simpleplayer["name"] = $player->name;
			$simpleplayer["id"] = $player->id;
			if(in_array($player->id, $awardplayers))
				$simpleplayer["checked"] = true;
			else
				$simpleplayer["checked"] = false;
			$simpleplayers[] = $simpleplayer;
		}

		return $simpleplayers;
	}

	function getBackUrl(){
		//generate the back url
		$back = util::getData("b");
		$page = util::getData("p");
		$sort = util::getData("s");
		$pid = util::getData("pid");
		$order = util::getData("o");

		$backurl = $this->baseurl;
		if($back == "a" ) {
			$backurl.="Awards/";
			$backurl.="$page/$sort/$order";
		}
		else if($back == "l") {
			$backurl.="Loot/";
			$backurl.="$page/$sort/$order";
		}
		else if($back == "p") {
			$backurl.="Player/";
			$pid = util::getData("pid");
			$player = new dkpUser();
			$player->loadFromDatabase($pid);
			$backurl.=$player->name."/";
			$backurl.="$page/$sort/$order";
		}
		else if($back == "e") {
			$backurl.="Award/";
			$aid = util::getData("aid");
			$backurl.="$aid";
		}
		else {
			$backurl.="Awards/";
			$backurl.="$page/$sort/$order";
		}

		return $backurl;
	}

	function eventUpdateAward(){

		//check permissions
		if( !$this->HasPermission("TableEditHistory", $this->tableid) )
			return $this->setEventResult(false, "You do not have permission to edit awards");

		//get all the post data
		$foritem = util::getData("foritem");
		$points = util::getData("points");
		$reason = util::getData("reason");
		$location = util::getData("location");
		$awardedby = util::getData("awardedby");
		$userids = util::getData("users");
		$player = util::getData("player");	//who recieved the award (for items, and zerosum root)
		$edittype = util::getData("edittype");
		$tableid = util::getData("awardtable");
		$awardid = util::getData("id");
		$award = new dkpAward();
		$award->loadFromDatabase($awardid);

		//editing a regular award
		if( $edittype == "normal" ) {
			$award->points = $points;
			$award->reason = $reason;
			$award->location = $location;
			$award->awardedby = $awardedby;
			$award->tableid = $tableid;
			$award->foritem = $foritem;

			if( $foritem == 1)
				$award->points = $points * -1;

			if( $foritem == 1 && $player != "")
				$userids = array($player);
			else if ( $foritem == 0 && empty($userids))
				$userids = array($player);
			else if ( !is_array($userids) )
				$userids = $award->getPlayerids();

			$this->updater->UpdateAward($award->id, $award, $userids);
		}
		//editing a transfer award
		else if ( $edittype == "transfer") {

			$toPlayer = util::getData("toplayer");
			$fromPlayer = util::getData("fromplayer");

			//get the other award
			$other = $this->updater->GetTransferAward($award);

			//figure out which award is which
			if($other->points > $award->points)	{
				$to = $other;
				$from = $award;
			}
			else {
				$to = $award;
				$from = $other;
			}

			//set the new values. We can't update some fields for transfers
			$to->tableid = $tableid;
			$to->points = $points;
			$to->awardedby = $awardedby;
			$from->tableid = $tableid;
			$from->points = -$points;
			$from->awardedby = $awardedby;

			//update them
			$this->updater->UpdateAward($to->id, $to, array($toPlayer));
			$this->updater->UpdateAward($from->id, $from, array($fromPlayer));
		}
		//editing a zerosum award
		else if ( $edittype == "zerosum") {
			$root = $this->updater->GetZerosumRootAward($award);
			$auto = $this->updater->GetZerosumAutoAward($award);

			if(sizeof($userids) == 0 || !is_array($userids)) {
				return $this->setEventResult(false, "You must select at least 1 zerosum user");
			}

			//update the root award
			$root->points = -1*$points;
			$root->reason = $reason;
			$root->location = $location;
			$root->awardedby = $awardedby;
			$root->tableid = $tableid;
			$this->updater->UpdateAward($root->id, $root, array($player));

			//update the auto award (the only thing we can change for this is the
			//number of people
			$auto->tableid = $tableid;

			$this->updater->UpdateAward($auto->id, $auto, $userids);
		}

		//and done
		$this->setEventResult(true,"Award Updated");
	}
}
?>