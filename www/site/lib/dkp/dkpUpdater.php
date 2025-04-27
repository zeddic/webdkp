<?php
/*===========================================================
CLASS DESCRIPTION - dkpUpdater
=============================================================
The dkpUpdater class is used to both query and update dkp
information for a single guild. This is NOT a static class
and must be instantiated.

This is the sole class that should be used when you wish to update
a guilds DKP table. Sample methods provided by this class include:

- adding dkp to 1 or many players
- subtracting dkp
- creating / deleting a player
- creating / deleting new dkp tables
- adding new players to specific tables
- update / delete history entries
- update / delete award entries

This class handles all zerosum considerations, which can get quite
complex when dealing with single history entries that are modified.
For example, when a single history is deleted that is part of a larger
zero sum award, all zerosum related history entries would need to
be recalculated, which results in new dkp totals for all players involved.
As long as you use this class to handle updates, all these adjustments
are made correctly.
*/
include_once("dkpAward.php");
include_once("dkpGuild.php");
include_once("dkpUser.php");
include_once("dkpPointsTable.php");
include_once("dkpPointsHistoryTable.php");
include_once("dkpPointsHistoryTableEntry.php");
include_once("dkpUtil.php");
include_once("dkpUserPermissions.php");

class dkpUpdater {

	/*===========================================================
	RETURN ERROR / UPDATE CODES
	============================================================*/
	//Update completed ok
	const UPDATE_OK = 1;
	//Current user does not have the required permissions to perform this
	//action
	const ERROR_NO_ACCESS = 2;

	const ERROR_INVALID_TABLEID = 3;

	const ERROR_TABLEID_TAKEN = 4;

	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//A dkpGuild instance of the current guild that is being updated
	var $guild;
	//The guildid of the dkpGuild instance that is being updated
	var $guildid;
	//A dkpSettings instance of the guild being created
	var $settings;

	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct($guildid)
	{
		//load the guild
		$this->guild = new dkpGuild();
		$this->guild->loadFromDatabase($guildid);

		//remember the guild id
		$this->guildid = $this->guild->id;

		//load the settings for this guild
		$this->settings = new dkpSettings();
		$this->settings->LoadSettings($guildid);
	}

	/*===========================================================
	Utility method - given an error code returned from one
	of the other functions in this class, this will generate
	a string representation.

	Parameters:
	$errorcode 	- The error code to convert to a string
	============================================================*/
	function GetErrorString($errorcode){
		if($errorcode == dkpUpdater::UPDATE_OK)
			return "Update Completed!";
		else if($errorcode == dkpUpdater::ERROR_NO_ACCESS)
			return "You do not have permissions to complete this task.";
		else if($errorcode == dkpUpdater::ERROR_INVALID_TABLEID)
			return "An invalid table id was selected.";
		else if($errorcode == dkpUpdater::ERROR_TABLEID_TAKEN)
			return "The entered tableid is already taken.";
		else
			return "Unknown Error";
	}

	/*===========================================================
	Returns true if a player with the given name exists
	============================================================*/
	function PlayerExists($name)
	{
		return dkpUtil::PlayerExists($name, $this->guild->server);
	}

	/*===========================================================
	Creates a new player with the given name and class.
	============================================================*/
	function CreatePlayer($name, $class, $guildname = null)
	{

		if(!$this->PlayerExists($name)) {
			if($guildname == null) {
				return dkpUtil::CreatePlayer($name, $class, $this->guildid, $this->guild->server, $this->guild->faction);
			}
			else {
				$guild = dkpUtil::EnsureGuildExists($guildname, $this->guild->server, $this->guild->faction);
				if(!$guild)
					return null;
				return dkpUtil::CreatePlayer($name, $class, $guild->id, $this->guild->server, $this->guild->faction);
			}
		}
	}
	/*===========================================================
	Returns a dkpPlayer instance of a player with the given name.
	Check $player->id to see if the returned player is valid
	or call PlayerExists ahead of time to make sure the player
	exists.
	============================================================*/
	function GetPlayer($name){
		return dkpUtil::GetPlayer($name, $this->guild->server);
	}

	/*===========================================================
	Ensures that the given player exists. If the player does not
	exist, it is created. Returns either the newly created player
	or one of the pre-existing players.

	Parameters:
	name - the name of the player
	class - the name of the class (only used if a player needs to be created)
	guildname - the name of the guild the player belongs to (only used if player
			    to be created. If null, the current guild is assumed
	============================================================*/
	function EnsurePlayerExists($name, $class, $guildname = -1)
	{
		if($guildname == -1 ) {
			return dkpUtil::EnsurePlayerExists($name, $class, $this->guild->id, $this->guild->server, $this->guild->faction);
		}
		else {
			$guildname = trim($guildname);
			$guild = dkpUtil::EnsureGuildExists($guildname, $this->guild->server, $this->guild->faction);
			if(!$guild)
				return null;

			return dkpUtil::EnsurePlayerExists($name, $class, $guild->id, $this->guild->server, $this->guild->faction);
		}
	}

	/*===========================================================
	Gets an array do dkpPointsTable instances for all tables
	for this guild
	============================================================*/
	function GetTables($ensureTableExists = true) {
		$tables = dkpPointsTable::getTableList($this->guildid);

		if($ensureTableExists) {
			$this->EnsureMainTableExists();
			$tables = dkpPointsTable::getTableList($this->guildid);
		}

		return $tables;
	}

	/*===========================================================
	Gets a list of all players in the selected table
	============================================================*/
	function GetPlayersInTable($tableid = 1){
		$players = array();
		global $sql;
		$tableid = sql::Escape($tableid);
		$guildid = sql::Escape($this->guild->id);
		$result = $sql->Query("SELECT *, dkp_users.id as userid
							   FROM dkp_users, dkp_points
							   WHERE dkp_points.guild='$guildid'
							   AND dkp_points.tableid='$tableid'
							   AND dkp_users.id = dkp_points.user
							   ORDER BY dkp_users.name ASC");
		while($row = mysqli_fetch_array($result)) {
			$player = new dkpUser();
			$player->loadFromRow($row);
			$players[] = $player;
		}

		return $players;
	}

	/*===========================================================
	Gets a list of all players in all of the guilds tables
	============================================================*/
	function GetPlayersInAllTables(){
		$players = array();
		global $sql;
		$guildid = sql::Escape($this->guild->id);
		$result = $sql->Query("SELECT *, dkp_users.id as userid
							   FROM dkp_users, dkp_points
							   WHERE dkp_points.guild='$guildid'
							   AND dkp_users.id = dkp_points.user
							   ORDER BY dkp_users.name ASC");
		while($row = mysqli_fetch_array($result)) {
			$player = new dkpUser();
			$player->loadFromRow($row);
			$players[] = $player;
		}

		return $players;
	}

	/*===========================================================
	Returns a table instance with the given id
	============================================================*/
	function GetTable($tableid){
		$table = new dkpPointsTable();
		$table->loadFromDatabaseByGuild($this->guildid, $tableid);
		return $table;
	}

	/*===========================================================
	Returns true if a table with the given id exists
	============================================================*/
	function TableExists($tableid){
		$table = $this->GetTable($tableid);
		return ($table->id != "");
	}

	/*===========================================================
	Attempts to update the name of the guilds table with the given
	tableid. Note that if the tableid does not exist, nothing will
	happen.
	============================================================*/
	function UpdateTableName($tableid, $newname){

		if (!$this->HasAccess("DKPTables"))
			return dkpUpdater::ERROR_NO_ACCESS;

		$table = new dkpPointsTable();
		$table->loadFromDatabaseByGuild($this->guildid, $tableid);

		$table->name = $newname;

		$table->save();

		return dkpUpdater::UPDATE_OK;
	}

	/*===========================================================
	Attempts to update the tableid of a table within the guild.
	============================================================*/
	function UpdateTableId($tableid, $newtableid, $skipsecurity = false) {

		if(!$skipsecurity && !$this->HasAccess("DKPTables"))
			return dkpUpdater::ERROR_NO_ACCESS;
		if($this->TableExists($newtableid))
			return dkpUpdater::ERROR_TABLEID_TAKEN;

		$table = $this->GetTable($tableid);
		$table->tableid = $newtableid;
		$table->save();

		//move all the data over
		global $sql;
		$tableid = sql::Escape($tableid);
		$newtableid = sql::Escape($newtableid);
		$guildid = sql::Escape($this->guild->id);
		$sql->Query("UPDATE IGNORE dkp_points SET tableid = '$newtableid' WHERE guild='$guildid' AND tableid='$tableid'");
		$sql->Query("UPDATE IGNORE dkp_awards SET tableid = '$newtableid' WHERE guild='$guildid' AND tableid='$tableid'");

		return dkpUpdater::UPDATE_OK;
	}

	/*===========================================================
	Makes sure that a tableid of 1 exists
	============================================================*/
	function EnsureMainTableExists(){
		$exists = $this->TableExists(1);

		if(!$exists) {

			$tables = dkpPointsTable::getTableList($this->guild->id);
			if(sizeof($tables) == 0 ) {
				$this->CreateTable("Main Table", true);
			}
			else {

				$this->UpdateTableId($tables[0]->tableid, 1, true);
			}
			return true;
		}
		return false;
	}

	/*===========================================================
	Creates a new dkp table with the given name
	============================================================*/
	function CreateTable($tablename, $skipsecurity = false) {

		if (!$skipsecurity && !$this->HasAccess("DKPTables"))
			return dkpUpdater::ERROR_NO_ACCESS;

		$table = new dkpPointsTable();
		$table->guild = $this->guild->id;
		$table->name = $tablename;
		$table->tableid = $this->GetNextTableId();
		$table->saveNew();

		return dkpUpdater::UPDATE_OK;
	}

	/*===========================================================
	Returns the next available tableid within a guild. This is
	used when creating new dkp tables.
	============================================================*/
	function GetNextTableId(){
		global $sql;

		//tables are already sorted by tableid
		$tables = $this->GetTables(false);

		//assume next id of 1. Iterate through
		//all tables, until we find the next
		//highest value that isn't taken
		$nextId = 1;
		foreach($tables as $table) {
			//if id taken, try to find next one
			//we can do this as long as $tables is sorted
			//by tableid
			if($table->tableid == $nextId)
				$nextId++;
		}

		return $nextId;
	}

	/*===========================================================
	Deletes a table with the given tableid
	WARNING!!!!!!!!!!!!!!!!!!!
	THIS DELETES ALL DATA IN THE TABLE TOO!!!!!!!!
	============================================================*/
	function DeleteTable($tableid) {
		if (!$this->HasAccess("DKPTables"))
			return dkpUpdater::ERROR_NO_ACCESS;

		//load the table
		$table = new dkpPointsTable();
		$table->loadFromDatabaseByGuild($this->guildid, $tableid);
		if(empty($table->id))
			return dkpUpdater::ERROR_INVALID_TABLEID;

		//delete the table
		$table->delete();

		//delete all related data
		$pointtable = dkpPointsTable::tablename;
		$pointstable = dkpPointsTableEntry::tablename;
		$historytable = dkpPointsHistoryTableEntry::tablename;
		global $sql;
		$guildid = sql::Escape($this->guildid);
		$tableid = sql::Escape($tableid);
		//delete points

		//dkp_pointhistory
		//dkp_pointhistory

		$sql->Query("DELETE FROM $pointtable WHERE guild='$guildid' AND tableid='$tableid'");
		$sql->Query("DELETE FROM $pointstable WHERE guild='$guildid' AND tableid='$tableid'");

		//delete history (linking players to awards)
		$sql->Query("DELETE FROM dkp_pointhistory USING dkp_awards, dkp_pointhistory
					 WHERE dkp_awards.guild = '$guildid' AND dkp_awards.tableid= '$tableid'
					 AND dkp_pointhistory.award = dkp_awards.id");
		//delete awards
		$sql->Query("DELETE FROM dkp_awards WHERE dkp_awards.guild = '$guildid' AND dkp_awards.tableid='$tableid'");


		//and done
		return dkpUpdater::UPDATE_OK;
	}
	/*===========================================================
	Returns true if the current user has access to the selected
	permission string. False otherwise
	============================================================*/
	function HasAccess($permission, $tableid = -1){
		return (dkpUserPermissions::currentUserHasPermission($permission,$this->guildid, $tableid));
	}


	/*===========================================================
	Returns true / false if the given player id is in the given table id

	Parameters:
	playerid - the player id to check
	tableid - the table to check if the player exists in. Assumes the main table
			  with the id of 1.
	============================================================*/
	function PlayerInTable($playerid, $tableid = 1)
	{
		global $sql;
		//if no point entry exists for them, create a new one from scratch
		$guildid = $this->guild->id;
		$exists = $sql->QueryItem("SELECT id FROM dkp_points WHERE guild='$guildid' AND tableid='$tableid' AND user='$playerid'");
		return ($exists!="");

	}
	/*===========================================================
	Returns true / false if the given player id is in any of the
	guilds tables.

	Parameters:
	playerid - the player id to check
	============================================================*/
	function PlayerInAnyTable($playerid){
		global $sql;
		$guildid = $this->guild->id;
		$playerid = sql::Escape($playerid);
		$exists = $sql->QueryItem("SELECT id FROM dkp_points WHERE guild='$guildid' AND user='$playerid'");
		return ($exists!="");
	}

	/*===========================================================
	Ensures that a player with the given id is in the given table.
	If they are nto already in the table, they are added to the table
	with 0 dkp and no history.
	============================================================*/
	function EnsurePlayerInTable($playerid, $tableid = 1){
		global $sql;

		if(!$this->PlayerInTable($playerid, $tableid)) {
			$sql->Query("INSERT INTO dkp_points SET
						 user='$playerid',
						 tableid='$tableid',
						 guild='$this->guildid',
						 lifetime='0',
						 points='0'");
		}
	}
	/*===========================================================
	Ensures that all the players in the playerids array in the given table.
	If they are not already in the table, they are added to the table
	with 0 dkp and no history.
	============================================================*/
	function EnsurePlayersInTable($playerids, $tableid){
		foreach($playerids as $playerid)
			$this->EnsurePlayerInTable($playerid, $tableid);
	}

	/*===========================================================
	Removes a player from a specific DKP table. This removes
	all table entries and history entries for this player. The data
	is deleted and can not be recovered.
	============================================================*/
	function RemovePlayerFromTable($playerid, $tableid = 1){
		global $sql;

		//if the player is not in the table, nothing to do
		if(!$this->PlayerInTable($playerid, $tableid))
			return;

		//escape all the data
		$guildid = sql::Escape($this->guild->id);
		$tableid = sql::Escape($tableid);
		$userid = sql::Escape($playerid);

		//delete the point entries
		$sql->Query("DELETE FROM dkp_points WHERE guild='$guildid' AND tableid = '$tableid' AND user='$userid'");

		//now to delete the point history entries. This is a bit tricky because we need to delete
		//only the users point history entries that are in this specific table. To do this, we first
		//perform an inner query that identifies the ids of all point histories in this table, then delete
		//all point histories who's id appears in this inner result.
		$sql->Query("DELETE FROM dkp_pointhistory
					 WHERE guild='$guildid' AND user='$userid'
					 AND id IN (
					 	SELECT id FROM (
					 	( SELECT dkp_pointhistory.id AS id FROM dkp_awards, dkp_pointhistory
						  WHERE dkp_awards.guild = '$guildid'
						  AND dkp_awards.tableid='$tableid'
						  AND dkp_awards.id = dkp_pointhistory.award
						  AND dkp_pointhistory.user = '$userid'
						 ) as temp )
					 )");
	}

	/*===========================================================
	TODO: !!!!!!!!!!!!
	============================================================*/
	function GetDkp($playername, $tableid = 1)
	{

	}

	function CloneAward($award){

	}

	/*===========================================================
	Adds DKP to the given player. This will take into account
	zerosum as well
	$playerid - 		the player to add dkp to
	$tableid - 			the table that is being worked with
	$award - 			a dkpAward instance that contains information about
			 			the award being made (see dkpUtil)
	$zeroSumPlayers - 	an array of players that should be given zerosum
					  	points as a result of this player being given dkp.
					  	Ignored if zerosum not used
	$ignoreZeroSum - 	if set to true, zerosum calculations will be skipped, even
					 	if the settings for the current guild require it
	============================================================*/
	function AddDkp($playerid, $tableid, & $award, $zeroSumPlayers = array() , $ignoreZeroSum = false )
	{

		global $sql;
		global $siteUser;

		//if an array of players was passed, use the correct method
		if (is_array($playerid)) {
			return $this->AddDkpToPlayers($playerid, $tableid, $award);
		}

		//make sure this user is in this table
		$this->EnsurePlayerInTable($playerid, $tableid);

		//update their lifetime and current dkp
		$this->AddDkpToTotal($playerid, $tableid, $award);

		//record the details of this award in the database
		$award->playercount = 1;
		$this->RecordAward($tableid, $award);

		//link the award to this users history
		$history = new dkpPointsHistoryTableEntry();
		$history->user = $playerid;
		$history->award = $award->id;
		$history->guild = $this->guild->id;
		$history->saveNew();

		//if zerosum is being used we need to add an equal but opposite amount
		//of dkp to the selected zeroSumPlayers
		if ( $this->settings->GetZerosumEnabled() && !$ignoreZeroSum) {
			$this->RecordZeroSumAward($playerid, $tableid, $award, $zeroSumPlayers);
		}
	}

	/*===========================================================
	Adds DKP to many players at once
	$playerids - 		an array of player ids to add dkp to
	$tableid - 			the table that is being worked with
	$award - 			a dkpAward instance that contains information about
			 			the award being made (see dkpUtil)
	============================================================*/
	function AddDkpToPlayers($playerids, $tableid, & $award){
		global $sql;

		//only handle multiple players
		if(!is_array($playerids))
			return;

		//make sure there is data to update
		if(sizeof($playerids) == 0 )
			return;

		//record the details of this award in the database
		$award->playercount = sizeof($playerids);
		$this->RecordAward($tableid, $award);

		//now to map the award to the players' history. To save time
		//we do this in one single compound query
		$awardid = $award->id;
		$guildid = $this->guild->id;

		//iterate through all the players we want to link to the award
		//and create an insert string
		$insertStrings = array();
		foreach($playerids as $playerid) {
			$temp = "('$awardid', '$playerid', '$guildid')";
			$insertStrings[]=$temp;
		}

		//now preform the insert all at once
		$insertStrings = implode(",",$insertStrings);
		$sql->Query("INSERT IGNORE INTO dkp_pointhistory (award, user, guild)
					 VALUES $insertStrings");

		//now that the award has been made, we can adjust the players lifetime
		//dkp and current dkp counts
		$this->AddDkpToTotalForPlayers($playerids, $tableid, $award->points);
	}




	/*===========================================================
	Deletes a history entry with the given history id.
	After the entry has been deleted this will also make any zerosum
	adjustments and adjust everyones dkp totals

	$historyid - 		id of the entry to delete
	============================================================*/
	function DeleteHistory($historyid){
		global $sql;
		global $siteUser;

		//load the history instance
		$history = new dkpPointsHistoryTableEntry();
		$history->loadFromDatabase($historyid);

		if(empty($history->id))
			return;

		//load the award for this history
		$award = $history->getAward();

		//delete the history entry
		$history->delete();

		//update the players total dkp to reflect the change
		$this->AdjustDkpTotalForPlayers(array($history->user), $award->tableid , $award->points , 0 );

		//update the awards player count variable
		$award->calculatePlayerCount();
		$award->save();

		//now for the wonderful zerosum calculations :(
		if($this->settings->GetZerosumEnabled()) {

			//1 - was an item - we need to remove the auto award for all players
			if($this->IsZerosumRootAward($award)) {
				$this->DeleteAward($award->id);
			}

			//2 - was an auto award - we need to distrubute these points to everyone else who got the
			//    autoaward so that zero sum is maintained
			else if($this->IsZerosumAutoAward($award)) {
				$this->UpdateZerosumAutoAward($award);
			}
		}

		//if this is a transfer award... we need to delete it completly
		//calling delete will also deleted the relatd transfer
		if($this->IsTransferAward($award)) {
			$this->DeleteAward($award->id);
		}

		//if it is an item, delete the award too
		if($award->playercount == 0 && $award->foritem == 1) {
			$this->DeleteAward($award->id);
		}
	}

	/*===========================================================
	Given a history entry, this will update the history entry to
	have new values. This can include new point values, names,
	etc. This will correctly adjust all needed related auto awards
	or root awards.

	$historyid -		Id of history entry to update
	$newHistory - 		An updated version of the original history
	============================================================*/
	/*function UpdateHistory($historyid, $newhistory) {
		global $sql;
		global $siteUser;

		$history = new dkpPointsHistoryTableEntry();
		$history->loadFromDatabase($historyid);
		if($siteUser->guild != $history->guild)
			return;

		$oldistory = $history;

		$history->reason = $newhistory->reason;
		$history->location = $newhistory->location;
		$history->user = $newhistory->user;
		$history->foritem = $newhistory->foritem;
		$history->points = $newhistory->points;
		$history->save();


		$isZeroSumRoot = $this->IsZerosumRootAward($history);
		if($oldhistory->points != $newhistory->points ||
		   	$oldhistory->user != $newhistory->user ||
		   	$oldhistory->tableid != $newhistory->tableid ) {

			$this->AdjustDkpTotal($oldhistory, 0);
			$this->AddDkpToTotal($newhistory->user, $newhistory->tableid, $newhistory->points);

			if($this->IsZerosumRootAward($oldhistory)){
				$this->UpdateRelatedZersosumAutoAwards($oldhistory, $newhistory);
			}
		}
	}*/

	/*===========================================================

	============================================================*/
	function DeleteAward($awardid, $deleteRelatedAwards = true){
		global $sql;

		$award = new dkpAward();
		$award->loadFromDatabase($awardid);
		if(empty($award->id))
			return;

		$this->AdjustDkpTotalForPlayersWithAward($award, $award->points, 0);

		//delete this award from players history
		global $sql;
		$awardid = sql::Escape($award->id);
		$guildid = sql::Escape($this->guild->id);
		$sql->Query("DELETE FROM dkp_pointhistory
					 WHERE guild='$guildid' AND
					 award = '$awardid'"); //guild check not needed... just speeds up query

		//delete the award itself
		$award->delete();

		//delete related awards
		if( $deleteRelatedAwards ) {
			//if deleted auto award, delete root
			if ($this->IsZerosumAutoAward($award)) {
				$other = $this->GetZerosumRootAward($award);
				$this->DeleteAward($other->id, false);
			}
			//if deleted root, delete auto
			else if( $this->IsZerosumRootAward($award) ) {
				$other = $this->GetZerosumAutoAward($award);
				$this->DeleteAward($other->id, false);
			}
			//if transfer, delete the other transfer
			else if( $this->IsTransferAward($award) ) {
				$other = $this->GetTransferAward($award);
				$this->DeleteAward($other->id, false);
			}
		}
	}

	/*===========================================================
	Returns true if a history entry for the current guild exists
	within the given table with the given reason and date
	============================================================*/
	function AwardExists($tableid, $reason, $date){
		global $sql;
		$tablename = dkpAward::tablename;
		$date = sql::Escape($date);
		$reason = sql::Escape($reason);
		$guild = sql::Escape($this->guild->id);
		$exists = $sql->QueryItem("SELECT id FROM $tablename
								   WHERE guild='$guild'
								   AND tableid='$tableid'
								   AND reason='$reason'
								   AND date='$date'");
		return ($exists != "");
	}

	/*===========================================================

	This method will correctly adjust for zerosum dkp.

	$historyid -		Id of history entry that is representative
						of all other history entries to update
	$newhistory - 		An updated version of the history entry
	$playerids - 		An array of player ids that should recieve
						the history entry. If a player is missing from
						the array who orignally had the award, their
						award will be removed and their dkp adjusted
						appropriatly.
	============================================================*/
	function UpdateAward($awardid, $newaward, $newplayerids = array()){
		global $sql;

		//load the old award
		$award = new dkpAward();
		$award->loadFromDatabase($awardid);

		//make sure the player count is up to date
		$award->calculatePlayerCount();
		$award->save();

		//see if we are switching tables...
		if($newaward->tableid != $award->tableid ) {
			//adjust points and move the award
			$this->AdjustDkpTotalForPlayersWithAward($award, $award->points, 0);
			$award->tableid = $newaward->tableid;
			$award->save();
			$this->EnsurePlayersInTable($award->getPlayerids(), $newaward->tableid);
			$this->AdjustDkpTotalForPlayersWithAward($award, 0, $award->points);

			//check to see if there are any other awards linked to this one
			//they should be moved too
			if($award->linked != 0 ) {
				//load the linked
				$other = new dkpAward();
				$other->loadFromDatabase($award->linked);
				//adjust dkp and lifetime from the old table id
				$this->AdjustDkpTotalForPlayersWithAward($other, $other->points, 0);
				//move it to the new table
				$other->tableid = $award->tableid;
				$other->save();
				//give dkp for thos players on the new table
				$this->EnsurePlayersInTable($other->getPlayerids(), $newaward->tableid);
				$this->AdjustDkpTotalForPlayersWithAward($other, 0, $other->points);
			}

			//at this point it is as if the award started out in this
			//table in the first place, so we can go on with the rest
			//of our calculations normally.
		}

		//see if any of the dkp values changed
		if( $newaward->points != $award->points ) {

			//update the award
			$this->AdjustDkpTotalForPlayersWithAward($award, $award->points, 0);
			$award->points = $newaward->points;
			$award->save();
			$this->AdjustDkpTotalForPlayersWithAward($award, 0, $award->points);

			//zerosum adjustments to related awards are handled later...

			//if this is a transfer award, we need to update its points to
			if($this->IsTransferAward($award)) {
				//load it
				$other = $this->GetTransferAward($award);
				//undo its adjustments
				$this->AdjustDkpTotalForPlayersWithAward($other, $other->points, 0);
				//change it
				$other->points = $award->points * -1;
				//redo its adjustments
				$this->AdjustDkpTotalForPlayersWithAward($other, 0, $other->points);
				//save
				$other->save();
			}
		}

		//update the other fields
		$award->reason = $newaward->reason;
		$award->location = $newaward->location;
		$award->awardedby = $newaward->awardedby;
		$award->foritem = $newaward->foritem;
		$award->save();

		//if award is for an item, we have a limit of only
		//1 player recieving it
		if( $award->foritem && sizeof($newplayerids) > 1) {
			$newplayerids = array($newplayerids[0]);
		}

		//find out who is new and who is gone

		//load the players who already have it

		$oldplayerids = $award->getPlayerids();

		$new = array();
		$gone = array();
		foreach($oldplayerids as $oldid) {
			if(!in_array($oldid, $newplayerids))
				$gone[] = $oldid;
		}

		foreach($newplayerids as $newid) {
			if(!in_array($newid, $oldplayerids))
				$new[] = $newid;
		}

		//remove the award from those who no longer have it
		$this->DeleteAwardForPlayers($award, $gone);

		//add the award for those who now do
		$this->AddAwardForPlayers($award, $new);

		//recalculate the number of players who now have this award
		$award->calculatePlayerCount();
		$award->save();

		//now for special zerosum checks
		if($this->settings->GetZerosumEnabled()) {

			//if the award is for an item, make sure that a related auto award exists.
			//If the user just made this award an item, go ahead and make the auto award
			if( $award->foritem && $award->linked == 0 ) {
				//make sure there isn't one that exists that we should already be linked to
				$root = sql::Escape($this->GetZerosumRootReason($reason));
				$linked = $sql->Query("SELECT id FROM dkp_awards
										WHERE guild='$award->guild'
										AND tableid='$award->tableid' AND date='$award->date'
										AND reason='$root'");
				if( $linked != "" ) {
					$sql->Query("UPDATE dkp_awards SET linked = '$linked' WHERE id='$id'");
					$sql->Query("UPDATE dkp_awards SET linked = '$id', zerosumauto='1' WHERE id='$linked'");
				}
				else {
					//no other linked one exists, lets create one
					$playerid == -1;
					if(sizeof($newplayerids) > 0)
						$playerid = $newplayerids[0];
					$this->RecordZeroSumAward($playerid, $award->tableid, $award);
				}
			}

			//if this is a root or auto award, make sure the auto
			//award is up to date
			if($this->IsZerosumRootAward($award) || $this->IsZerosumAutoAward($award)) {
				$this->UpdateZerosumAutoAward($award);
			}
		}

		//if it was a transfer award, make sure that the
		//reasons between the two linked awards are matched.
		if($this->IsTransferAward($award)) {
			$this->UpdateTransferReasons($award);
		}
	}



	function UpdateTransferReasons(&$award){

		$other = $this->GetTransferAward($award);

		if(empty($other->id) || empty($award->id))
			return;
		if($other->points > $award->points) {
			$to = $other;
			$from = $award;
		}
		else {
			$to = $award;
			$from = $other;
		}

		$to->loadPlayer();
		$from->loadPlayer();

		$to->reason = "Transfer from ".$from->player->name." to ".$to->player->name;
		$from->reason = $to->reason;

		$to->save();
		$from->save();
	}

	function UpdateZerosumAutoAward(&$award) {

		//get the root and auto award
		$root = $this->GetZerosumRootAward($award);
		$auto = $this->GetZerosumAutoAward($award);

		//make sure we have valid awards
		if(empty($root->id) || empty($auto->id))
			return;

		//see if the points for the auto award have changed
		//based on either the number of recipients changeing
		//or the amount given for the root awrad changing
		if($auto->playercount == 0 )
			$points = 0;
		else
			$points = ( $root->points * -1 ) / $auto->playercount;
		if($points != $auto->points ) {
			//adjust recieving players dkp
			$this->AdjustDkpTotalForPlayersWithAward($auto, $auto->points, $points);
			$auto->points = $points;
			$auto->save();
		}

		//check to see if the root reason was renamed
		//if it was, rename the auto award too
		$expectedRootReason = $this->GetZerosumRootReason($auto->reason);
		if($expectedRootReason != $root->reason ) {
			$auto->reason = "Zerosum: ".$root->reason;
			$auto->save();
		}

		if( $auto->location != $root->location ||
		    $auto->awardedby != $root->awardedby ) {
			$auto->location = $root->location;
			$auto->awardedby = $root->awardedby;
			$auto->save();
		}
	}

	function AddAwardForPlayers($award, $playerids){
		global $sql;

		if( sizeof($playerids) == 0 )
			return;

		$awardid = $award->id;
		$guildid = $this->guild->id;

		//make sure all the players are in the required table
		$this->EnsurePlayersInTable($playerids, $award->tableid);


		//iterate through all the players we want to link to the award
		//and create an insert string
		$insertStrings = array();
		foreach($playerids as $playerid) {
			$temp = "('$awardid', '$playerid', '$guildid')";
			$insertStrings[]=$temp;
		}

		//now preform the insert all at once
		$insertStrings = implode(",",$insertStrings);
		$sql->Query("INSERT IGNORE INTO dkp_pointhistory (award, user, guild)
					 VALUES $insertStrings");

		$this->AdjustDkpTotalForPlayers($playerids, $award->tableid , 0 , $award->points );
		//$this->AddDkpToPlayers($playerids, $award->tableid, $award);
	}

	function DeleteAwardForPlayers($award, $playerids){
		global $sql;

		if(sizeof($playerids) == 0 )
			return;

		$this->AdjustDkpTotalForPlayers($playerids, $award->tableid, $award->points, 0);

		$playerStrings = array();
		foreach($playerids as $id) {
			$playerStrings[] = " dkp_pointhistory.user = '$id' ";
		}
		$playerClause = implode(" OR ", $playerStrings);

		$awardid = sql::Escape($award->id);

		$sql->Query("DELETE FROM dkp_pointhistory USING dkp_pointhistory, dkp_awards
					 WHERE dkp_awards.id = '$awardid' AND dkp_pointhistory.award = dkp_awards.id
					 AND ( $playerClause )");
	}

	function UpdateTransfersForAlts(&$award, $olddkp, $newdkp){
		global $sql;

		//figure out the change
		$delta = $newdkp - $olddkp;

		//if no point change, don't botter with any calculations
		if($detla == 0)
			return;

		//if its a transfer itself... don't do anything
		if($award->transfer == 1)
			return;
		//if automatted transfers arn't enabled, don't do anything
		if(!$this->settings->GetCombineAltsEnabled())
			return;

		//find the players who recieved the award AND have mains
		$players = $award->loadPlayers();
		$playerids = array();
		foreach($players as $player) {
			if($player->main != "0") {
				$playerids[] = $player->id;
			}
		}
		//award involed no alts - no work to do
		if(sizeof($playerids) == 0)
			return;

		//for each of these players we will need to see
		//if they have a transfer award located somewhere
		//AFTER this award was just edited. If they have an
		//existing transfer, we modify it. If they don't have
		//an existing transfer (would be odd..., but you never know),
		//we create one for them
		$guildid = sql::Escape($this->guildid);
		$tableid = sql::Escape($award->tableid);
		$awarddate = sql::Esacpe($award->date);
		foreach($playerids as $playerid) {
			//see if they have a transfer award after the edited award
			$userid = sql::Escape($playerid);
			$row = $sql->QueryRow("SELECT * FROM dkp_awards, dkp_pointhistory
								   WHERE dkp_awards.guild='$guildid'
								   AND dkp_awards.tableid='$tableid'
								   AND dkp_poionthistory.user = '$userid'
								   AND dkp_pointhistory.award = dkp_awards.id
								   AND dkp_awards.date >=  '$awarddate'
								   AND dkp_awards.transfer = 1
								   ORDER BY date ASC");
			$transfer = new dkpAward();
			$transfer->loadFromRow($row);
			//make sure a transfer to edit exists
			if($transfer->id != "") {
				//a transfer does not exist - make one
				$mainid = $sql->QueryItem("SELECT main FROM dkp_users WHERE id='$userid'");
				$this->TransferDkp($playerid, $mainid);
			}
			else {
				//a trasnfer already exists - update it
				$newpoints = $transfer->points + $delta;
				$this->AdjustDkpTotalForPlayersWithAward($transfer, $transfer->points, $newpoints );
				$transfer->points = $newpoints;
				$transfer->save();
			}
		}
	}

	/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	The following are helper methods used by the other updating
	methods. The should be considered PRIVATE and should not
	be called externally
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/


	/*===========================================================
	Given an award that has already been made, this method will
	record a corresponding zerosum award to a set of player identified
	in the zeroSumPlayers array

	$playerid - 		id fo the player who got the root award
	$tableid - 			the table that is being worked with
	$award - 			a dkpAward instance that contains information about
			 			the award being made
	$zerosumPlayers - 	An array of playerids of players who should get
						dkp as a result of the root award
	============================================================*/
	function RecordZeroSumAward($playerid, $tableid, &$award, $zeroSumPlayers = array() ){
		//if the array is empty, there is nothing we can do
		if( sizeof($zeroSumPlayers) == 0 )
			return;

		if( $playerid != -1 && !in_array($playerid,$zeroSumPlayers))
			$zeroSumPlayers[]  = $playerid;

		$autoaward = clone($award);
		$autoaward->id = "";
		$autoaward->points = ($award->points * -1) / sizeof($zeroSumPlayers);
		$autoaward->reason = "Zerosum: ".$award->reason;
		$autoaward->foritem = 0;
		$autoaward->linked = $award->id;
		$autoaward->zerosumauto = 1;

		$this->AddDkpToPlayers($zeroSumPlayers, $tableid, $autoaward);

		$award->linked = $autoaward->id;
		$award->save();
	}

	/*===========================================================
	Adds a given DKP ammount to the given players total. This includes:
	- their lifetime dkp
	- their current total dkp for a table

	$playerid - 		id fo the player who got the root award
	$tableid - 			the table that is being worked with
	$points - 			the number of points to add (can be + or - )
	$filterlifetime - 	(optional) Allows you to skip the lifetime check.
					    Ussually adjustments to lifetime dkp will not be
					    made if the points data is < 0. If this is set to false,
					    negative adjustments to lifetime dkp will be allowed
	============================================================*/
	function AddDkpToTotal($playerid, $tableid, $points, $filterlifetime = true){

		if(is_array($playerid)) {
			return $this->AddDkpToTotalForPlayers($playerid, $tableid, $points, $filterlifetime);
		}

		if(is_a($points,"dkpPointsHistoryTableEntry") || is_a($points,"dkpAward"))
			$points = $points->points;
		else
			$points = $points;

		//add dkp to their total dkp and lifetime dkp
		$pointEntry = new dkpPointsTableEntry();
		$pointEntry->loadFromDatabaseByGuild($this->guild->id, $playerid, $tableid);
		$pointEntry->points += $points;

		if( !$filterlifetime || $points > 0 )
			$pointEntry->lifetime += $points;

		$pointEntry->save();
	}

	/*===========================================================
	Adds a given DKP ammount to multiple players at once. This includes:
	- their lifetime dkp
	- their current total dkp for a table

	$playerids - 		ids of the player who got the root award
	$tableid - 			the table that is being worked with
	$points - 			the number of points to add (can be + or - )
	$filterlifetime - 	(optional) Allows you to skip the lifetime check.
					    Ussually adjustments to lifetime dkp will not be
					    made if the points data is < 0. If this is set to false,
					    negative adjustments to lifetime dkp will be allowed
	============================================================*/
	function AddDkpToTotalForPlayers($playerids, $tableid, $points, $filterlifetime=true){
		global $sql;

		if(sizeof($playerids) == 0)
			return;

		if(is_a($points,"dkpPointsHistoryTableEntry"))
			$points = $points->points;
		else
			$points = $points;

		if($points=="")
			$points = 0;

		if(!$filterlifetime || $points > 0)
			$deltaLifetime = $points;
		else
			$deltaLifetime = 0;

		$whereStrings = array();
		foreach($playerids as $playerid) {
			if($playerid != "") {
				$temp = "dkp_points.user = ".sql::Escape($playerid);
				$whereStrings[]=$temp;
			}
		}

		if(sizeof($whereStrings) == 0 )
			return;

		$whereClause = implode(" OR ", $whereStrings);
		$guildid = sql::Escape($this->guild->id);
		$sql->Query("UPDATE dkp_points
					 SET dkp_points.points = dkp_points.points + $points, dkp_points.lifetime=dkp_points.lifetime + $deltaLifetime
					 WHERE guild = '$guildid' AND tableid='$tableid' AND ( $whereClause )");
	}

	/*===========================================================
	Given an award, this will save that award to the database
	for the given player and guild.

	$playerid - 		id fo the player who got the root award
	$tableid - 			the table that is being worked with
	$award - 			The award to save to the database
	============================================================*/
	function RecordAward($tableid, & $award){
		$award->guild = $this->guild->id;
		$award->tableid = $tableid;
		$award->saveNew();
		if(empty($award->date)) {
			$award->timestamp();
			$award->loadFromDatabase($award->id);
		}
	}


	/*===========================================================
	Adjusts the dkp totals for only players who have the given award.
	This is the same as AdjustDkpTotal but it will only apply changes
	to players who have the current award in thier history.

	For example, given a current
	award and a new dkp value to be represented by that award, this method
	will adjust everyones total dkp and lifetime dkp appropriatly.

	$award - the award that is being adjusted
	$newdkp - the new dkp value that is to be represented by this award

	NOTE - a common use of this method is to 'undo' an award. Ie, you
	pass an award instance and 0 for $newdkp. This will correctly
	update the table as if the given award never existed for any players.
	============================================================*/
	function AdjustDkpTotalForPlayersWithAward($award, $olddkp, $newdkp){

		//first, figure out the delta for the total dkp
		$deltaDkp = $newdkp - $olddkp;

		//now to calculate the delta to the total dkp
		$deltaTotalDkp = $this->GetDeltaLifetimeDkp($olddkp, $newdkp);

		$deltaDkp = sql::Escape($deltaDkp);
		$deltaTotalDkp = sql::Escape($deltaTotalDkp);
		$awardid = sql::Escape($award->id);
		$guildid = sql::Escape($award->guild);
		$tableid = sql::Escape($award->tableid);

		global $sql;
		$sql->Query("UPDATE dkp_points, dkp_pointhistory, dkp_awards
					 SET dkp_points.points=dkp_points.points + $deltaDkp, dkp_points.lifetime=dkp_points.lifetime + $deltaTotalDkp
					 WHERE dkp_awards.id = '$awardid'
					 AND dkp_pointhistory.award = dkp_awards.id
					 AND dkp_points.user = dkp_pointhistory.user
					 AND dkp_points.guild = dkp_awards.guild
					 AND dkp_points.tableid = dkp_awards.tableid");
	}

	/*===========================================================
	Adjusts the dkp totals for only players in the given array

	For example, given a current
	award and a new dkp value to be represented by that award, this method
	will adjust everyones total dkp and lifetime dkp appropriatly.
	============================================================*/
	function AdjustDkpTotalForPlayers($playerids, $tableid, $olddkp, $newdkp){

		//first, figure out the delta for the total dkp
		$deltaDkp = $newdkp - $olddkp;

		//now to calculate the delta to the total dkp
		$deltaTotalDkp = $this->GetDeltaLifetimeDkp($olddkp, $newdkp);

		$deltaDkp = sql::Escape($deltaDkp);
		$deltaTotalDkp = sql::Escape($deltaTotalDkp);
		$guildid = sql::Escape($this->guild->id);
		$tableid = sql::Escape($tableid);

		//create a player clause string that will identify the players
		$playerStrings = array();
		foreach($playerids as $id) {
			$playerStrings[] = " dkp_points.user = '$id' ";
		}
		$playerClause = implode(" OR ", $playerStrings);

		global $sql;
		//make the update
		$sql->Query("UPDATE dkp_points
					 SET dkp_points.points=dkp_points.points + $deltaDkp, dkp_points.lifetime=dkp_points.lifetime + $deltaTotalDkp
					 WHERE dkp_points.guild = '$guildid'
					 AND dkp_points.tableid = '$tableid'
					 AND ( $playerClause )");
	}

	/*===========================================================
	Given an old and a new dkp value for an award, this method
	will return the appropriate change that should be made to a players
	lifetime dkp given that change.
	This must appropriatly take into account adjustments that may have
	taken the award from being + to - and just how much the lifetime
	dkp value should be adjusted.

	$oldkp - 	the original dkp value represented by the award
	$newdkp - 	the new dkp value that is to be represented by this award

	Returns: the amount that a players lifetime dkp should be adjusted by
			 given the change between the two values
	============================================================*/
	function GetDeltaLifetimeDkp($olddkp, $newdkp){

		//we can adjust the total dkp easily enough, but the lifetime
		//dkp needs to be adjusted by the following rules:

		$deltaTotalDkp = 0;

		//Case 1: Originally had + award, now have - award
		//		  lifetime only goes down based on what they originally had
		if( $olddkp >= 0 && $newdkp <= 0 )
			$deltaTotalDkp = -$olddkp;
		//Case 2: Originally had + award, now also have a + award
		//		  lifetime changes based on the different of the two
		else if ( $olddkp >= 0 && $newdkp >= 0)
			$deltaTotalDkp = $newdkp - $olddkp;
		//Case 3: Originally had a - award, now have a + award
		// 		  liftime only goes up based on the new award
		else if($olddkp <= 0 && $newdkp >=0 )
			$deltaTotalDkp = $newdkp;
		//Case 4: Originally had a - award, now also have a - award
		// 		  lifetime dkp does not change
		else if($olddkp <= 0 && $newdkp <= 0)
			$deltaTotalDkp = 0;

		return $deltaTotalDkp;
	}

	/*===========================================================
	Given an award, this will return the zerosum root award
	============================================================*/
	function GetZerosumRootAward($award){

		if( $award->zerosumauto == 1 ) {
			$root = new dkpAward();
			$root->loadFromDatabase($award->linked);
			return $root;
		}
		else {
			return $award;
		}
	}

	/*===========================================================
	Given a root zerosum award, this will return the zerosum auto
	award
	============================================================*/
	function GetZerosumAutoAward($award) {

		if( $award->zerosumauto == 1 ) {
			return $award;
		}
		else {
			$auto = new dkpAward();
			$auto->loadFromDatabase($award->linked);
			return $auto;
		}
	}

	/*===========================================================
	Returns true if the given award is a zerosum auto award
	============================================================*/
	function IsZerosumAutoAward(&$award){
		return ($award->zerosumauto == 1 );
	}

	/*===========================================================
	Returns true if the given award is a zerosum root award
	that resulted in other zerosum awards being created
	============================================================*/
	function IsZerosumRootAward(&$award){

		if( $award->linked == 0 )
			return false;

		$other = new dkpAward();
		$other->loadFromDatabase($award->linked);

		return ( $other->zerosumauto == 1 );
	}

	function IsTransferAward(&$award){
		return ( $award->transfer == 1 );
	}

	function GetTransferAward(&$award){
		$other = new dkpAward();
		$other->loadFromDatabase($award->linked);
		return $other;
	}

	function LinkZerosum(){
		global $sql;
		$guildid = $this->guild->id;
		$result = $sql->Query("SELECT * FROM dkp_awards WHERE guild='$guildid' AND zerosumauto='1' AND linked='0'");
		while( $row = mysqli_fetch_array($result) ) {
			$id = $row["id"] ?? null;
			$guild = $row["guild"] ?? null;
			$reason = $row["reason"] ?? null;
			$tableid = $row["tableid"] ?? null;
			$date = $row["date"] ?? null;

			$root = sql::Escape($this->GetZerosumRootReason($reason));

			$linked = $sql->QueryItem("SELECT id FROM dkp_awards
									WHERE guild='$guild'
									AND tableid='$tableid' AND date='$date'
									AND reason='$root'");
			if( $linked != "" ) {
				$sql->Query("UPDATE dkp_awards SET linked = '$linked' WHERE id='$id'");
				$sql->Query("UPDATE dkp_awards SET linked = '$id' WHERE id='$linked'");
			}
		}
	}

	function GetZerosumRootReason($reason){
		if( stripos($reason,"Zerosum: ") === false )
			return $reason;
		else {
			$temp = str_replace("Zerosum: ", "", $reason);
			return str_replace("ZeroSum: ", "", $temp);
		}
	}

	/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	Methods related to transfering DKP and moving dkp from alts to mains.
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

	/*===========================================================
	Combines all alts with their mains in the given guild id.
	This means that all alts will have their current dkp and dkp history
	transfered to their main character. The alts will be left empty
	aftewards with a link to their main.
	============================================================*/
	function CombineAltsWithMain(){
		//return;
		global $sql;
		$guildid = sql::Escape($this->guild->id);
		//get all the alts with dkp
		$result = $sql->Query("SELECT dkp_users.id as userid, dkp_users.main as main
							   FROM dkp_users, dkp_points
							   WHERE dkp_points.guild='$guildid' AND
							   dkp_users.id = dkp_points.user AND
							   dkp_users.main!='0' AND
							   dkp_users.main!=dkp_users.id AND
							   dkp_points.points!='0'");
		//alts may appear multiple times (once for each point table) so keep
		//track to eliminate redundant transfers
		$alreadyTransfered = array();
		//for each of the alts, transfer the dkp to the main player
		while($row = mysqli_fetch_array($result)){
			$main = $row["main"] ?? null;
			$alt = $row["userid"] ?? null;
			if ( !in_array($alt, $alreadyTransfered) ) {
				$this->TransferDkp($alt, $main);
				$alreadyTransfered[] = $alt;
			}
		}
	}


	/*===========================================================
	Transfers points from one player to another. This only transfers DKP
	and NOT history. A new history event will be recorded in both players noting
	the dkp transfer

	$guild = the id of the guild where this transfer is taking place
	$sourcePlayerId = the player's whos history / points are being transfered
	$destinationPlayerId = the id of the player who is going to get all the new points
	============================================================*/
	function TransferDkp($sourcePlayerid, $destPlayerid){
		global $sql;
		global $siteUser;
		//make sure the users ids are valid
		if(empty($sourcePlayerid))
			return;
		if(empty($destPlayerid))
			return;

		//load the source and dest player
		$source = new dkpUser();
		$source->loadFromDatabase($sourcePlayerid);
		$dest = new dkpUser();
		$dest->loadFromDatabase($destPlayerid);

		//make sure the destination player is in at least one of our tables
		if (!$this->PlayerInAnyTable($dest->id))
			return;

		$sourceName = sql::Escape($source->name);
		$destName = sql::Escape($dest->name);
		$sourceid = sql::Escape($source->id);
		$guildid = sql::Escape($this->guild->id);

		//We need to get the source players dkp from all the tables.
		//We then need to iterate through each of these and move these points to the
		//destination player
		$result = $sql->Query("SELECT tableid, points
					 		   FROM dkp_points
					 		   WHERE user='$sourceid'
							   AND guild='$guildid'");

		while($row = mysqli_fetch_array($result)){

			$points = $row["points"] ?? null;
			$tableid = $row["tableid"] ?? null;

			if($points != 0){

				//create the award
				$fromAward = new dkpAward();
				$fromAward->guild = $this->guild->id;
				$fromAward->points = $points;
				$fromAward->reason = "Transfer from $sourceName to $destName";
				$fromAward->location = "WebDKP";
				$fromAward->awardedby = $siteUser->username;
				$fromAward->foritem = 0;
				$fromAward->transfer = 1;

				//add points to dest
				$this->AddDkp($dest->id, $tableid, $fromAward);

				//remove points from source
				$toAward = clone($fromAward);
				$toAward->reason = "Transfer from $sourceName to $destName";
				$toAward->points = $points * -1;
				$this->AddDkp($source->id, $tableid, $toAward);

				//link the two awards so they point to each other
				$fromAward->linked = $toAward->id;
				$fromAward->save();
				$toAward->linked = $fromAward->id;
				$toAward->save();
			}
		}
	}
}
?>