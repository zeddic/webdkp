<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Represents a points history table for a single player within a
givens guild dkp table.
This is a record of all dkp awards and expenses for a single player.
*/

include_once("dkpPointsHistoryTableEntry.php");

class dkpPointsHistoryTable {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/

	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{

	}

	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed user name. The table id identifies which
	of the guilds dkp table's to load data from. Defaults to 1
	============================================================*/
	function loadFromDatabase($playerid, $guildid, $tableid = 1, $limit = -1)
	{
		//check if limit data was passed
		if($limit != -1) {
			$limit = strtolower($limit);
			$limit = str_replace("limit","",$limit);//remove the limit tag if they added it
			$limit = sql::Escape($limit);
			$limitClause = " LIMIT $limit";
		}
		global $sql;

		//sanatize data
		$playerid = sql::Escape($playerid);
		$guildid = sql::Escape($guildid);
		$tableid = sql::Escape($tableid);

		$historytable = dkpPointsHistoryTableEntry::tablename;
		$result = $sql->Query("SELECT * FROM $historytable
							   WHERE user='$playerid'
							   AND guild='$guildid'
							   AND tableid='$tableid'
							   ORDER BY date DESC $limitClause");
		//load in data
		while($row = mysqli_fetch_array($result)) {
			$dkpPointsEntry = new dkpPointsHistoryTableEntry();
			$dkpPointsEntry->loadFromRow($row);
			$this->table[] = $dkpPointsEntry;
		}
		if($this->table!="")
			$this->numberOfRows = count($this->table);
		$this->tableid = $tableid;
	}

	/*===========================================================
	loadLootFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed user id and guild. This method will only load
	entries that were LOOT for the player. If no guild id is passed
	it loads up all the loot for the player spanning all guilds
	Tableid specifies which of a guilds tables to get the loot for.
	Defaults to -1 which specifies all of the guilds tables
	============================================================*/
	function loadLootFromDatabase($playerid,$guildid=-1, $tableid=-1, $limit=-1){
		global $sql;
		$playerid = sql::Escape($playerid);
		$guildid = sql::Escape($guildid);
		$tablelid = sql::Escape($tableid);
		$guildCondition = ($guildid!=-1?" AND guild='$guildid' ":"");
		$tableCondition = ($tableid!=-1?" AND tableid='$tableid' ":"");

		//if a limit amount was set - create a limit clause
		if($limit != -1) {
			$limit = strtolower($limit);
			$limit = str_replace("limit","",$limit);//remove the limit tag if they added it
			$limit = sql::Escape($limit);
			$limitClause = " LIMIT $limit";
		}

		//perform the query
		$historytable = dkpPointsHistoryTableEntry::tablename;
		$result = $sql->Query("SELECT * FROM $historytable
							   WHERE user='$playerid'
							   $guildCondition
							   $tableCondition
							   AND forItem=1
							   ORDER BY date DESC $limitClause");

		//load data
		while($row = mysqli_fetch_array($result)) {
			$dkpPointsEntry = new dkpPointsHistoryTableEntry();
			$dkpPointsEntry->loadFromRow($row);
			$this->table[] = $dkpPointsEntry;
		}
		if($this->table!="")
			$this->numberOfRows = count($this->table);

		$this->tableid = $tableid;
	}

	/*===========================================================
	loadGuildLootHistoryFromDatabase(
	Loads a point history table for a given guild and table, composed
	only of loot history.

	Parameters:
	$guildid - id of the guild to get data for
	$tableid - the id of the table within the guild to get data for
	$limit - an optional limit clause to constrain how many rows should be gathered
			 For example, "5" "20". This is the same as a normal limit clause for
			 sql, so limit could also be "5,20" which would be the first 20 rows
			 after the 5th row. Here so that you could query 'pages' of data at a time.
	============================================================*/
	function loadGuildLootHistoryFromDatabase($guildid, $tableid = 1, $limit=-1)
	{
		global $sql;
		//check if a limit class was passed
		if($limit != -1) {
			$limit = strtolower($limit);
			$limit = str_replace("limit","",$limit);//remove the limit tag if they added it
			$limit = sql::Escape($limit);
			$limitClause = " LIMIT $limit";
		}

		//dkp_pointhistory.id, dkp_users.id as userid, name, points, reason, location, date, class
		$usertable = dkpUser::tablename;
		$historytable = dkpPointsHistoryTableEntry::tablename;
		$result = $sql->Query("SELECT *,
					$usertable.id AS userid, $historytable.id AS historyid
					FROM $historytable, $usertable
					WHERE forItem='1'
					AND $historytable.guild='$guildid'
					AND $historytable.user = $usertable.id
					AND tableid = '$tableid'
					ORDER BY $historytable.date DESC $limitClause"); //

		//load the data
		while($row = mysqli_fetch_array($result)) {
			$dkpPointsEntry = new dkpPointsHistoryTableEntry();
			$dkpPointsEntry->loadFromRow($row);
			$user = new dkpUser();
			$user->loadFromRow($row);
			$dkpPointsEntry->user = $user;
			$this->table[] = $dkpPointsEntry;
		}
		if($this->table!="")
			$this->numberOfRows = count($this->table);

		$this->tableid = $tableid;
	}

	/*===========================================================
	getGuildLootHistoryCount($guildid, $tableid)
	Returns the total number of rows in a guilds loot history table. This is
	manually used in conjunction loadGuildLootHistoryFromDatabase() where
	limits are used and you still want to know the total possible rows.

	Parameters:
	$guildid - id of the guild to get data for
	$tableid - the id of the table within the guild to get data for
	============================================================*/
	function getGuildLootHistoryCount($guildid, $tableid = 1){
		global $sql;

		$historytable = dkpPointsHistoryTableEntry::tablename;
		$numRows = $sql->QueryItem("SELECT count(*)
			FROM $historytable
			WHERE forItem='1'
			AND $historytable.guild='$guildid'
			AND tableid = '$tableid'");
		return $numRows;
	}

	/*===========================================================
	containsEntry($reason)
	Returns true if the current loaded table contans a history entry
	with the given reason
	============================================================*/
	function containsEntry($reason){
		$reason = strtolower($reason);
		if($this->table != ""){
			foreach($this->table as $entry){
				if(strtolower($entry->reason) == $reason)
					return true;
			}
		}
		return false;
	}

	/*===========================================================
	getEntry($reason)
	Returns an entry instance in this table with the specifeid
	name. If no entry is found with the specified reason, false is returned
	============================================================*/
	function getEntry($reason){
		$reason = strtolower($reason);
		if($this->table != ""){
			foreach($this->table as $entry){
				if(strtolower($entry->reason) == $reason)
					return $entry;
			}
		}
		return false;
	}


	/*===========================================================
	getCount
	Returns the total number of point history entries for the given
	player / guild / tableid combo
	============================================================*/
	/*function getCount($playerid, $guildid, $tableid){
		global $sql;
		$count = $sql->QueryItem("SELECT count(*) AS count
		 					  FROM dkp_pointhistory WHERE user='$playerid' AND guild='$guildid' AND tableid='$tableid'");
		return $count;

	}*/

	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	/*function save()
	{

	}*/
	/*===========================================================
	saveNew()
	Saves data into the backend database as a new row entry. After
	calling this method $id will be filled with a new value
	matching the new row for the data
	============================================================*/
	/*function saveNew()
	{

	}*/
	/*===========================================================
	delete()
	Deletes the row with the current id of this instance from the
	database
	============================================================*/
	/*function delete()
	{

	}*/
	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if the given entry exists in the database
	database
	============================================================*/
	/*function exists($name)
	{

	}*/
}
?>