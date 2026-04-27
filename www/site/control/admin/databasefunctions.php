<?php
include_once("bin/lib/controlPanel.php");
include_once("lib/dkp/dkpUtil.php");
include_once("lib/dkp/dkpCleanup.php");

/*===========================================================
Controller - Database Management
Displays Database Function Options
============================================================*/
class pageDatabaseFunctions extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $title = "WebDKP Database Management";
	var $layout = "Columns1";
	var $deleteLog = "";
	var $deleteServersLog = "";
	var $updateServerTotalsLog = "";

	/*===========================================================
	Renders the center of the page
	============================================================*/
	function area2(){
		$this->title = "Database Functions";
		$this->set("deleteLog", $this->deleteLog);
		$this->set("deleteServersLog", $this->deleteServersLog);
		$this->set("updateServerTotalsLog", $this->updateServerTotalsLog);
		return $this->fetch("database.tmpl.php");
	}

	function area3(){
		global $SiteRoot;
		$breadcrumbs = array();
		$breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$breadcrumbs[] = array("Database Functions");
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}

	/*===========================================================
	EVENT - DELETES A USER & ALL ASSOCIATED DATA!
	============================================================*/
	function eventPurgeBlankGuilds(){
		global $sql;
		$totaldeleted = 0;
		if(!security::hasAccess("Edit Users"))
			return;

		$guilds = $sql->Query("SELECT id FROM dkp_guilds WHERE gserver = 'ThrokFeroth'");
		

		while($row= mysqli_fetch_array($guilds))
		{

			// Check the dkp_pointhistory table to see if there are any awards associated with the guild id. We only need to verify 1.
			$guildid = $row["id"] ?? null;
			$historyresult = $sql->Query("SELECT id FROM dkp_pointhistory WHERE guild=$guildid LIMIT 1");

   			if($row = mysqli_fetch_array($historyresult)) 
			{//if we did return a record
      				// Do nothing at this time.
   			}
			else
			{
  
				// Delete all guild information using the guild id
				dkpUtil::DeleteGuild($guildid);
				$totaldeleted = $totaldeleted + 1;
			}


	

		}  
		
		echo ("FINISHED!");
		echo ("Deleted ".$totaldeleted);
	}


	/*===========================================================
	EVENT - Check data in dkp_users table and compare against the dkp_guilds table.
	data in dkp_users is deleted if there is no matching guild in dkp_guilds.
	============================================================*/
	function eventUserCleanCode(){
		global $sql;
		$totaldeleted = 0;
		// Security Check
		if(!security::hasAccess("Edit Users"))
			return;
		
		// Start off by selecting the guild column from dkp_settings
		$dkpusersdata = $sql->Query("SELECT guild FROM dkp_users");

		// With all of the guild ids in an array we will now loop through them and check to see if that guild exists in dkp_guilds
		// If the guild does not exist then delete all data from dkp_settings that contains that guild
		while($row= mysqli_fetch_array($dkpusersdata))
		{
			$guildid = $row["guild"] ?? null;

			// Search through dkp_settings to see if that guild exists
			$guildresult = $sql->Query("SELECT id FROM dkp_guilds WHERE id=$guildid LIMIT 1");

   			if($row = mysqli_fetch_array($guildresult)) 
			{//if we did return a record
      				// Do nothing at this time.
   			}
			else
			{
  
				// Delete all records from dkp_users with this guild id
				$sql->Query("DELETE from dkp_users WHERE guild = '$guildid'");				
				$totaldeleted = $totaldeleted + 1;
			}
		
		}

		echo ("FINISHED CLEANING DKP_USERS!");
		echo ("Deleted ".$totaldeleted);
	}
	
	
	function eventDeleteOldData() {
		if (!security::hasAccess("Edit Users")) {
			return;
		}
		$dryrun = util::getData("dryrun");
		$this->deleteLog = dkpCleanup::deleteOldData($dryrun);
	}
	
	function eventDeleteEmptyServers() {
		if (!security::hasAccess("Edit Users")) {
			return;
		}
		$dryrun = util::getData("dryrun");
		$this->deleteServersLog = dkpCleanup::deleteEmptyServers($dryrun);
	}
	
	function eventUpdateServerTotals() {
		if (!security::hasAccess("Edit Users")) {
			return;
		}
		$this->updateServerTotalsLog = dkpCleanup::updateServerTotals();
	}
}

?>