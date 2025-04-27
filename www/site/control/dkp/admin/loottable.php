<?php
include_once("adminmain.php");
/*=================================================
Allows user to manage their loot table.
=================================================*/
class pageLootTable extends pageAdminMain {

	var $log = "";

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;

		$this->title = "Manage Loot Table";
		$this->border = 1;


		$tables = dkpUtil::GetLootTables($this->guild->id);

		$this->addJavascriptHeader($siteRoot."js/dkpAdmin.js");

		//call the template
		$this->set("tables",$tables);
		return $this->fetch("settings/loottable.tmpl.php");
	}

	/*=================================================
	EVENT - CREATES A NEW TABLE
	=================================================*/
	function eventCreateTable(){
		$name = strip_tags(util::getData("name"));

		if(!$this->HasPermission("LootTable"))
			return $this->setEventResult(false, "You do not have permission to edit loot tables.");

		if(dkpLootTable::exists($this->guild->id, $name))
			return $this->setEventResult(false, "A table with this name already exists.");


		$table = new dkpLootTable();
		$table->name = $name;
		$table->guild = $this->guild->id;
		$table->saveNew();

		$this->setEventResult(true, "Table Created");
	}

	/*=================================================
	EVENT - DELETES A SUB TABLE
	=================================================*/
	function eventDeleteTable(){
		if(!$this->HasPermission("LootTable"))
			return $this->setEventResult(false, "You do not have permission to delete loot tables.");

		$tableid = util::getData("id");

		$table = new dkpLootTable();
		$table->loadFromDatabase($tableid);

		if(empty($table->id))
			return;/* $this->setEventResult(false, "Invalid tableid passed");*/

		if($table->guild != $this->guild->id )
			return $this->setEventResult(false, "You cannot delete another guild's loot table.");

		$table->delete();

		$this->setEventResult(true, "Table Deleted");
	}

	/*=================================================
	EVENT - GENERATES A DOWNLOADED FORM OF THE LOOT TABLE
	=================================================*/
	function eventDownload(){
		header("Content-Type: text/plain;charset=UTF-8");
		header("Content-Disposition: attachment; filename=WebDKP_Loot_Table.txt");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		$tables = dkpUtil::GetLootTables($this->guild->id);
		$lastSubtable = "";
		$lastSection = "";
		foreach($tables as $table) {
			$table->loadTableData();
			foreach($table->sections as $section) {
				foreach($section->loot as $loot) {
					if($loot->name=="" && $loot->cost=="")
						continue;

					if($table->name != $lastSubtable)
						echo("$table->name");
					echo("\t");
					if($section->name != $lastSection)
						echo("$section->name");
					echo("\t");
					echo("$loot->name");
					echo("\t");
					echo("$loot->cost");
					echo("\r\n");

					$lastSubtable = $table->name;
					$lastSection = $section->name;
				}
			}
		}

		die();
	}

	/*=================================================
	EVENT - UPLOADS A NEW LOG FILE
	=================================================*/
	function eventUpload(){
		set_time_limit(0);

		//make sure they have permissions
		if(!$this->HasPermission("LootTable"))
			return $this->setEventResult(false, "You do not have permission to upload a loot table file.");

		//make sure the file type is valid
		if($_FILES['userfile']['type'] != "text/plain") {
			return $this->setEventResult(false, "Invalid file format uploaded. You must upload a Tab-Delimited .txt file");
		}

		//get the upload file
		$file = $_FILES['userfile']['tmp_name'];
		//split it into lines
		$lines = file($file);

		//make sure the log is valid utf8
		if(!$this->IsUTF8($lines)) {
			$this->log .= "File Is NOT UTF8. Converting to UTF8 before parse.<br />";
			for($i = 0 ; $i < sizeof($lines) ; $i++)
			  $lines[$i] = mb_convert_encoding($lines[$i], "UTF-8", mb_detect_encoding($lines[$i]));
		}
		else {
			$this->log .= "File is UTF8<br />";
		}


		//keep track of some items while we run through each of the lines
		$count = 0;				//number of updates
		$lasttable = "";		//the table name of the previous row
		$lastsection = "";		//the section name of the previous row
		$toDelete = array();	//a series of ids of current items that need to be deleted before being updated
		$toInsert = array();	//a series of insert strings of items that should be added to the database

		//start running through each of the lines
		foreach($lines as $line) {
			//explode the line by tabs
			$entry = explode("\t",$line);
			$numCols = count($entry);
			//determine the file format being uploaded and
			//pull out the relevent data
			if($numCols == 4 ) {			//Full Format
				$subtable = $entry[0];
				$section = $entry[1];
				$name = $entry[2];
				$cost = $entry[3];
			}
			else if($numCols == 3) {		//Format 2
				$subtable = $entry[0];
				$section = "General";
				$name = $entry[1];
				$value = $entry[2];
			}
			else if($numCols == 2) {		//Format 1
				$subtable = "Loot Table";
				$section = "General";
				$name = $entry[0];
				$cost = $entry[1];
			}
			else
				continue;

			//if the subtable or section field is missing, grab it from the
			//previous entry
			if(empty($subtable))
				$subtable = $lasttable;
			if(empty($section))
				$section = $lastsection;

			//make sure the sub table exists
			$table = $this->EnsureLootTableExists($subtable);

			//make sure the section exists
			$sectionObj = $this->EnsureLootSectionExists($table->id, $section);

			//now check to see if the object exists
			if(dkpLootTableEntry::exists($table->id, $sectionObj->id, $name)) {
				//if it does exist, check to see if its cost has changed
				$entry = new dkpLootTableEntry();
				$entry->loadFromDatabaseByName($sectionObj->id, $name);
				if(trim($entry->cost) != trim($cost)) {
					//if the cost changed, we will delete the current entry
					//then insert a newly modified version. We end up doing the
					//build delete and insert to speed up the process and reduce query
					//counts
					$toDelete[] = " id = $entry->id ";
					$name = sql::Escape($name);
					$cost = sql::Escape($cost);
					$toInsert[] = "('$table->id', '$sectionObj->id', '$name', '$cost')";
					$count++;
				}
			}
			else {
				//item didn't exist yet, go ahead and add it now
				$name = sql::Escape($name);
				$cost = sql::Escape($cost);
				$toInsert[] = "('$table->id', '$sectionObj->id', '$name', '$cost')";
				$count++;
			}

			//remember the table and section so it can be autofilled in (if neccessary)
			//for the next entry in the table
			$lasttable = $subtable;
			$lastsection = $section;
		}

		global $sql;
		//delete any of the entries that need to be updated
		if(sizeof($toDelete) > 0 ) {
			$toDelete = implode(" OR ",$toDelete);
			$sql->Query("DELETE FROM dkp_loottable_data WHERE $toDelete");
		}
		//make all loot table updates at once
		if(sizeof($toInsert) > 0 ) {
			$toInsert = implode(",",$toInsert);
			$sql->Query("INSERT IGNORE INTO dkp_loottable_data (loottable, section, name, cost)
						 VALUES $toInsert");
		}

		//and done
		$this->setEventResult(true, "Upload Complete! $count Updates");
	}

	/*=================================================
	Ensures that a given sub loot table exists for the current guild.
	If it doesn't, one is created.
	Returns the loot table object.
	=================================================*/
	function EnsureLootTableExists($name){
		//make sure the table exists
		$table = new dkpLootTable();
		if(!dkpLootTable::exists($this->guild->id, $name)) {
			$table->guild = $this->guild->id;
			$table->name = $name;
			$table->saveNew();
		}
		else {
			$table->loadFromDatabaseByName($this->guild->id, $name);
		}

		return $table;
	}
	/*=================================================
	Ensures that the given loot section exists. If it doesn't, one
	is created. Returns the loot table section.
	=================================================*/
	function EnsureLootSectionExists($loottable, $name){
		//now make sure the section exists
		$sectionObj = new dkpLootTableSection();
		if(!dkpLootTableSection::exists($loottable, $name)) {
			$sectionObj->loottable = $loottable;
			$sectionObj->name = $name;
			$sectionObj->saveNew();
		}
		else {
			$sectionObj->loadFromDatabaseByName($loottable, $name);
		}
		return $sectionObj;
	}

	/*=================================================
	Returns true if the givevn string ( or file array)
	is in utf 8 form or not
	=================================================*/
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
}
?>