<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
A class that is used to generate the WebDKP.lua file
that contains information for the addon. This is the file that
the addon will access and use to read data about the guilds dkp.
*/

include_once("dkpSettings.php");
include_once("dkpGuild.php");
include_once("loottable/dkpLootTable.php");
include_once("dkpUpdater.php");

class dkpLuaGenerator {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $guild;
	var $guildid;
	var $settings;
	/*===========================================================
	FUNCTIONS
	============================================================*/
	function __construct($guildid){
		//load the guild
		$this->guild = new dkpGuild();
		$this->guild->loadFromDatabase($guildid);
		$this->guildid = $guildid;

		//load the settings for this guild
		$this->settings = new dkpSettings();
		$this->settings->LoadSettings($guildid);
	}
	/*===========================================================
	generateLuaFile
	Generates the lua file for the addon. This contains a collection of
	data including:
	- The dkp table
	- list of available tables
	- The loot table
	- Web options
	- list of alts

	Contents are printed directly to echo();

	Parameters:
	$guildid - 			The id of the guild to generate the file for
	$useFileHeaders - 	If ture file headers will be added so that
						a download prompt window will appear on this
						page that is generated. If this is set to true
						there CANNOT be any other echos up to this point.
						You should also call die() immediatly after.
	============================================================*/
	function generateLuaFile($useFileHeaders = false){

		global $sql;
		global $siteUser;

		//load up the guild table (for all tables)
		$tables = $this->loadTableList();
		$users = $this->loadDkpTable();
		$loot = $this->loadLootTable();
		$alts = $this->loadAlts();

		//if the user is downloading this manually, the value download will be in GET
		//we therefore need to add special headers so that they get the 'download file' dialog box
		if($useFileHeaders==true)
		{
			header("Content-Type: text/plain;charset=UTF-8");
			header("Content-Disposition: attachment; filename=WebDKP.lua");
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
		}

		echo("WebDKP_DkpTable = {\r\n");
		if($users!=""){
		foreach($users as $name => $userdata){
			$class = $userdata["class"];
			$name = (sql::Escape($name));
			echo("[\"$name\"] = { \r\n");
			echo("     [\"class\"]=\"$class\", \r\n");
			$count = 0;
			foreach($userdata["entries"] as $pointsdata){
				$points = $pointsdata["points"];
				$points = str_replace("+","",$points);
				$tableid = $pointsdata["tableid"];
				if($count==0)
					echo("     [\"dkp\"]=$points, \r\n");
				$count++;
				echo("     [\"dkp_$tableid\"]=$points, \r\n");
			}
			echo("},\r\n");
		}}
		echo("} \r\n\r\n");
		echo("WebDKP_Tables = {\r\n");
		if($tables!=""){
		foreach($tables as $table){
			$name = (sql::Escape($table->name));
			$id = $table->tableid;
			echo("[\"$name\"] = { \r\n");
			echo("		[\"id\"] = $id, \r\n");
			echo("},\r\n");
		}}
		echo("}\r\n\r\n");
		echo("WebDKP_Loot = {\r\n");
		if($loot!=""){
		foreach($loot as $entry){
			$item = (sql::Escape($entry->name));
			$cost = $entry->cost;
			echo("[\"$item\"] = \"$cost\",\r\n");
		}}
		echo("}\r\n\r\n");
		echo("WebDKP_Alts = {\r\n");
		if($alts!=""){
		foreach($alts as $alt => $main){
			$alt = (sql::Escape($alt));
			$main = (sql::Escape($main));
			echo("[\"$alt\"] = \"$main\",\r\n");
		}}
		echo("}\r\n\r\n");

		echo("WebDKP_WebOptions = {\r\n");
		echo("[\"ZeroSumEnabled\"] = ".($this->settings->GetZerosumEnabled()+0).",\r\n");
		echo("[\"CombineAlts\"] = ".($this->settings->GetCombineAltsEnabled()+0).",\r\n");
		echo("[\"TiersEnabled\"] = ".($this->settings->GetTiersEnabled()+0).",\r\n");
		echo("[\"TierSize\"] = ".($this->settings->GetTierSize()+0).",\r\n");
		echo("[\"LifetimeEnabled\"] = ".($this->settings->GetLifetimeEnabled()+0).",\r\n");
		echo("}\r\n\r\n");
	}

	/*===========================================================
	Loads the dkp table for the specified guild.
	Contents are saved into an associative array which are returned.
	The array's structure is as follows:
	["PlayerName"] = ["class"],
					 ["entries"] =  ["tableid"],["points"]
					 				["tableid"],["points"]
					 				...
	etc..
	============================================================*/
	function loadDkpTable(){
		global $sql;
		//load up the guild table (for all tables)
		$result = $sql->Query("SELECT
							   dkp_users.name, dkp_users.class, dkp_points.tableid, dkp_points.points
							   FROM dkp_points, dkp_users
							   WHERE dkp_users.id = dkp_points.user
							   AND dkp_points.guild='$this->guildid'
							   ORDER BY dkp_users.name ASC, dkp_points.tableid ASC");
		$users = array();
		while($row = mysqli_fetch_array($result)) {
			$name = $row["name"] ?? null;
			$class = $row["class"] ?? null;
			$tableid = $row["tableid"] ?? null;
			$points = $row["points"] ?? null;
			$userdetails["class"]=$class;
			$pointentry["points"]=$points;
			$pointentry["tableid"]=$tableid;
			if(empty($users[$name]))
				$users[$name]=$userdetails;
			$users[$name]["entries"][]=$pointentry;
		}
		return $users;
	}

	/*===========================================================
	Loads up a list of tables and their associated tableids.
	Returns the information as an array.
	============================================================*/
	function loadTableList(){

		$updater = new dkpUpdater($this->guild->id);
		$updater->EnsureMainTableExists();

		global $sql;
		//load up a list of tables
		$result = $sql->Query("SELECT * FROM dkp_tables WHERE guild='$this->guildid' ORDER BY tableid ASC");
		while($row = mysqli_fetch_array($result))
		{
			$table = new dkpPointsTable();
			$table->loadFromRow($row);
			$tables[] = $table;
		}
		return $tables;
	}

	/*===========================================================
	Loads up a loot table list. The array is composed of individual
	dkpCostTableEntry instances.
	If the settings structure that is passed has loot tables disabled
	a empty array is returned.
	============================================================*/
	function loadLootTable(){
		global $sql;
		//load up the loot table
		$loot = array();
		if($this->settings->GetLootTableEnabled()){

			//load up a list of loot for this player
			//funky embedded query to ensure the sub query is executed first
			$result = $sql->Query("SELECT * FROM dkp_loottable_data
								   WHERE loottable
								   IN ( SELECT id FROM (
								   		SELECT id FROM dkp_loottable WHERE guild='$this->guildid')
										AS x )");

			while($row = mysqli_fetch_array($result))
			{
				$entry = new dkpLootTableEntry();
				$entry->loadFromRow($row);
				$loot[]=$entry;
			}
		}
		return $loot;
	}

	/*===========================================================
	Returns a list of alts for all players in the guild. Alts
	are just a many to 1 mappying of alternative characters and their
	main.
	The array structure is:
	["altName"] = "mainName",
	["altName"] = "mainName",

	If alt support is disabled in the passed settings instance an empty
	array is returned.
	============================================================*/
	function loadAlts(){
		global $sql;
		$guildid = $this->guild->id;
		//load up the list of alts
		$alts = array();
		if($this->settings->GetCombineAltsEnabled()) {
			$result = $sql->Query("SELECT dkp_users.name AS alt , dkp_users2.name AS main
								   FROM dkp_points, dkp_users, dkp_users AS dkp_users2
								   WHERE dkp_points.guild = '$guildid' AND
								   dkp_points.user = dkp_users.id AND
								   dkp_users.main!='0' AND
								   dkp_users.main = dkp_users2.id
								   GROUP BY dkp_users.id");

			while($row = mysqli_fetch_array($result)){
				$alt = $row["alt"] ?? null;
				$main = $row["main"] ?? null;
				$alts[addslashes($alt)] = addslashes($main);
			}
		}
		return $alts;
	}
}
?>