<?php
include_once("dkpPointsTableEntry.php");
include_once("dkpUser.php");

class dkpPointsTable {

	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id; 			//unique id for this table
	var $guild;			//the id of the guild whos table this is
	var $tableid;		//the id of this table
						//Unique only within a guild. IE, each guild will have a
						//dkp table 1 , 2 , 3 , etc. Count starts at 1 (not 0)
	var $name;			//The unique name of this table

	//The following values are loaded On Demand at run time.
	var $table = array();//The actual tables data. (An array of dkpPointsTableEntry instances)
						//LoadTable must be called to obtain this data.
	var $numberOfRows;		//The number of entries in the table.
	var $tablename;
	const tablename = "dkp_tables";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpPointsTable::tablename;
	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabase($id)
	{
		global $sql;
		$id = sql::Escape($id);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed guildid and tableid. The tableid is optional
	and will assume 1 if not passed.
	============================================================*/
	function loadFromDatabaseByGuild($guildid, $tableid)
	{
		global $sql;
		$guildid = sql::Escape($guildid);
		$tableid = sql::Escape($tableid);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename
							   WHERE guild='$guildid'
							   AND tableid='$tableid'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->guild = $row["guild"] ?? null;
		$this->tableid = $row["tableid"] ?? null;
		$this->name = $row["name"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$sql->Query("UPDATE $this->tablename SET
					guild = '$this->guild',
					tableid = '$this->tableid',
					name = '$name'
					WHERE id='$this->id'");
	}
	/*===========================================================
	saveNew()
	Saves data into the backend database as a new row entry. After
	calling this method $id will be filled with a new value
	matching the new row for the data
	============================================================*/
	function saveNew()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$sql->Query("INSERT INTO $this->tablename SET
					guild = '$this->guild',
					tableid = '$this->tableid',
					name = '$name'
					");
		$this->id=$sql->GetLastId();
	}
	/*===========================================================
	delete()
	Deletes the row with the current id of this instance from the
	database
	============================================================*/
	function delete()
	{
		global $sql;
		$sql->Query("DELETE FROM $this->tablename WHERE id = '$this->id'");
	}
	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if the given entry exists in the database
	database
	============================================================*/
	function exists($name)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = dkpPointTable::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}

	/*===========================================================
	loadTable($id)
	Loads a points table that contains all the information for a
	guild. This includes multiple players but is limited to a single
	guild.
	============================================================*/
	function loadTable($guildid, $tableid = 1, $sort = "name")
	{
		$this->guild = $guildid;
		$this->tableid = $tableid;

		//$this->guild = $guild;
		global $sql;

		$guildid = sql::Escape($guildid);
		$tableid = sql::Escape($tableid);

		$usertable = dkpUser::tablename;
		$guildtable = dkpGuild::tablename;
		$pointstable = dkpPointsTableEntry::tablename;

		if($sort == "dkp") {
			$sortClause = $pointstable.".points DESC";
		}
		else {
			$sortClause = $usertable.".name ASC";
		}


		$result = $sql->Query("SELECT *,
							   $usertable.id AS userid,
							   $guildtable.id AS guildid,
							   $pointstable.guild AS pointsguildid,
							   $pointstable.id AS pointsid
							   FROM $pointstable, $usertable, $guildtable
							   WHERE $pointstable.guild='$guildid'
							   AND $pointstable.user = $usertable.id
							   AND $usertable.guild = $guildtable.id
							   AND $pointstable.tableid = '$tableid'
							   ORDER BY $sortClause");
		//load up the details
		while($row = mysqli_fetch_array($result)) {
			$tableEntry = new dkpPointsTableEntry();
			$tableEntry->loadFromRow($row);
			$this->table[] = $tableEntry;
		}
		$this->numberOfRows = count($this->table);

		//save some extra data
		if(empty($this->id)) {
			$this->loadFromDatabaseByGuild($guildid, $tableid);
		}
	}

	/*===========================================================
	loadSortedTable
	Loads a points table that contains all the information for a
	guild. This includes multiple players but is limited to a single
	guild.
	$guildid = the id of the guild to get data for
	$sortBy = the column to sort by. Accepts "player" "class" "points" "guild" "totaldkp"
	$sortOrder = Accepts "ASC" or "DESC"
	$
	============================================================*/
	function loadSortedTable($guildid, $sortBy, $sortOrder, $tableid = 1)
	{
		$usertable = dkpUser::tablename;
		$guildtable = dkpGuild::tablename;
		$pointstable = dkpPointsTableEntry::tablename;
		//sanatize $sortBy and $sortOrder
		if($sortBy=="player")
			$sortString = "$usertable.name";
		else if($sortBy=="class")
			$sortString = "$usertable.class";
		else if($sortBy=="points")
			$sortString = "$pointstable.points";
		else if($sortBy=="guild")
			$sortString = "$guildtable.guildName";
		else if($sortBy=="totaldkp")
			$sortString = "$pointstable.totaldkp";
		else {
			$sortString = "$pointstable.points";
			$sortOrder = "DESC";
		}
		if($sortOrder=="" || ($sortOrder != "DESC" && $sortOrder != "ASC"))
			$sortOrder = "DESC";

		//get the data
		global $sql;
		$result = $sql->Query("SELECT *, $usertable.id AS userid,
							   $guildtable.id AS guildid,
							   $pointstable.guild AS pointsguildid,
							   $pointstable.id AS pointsid
							   FROM $pointstable, $usertable, $guildtable
							   WHERE $pointstable.guild='$guild'
							   AND $pointstable.user = $usertable.id
							   AND $usertable.guild = $guildtable.id
							   AND $pointstable.tableid = '$tableid'
							   ORDER BY $sortString $sortOrder, $pointstable.points DESC");
		//increment through all of the entires for this table
		while($row = mysqli_fetch_array($result))
		{
			$tableEntry = new dkpPointsTableEntry();
			$tableEntry->loadFromRow($row);
			$this->table[]=$tableEntry;
		}
		$this->numberOfRows = count($this->table);
		//save some extra data
		if(empty($this->id)) {
			$this->loadFromDatabaseByGuild($guildid, $tableid);
		}
	}

	/*===========================================================
	getPlayerPoints($userId)
	Returns the number of points that the given user has in the
	points table. Takes that users id as a parameter
	============================================================*/
	function getPlayerPoints($userid){
		if($this->table != "")
			foreach($this->table as $entry)
				if($entry->user->id == $userId)
					return $entry->points;
		return 0;
	}

	/*===========================================================
	containsPlayer($playerId)
	Returns true if the given player id appears in this guilds table
	False otherwise
	============================================================*/
	function containsPlayer($userid){
		if($this->table != "") {
			foreach($this->table as $entry) {
				if($entry->user->id == $userid) {
					return $entry->user;
				}
			}
		}
		return false;
	}

	/*===========================================================
	Returns an array of dkpPointsTable instances
	that represent all the tables for a guild. These
	instances only have their id / tableid / and name filled in.
	LoadTable must be called before they actually contain data
	============================================================*/
	static function getTableList($guildid){
		global $sql;
		$tablelisttable = dkpPointsTable::tablename;
		$guildid = sql::Escape($guildid);
		$result = $sql->Query("SELECT * FROM $tablelisttable
							   WHERE guild='$guildid'
							   ORDER BY tableid ASC");
		$tables = array();
		while($row = mysqli_fetch_array($result)) {
			$table = new dkpPointsTable();
			$table->loadFromRow($row);
			$tables[] = $table;
		}
		return $tables;
	}

	/*===========================================================
	Returns an array of dkpUser instances of users that are in the
	specified table. Can be called either statically (providing
	a guild id and tableid) or via an instance (in which case the
	instances guildid and tableid will be used)

	Prameters:
	$guildid - id of the guild to get info for
	$tableid - tableid within the guild to get info for
	$getIdsOnly - if true, the array will only return ids of users instead of actual instances
	============================================================*/
	function getPlayersInTable($guildid=-1, $tableid=-1, $getIdsOnly=false){
		//determine the guild id and table id for the table
		if($guildid == -1) {
			$guildid = $this->guild;
		}
		if($tableid == -1) {
			$tableid = $this->tableid;
		}
		global $sql;
		//get tablenames
		$usertable = dkpUser::tablename;
		$pointtable = dkpPointsTableEntry::tablename;

		//load the points table (it will contain all the user instances)
		if(!$this->table)
			$this->loadTable($guildid, $tableid);

		//parse the data out of the user instances
		$allPlayers = array();
		if(is_array($this->table)) {
			foreach($this->table as $entry) {
				if($getIdsOnly)
					$allPlayers[] = $entry->user->id;
				else
					$allPlayers[] = $entry->user;
			}
		}
		return $allPlayers;
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpPointsTable::tablename)) {
			$tablename = dkpPointsTable::tablename;
			global $sql;
			$sql->Query("CREATE TABLE IF NOT EXISTS `$tablename` (
			  `id` int(11) NOT NULL auto_increment,
			  `guild` int(11) NOT NULL default '0',
			  `name` varchar(255) NOT NULL default '',
			  `tableid` int(11) NOT NULL default '1',
			  PRIMARY KEY  (`id`),
			  UNIQUE KEY `guild` (`guild`,`tableid`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpPointsTable::setupTable();
?>