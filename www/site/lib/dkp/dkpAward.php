<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
include_once("dkpUser.php");
class dkpAward {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $tableid = 1;
	var $guild = 0;
	var $points = 0;
	var $reason = "";
	var $location = "WebDKP";
	var $awardedby = "Unknown";
	// Added for itemid
	var $itemid = "0";
	var $date;
	var $dateDate;
	var $dateTime;
	var $foritem = 0;
	var $playercount  = 0;
	var $transfer = 0;
	var $zerosumauto = 0;
	var $linked = 0;
	var $players = array();	//array of players with award. Filled with dkp_users
							//after loadPlayers() is called.

	// Occasionally populated at before passing item to templates
	var $historyid;
	var $player;

	var $tablename;
	const tablename = "dkp_awards";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = dkpAward::tablename;
	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabase($id)
	{
		global $sql;
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromDetails($id)
	Attempts to load details for an award given details about it
	============================================================*/
	function loadFromDetails($guildid, $tableid, $reason, $date){
		global $sql;
		$guildid = sql::Escape($guildid);
		$tableid = sql::Escape($tableid);
		$reason = sql::Escape($reason);
		$date = sql::Escape($date);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename
							   WHERE guild='$guildid' AND tableid='$tableid' AND reason='$reason' AND date='$date'");
		$this->loadFromRow($row);
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		if(isset($row["awardid"]))
			$this->id = $row["awardid"] ?? null;
		if(isset($row["historyid"]))
			$this->historyid = $row["historyid"] ?? null;
		$this->tableid = $row["tableid"] ?? null;
		$this->guild = $row["guild"] ?? null;
		$this->playercount = $row["playercount"] ?? null;
		$this->points = $row["points"] ?? null;
		$this->points = str_replace(".00", "", $this->points ?? "");
		$this->reason = $row["reason"] ?? null;
		$this->location = $row["location"] ?? null;
		$this->awardedby = $row["awardedby"] ?? null;
		$this->date = $row["date"] ?? null;
		if(isset($row["date"]))
		{
			$this->dateDate = date("F j, Y", strtotime($row["date"]));
			$this->dateTime = date("g:i A", strtotime($row["date"]));
		}
		$this->foritem = $row["foritem"] ?? null;
		$this->transfer = $row["transfer"] ?? null;
		$this->zerosumauto = $row["zerosumauto"] ?? null;
		$this->linked = $row["linked"] ?? null;
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$reason = sql::Escape($this->reason);
		$location = sql::Escape($this->location);
		$awardedby = sql::Escape($this->awardedby);
		$date = sql::Escape($this->date);
		$transfer = sql::Escape($this->transfer);
		$zerosumauto = sql::Escape($this->zerosumauto);
		$linked = sql::Escape($this->linked);
		$sql->Query("UPDATE $this->tablename SET
					tableid = '$this->tableid',
					guild = '$this->guild',
					playercount = '$this->playercount',
					points = '$this->points',
					reason = '$reason',
					location = '$location',
					awardedby = '$awardedby',
					foritem = '$this->foritem',
					date ='$date',
					transfer = '$transfer',
					zerosumauto = '$zerosumauto',
					linked = '$linked'
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
		$reason = sql::Escape($this->reason);
		$location = sql::Escape($this->location);
		$awardedby = sql::Escape($this->awardedby);
		$itemid = sql::Escape($this->itemid);
		$date = sql::Escape($this->date);
		$date = empty($date) ? "NOW()" : "'$date'";
		$transfer = sql::Escape($this->transfer);
		$zerosumauto = sql::Escape($this->zerosumauto);
		$linked = sql::Escape($this->linked);
		$sql->Query("INSERT INTO $this->tablename SET
					tableid = '$this->tableid',
					guild = '$this->guild',
					playercount = '$this->playercount',
					points = '$this->points',
					reason = '$reason',
					location = '$location',
					awardedby = '$awardedby',
					itemid = '$itemid',
					foritem = '$this->foritem',
					date = $date,
					transfer = '$transfer',
					zerosumauto = '$zerosumauto',
					linked = '$linked'
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
	function exists($id)
	{
		global $sql;
		$name = sql::escape($name);
		$tablename = dkpAward::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE
		return ($exists != "");
	}

	/*===========================================================
	timestamp()
	Applies a timestamp to the award
	============================================================*/
	function timestamp() {
		global $sql;
		$table = $this->tablename;
		$sql->Query("UPDATE $table SET date=NOW() WHERE id='$this->id'");
	}

	/*===========================================================
	loadPlayers()
	Loads all players with this award and stores them in the
	$players member variable
	============================================================*/
	function loadPlayers(){
		global $sql;

		$awardid = sql::Escape($this->id);

		$result = $sql->Query("SELECT *, dkp_users.id as userid
		 					   FROM dkp_pointhistory, dkp_users
							   WHERE dkp_pointhistory.award = '$awardid'
							   AND dkp_pointhistory.user = dkp_users.id
							   ORDER BY dkp_users.name ASC");
		$this->players = array();
		while($row = mysqli_fetch_array($result)) {
			$player = new dkpUser();
			$player->loadFromRow($row);
			$this->players[] = $player;
		}

		return $this->players;
	}
	/*===========================================================
	loadPlayer()
	Returns a single player that recieved this award. This is only
	helpful if the award is for an item (or a transfer), in which case only
	one person should have it anyways.
	============================================================*/
	function loadPlayer(){
		$awardid = sql::Escape($this->id);
		global $sql;
		$row = $sql->QueryRow("SELECT *, dkp_users.id as userid
		 					   FROM dkp_pointhistory, dkp_users
							   WHERE dkp_pointhistory.award = '$awardid'
							   AND dkp_pointhistory.user = dkp_users.id
							   ORDER BY dkp_users.name ASC LIMIT 1");

		$player = new dkpUser();
		$player->loadFromRow($row);

		$this->player = $player;

		return $player;
	}
	/*===========================================================
	getPlayerids()
	Returns an array of playerids of all players with this award.
	============================================================*/
	function getPlayerids(){
		$this->loadPlayers();
		$playerids = array();
		foreach($this->players as $player)
			$playerids[] = $player->id;
		return $playerids;
	}

	/*===========================================================
	calculatePlayerCount()
	Updates this awads current player could field by querying the
	database to see who all refers to it. You MUST call SAVE afterwards
	to save this new value;
	============================================================*/
	function calculatePlayerCount(){
		if(empty($this->id))
			return;

		global $sql;
		$awardid = sql::Escape($this->id);
		$count = $sql->QueryItem("SELECT count(*) as total
								  FROM dkp_pointhistory
								  WHERE award='$awardid'
								  AND user != 0");

		$this->playercount = $count;
	}

	/*===========================================================
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(dkpAward::tablename)) {
			$tablename = dkpAward::tablename;
			global $sql;
			$sql->Query("CREATE TABLE IF NOT EXISTS `$tablename` (
			  `id` int(11) NOT NULL auto_increment,
			  `tableid` int(11) NOT NULL,
			  `guild` int(11) NOT NULL,
			  `playercount` int(11) NOT NULL,
			  `points` decimal(11,2) NOT NULL,
			  `reason` varchar(256) character set latin1 collate latin1_general_ci NOT NULL,
			  `location` varchar(256) character set latin1 collate latin1_general_ci NOT NULL,
			  `awardedby` varchar(256) character set latin1 collate latin1_general_ci NOT NULL,
			  `date` datetime NOT NULL,
			  `foritem` int(11) NOT NULL default '0',
			  `transfer` int(1) NOT NULL default '0',
			  `zerosumauto` int(1) NOT NULL default '0',
			  `linked` int(11) NOT NULL default '0',
			  PRIMARY KEY  (`id`),
			  KEY `guild` (`guild`),
			  KEY `tableid` (`tableid`),
			  KEY `date` (`date`),
			  KEY `reason` (`reason`(255),`date`,`points`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
		}
	}
}
dkpAward::setupTable()
?>