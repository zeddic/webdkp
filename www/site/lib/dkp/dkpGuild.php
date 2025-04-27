<?php
include_once("lib/dkp/dkpUtil.php");
/*===========================================================
CLASS DESCRIPTION
=============================================================
Contains information for a single known guild.
This contains information about the guilds id, name, faction,
server, etc. The id of this a guild is used throughout the rest
of the system to collect guild DKP tables, etc.

One note about the system: it keeps track of all known guilds
in game, even guilds that do not have a DKP table. Guilds that
do have a DKP table and an account tied to them will have
their $claimed tag set to true.
*/
include_once("dkpPointsTable.php");
include_once("dkpSettings.php");

class dkpGuild {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id; 			//unique id for the guild
	var $name;			//name for the guild
	var $faction;		//faction (horde / alliance / etc.)
	var $server;		//the name of the server (text, not foreign key)
	var $claimed;		//Keeps track of whether this guild is claimed by an account or not
						//The system keeps track of all guilds on the server, assigning them
						//an ID. A guild will become claimed as soon as someone
						//creates a user account to manage its DKP.
						//0 = Not claimed
						//1 = Claimed

	//The following variables are loaded at run time on demand
	var $pointsTable = null;	//A dkpPointsTable instance for this guild
	var $settings = null;		//A dkpSettings instance that contains settings for
								//this guild.

	var $url;
	var $tablename;
	const tablename = "dkp_guilds";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpGuild::tablename;
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
	using the passed string.
	============================================================*/
	function loadFromDatabaseByName($name, $server)
	{
		global $sql;
		$name = sql::Escape($name);
		$server = sql::Escape($server);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE gname='$name' AND gserver='$server'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->name = $row["gname"] ?? null;
		$this->faction = $row["gfaction"] ?? null;
		$this->server = $row["gserver"] ?? null;
		$this->claimed = $row["claimed"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$faction = sql::Escape($this->faction);
		$server = sql::Escape($this->server);
		$sql->Query("UPDATE $this->tablename SET
					gname = '$name',
					gfaction = '$faction',
					gserver = '$server',
					claimed = '$this->claimed'
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
		$faction = sql::Escape($this->faction);
		$server = sql::Escape($this->server);
		$sql->Query("INSERT INTO $this->tablename SET
					gname = '$name',
					gfaction = '$faction',
					gserver = '$server',
					claimed = '$this->claimed'
					");
		$this->id=$sql->GetLastId();
		// Increment the server guild total
		dkpUtil::UpdateGuildTotal($server, "Increment");
	}
	/*===========================================================
	delete()
	Deletes the row with the current id of this instance from the
	database
	============================================================*/
	function delete()
	{
		//global $sql;
		//$sql->Query("DELETE FROM $this->tablename WHERE id = '$this->id'");
		global $sql;
		$sql->Query("UPDATE $this->tablename SET claimed='0' WHERE id = '$this->id'");
		$sql->Query("DELETE FROM ".dkpPointsTable::tablename." WHERE guild = '$this->id'");
		$sql->Query("DELETE FROM dkp_points WHERE guild= '$this->id'");
	}
	/*===========================================================
	exists()
	STATIC METHOD
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($name, $server)
	{
		global $sql;
		$name = sql::Escape($name);
		$server = sql::Escape($server);
		$tablename = dkpGuild::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE gname='$name' AND gserver='$server'");
		return ($exists != "");
	}
	/*===========================================================
	isClaimed()
	STATIC METHOD
	Returns true if the given guilde name / server is claimed
	============================================================*/
	static function isClaimed($name, $server){
		global $sql;
		$name = sql::Escape($name);
		$server = sql::Escape($server);
		$tablename = dkpGuild::tablename;
		$claimed = $sql->QueryItem("SELECT claimed FROM $tablename WHERE gname='$name' AND gserver='$server'");
		return ($claimed == "1");
	}

	/*===========================================================
	loadPointsTable()
	Loads the points table for this guild. This holds all the players
	/ dkp for the guild
	============================================================*/
	function loadPointsTable($tableid = 1)
	{
		$this->pointsTable = new dkpPointsTable();
		//load the points table for this guild
		$this->pointsTable->loadFromDatabase($this->id);

		return $this->pointsTable;
	}

	/*===========================================================
	loadSettings()
	Gets the settings associated with this guild. After calling
	this->settings will be available
	============================================================*/
	function loadSettings(){
		//cahced version already available, use it
		if($this->settings)
			return $this->settings;

		$settings = new dkpSettings();
		$settings->LoadSettings($this->id);

		//cache for future queries
		$this->settings = $settings;
		return $settings;
	}

	/*===========================================================
	isProAccount()
	Returns true if a guild is a pro account
	============================================================*/
	function isProAccount(){
		$settings = $this->loadSettings();
		return ($settings->GetProaccount());
	}

	/*===========================================================
	setProStatus()
	Sets the pro account status for the guild
	$proStatus - available options are:
				 "Active"  		-	Account active and being paid for
				 "Disabled"		-	Not subscribed
				 "Suspended"	- 	Subscribed, but failed to pay
	============================================================*/
	function setProStatus($proStatus){
		$settings = $this->loadSettings();
		if($proStatus=="Active"){
			$settings->SetProaccount(1);
			$settings->SetProstatus($proStatus);
		}
		else if($proStatus=="Disabled"){
			$settings->SetProaccount(0);
			$settings->SetProstatus($proStatus);
		}
		else if($proStatus=="Suspended"){
			$settings->SetProaccount(0);
			$settings->SetProstatus($proStatus);
		}
		else if($proStatus=="Active Until End of Term"){
			$settings->SetProaccount(1);
			$settings->SetProstatus($proStatus);
		}
		//$settings->();
	}

	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpGuild::tablename)) {
			$tablename = dkpGuild::tablename;
			global $sql;
			$sql->Query("CREATE TABLE IF NOT EXISTS `$tablename` (
				  `id` int(11) NOT NULL auto_increment,
				  `gname` varchar(44) character set latin1 collate latin1_general_ci NOT NULL,
				  `gfaction` varchar(44) character set latin1 collate latin1_general_ci NOT NULL,
				  `gserver` varchar(44) character set latin1 collate latin1_general_ci NOT NULL,
				  `claimed` int(11) NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  UNIQUE KEY `guildName` (`gname`,`gserver`),
				  KEY `claimed` (`claimed`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpGuild::setupTable();
?>