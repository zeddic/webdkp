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
		while($guildid != "" && $times < $guildsPerQuery) {
			$this->log .= "Converting Guild $guildid - ";
			$times++;
			$toInsert = array();
			$result = $sql->Query("SELECT *, count(*) AS total FROM dkp_pointhistory_old WHERE guild='$guildid' GROUP BY date, reason, points ");
			$toInsert = array();
			while($row = mysqli_fetch_array($result)) {

				$guild = $row["guild"] ?? null;
				$tableid = $row["tableid"] ?? null;
				$reason = sql::Escape($row["reason"]);
				$date = $row["date"] ?? null;
				$location = sql::Escape($row["location"]);
				$awardedby = sql::Escape($row["awardedby"]);
				$points = $row["points"] ?? null;
				$foritem = $row["forItem"] ?? null;
				$playercount = $row["total"] ?? null;
				if(strpos($reason,"Transfer")!== false)
					$transfer = 1;
				else
					$transfer = 0;

				$exists = $sql->QueryItem("SELECT id FROM dkp_awards WHERE guild='$guild' AND tableid='$tableid' AND reason='$reason' AND date='$date'");
				if ( $exists == "") {

					$temp = "('$guild', '$tableid', '$points', '$reason', '$location', '$awardedby', '$date', '$foritem', '$playercount', '$transfer')";
					$toInsert[] =  $temp;
					$count++;
				}
			}

			if( sizeof($toInsert) > 0 ) {
				$temp = array();
				foreach($toInsert as $insertString ) {
					$temp[] = $insertString;
					if(sizeof($temp > 2000)) {
						$inserts = implode(",",$temp);
						$sql->Query("INSERT IGNORE INTO dkp_awards (guild, tableid, points, reason, location, awardedby, date, foritem, playercount, transfer)
							 VALUES $inserts");
						$temp = array();
					}
				}
				if(sizeof($temp) > 0 ) {
					$inserts = implode(",",$temp);
					$sql->Query("INSERT IGNORE INTO dkp_awards (guild, tableid, points, reason, location, awardedby, date, foritem, playercount, transfer)
							 VALUES $inserts");
					$temp = array();
				}
			}

			$this->log .= " ".count($toInsert)." Awards <br />";

			$guildid = $this->getNextGuild("CreateAwards");
		}

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

	function getNextGuild($area){
		$current = $this->getWorkingGuild($area);

		global $sql;
		if($current != "")
			$guild = $sql->QueryItem("SELECT id FROM dkp_guilds WHERE id > $current ORDER BY id ASC LIMIT 1");

		$progress = new convertProgress();
		$progress->loadFromName($area);
		$progress->progress = $guild;
		$progress->save();

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
		$skipped = 0;
		while($guildid != "" && $times < $guildsPerQuery) {

			$toInsert = array();
			$this->log .= "Converting Guild $guildid - ";

			$times++;
			$toInsert = array();
			$result = $sql->Query("SELECT * FROM dkp_pointhistory_old WHERE guild='$guildid'");
			while( $row = mysqli_fetch_array($result)) {

				$user = $row["user"] ?? null;
				$reason = sql::Escape($row["reason"]);
				$date = $row["date"] ?? null;
				$points = $row["points"] ?? null;


				$award = $sql->QueryItem("SELECT id FROM dkp_awards WHERE guild='$guildid' AND reason='$reason' AND date='$date' AND points='$points'");
				if($award != "") {

					$exists = $sql->QueryItem("SELECT id FROM dkp_pointhistory WHERE guild='$guildid' AND award='$award' AND user='$user'");
					if($exists == "") {
						$temp = "('$user', '$award', '$guildid')";
						$toInsert[] = $temp;
						$count++;
					}
					else {
						$skipped++;
					}
				}
				else {
					$this->log .= " Award w/o for $date, $reason, and $points <br />ID! <br />";
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

		$this->setEventResult(true, $count." History Entries Converted. Skipped $skipped . On Guild $guildid . $left Guilds Left");
	}

	function eventConvertUsers(){
		global $sql;
		$result = $sql->Query("SELECT * FROM users ORDER BY id ASC");

		$user = new user();

		$values = array();
		while($row = mysqli_fetch_array($result)) {
			$id = $row["id"] ?? null;
			$name = sql::Escape($row["name"]);
			$password = $row["password"] ?? null;
			$email = sql::Escape($row["email"]);
			$registered = $row["regDate"] ?? null;
			$guild = $row["guild"] ?? null;
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

		$temp = array();
		foreach($values as $insertString){
			$temp[]=$insertString;
			if(sizeof($temp) > 2000) {
				$tempInserts = implode(",",$temp);
				$sql->Query("INSERT IGNORE INTO security_users (id, username, password, email, usergroup, registerdate, guild )
			 	VALUES $tempInserts");
				$temp = array();
			}
		}

		//if there are any others left, insert them now
		if(sizeof($temp) > 0 ) {
			$tempInserts = implode(",",$temp);
			$sql->Query("INSERT IGNORE INTO security_users (id, username, password, email, usergroup, registerdate, guild )
		 	VALUES $tempInserts");
			$temp = array();

		}


		/*if(sizeof($values) > 0 ) {
			$tempInserts = implode(",",$values);
			$sql->Query("INSERT IGNORE INTO security_users (id, username, password, email, usergroup, registerdate, guild )
			 VALUES $tempInserts");
			$temp = array();
		}*/

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
					$tablename = $row["category"] ?? null;
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
							$sectionName = $row["subcategory"] ?? null;
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
			$guild = $row["guild"] ?? null;
			$loottableenabled = $row["loottableenabled"] ?? null;
			$tiersenabled = $row["tiersenabled"] ?? null;
			$tiersize = $row["tiersize"] ?? null;
			$zerosumenabled = $row["zerosumenabled"] ?? null;
			$totalscalculated = $row["totalscalculated"] ?? null;
			$totaldkpenabled = $row["totaldkpenabled"] ?? null;
			$prostatus = $row["prostatus"] ?? null;
			$proaccount = $row["proaccount"] ?? null;
			$combinealts = $row["combinealts"] ?? null;
			$setsenabled = $row["setsenabled"] ?? null;

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
			$permissions = $row["permissions"] ?? null;
			if(strpos($permissions,"$|$") !== false ) {
				$permissions = str_replace("$|$", "," , $permissions );
				$id = $row["id"] ?? null;
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
		//if($progress == "1")
		//	return $this->setEventResult(false,"Conversion Was Previously Done");

		global $sql;
		$count = 0;
		$result = $sql->Query("SELECT id, name FROM dkp_users ORDER BY id ASC");
		while($row = mysqli_fetch_array($result)) {
			$name = $row["name"] ?? null;
			$id = $row["id"] ?? null;
			$count++;

			$type = mb_detect_encoding($name);
			$changed = false;

			if($type != "ASCII") {
				$newname = utf8_decode($name);
				$newname2 = mb_convert_encoding($name, "UTF-8", mb_detect_encoding($name));
				$this->log .= "Converted: $name -> $newname -> $newname2 <br />";
				$name = $newname;
				$name = sql::Escape($name);
				//$sql->Query("UPDATE dkp_users SET name='$name' WHERE id='$id'");
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
				//$sql->Query("UPDATE dkp_guilds SET gname='$newname' WHERE id='$id'");
			}
		}

		$this->setSimpleProgress("CheckGuilds", 1);
	}

	function eventFixGuilds(){
		global $sql;
		$result = $sql->Query("SELECT * FROM dkp_guilds WHERE gname LIKE '%/%'");
		while($row = mysqli_fetch_array($result)) {
			$id = $row["id"];
			$name = $row["gname"];
			$server = $row["gserver"];

			$this->log .= "Have to convert $name <br />";

			$name = str_replace("/", "-" , $name);


			$tempname = sql::Escape($name);
			$tempserver = sql::Escape($server);
			$exists = $sql->QueryItem("SELECT id FROM dkp_guilds WHERE gname='$tempname' AND gserver='$tempserver'");
			if( $exists != "") {
				$name = str_replace("-", ".", $name);
				$tempname = sql::Escape($name);
				$exists = $sql->QueryItem("SELECT id FROM dkp_guilds WHERE gname='$tempname' AND gserver='$tempserver'");
				if($exists != "") {
					$this->log .= "Had to skip a guild... could not convert it. <br />";
					continue;
				}
			}

			$newname = sql::Escape($name);
			$sql->Query("UPDATE dkp_guilds SET gname='$newname' WHERE id='$id'");
			$this->log .= "Converteed form is $name <br />";
		}

	}

	function getZerosumRoot($reason){
		if(strpos($reason,"Zerosum: ") === false && strpos($reason,"ZeroSum: ") === false)
			return $reason;
		else {
			$temp = str_replace("Zerosum: ", "", $reason);
			return str_replace("ZeroSum: ", "", $temp);
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

	function eventCheckZerosum(){
		set_time_limit(0);
		global $sql;

		$count = 0;
		$skipped = 0;
		$guildid = $this->guild->id;
		$result = $sql->Query("SELECT * FROM dkp_awards WHERE zerosumauto='1' AND linked='0'");
		while( $row = mysqli_fetch_array($result) ) {
			$id = $row["id"];
			$guild = $row["guild"];
			$reason = $row["reason"];
			$tableid = $row["tableid"];
			$date = $row["date"];
			$root = sql::Escape($this->GetZerosumRootReason($reason));

			$linked = $sql->QueryItem("SELECT id FROM dkp_awards
									WHERE guild='$guild'
									AND tableid='$tableid' AND date='$date'
									AND reason='$root'");
			if( $linked != "" ) {
				$count++;
				$sql->Query("UPDATE dkp_awards SET linked = '$linked' WHERE id='$id'");
				$sql->Query("UPDATE dkp_awards SET linked = '$id' WHERE id='$linked'");
			}
			else
				$skipped++;
		}

		/*$result = $sql->Query("SELECT * FROM dkp_awards WHERE reason LIKE '%Zerosum: %' AND ( zerosumauto='0' || linked = id )");
		$count = 0;
		$skipped = 0;
		while($row = mysqli_fetch_array($result)) {
			$id = $row["id"];
			$reason = $row["reason"];
			$root = sql::Escape($this->getZerosumRoot($reason));
			$guild = $row["guild"];
			$date = $row["date"];
			$tableid = $row["tableid"];


			$related = $sql->QueryItem("SELECT id FROM dkp_awards
										WHERE guild='$guild' AND tableid='$tableid' AND date='$date' AND reason='$root'");

			if($related != "") {
				$count++;
				//$this->log .= "Found related $related <br />";
				$sql->Query("UPDATE dkp_awards SET zerosumauto='1', linked='$related' WHERE id='$id'");
				$sql->Query("UPDATE dkp_awards SET linked='$id' WHERE id='$related'");
			}
			else {
				$skipped++;
				//$this->log .= "Skipped award $reason - $root - $guild - table $tableid no related found <br />";
			}
		}*/

		//now check on the transfers
		//$result = $sql->Query("SELECT * FROM dkp_awards WHERE reason LIKE '%Transfer from Alt%'");
		//while($row = mysqli_fetch_array($result)) {
		//}

		$this->setEventResult(true, $count." Linked. $skipped Skipped.");
	}

	function eventCheckUserSpaces(){
		global $sql;

		$result = $sql->Query("SELECT id, name FROM dkp_users");
		while($row = mysqli_fetch_array($result)) {
			$name = $row["name"];
			$id = $row["id"];
			if( $name != trim($name)) {
				$count++;
				$newname = trim($name);
				$this->log.= "Converted '$name' to '$newname'<br />";
				$newname = sql::Escape($newname);
				$sql->Query("UPDATE IGNORE dkp_users SET name = '$newname' WHERE id='$id'");
			}
		}

		$this->setEventResult(true, $count." Users Converted");
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
