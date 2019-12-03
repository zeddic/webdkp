<?php
include_once("lib/dkp/dkpAward.php");
include_once("lib/convertProgress.php");
include_once("lib/dkp/loottable/dkpLootTable.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageConvert extends page {

	var $layout = "Columns1";
	/*=================================================
	Gives the user a form where they can list the server
	that is missing.
	=================================================*/
	function area2()
	{
		$this->title = "Database Convert";
		$this->border = 1;


		//return $this->fetch("join/servermissing.tmpl.php");
		if($this->log != "")
			$this->set("log", $this->log);

		if($this->creatingAwards) {
			$this->set("creatingAwards", true);
			$this->set("lastGuild", $this->awardGuildId);
		}
		if($this->convertingHistory) {
			$this->set("convertingHistory", true);
			$this->set("lastGuild", $this->historyGuildId);
		}
		if($this->convertingLoot) {
			$this->set("convertingLoot", true);
			$this->set("lastGuild", $this->lastGuildId);
		}

		return $this->fetch("convert.tmpl.php");
	}

	function eventCreateAwards(){
		$this->creatingAwards = true;
		set_time_limit(0);

		$guildid = $this->getWorkingGuild("CreateAwards");

		global $sql;

		$guildsPerQuery = 100;

		$count = 0;
		$times = 0;
		$toInsert = array();

		while($guildid != "" && $times < $guildsPerQuery) {
			$this->log .= "Converting Guild $guildid - ";
			$times++;

			//$result = $sql->Query("SELECT *, count(*) AS total FROM dkp_pointhistory_old WHERE guild='$guildid' GROUP BY date ");
			$result = $sql->Query("SELECT * FROM dkp_pointhistory_old WHERE guild='$guildid'");
			$toInsert = array();
			while($row = mysqli_fetch_array($result)) {

				$guild = $row["guild"];
				$tableid = $row["tableid"];
				$reason = sql::Escape($row["reason"]);
				$date = $row["date"];
				$location = sql::Escape($row["location"]);
				$awardedby = sql::Escape($row["awardedby"]);
				$points = $row["points"];
				$foritem = $row["forItem"];
				$playercount = 0;//$row["total"];
				if(strpos($reason,"Transfer")!== false)
					$transfer = 1;
				else
					$transfer = 0;

				$exists = $sql->QueryItem("SELECT id FROM dkp_awards WHERE guild='$guild' AND tableid='$tableid' AND reason='$reason' AND date='$date'");
				if ( $exists == "") {

					$temp = "('$guildid', '$tableid', '$points', '$reason', '$location', '$awardedby', '$date', '$foritem', '$playercount', '$transfer')";
					$toInsert[] =  $temp;
					$count++;
				}
				//else {
				//	$sql->Query("UPDATE dkp_awards SET playercount = playercount + 1 WHERE ")
				//}
			}

			$this->log .= " <br />";

			$guildid = $this->nextGuildId($guildid);

			/*if($times < $guildsPerQuery)
				$guildid = $this->getNextGuild("CreateAwards", false);
			else
				$guildid = $this->getNextGuild("CreateAwards");*/
		}

		if( sizeof($toInsert) > 0 ) {
			$inserts = implode(",",$toInsert);
			$sql->Query("INSERT IGNORE INTO dkp_awards (guild, tableid, points, reason, location, awardedby, date, foritem, playercount, transfer)
					 VALUES $inserts");
		}

		$this->setSimpleProgress("CreateAwards", $guildid);

		if($guildid != "")
			$left = $sql->QueryItem("SELECT count(*) FROM dkp_guilds WHERE id > $guildid");
		else
			$left = 0;

		$this->awardGuildId = $guildid;
		$this->setEventResult(true, $count." Awards Created. On guild $guildid . $left Guilds Remaining to Convert");
	}

	function getWorkingGuild($area){
		global $sql;
		if(!convertProgress::exists($area)) {
			$progress = new convertProgress();
			$progress->name = $area;
			$progress->progress = $sql->QueryItem("SELECT id FROM dkp_guilds ORDER BY id ASC LIMIT 1");
			$progress->saveNew();
			return $progress->progress;
		}
		$progress = new convertProgress();
		$progress->loadFromName($area);
		return $progress->progress;
	}

	function nextGuildId($id){
		global $sql;
		$guild = $sql->QueryItem("SELECT id FROM dkp_guilds WHERE id > $id ORDER BY id ASC LIMIT 1");
		return $guild;
	}

	function getNextGuild($area, $update = true){
		$current = $this->getWorkingGuild($area);

		global $sql;
		if($current != "")
			$guild = $sql->QueryItem("SELECT id FROM dkp_guilds WHERE id > $current ORDER BY id ASC LIMIT 1");

		if($update) {
			$progress = new convertProgress();
			$progress->loadFromName($area);
			$progress->progress = $guild;
			$progress->save();
		}

		return $guild;
	}

	function eventConvertHistory(){

		global $sql;

		$this->convertingHistory = true;
		set_time_limit(0);

		$guildid = $this->getWorkingGuild("ConvertHistory");

		$guildsPerQuery = 20;
		$count = 0;
		$times = 0;
		while($guildid != "" && $times < $guildsPerQuery) {

			$toInsert = array();
			$this->log .= "Converting Guild $guildid - ";

			$times++;
			$toInsert = array();
			$result = $sql->Query("SELECT * FROM dkp_pointhistory_old WHERE guild='$guildid'");
			while( $row = mysqli_fetch_array($result)) {

				$user = $row["user"];
				$reason = sql::Escape($row["reason"]);
				$date = $row["date"];
				$points = $row["points"];

				$award = $sql->QueryItem("SELECT id FROM dkp_awards WHERE reason='$reason' AND date='$date' AND points='$points'");
				if($award != "") {
					$temp = "('$user', '$award', '$guildid')";
					$toInsert[] = $temp;
					$count ++;
					$sql->QueryItem("UPDATE dkp_awards SET playercount = playercount + 1 WHERE id='$award'");
				}
				else {
					$this->log .= " Award w/o ID! <br />";
				}
			}

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

			$this->log .= " ".count($toInsert)." Awards <br />";

			$guildid = $this->getNextGuild("ConvertHistory");
		}

		if($guildid != "")
			$left = $sql->QueryItem("SELECT count(*) FROM dkp_guilds WHERE id > $guildid");
		else
			$left = 0;

		$this->historyGuildId = $guildid;

		$this->setEventResult(true, $count." History Entries Converted. On Guild $guildid . $left Guilds Left");
	}

	function eventConvertUsers(){
		global $sql;
		$result = $sql->Query("SELECT * FROM users ORDER BY id ASC");

		$user = new user();

		$values = array();
		while($row = mysqli_fetch_array($result)) {
			$id = $row["id"];
			$name = sql::Escape($row["name"]);
			$password = $row["password"];
			$email = sql::Escape($row["email"]);
			$registered = $row["regDate"];
			$guild = $row["guild"];
			$usergroup = 2;
			if($row["userGroup"] == "Admin")
				$usergroup = 3;

			$user->setPassword($password);
			$password = $user->password;

			$exists = $sql->QueryItem("SELECT id FROM security_users WHERE id='$id'");
			if($exists == "") {
				$temp = "('$id', '$name', '$password', '$email', '$usergroup', '$registered', '$guild')";
				$values[] = $temp;
			}
		}

		if(sizeof($values) > 0 ) {
			$tempInserts = implode(",",$values);
			$sql->Query("INSERT IGNORE INTO security_users (id, username, password, email, usergroup, registerdate, guild )
			 VALUES $tempInserts");
			$temp = array();
		}

		$this->setEventResult(true, sizeof($values)." accounts converted.");
	}

	function eventConvertLoot(){
			global $sql;

		$this->convertingLoot = true;
		set_time_limit(0);
		$guildid = $this->getWorkingGuild("ConvertLoot");
		$guildsPerQuery = 50;
		$count = 0;
		$times = 0;
		while($guildid != "" && $times < $guildsPerQuery) {
			$times++;

			$this->log .= "Working on Guild $guildid <br />";

			//get their loot table
			$loottable = $sql->QueryItem("SELECT id FROM dkp_costtable WHERE guild='$guildid'");
			if($loottable == "") {
				$this->log .= "No Loot Table - Skipping <br />";
			}
			else {
				$this->log .= "Has loottable id of $loottable <br />";
				$catResult = $sql->Query("SELECT category
									 FROM dkp_costtableentry
									 WHERE loottable=$loottable
									 GROUP BY category
									 ORDER BY category ASC");
				while($row = mysqli_fetch_array($catResult)) {
					$tablename = $row["category"];
					if($tablename == "")
						$tablename = "General";
					$this->log.="Found table $tablename <br />";
					if(!dkpLootTable::exists($guildid, $tablename)) {
						$table = new dkpLootTable();
						$table->guild = $guildid;
						$table->name = $tablename;
						$table->saveNew();

						$inserts = array();

						//now to create any subsections
						$escapeName = sql::Escape($tablename);
						$sectionsResult = $sql->Query("SELECT subcategory
												 FROM dkp_costtableentry
												 WHERE loottable=$loottable
												 AND category='$escapeName'
												 GROUP BY subcategory
												 ORDER BY subcategory ASC");
						while($row = mysqli_fetch_array($sectionsResult)) {
							$sectionName = $row["subcategory"];
							if($sectionName == "")
								$sectionName = "General";

							$this->log.="Found sub-table $sectionName <br />";
							if(!dkpLootTableSection::exists($table->id, $sectionName)) {
								$section = new dkpLootTableSection();
								$section->loottable = $table->id;
								$section->name = $sectionName;
								$section->saveNew();

								$sectionEscape = sql::Escape($sectionName);
								//now to add all the loot from this section
								$lootResult = $sql->Query("SELECT name, value
														   FROM dkp_costtableentry
														   WHERE loottable=$loottable
														   AND category='$escapeName'
														   AND subcategory='$sectionEscape'
														   ORDER BY name ASC");
								while($row = mysqli_fetch_array($lootResult)) {
									$name = sql::Escape($row["name"]);
									$value = sql::Escape($row["value"]);
									$sectionid = $section->id;
									$loottableid = $table->id;
									$count++;

									$temp = "('$sectionid', '$loottableid', '$name', '$value')";
									$inserts[] = $temp;

								} //end load items
							} //end if section not exist
						} //end load sections

						//add all the items for this loot table at once
						if(sizeof($inserts) > 0 ) {
							$insertString = implode(",",$inserts);
							$sql->Query("INSERT IGNORE INTO dkp_loottable_data (section, loottable, name, cost)
							 VALUES $insertString");
							$this->log .=" Added ".count($inserts)." for $tablename <br /> ";
						}

					} //end if loot table not exist
				}
				$this->log.="<br />";
			}

			//ended converting loot table for a single guild
			$guildid = $this->getNextGuild("ConvertLoot");
		}

		if($guildid != "")
			$left = $sql->QueryItem("SELECT count(*) FROM dkp_guilds WHERE id > $guildid");
		else
			$left = 0;

		$this->lastGuildId = $guildid;

		$this->setEventResult(true, $count." Loot Entries Made. On Guild $guildid . $left Guilds Left");
	}

	function eventConvertOptions(){
		global $sql;
		set_time_limit(0);
		//we can do this one in one shot

		$inserts = array();
		$result = $sql->Query("SELECT * FROM dkp_settings_old ORDER BY guild ASC");
		while($row = mysqli_fetch_array($result)) {
			$guild = $row["guild"];
			$loottableenabled = $row["loottableenabled"];
			$tiersenabled = $row["tiersenabled"];
			$tiersize = $row["tiersize"];
			$zerosumenabled = $row["zerosumenabled"];
			$totalscalculated = $row["totalscalculated"];
			$totaldkpenabled = $row["totaldkpenabled"];
			$prostatus = $row["prostatus"];
			$proaccount = $row["proaccount"];
			$combinealts = $row["combinealts"];
			$setsenabled = $row["setsenabled"];

			$inserts[] = "('$guild', 'LootTableEnabled', '$loottableenabled')";
			$inserts[] = "('$guild', 'TiersEnabled', '$tiersenabled')";
			$inserts[] = "('$guild', 'TierSize', '$tiersize')";
			$inserts[] = "('$guild', 'ZerosumEnabled', '$zerosumenabled')";
			$inserts[] = "('$guild', 'LifetimeEnabled', '$totaldkpenabled')";
			$inserts[] = "('$guild', 'Prostatus', '$prostatus')";
			$inserts[] = "('$guild', 'Proaccount', '$proaccount')";
			$inserts[] = "('$guild', 'CombineAltsEnabled', '$combinealts')";
			$inserts[] = "('$guild', 'SetsEnabled', '$setsenabled')";

		}

		$temp = array();
		foreach($inserts as $insertString){
			$temp[]=$insertString;
			if(sizeof($temp) > 2000) {
				$tempInserts = implode(",",$temp);
				$sql->Query("INSERT IGNORE INTO dkp_settings (guild, name, value)
				 VALUES $tempInserts");
				$temp = array();
			}
		}

		//if there are any others left, insert them now
		if(sizeof($temp) > 0 ) {
			$sql->Query("INSERT IGNORE INTO dkp_settings (guild, name, value)
				 VALUES $tempInserts");

		}

		$this->log .= " ".count($inserts)." Settings Loaded <br />";
		$this->setEventResult(true, "Settings Loaded");
	}

	function eventConvertPermissions(){
		set_time_limit(0);

		global $sql;
		$count = 0;
		$result = $sql->Query("SELECT id, permissions FROM dkp_userpermissions");
		while($row = mysqli_fetch_array($result)) {
			$permissions = $row["permissions"];
			if(strpos($permissions,"$|$") !== false ) {
				$permissions = str_replace("$|$", "," , $permissions );
				$id = $row["id"];
				$sql->Query("UPDATE dkp_userpermissions SET permissions ='$permissions' WHERE id='$id'");
				$count++;
			}
		}

		$this->setEventResult(true, "Finished - Had to update $count Entries");
	}

	function getSimpleProgress($area){
		if(!convertProgress::exists($area)) {
			$progress = new convertProgress();
			$progress->name = $area;
			$progress->progress = 0;
			$progress->saveNew();
			return $progress->progress;
		}
		$progress = new convertProgress();
		$progress->loadFromName($area);
		return $progress->progress;
	}

	function setSimpleProgress($area, $value){
		$progress = new convertProgress();
		$progress->loadFromName($area);
		$progress->progress = $value;
		$progress->save();
	}

	function eventCheckUsers(){

		$progress = $this->getSimpleProgress("CheckUsers");
		if($progress == "1")
			return $this->setEventResult(false,"Conversion Was Previously Done");

		global $sql;
		$count = 0;
		$result = $sql->Query("SELECT id, name FROM dkp_users ORDER BY id ASC");
		while($row = mysqli_fetch_array($result)) {
			$name = $row["name"];
			$id = $row["id"];
			$count++;

			$type = mb_detect_encoding($name);
			$changed = false;

			if($type != "ASCII") {
				$name = utf8_decode($name);
				$this->log .= "Converted: $name <br />";
				$name = sql::Escape($name);
				$sql->Query("UPDATE dkp_users SET name='$name' WHERE id='$id'");
			}
		}

		$this->setSimpleProgress("CheckUsers", 1);
	}

	function eventCheckGuilds(){

		$progress = $this->getSimpleProgress("CheckGuilds");
		if($progress == "1")
			return $this->setEventResult(false,"Conversion Was Previously Done");

		global $sql;
		$count = 0;
		$result = $sql->Query("SELECT id, gname FROM dkp_guilds ORDER BY id ASC");
		while($row = mysqli_fetch_array($result)) {
			$name = $row["gname"];
			$id = $row["id"];
			$count++;

			$type = mb_detect_encoding($name);
			$changed = false;

			if($type != "ASCII") {
				$newname = utf8_decode($name);
				$this->log .= "Converted: $name to $newname <br />";
				$newname = sql::Escape($newname);
				$sql->Query("UPDATE dkp_guilds SET gname='$newname' WHERE id='$id'");
			}
		}

		$this->setSimpleProgress("CheckGuilds", 1);
	}
}

//tables to rename
//dkp_pointhistory --> dkp_pointhistory_old
//dkp_points : rename 'totaldkp' to 'lifetime'
//dkp_guilds : rename to gname, gfaction, gserver
//dkp_userpermissions: rename accountAdmin to isadmin
//dkp_userpermissions: rename usertables to tables
//dkp_settings to dkp_settings_old
//convert TABLES to UTF8 FORM (Must be done before people start syncing)

?>
