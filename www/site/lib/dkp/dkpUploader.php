<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
DKPUploader provides all upload functionality for taking
an WebDKP.lua file, parsing it, and combining it into the
database.

This supports both v1 formats (v2.5 and earlier) and
v2.
*/

include_once("dkpUpdater.php");
include_once("lua2php.php");
include_once("dkpGuild.php");
//include_once("modules/moduleDkp/dkpUserPermissions.php");

class dkpUploader {

	var $addedCount = 0;
	var $log;
	/*===========================================================
	Uploads a log into the system. for the given guild. This will
	look for the log in one of two places: a file that has been uploaded
	in $_FILE['userfile'] (through the site interface) or in
	$_FILE['file'] (sent via the c# client). $_FILE['file'] will only
	be searched for if $clientUpload is true.

	Note that this method will immediatly return if the current site user
	does not have an id that matches the guild that the log is being uploaded.

	Also note that the upload will check for permissions to see if you have
	the right to upload information on a per table basis. So - if you
	have permissions to upload to table 1, but not table 2, only your table 1
	writes will be uploaded while table 2 writes will be ignored.
	============================================================*/
	function UploadLog($guildid, $clientUpload = false)
	{
		global $siteUser;
		global $sql;

		//echo("AT THE START");

		//$this->log .= "At the start...";

		//load up the guild instance
		$guild = new dkpGuild();
		$guild->loadFromDatabase($guildid);

		//if this guild doesn't match the person doing the uploading, quit
		if($siteUser->guild != $guild->id && $siteUser->usergroup->name != "Admin")
			return "You cannot upload to this account";

		$this->log .= "Loaded the guild<br />";
		//give us time to handle the upload
		set_time_limit(0);

		//now get the file that was uploaded. If this
		//was a clinet upload (from the .exe client) it will be located
		//someplace else

		$this->log .= "Loading file<br />";
		if($clientUpload)
			$luaFile = $_FILES['file']['tmp_name'];
		else
			$luaFile = $_FILES['userfile']['tmp_name'];

		$this->log .= "file loaded - making it into a php array<br />";
		//convert the lua file to a php array
		$log = makePhpArray($luaFile);

		$this->log .= "About to run tasks <br />";


		//if the file checks out, go ahead and do the upload
		if(isset($log["WebDKP_Log"]) && $log["WebDKP_Log"]["Version"]==2) {
			$this->log .="File is valid - about to parse <br />";
			$this->ParseLog($guild, $log, $clientUpload);
		}
		else {
			if( !$clientUpload ) {
				$this->log = "Empty Log File";
			}
			else {
				$this->log = "OK - NoNewEntries";
			}
		}

		//return our upload log
		return $this->log;
	}


	/*===========================================================
	To increase performance the log file was changed to keep track of people
	who had similar awards. This means that all awards for a set of people could
	be inserted into the database with a single query instead of with multiple queries.
	So, in the old system - a raid with 40 people might have 10 awards, resulting in 400
	inserts. In the new format, there would only 10 inserts, as the inserts would
	be grouped by award.

	Parameters:
	$guild - A dkpGuild instance of the guild where this information is being uploaded
	$log - A log file that was uploaded. This log file should have already been converted to an associative php array
	$clientUpload - Whether or not that log file was uploaded from the .exe app that comes with the addon. Defaults to fasle.

	Returns:
	$updateLog - A log of all the changes / inserts made
	============================================================*/
	function ParseLog($guild, $log, $clientUpload = false)
	{
		global $sql;
		global $siteUser;

		//create an updater that can handle all our database
		//updates for us
		$updater = new dkpUpdater($guild->id);

		//update log will contain a string record of all our changes
		$this->log = "";

		//update the settings on the server based on what is set in the log.
		$this->UpdateSettingsFromLog($guild, $log);

		//load current settings for guild
		$this->settings = $guild->loadSettings();

		//direct the log to only the list of players
		$log = $log["WebDKP_Log"];

		//keep track of how many awards we make
		$this->addedCount = 0;

		//now increment through each log entry.
		//Each log entry will be composed of a single award, then within that award
		//will be a list of the player recieving it.
		if($log != "") {
			foreach($log as $key => $awardEntry) {
				$this->ParseAwardEntry($guild, $updater, $awardEntry );
			}
			//if zerosum awards were created, make sure they
			//are linked together in the database
			$updater->LinkZerosum();
		}

		//if they have the combine alts setting turned on, transfer any dkp that
		//alts made to their main
		//if enabled, transfer dkp from alts to main
		if($this->settings->GetCombineAltsEnabled())
			$updater->CombineAltsWithMain();

		//update the message to show to the user
		if($this->addedCount==0)
		{
			if($clientUpload)
				$this->log = "OK - NoNewEntries";
			else
				$this->log = "No new entries were uploaded to your guilds
							  DKP table. The two main reasons for this occuring are: <br />
							  - You have already uploaded this log file and there is no new information in it <br />
							  - This log file is empty";
		}
		else
		{
			if($clientUpload)
				$this->log = "OK";
			else
				$this->log = "The following information was added to your dkp table: <br /><br />". $this->log;
		}
	}


	function ParseAwardEntry($guild, $updater, $entry){
		//get the information for this award.

		//create an award instance for this entry
		$award = new dkpAward();
		$award->guild = $guild->id;
		$award->reason = $entry["reason"];
		$award->awardedby = $entry["awardedby"];
		// Added for Item IDs
		$award->itemid = empty($entry["itemid"]) ? '0' : $entry["itemid"];
		$award->location = $entry["zone"];
		$award->date = $entry["date"];
		$award->points = $entry["points"];
		$award->tableid = $entry["tableid"];
		$award->foritem = ($entry["foritem"] == "true" ? 1 : 0);

		if( stripos($award->reason, "ZeroSum: ") !== false ) {
			$award->zerosumauto = 1;
		}

		//get the list of players who recieved the award
		$players = $entry["awarded"];

		$this->log.="AWARD: ".$award->reason."<br />";

		//make sure the user has rights to append information to this table
		if (dkpUserPermissions::currentUserHasPermission("TableUploadLog",$guild->id,$tableid)) {

			//only bother making an insert if people actually recieved the award
			if($players!="" && is_array($players)){

				//iterate through all players for the award
				//we need to:
				//1 - make sure we know about the player and thier guild
				//2 - check if they already have the award
				//3 - make a note of which players should recive the award
				$playersToAward = array();
				foreach($players as $player){

					//get player data
					$name = $player["name"];
					$class = $player["class"];
					$playerGuild = $player["guild"];
					if(empty($playerGuild))
						$playerGuild = "Unknown";

					//get the players guild (adding it to the database if needed)
					$newGuild = dkpUtil::EnsureGuildExists($playerGuild, $guild->server, $guild->faction);

					//get the player (adding them to the database if needed)
					$currentPlayer = dkpUtil::EnsurePlayerExists($name, $class, $newGuild->id, $guild->server, $guild->faction);

					//if the player is in a new guild since the last time we checked,
					//update it
					if( $currentPlayer->guild != $newGuild->id &&
						$newGuild->name != "Unknown" &&
						$newGuild->id != 0 && $newGuild->id != "") {
						$currentPlayer->guild = $newGuild->id;
						$currentPlayer->save();
					}

					//check if the player already has the award. If they don't,
					//put them in our array
					if(!dkpUtil::AwardExistsForPlayer($currentPlayer->id, $award) ) {
						$this->addedCount++;
						$playersToAward[] = $currentPlayer->id;
						$this->log.="Adding $award->points to $name <br />";
					}

					//finally, make sure they are in our table
					$updater->EnsurePlayerInTable($currentPlayer->id, $award->tableid);
				}

				//we now have a list of the players we want to award. We will
				//award them all at once. Internally, this uses a single sql query,
				//greatly speeding up the insert time
				if(sizeof($playersToAward>0)) {
					$updater->AddDkpToPlayers($playersToAward, $award->tableid, $award);
				}
			}
		} //end permissions check
		else {
			//echo("skipped  $tableid <br />");
		}
	}

	/*===========================================================
	Helper method for uploading a log file. Looks at settings in the uploaded log file and
	updates the settings online to reflect.
	============================================================*/
	function UpdateSettingsFromLog($guild, $log){
		global $siteUser;
		global $sql;

		if(empty($log))
			return;

		$options = $log["WebDKP_WebOptions"];
		if(empty($options))
			return;

		//load up the current settings
		$settings = $guild->loadSettings();

		//update the zerosum dkp settings
		if($options["ZeroSumEnabled"]==1) {
			$settings->SetZerosumEnabled(1);
		}
		else if($options["ZeroSumEnabled"]==0) {
			$settings->SetZerosumEnabled(0);
		}
	}
}
?>