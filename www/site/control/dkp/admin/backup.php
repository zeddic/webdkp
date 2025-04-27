<?php
include_once("adminmain.php");
include_once("lib/dkp/lua2php.php");

/*=================================================
Page that allows users to both download and restore
backup files.
=================================================*/
class pageBackup extends pageAdminMain {

	var $log = null;
	var $minilog = null;

	var $tableMap;
	var $playerMap;
	var $dkpTableLog;
	var $dkpToAdd;
	var $awardInserts;
	var $historyData;
	var $historyInserts;

	/*=================================================
	Shows the main backup form
	=================================================*/
	function area2()
	{
		global $sql;

		$this->title = "Backups";
		$this->border = 1;

		$this->set("log",$this->log);
		$this->set("minilog",$this->minilog);
		$this->set("tabs",$this->GetTabs("admin"));
		return $this->fetch("backup.tmpl.php");
	}

	/*=================================================
	Generates a backup file for the guild
	=================================================*/
	function eventBackup(){

		global $sql;
		$lastId = 0;

		//get a list of the users tables
		$tables = $this->updater->GetTables(true);

		//get a list of the guilds dkp table
		$points = array();
		$guildid = $this->guild->id;
		$result = $sql->Query("SELECT *, dkp_users.id AS userid FROM dkp_points, dkp_users, dkp_guilds
						 	   WHERE dkp_points.guild='$guildid' AND dkp_points.user = dkp_users.id AND dkp_guilds.id = dkp_users.guild ORDER BY tableid, points DESC");
		while($row = mysqli_fetch_array($result)){
			$points[] = $row;
		}
		//get a list of all the guilds point history

		// Go through the point_history table and select the user and awards for the entire guild id.
		$result = $sql->Query("SELECT user, award FROM dkp_pointhistory WHERE dkp_pointhistory.guild='$guildid'");

		$history = array();
		$entry = array();

		//we are going to run though all of the history entries for the guild.
		//Whenever we find a history entry shared by more than one person, we create
		//a single entry but list all players under it. This allows us to cut down
		//on our backup file size


		while($row = mysqli_fetch_array($result)) {
			
			
			if (isset($entry[$row["award"]])){

				// Simply attach the new users name to the existing award

				// Pull player's name
				$userID = $row["user"] ?? null;
				$playernamevalue = $sql->Query("SELECT DISTINCT name FROM dkp_users WHERE dkp_users.id='$userID' LIMIT 1");
				$playername = mysqli_fetch_array($playernamevalue);

				$entry[$row["award"]]["users"][] = $playername["name"] ?? null;
			}
			else {
			// It's a new award so populate all fields and add the users name
				
				$awardID = $row["award"] ?? null;
				$userID = $row["user"] ?? null;
				// Pull the award info
				
				$awardinfovalue = $sql->Query("SELECT * FROM dkp_awards WHERE id='$awardID' LIMIT 1");
				$awardinfo = mysqli_fetch_array($awardinfovalue);
				$entry[$row["award"]]["tableid"] = $awardinfo["tableid"] ?? null;
				$entry[$row["award"]]["points"] = $awardinfo["points"] ?? null;

				$reason = str_replace(",","[!]",$awardinfo["reason"]);
				$entry[$row["award"]]["reason"] = $reason;
				$entry[$row["award"]]["foritem"] = $awardinfo["foritem"] ?? null;
				$location = str_replace(",","[!]",$awardinfo["location"]);
				$entry[$row["award"]]["location"] = $location;
				$entry[$row["award"]]["awardedby"] = $awardinfo["awardedby"] ?? null;
				$entry[$row["award"]]["date"] = $awardinfo["date"] ?? null;

				// Pull player's name
				$playernamevalue = $sql->Query("SELECT DISTINCT name FROM dkp_users WHERE id='$userID' LIMIT 1");
				$playername = mysqli_fetch_array($playernamevalue);

				$entry[$row["award"]]["users"][] = $playername["name"] ?? null;
				$entry[$row["award"]]["transfer"] = $awardinfo["transfer"] ?? null;

			}
			

		}
		
		$history[]=$entry;


		//now to echo all this information to a stream
		$filename = "WebDKP_Backup_".date("M_d_g-ia");
		header("Content-Type: text/plain; charset=UTF-8");
		header("Content-Disposition: attachment; filename=$filename.csv");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		//header("Content-Transfer-Encoding: binary\n");
		echo("TABLES\r\n");
		foreach($tables as $table) {
			echo("$table->tableid, $table->name \r\n");
		}
		echo("DKPTABLE\r\n");
		if($points!=""){
		foreach($points as $entry) {
			if($entry["tableid"] > 1000 )
				continue;
			echo($entry["tableid"].",");
			echo($entry["name"].", ");
			echo($entry["class"].",");
			echo($entry["gname"].",");

			$points =  str_replace(".00", "", $entry["points"]);
			$totaldkp =  str_replace(".00", "", $entry["lifetime"]);
			echo("$points,$totaldkp\r\n");
		}}
		echo("POINTHISTORY\r\n");
		if ($history != "" && $history[0] != ""){
		foreach($history as $entry){
			foreach ($entry as $resultsentry){

			echo($resultsentry["tableid"].",");
			echo($resultsentry["points"].",");
			echo($resultsentry["reason"].",");
			echo($resultsentry["foritem"].",");
			echo($resultsentry["location"].",");
			echo($resultsentry["awardedby"].",");
			$date = date('Y-m-d H:i:s', strtotime($resultsentry["date"]));
			echo($date.",");
			echo(implode("|",$resultsentry["users"]).",");
			echo($resultsentry["transfer"]. "\r\n");
		}}}
		die();
	}

	/*=================================================
	Uploads a 'mini' lua file restore. This allows log fi1ed
	as a backup file. This is more of a 'last resort' measure.
	For the real normal backup, see "eventUpload"
	=================================================*/
	function eventMiniUpload(){

		//allow time to upload
		set_time_limit(0);

		global $sql;

		//make sure they have permissions
		if(!$this->HasPermission("BackupRestore"))
			return $this->setEventResult(false, "You do not have permission to restore a backup file.");

		//get the lua file
		$luafile = $_FILES['userfile']['tmp_name'];

		//convert lua to php
		$log = makePhpArray($luafile);

		//make sure valid
		if (sizeof($log) < 0 ) {
			return $this->setEventResult(false, "Invalid LUA File Selected");
		}

		//attempt to load data
		$count = 0;
		$people = $log["WebDKP_DkpTable"];
		$tables = $log["WebDKP_Tables"];

		//make sure data could be loaded
		if ( sizeof($people) == 0 || sizeof($tables) == 0 ) {
			return $this->setEventResult(false, "Invalid LUA File Selected");
		}

		//start our log
		$this->minilog = "Starting Mini Restore<br />";
		$this->minilog .= "Finding Tables<br />";
		$this->minilog .= "====================<br />";

		//Load the tables and make sure they all exist
		$this->tableMap = array();
		foreach($tables as $name => $data) {

			//get the table
			$tableid = $data["id"];
			//find it in the database
			$table = $this->updater->GetTable($tableid);
			$this->minilog .= "Found table $name with id $tableid<br />";

			//make sure in database. If not, create it
			if ($table->id != "") {
				$this->tableMap[$tableid] = $table->tableid;
			}
			else
			{
				$table = new dkpPointsTable();
				$table->guild = $this->guild->id;
				$table->name = $tablename;
				$table->tableid = $this->updater->GetNextTableId();
				$table->saveNew();
				$this->tableMap[$tableid] = $table->tableid;
			}
		}

		//now moving onto players...
		$this->minilog .= "<br />";
		$this->minilog .= "Loading Player DKP<br />";
		$this->minilog .= "====================<br />";

		//Now to load all the players from the file in
		foreach($people as $name => $data) {

			$class = $data["class"];

			//the - character is only present in characters from battlegrounds
			if (strpos($name,"-") === false) {

				//make sure the player exists
				$player = $this->updater->EnsurePlayerExists($name, $class, "Unknown");

				$this->minilog .= "$name - $class with id $player->id<br />";

				//now see what their dkp is for each of the tables
				foreach( $this->tableMap as $tableid => $dbtableid ) {

					//form in lua file was dkp_tableid
					$key = "dkp_".$tableid;
					$dkp = $data[$key];

					//only bother adding if they have > 0 dkp
					if($dkp != 0) {
						$this->minilog .= "$dkp dkp in table $tableid<br />";
						$count++;
						//make sure they are in the table
						$this->updater->EnsurePlayerInTable($player->id, $dbtableid);
						//make sure their dkp is valid
						$this->miniUploadSetDKP($player->id, $dbtableid, $dkp);
					}
				}
			}
		}

		//and done
		$this->minilog .= "Finished upload";
		$this->setEventResult(true, "Mini Restore Complete - $count records added");
	}

	/*=================================================
	A helper function for the mini upload - this will
	set someones dkp to a set value in the given table.
	Additionally it records an award in their history
	to account for the new adjustment.
	=================================================*/
	function miniUploadSetDKP($playerid, $tableid, $newdkp){
		global $siteUser;

		$currentdkp = dkpUtil::GetPlayerDKP($this->guild->id, $tableid, $playerid);

		if($currentdkp != $newdkp) {

			$delta = $newdkp - $currentdkp;

			$award = new dkpAward();
			$award->guild = $this->guild->id;
			$award->points = $delta;
			$award->reason = "Backup Restore";
			$award->location = "WebDKP";
			$award->awardedby = $siteUser->username;
			$award->foritem = 0;
			$this->updater->AddDkp( $playerid, $this->tableid, $award);
		}
	}

	/*=================================================
	EVENT - Uploads and performs a restore for a backup
	file
	=================================================*/
	function eventUpload(){

		set_time_limit(0);

		global $sql;

		//make sure they have permissions
		if(!$this->HasPermission("BackupRestore"))
			return $this->setEventResult(false, "You do not have permission to restore a backup file.");

		//make sure the file type is valid
		//if($_FILES['userfile']['type'] != "text/plain") {
		//	return $this->setEventResult(false, "Invalid file format uploaded. You must comma seperated .csv file");
		//}

		//get the upload file
		$file = $_FILES['userfile']['tmp_name'];
		if (!file_exists($file)) {
			return $this->setEventResult(false, "An invalid backup file was selected. Restore NOT completed.");
		}

		//split it into lines
		$lines = file($file);

		if(count($lines) < 3) {
			return $this->setEventResult(false, "An invalid backup file was selected. Restore NOT completed.");
		}

		$this->log = "";

		//make sure the log is valid utf8
		if(!$this->IsUTF8($lines)) {
			$this->log .= "File Is NOT UTF8. Converting to UTF8 before parse.<br />";
			for($i = 0 ; $i < sizeof($lines) ; $i++)
				$lines[$i] = mb_convert_encoding($lines[$i], "UTF-8", mb_detect_encoding($lines[$i]));
			
		}
		else {
			$this->log .= "File is UTF8 - Do not need to convert it<br />";
		}


		$type = util::getData("restoreType");

		if( $type=="fullrestore" && count($lines) > 3 ) {
			$guildid = $this->guild->id;
			$this->log .= "FULL RESTORE - CLEARING TABLE <br />";
			$sql->Query("DELETE FROM dkp_tables WHERE guild='$guildid'");
			$sql->Query("DELETE FROM dkp_points WHERE guild='$guildid'");
			$sql->Query("DELETE FROM dkp_pointhistory WHERE guild='$guildid'");
			$sql->Query("DELETE FROM dkp_awards WHERE guild='$guildid'");
		}

		$state = "tables";

		$this->tableMap = array();
		$this->playerMap = array();
		$this->dkpTableLog = array();
		$this->dkpToAdd = array();

		$this->awardInserts = array();
		$this->historyData = array();
		$this->historyInserts = array();

		foreach($lines as $line) {
			$entry = explode(",",$line);

			if($this->IsStageEntry($entry)) {
				$stage = strtolower(trim($entry[0]));
				$this->log .= "STAGE: $stage <br />";
			}
			else if($stage == "tables") {
				$this->RestoreTablesStage($entry);
			}
			else if($stage == "dkptable") {
				$this->RestoreDkpTableStage($entry);
			}
			else if($stage == "pointhistory") {
				$this->RestoreDkpHistoryStage($entry);
			}
		}
		$this->InsertAwards();
		$this->InsertHistory();
		//$this->InsertHistoryEntries();
		$this->InsertPlayerPoints();

		$this->log .= "Restore Complete<br />";

		$this->setEventResult(true,"Backup Restored! - ".count($this->awardInserts)." Entries Loaded");

	}

	/*=================================================
	Returns true if the given line in the database file is
	a 'stage' entry. A stage entry represents a transistion
	from one set of backup data to another.
	=================================================*/
	function IsStageEntry(&$entry){
		$num = count($entry);
		if($num == 1)
			return true;

		$colWithContent = 0;
		foreach($entry as $col) {
			if(trim($col)!="")
				$colWithContent++;
		}

		return ( $colWithContent == 1 );
	}

	/*=================================================
	Parses a single line in the table stage. This line
	contains an id and a name of a table that the id represents.
	All other entries in the backup file that belong to a table
	will refer to the table id here, so we must store it in a map.
	Also note, that while a table may have an id loaded here,
	this may be different than the table id on the site. This
	might be the case if tables already existed on the site.
	The tableMap takes care of this by mapping the tableid in the
	file to the real tabeleid in the database.
	=================================================*/
	function RestoreTablesStage(&$entry){
		$tableid = trim(sql::Escape($entry[0]));
		$tablename = $entry[1];
		$tablename = trim(str_replace('\r\n',"",$tablename));
		if($tableid==0)
			return;

		$table = $this->updater->GetTable($tableid);
		if ($table->id != "") {
			$this->tableMap[$tableid] = $table->tableid;
		}
		else
		{
			$table = new dkpPointsTable();
			$table->guild = $this->guild->id;
			$table->name = $tablename;
			$table->tableid = $this->updater->GetNextTableId();
			$table->saveNew();
			$this->tableMap[$tableid] = $table->tableid;
		}

		$this->log .= "Added table: $tablename w/ id $tableid <br />";
	}

	/*=================================================
	Parses a single entry in the DKP stage of the backup
	file. This ignores the DKP total that they have there.
	Instead it is interested in detecting player names,
	classes, and guilds and making sure it exists
	in the database. It stores a map of player names to
	database ids so that other stages can refer to it.
	=================================================*/
	function RestoreDkpTableStage(&$entry){
		global $sql;
		$tableid = sql::Escape(trim($entry[0]));
		$name = $this->EnsureUTF8(trim($entry[1]));
		$class = trim($entry[2]);
		$guildname = $this->EnsureUTF8(trim($entry[3]));
		$points = sql::Escape(trim($entry[4]));
		$totaldkp = sql::Escape(trim($entry[5]));

		if($guildname=="")
			$guildname = "Unknown";

		if($tableid==0 || $tableid=="")
			return;

		//have we seen this player yet?
		if(!isset($this->playerMap[$name])) {
			$player = $this->updater->EnsurePlayerExists($name, $class, $guildname);
			$this->playerMap[$name] = $player->id;
			$playerid = $player->id;
		}
		else {
			$playerid = $this->playerMap[$name];
		}

		$this->dkpTableLog[$tableid][$playerid] = $points;
	}

	/*=================================================
	Parses a single history entry line. A history entry
	line contains details on an award as well as a list
	of all players who recieved that award. Instead of performing
	the insert here, the backup file stores potentialal database
	awards into an 'insert strings' array. This way we can perform
	mass database inserts all at once at the end. This dramatically
	saves upload time.
	=================================================*/
	function RestoreDkpHistoryStage(&$entry){

		//first, load the information for this entry
		$tableid = sql::Escape(trim($entry[0]));
		$points = sql::Escape(trim($entry[1]));
		$reason = $this->EnsureUTF8(sql::Escape(str_replace("[!]",",",trim($entry[2]))));
		$foritem = sql::Escape(trim($entry[3]));
		$location = sql::Escape(str_replace("[!]",",",trim($entry[4])));
		$awardedby = $this->EnsureUTF8(sql::Escape(trim($entry[5])));
		$date = sql::Escape(trim($entry[6]));
		$users = explode("|",$this->EnsureUTF8(sql::Escape(trim($entry[7]))));
		$playercount = count($users);
		$guildid = $this->guild->id;
		$transfer = sql::Escape(trim($entry[8]));
		if (empty($transfer)){
			$transfer = 0;
				if(strpos($reason, "Transfer") !== false)
					$transfer = 1;
		}

		//error checking
		if(sizeof($users)==0)
			return;
		if($tableid ==0 || $tableid=="")
			return;

		$this->log .= "Loaded award $reason with $points<br />";

		if($this->updater->AwardExists($tableid, stripslashes($reason), stripslashes($date))){
		//	$this->log .= "EXISTS!<br />";
			return;
		}

		$date = date('Y-m-d H:i:s', strtotime($date));
		$temp = "('$guildid', '$tableid', '$points', '$reason', '$location', '$awardedby', '$date', '$foritem', '$playercount', '$transfer')";
		$this->awardInserts[] = $temp;

		//convert the user names into user ids
		$userids = array();
		foreach($users as $username) {
			$id = $this->playerMap[$username] ?? null;
			//player in history but not in the table? See if we know something
			//about them in the database
			if($id == null) {
				$player = $this->updater->GetPlayer($username);
				if($player->id != "") {
					$this->playerMap[$username] = $player->id;
					$id = $player->id;
				}
			}
			//only add them to the list if a match was found
			if($id != null) {
				$userids[]=$id;
			}

			$this->dkpToAdd[$tableid] ??= [];
			$this->dkpToAdd[$tableid][$id] ??= [];
			$this->dkpToAdd[$tableid][$id]["dkp"] ??= 0;
			$this->dkpToAdd[$tableid][$id]["totaldkp"] ??= 0;

			$this->dkpToAdd[$tableid][$id]["dkp"]+=$points;
			if($points > 0)
				$this->dkpToAdd[$tableid][$id]["totaldkp"]+=$points;

			if($foritem == 1)
				break;
		}

		//create an entry in memory for this this history and the players
		//we will use this later to make entries into dkp_pointhistory
		//We can't make this yet because we don't know the awards id in the
		//database yet because it hasn't been created. We can't create it now
		//because we must do a bulk insert in order to save time. We'll come
		//back to this entry later after we've performed the bult insert
		$temp = array();
		$temp["data"] = array($tableid, $reason, $date);
		$temp["users"] = $userids;
		$this->historyData[] = $temp;

	}

	/*=================================================
	Inserts all the awards that we found in the backup file
	=================================================*/
	function InsertAwards(){
		global $sql;
		$temp = array();

		//iterate through each of the insert strings, placing them
		//into the array $temp
		//Perform inserts of 200 items at a time
		foreach($this->awardInserts as $insertString){
			$temp[]=$insertString;
			if(sizeof($temp) > 200) {
				$inserts = implode(",",$temp);
				$sql->Query("INSERT IGNORE INTO dkp_awards (guild, tableid, points, reason, location, awardedby, date, foritem, playercount, transfer)
					 VALUES $inserts");
				//clear temp
				$temp = array();
			}
		}

		//if there are any others left, insert them now
		if(sizeof($temp) > 0 ) {
			$inserts = implode(",",$temp);
			$sql->Query("INSERT IGNORE INTO dkp_awards (guild, tableid, points, reason, location, awardedby, date, foritem, playercount, transfer)
					 VALUES $inserts");
		}

		$this->log .= "Inserted ".count($this->awardInserts)." Award Entries<br />";
	}
	/*=================================================
	Inserts all of the history entries for individual players
	This maps an entry in their history to the award database
	entry that contains all the real data
	=================================================*/
	function InsertHistory(){
		global $sql;

		$toInsert = array();
		//run through our history data and convert them into a series of insert strings
		//var_dump ($this->historyData);
		foreach($this->historyData as $data) {
			$tableid = $data["data"][0];
			$reason = $data["data"][1];
			$date = $data["data"][2];
			$guildid = $this->guild->id;

			//find out the award id
			$awardid = $sql->QueryItem("SELECT id FROM dkp_awards WHERE guild='$guildid' AND tableid='$tableid' AND reason='$reason' AND date='$date'");
			$userids = $data["users"];

			foreach($userids as $userid) {
				$temp = "('$userid', '$awardid', '$guildid')";
				$toInsert[] = $temp;
			}
		}

		//perform the mass insert now
		$temp = array();
		foreach($toInsert as $insertString){
			$temp[]=$insertString;
			if(sizeof($temp) > 2000) {
				$tempInserts = implode(",",$temp);
				$sql->Query("INSERT IGNORE INTO dkp_pointhistory (user, award, guild)
				 VALUES $tempInserts");
				$temp = array();
			}
		}

		//if there are any others left, insert them now
		if(sizeof($temp) > 0 ) {
			$tempInserts = implode(",",$temp);
			$sql->Query("INSERT IGNORE INTO dkp_pointhistory (user, award, guild)
				 VALUES $tempInserts");

		}
	}

	/*=================================================
	Sets players lifetime and total dkp points. This number
	is calcualted based on the total history awards that they
	recieved.
	=================================================*/
	function InsertPlayerPoints(){
		global $sql;

		$guildid = $this->guild->id;

		//while were were scanning in dkp history data
		//we picked up a running total of each players dkp and lifetime dkp
		//we need to add that to the table now

		//each entry in dkpToAdd holds information for a single tableid
		foreach($this->dkpToAdd as $tableid => $table) {
			if(empty($tableid))
				continue;

			//each entry for a tableid holds an array of all the players
			//in that table and what their dkp and total dkp should be
			foreach($table as $playerid => $entry){
				if(empty($playerid))
					continue;

				//load data from the structure
				$points = $entry["dkp"];
				$totaldkp = $entry["totaldkp"];
				$totaldkp = isset($totaldkp) ? $totaldkp : 0;

				$this->log .= "Updating points for $playerid to $points / $totaldkp <br />";

				//check to see if the user already exists with data
				$exists = $sql->QueryItem("SELECT id FROM dkp_points WHERE guild='$guildid' AND tableid='$tableid' AND user='$playerid'");

				//if they do not exist, set their dkp and total dkp to what we calculated
				if($exists==""){
					$sql->Query("INSERT INTO dkp_points SET user='$playerid',tableid='$tableid', guild='$guildid', lifetime='$totaldkp', points='$points'");
				}

				//if the user already exists in the dkp table, just update their current values
				else {
					$sql->Query("UPDATE dkp_points set points=points+'$points', lifetime=lifetime+'$totaldkp' WHERE user='$playerid' AND tableid='$tableid' AND guild='$guildid'");
				}
			}
		}
	}

	function IsUTF8($string)
	{
		if(is_array($string)) {
			$string = implode('',$string);
		}

		$type = mb_detect_encoding($string,"ASCII, UTF-8", true);

		if($type == "UTF-8") {
			return true;
		}
		return false;
	}

	function EnsureUTF8($string){
		/*if(!$this->IsUTF8($string)) {
			$this->log .= "Need to Encode $string - ";
			$temp1 = utf8_decode($string);
			$temp2 = utf8_encode($temp1);
			$this->log .= "$temp1 > $temp2 ";
			$string = utf8_encode($string);
			$this->log .= " Turned into $string <br />";
		}*/
		return $string;
	}
}
?>