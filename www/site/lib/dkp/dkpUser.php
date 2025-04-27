<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Contains all information for a single player in the system.
This is a person that appears in a dkp table and has a history
of dkp points. Each player belongs to a single guild.

Note that players are different than user accounts for the site.
User accounts to the site have a username and password and are
able to manage site features. Players are only visiable in
dkp tables and can't actually sign into the site.
*/
include_once("dkpGuild.php");
class dkpUser {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;			//unique id in the database
	var $name;			//the name of the user
	var $guild;			//the id of the guild (could be a dkpGuild instance)
	var $faction;			//the name of the faction (alliance / horde)
	var $server;			//the name of the server
	var $class;			//the class of the user
	var $main = 0;			//The id of a 'main account'. Only applies if this is
					//an alt account (ie, an alternative character for
					//another player in the dkp list)

	//the following variables are loaded at run time and only
	//when specific methods are called.
	var $alts = array();		//An arry of other dkpUser instnaces that represent
					//alts for this character. This is not populated until
					//loadAlts is called
	var $mainUser;	//The main user for this account (if alt) represented as
					//a dkpUser instance. Only loaded once loadMain is called.

	var $tablename;
	const tablename = "dkp_users";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpUser::tablename;
	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabase($id)
	{
		global $sql;
		$usertable = dkpUser::tablename;
		$guildtable = dkpGuild::tablename;

		$row = $sql->QueryRow("SELECT *,
							   $usertable.id AS userid,
							   $guildtable.id AS guildid
							   FROM $usertable,$guildtable
							   WHERE $usertable.id='$id' AND $guildtable.id=$usertable.guild");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		if(!empty($row["userid"]))
	  		$this->id = $row["userid"] ?? null;
		$this->name = $row["name"] ?? null;
		$this->faction = $row["faction"] ?? null;
		$this->server = $row["server"] ?? null;
		$this->class = $row["class"] ?? null;
		$this->main = $row["main"] ?? null;

		if(!empty($row["gname"])) {
			$guild = new dkpGuild();
			$guild->loadFromRow($row);
			$this->guild = $guild;
		} else {
			$this->guild = $row["guildid"] ?? null;
		}
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
		$class = sql::Escape($this->class);
		if(is_a($this->guild,'dkpGuild'))
  	  		$guild = sql::Escape($this->guild->id);
  	  	else
  	  		$guild = sql::Escape($this->guild);

		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					guild = '$guild',
					faction = '$faction',
					server = '$server',
					class = '$class',
					main = '$this->main'
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
		$name = sql::Escape(trim($this->name));
		$faction = sql::Escape($this->faction);
		$server = sql::Escape($this->server);
		$class = sql::Escape($this->class);
		if(is_a($this->guild,'dkpGuild'))
			$guild = sql::Escape($this->guild->id);
		else
			$guild = sql::Escape($this->guild);

		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					guild = '$guild',
					faction = '$faction',
					server = '$server',
					class = '$class',
					main = '$this->main'
					");
		$this->id=$sql->GetLastId();
	}

	/*===========================================================
	getDKP()
	Gets the current players DKP values for the passed guild
	============================================================*/
	function getDKP($guildid, $tableid=1){
		global $sql;
		$guildid = sql::Escape($guildid);
		$tableid = sql::Escape($tableid);
		$pointstable = dkpPointsTable::tablename;
		$dkp = $sql->QueryItem("SELECT points FROM $pointstable
								WHERE user='$this->id'
								AND guild='$guildid'
								AND tableid='$tableid'");
		return $dkp;
	}
	/*===========================================================
	setDKP()
	Sets the dkp of the current player to the specified amount.
	If it is different than the current dkp, a new history entry is made
	============================================================*/
	function setDKP($guildid, $newDkp, $tableid=1){
		global $sql;
		global $siteUser;
		/*//while updating their dkp we should also make an entry in thier history
		//telling about this adjustment ( so that their history still adds up)
		$currentDkp = $this->getDKP($guildID,$tableid);
		$dif = $newDkp - $currentDkp;
		//if there is a difference, go ahead and record it in the players history
		if($dif != 0) {
			$history = new dkpPointsHistoryTableEntry();
			$history->guild = $guildID;
			$history->tableid = $tableid;
			$history->user = $this->id;
			$history->points = $dif;
			$history->reason = "Website Adjustment";
			$history->awardedBy = $siteUser->name;
			$history->forItem = 0;
			$history->location = "WebDKP";
			$history->saveNew();
		}*/
		$pointstable = dkpPointsTable::tablename;
		$guildid = sql::Escape($guildid);
		$tableid = sql::Escape($tableid);
		$sql->Query("UPDATE $pointstable
					 SET points = '$newDkp'
					 WHERE user='$this->id'
					 AND guild='$$guildid'
					 AND tableid='$tableid'");
	}


	/*===========================================================
	duplicatePlayerExists()
	Checks to see if a player instance already exists with the same
	server / name / but with a different id
	Returns the id of the duplicate player if it exists, false otherwise
	============================================================*/
	function duplicatePlayerExists(){
		global $sql;
		$name = sql::Escape($this->name);
		$server = sql::Escape($this->server);
		$usertable = dkpUser::tablename;
		$playerid = $sql->QueryItem("SELECT id
									 FROM $usertable
									 WHERE name='$name'
									 AND server='$server'
									 AND id != '$this->id'
									 LIMIT 1");
		//player doesn't exist
		if(empty($playerid))
			return false;
		//player exists
		return $playerid;
	}

	/*===========================================================
	loadAlts()
	Loads a list of all the 'alts' for this character.
	Alts are other characters played by the same person.
	Loaded alts are made available in the $this->alts variable in the form of an
	array of dkpUser instances.
	============================================================*/
	function loadAlts(){
		global $sql;
		$usertable = dkpUser::tablename;
		if($this->id == 0)
			return;
		$result = $sql->Query("SELECT * FROM $usertable
							   WHERE main='$this->id'
							   AND id != '$this->id'
							   ORDER BY name ASC");
		while($row = mysqli_fetch_array($result)){
			$user = new dkpUser();
			$user->loadFromRow($row);
			$this->alts[]=$user;
		}
	}

	/*===========================================================
	addAlt()
	Adds another player as an alt for this class.
	Parameter: $userID = userid of the player to make an alt
	============================================================*/
	function addAlt($userid){
		global $sql;
		$usertable = dkpUser::tablename;
		$userid = sql::Escape($userid);
		//We need to update 2 things:
		//1 the passed player must have its 'main' field updated to point to this user
		//2 all other players that pointed to this new alt as a main should now point to this
		//  user as their main instead.
		if($userid == 0)
			return;
		$sql->Query("UPDATE $usertable SET main='$this->id'
					 WHERE id='$userid' OR main='$userid'");
	}

	/*===========================================================
	removeAlt()
	Removes the specified player id from being an alt to this player
	============================================================*/
	function removeAlt($userid){
		global $sql;
		$usertable = dkpUser::tablename;
		$userid = sql::Escape($userid);
		$sql->Query("UPDATE $usertable SET main='0'
					 WHERE id='$userid'");
	}

	/*===========================================================
	makeAltTo()
	Makes this player an 'alt' to another player in the system.
	Parameter is the id of the new player to be a main to.
	============================================================*/
	function makeAltTo($mainid){
		global $sql;
		$usertable = dkpUser::tablename;
		$mainid = sql::Escape($mainid);
		if($this->id == 0)
			return;
		//We need to update 2 things
		//1 This user needs to be pointed to the new main
		//2 All alts who pointed to this user as a main should now point to the new main
		$sql->Query("UPDATE $usertable SET main='$mainid'
					 WHERE id='$this->id' OR main='$this->id'");

		$this->main = $mainid;
	}
	/*===========================================================
	makeMain()
	Makes this player a 'main' character. If they are already a main
	this does nothing. If it is an alt, this causes the previous main
	and all alts to point to it instead.
	============================================================*/
	function makeMain(){
		global $sql;
		$usertable = dkpUser::tablename;
		if($this->main == 0)
			return;
		//find the main
		$mainid = sql::Escape($this->main);
		$id = sql::Escape($this->id);
		//update the old main to point to this character as well
		//as all the other alts
		$sql->Query("UPDATE $usertable SET main='$id'
		             WHERE id='$mainid' OR main='$mainid'");

		$this->main = 0;
		$this->save();
	}
	/*===========================================================
	isMain()
	Returns true if this is a main account. An account is a main account
	if it is not an alt to any other account. Ie, it is the master account
	played.
	============================================================*/
	function isMain(){
		return ($this->main == 0 ||
				$this->main == $this->id
		);
	}
	/*===========================================================
	loadMain()
	Loads the main account (if this is an alt)
	============================================================*/
	function loadMain(){
		//if this is a main account, don't do anything
		if($this->isMain()) {
			return;
		}
		$this->mainUser = new dkpUser();
		$this->mainUser->loadFromDatabase($this->main);
	}

	/*===========================================================
	loadAltAndMainData()
	Loads alt and main data. If this is a main, finds alts for this
	player. If it is an alt, loads the main player data.
	============================================================*/
	function loadAltAndMainData(){
		if ($this->isMain()) {
			$this->loadAlts();
		}
		else {
			$this->loadMain();
		}
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
		$slq->Query("UPDATE $this->tablename SET main='0' WHERE main='$this->id'");
	}

	/*===========================================================
	Returns true if the given entry exists in the database
	database
	============================================================*/
	static function exists($name, $server)
	{
		global $sql;
		$name = sql::escape($name);
		$server = sql::escape($server);
		$usertable = dkpUser::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $usertable WHERE name='$name' AND server='$server'");
		return ($exists != "");
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpUser::tablename)) {
			$tablename = dkpUser::tablename;
			global $sql;
			$sql->Query("CREATE TABLE IF NOT EXISTS `$tablename` (
				  `id` int(11) NOT NULL auto_increment,
				  `name` varchar(44) character set latin1 collate latin1_general_ci NOT NULL,
				  `guild` int(11) NOT NULL default '0',
				  `faction` varchar(44) character set latin1 collate latin1_general_ci NOT NULL,
				  `server` varchar(44) character set latin1 collate latin1_general_ci NOT NULL,
				  `class` varchar(44) character set latin1 collate latin1_general_ci NOT NULL,
				  `main` int(11) NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  UNIQUE KEY `name` (`name`,`server`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpUser::setupTable();
?>