<?php
include_once("bin/lib/controlPanel.php");
include_once("lib/dkp/dkpUtil.php");

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

	/*===========================================================
	Renders the center of the page
	============================================================*/
	function area2(){



		$this->title = "Database Functions";
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
	EVENT - Re-Tally the number of guilds each server has
	============================================================*/
	function eventTallyGuildPerServer(){
		global $sql;
		if(!security::hasAccess("Edit Users"))
			return;

		// Reset all totals to 0 before re-incrementing
		$sql->Query("UPDATE dkp_servers SET totalguilds = 0");

		$guildserver = $sql->Query("SELECT gserver FROM dkp_guilds");

		while($row= mysqli_fetch_array($guildserver))
		{

			// Check the dkp_pointhistory table to see if there are any awards associated with the guild id. We only need to verify 1.
			$guildservername = mysqli_real_escape_string($row["gserver"]);

			// Increment the value guild total value
			$sql->Query("UPDATE dkp_servers SET totalguilds = totalguilds + 1 WHERE name='$guildservername'");


		}  
		
		echo ("FINISHED THE TALLY!");

	}

	/*===========================================================
	EVENT - Check data in dkp_settings table and compare against the dkp_guilds table.
	data in dkp_settings is deleted if there is no matching guild in dkp_guilds.
	============================================================*/
	function eventCleanDKPSettingsCode(){
		global $sql;
		$totaldeleted = 0;
		// Security Check
		if(!security::hasAccess("Edit Users"))
			return;
		
		// Start off by selecting the guild column from dkp_settings
		$dkpsettingsdata = $sql->Query("SELECT guild FROM dkp_settings");

		// With all of the guild ids in an array we will now loop through them and check to see if that guild exists in dkp_guilds
		// If the guild does not exist then delete all data from dkp_settings that contains that guild
		while($row= mysqli_fetch_array($dkpsettingsdata))
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
  
				// Delete all records from dkp_settings with this guild id
				$sql->Query("DELETE from dkp_settings WHERE guild = '$guildid'");				
				$totaldeleted = $totaldeleted + 1;
			}
		
		}



		echo ("FINISHED CLEANING DKP_SETTINGS!");
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

	/*===========================================================
	EVENT - Create Custom One Time use function here to execute
	Created this special function for SQL database clean up etc.
	============================================================*/
	function eventBlackBoxCode(){
		global $sql;
		$total23 = 0;

		if(!security::hasAccess("Edit Users"))
			return;

		// Delete the guilds in the table that aren't claimed. No pt in taking up space unless someone is using them.

		//$sql->Query("DELETE from dkp_guilds WHERE claimed = '0'");
		//dkpUtil::DeleteGuild('64856');

		//$sql->Query("UPDATE dkp_guilds SET gserver = 'Ragnaros (EU)' WHERE gserver='La Croisade �carlate'");


		//$guilds = $sql->Query("SELECT id FROM dkp_guilds WHERE gserver = 'wod'");
		

		//while($row= mysqli_fetch_array($guilds))
		//{

			// Check the dkp_pointhistory table to see if there are any awards associated with the guild id. We only need to verify 1.
			//$guildid = $row["id"] ?? null;
  
				// Delete all guild information using the guild id
			//dkpUtil::DeleteGuild($guildid);

		//}



		// Total up the number of pro subscribers
		// $dkpsettingsdata = $sql->Query("SELECT name,value FROM dkp_settings");

		// while($row= mysqli_fetch_array($dkpsettingsdata))
		// {
		// 	if (mysqli_real_escape_string($row["name"] == "Proaccount") && mysqli_real_escape_string($row["value"] == 1 )){

		// 		$total23 = $total23 + 1;

		// 	}
		
		// }

		


		echo ("FINISHED THE BLACK BOX CODE!");
		echo $total23;
	}

		
}

?>