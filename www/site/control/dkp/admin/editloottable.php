<?php
include_once("adminmain.php");
include_once("lib/dkp/loottable/dkpLootTable.php");
/*=================================================
Allows user to edit a single loot table
From this page they can add new sections to the loot table
as well as add items to these sections.
Sections are created and deleted using page refreshes.
All other interactions occur using AJAX calls.
=================================================*/
class pageEditLootTable extends pageAdminMain {

	//contains the loot table that is currently being edited
	var $loottable;

	/*=================================================
	Main Page Content
	=================================================*/
	function __construct(){
		parent::__construct();

		$id = util::getData("table");
		$this->loottable = new dkpLootTable();
		$this->loottable->loadFromDatabase($id);
	}

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;

		$this->title = "Edit Loot Table: ".$this->loottable->name;
		$this->border = 1;

		if($this->loottable->guild != $this->guild->id) {
			$this->title = "Loot Table Error";
			return "An invalid loot table id (".$this->loottable->guild.") was passed to this page.";
		}

		//$tables = dkpUtil::GetLootTables($this->guild->id);

		$this->loottable->loadTableData();

		$this->addJavascriptHeader($siteRoot."js/dkpAdmin.js");

		//call the template
		$this->set("table",$this->loottable);
		return $this->fetch("settings/editloottable.tmpl.php");
	}

	/*=================================================
	EVENT - Update (Rename) a table
	=================================================*/
	function eventUpdateTable(){
		$name = strip_tags(util::getData("name"));

		if(!$this->HasPermission("LootTable"))
			return $this->setEventResult(false, "You do not have permission to edit loot tables.");

		if($this->loottable->guild != $this->guild->id)
			return $this->setEventResult(false, "You can not edit someone elses loot table");

		$this->loottable->name = $name;
		$this->loottable->save();

		$this->setEventResult(true, "Table Renamed");
	}

	/*=================================================
	EVENT - CREATE A NEW LOOT TABLE SECTION
	=================================================*/
	function eventCreateSection(){
		$name = strip_tags(util::getData("name"));

		if(!$this->HasPermission("LootTable"))
			return $this->setEventResult(false, "You do not have permission to edit loot tables.");

		if($this->loottable->guild != $this->guild->id)
			return $this->setEventResult(false, "You can not edit someone elses loot table");

		if(dkpLootTableSection::exists($this->loottable->id, $name))
			return $this->setEventResult(false, "A section with this name already exists in this table");

		$section = new dkpLootTableSection();
		$section->name = $name;
		$section->loottable = $this->loottable->id;
		$section->saveNew();

		$this->setEventResult(true, "Section created");

	}
	/*=================================================
	EVENT - DELETE A LOOT TABLE SECTION
	=================================================*/
	function eventDeleteSection(){
		if(!$this->HasPermission("LootTable"))
			return $this->setEventResult(false, "You do not have permission to edit loot tables.");

		if($this->loottable->guild != $this->guild->id)
			return $this->setEventResult(false, "You can not edit someone elses loot table");

		$id = strip_tags(util::getData("id"));

		$section = new dkpLootTableSection();
		$section->loadFromDatabase($id);

		if($section->loottable != $this->loottable->id)
			return $this->setEventResult(false, "You cannot delete that section - it does not belong to this table");

		$section->delete();

		$this->setEventResult(true, "Section Deleted");
	}
	/*=================================================
	AJAX - RENAME AN ALREADY CREATED LOOT TABLE SECTION
	=================================================*/
	function eventRenameSection(){
		if(!$this->HasPermission("LootTable"))
			return $this->setEventResult(false, "You do not have permission to edit loot tables.");

		if($this->loottable->guild != $this->guild->id)
			return $this->setEventResult(false, "You can not edit someone elses loot table");

		$id = util::getData("id");
		$name = strip_tags(util::getData("name"));

		$section = new dkpLootTableSection();
		$section->loadFromDatabase($id);

		if($section->loottable != $this->loottable->id)
			return $this->setEventResult(false, "You cannot edit this section - it does not belong to this table");

		$section->name = $name;
		$section->save();

		$this->setEventResult(true, "Section renamed.");
	}

	/*=================================================
	AJAX - ADD AN ITEM TO A LOOT TABLE
	=================================================*/
	function ajaxAddItem(){
		if(!$this->HasPermission("LootTable"))
			return $this->setAjaxResult(false, "You do not have permission to edit loot tables.");
		if($this->loottable->guild != $this->guild->id)
			return $this->setAjaxResult(false, "You can not edit someone elses loot table");

		$name = strip_tags(util::getData("name"));
		$cost = util::getData("cost");
		$section = util::getData("section");

		$entry = new dkpLootTableEntry();
		$entry->name = $name;
		$entry->cost = $cost;
		$entry->section = $section;
		$entry->loottable = $this->loottable->id;
		$entry->saveNew();

		$entry->loadFromDatabase($entry->id);

		//$temp = array(true,"Item Added!", $entry->id);
		$this->setAjaxResult(true,"Item Added!", $entry);
	}
	/*=================================================
	AJAX - DELETE AN ITEM FROM A LOOT TABLE
	=================================================*/
	function ajaxDeleteItem(){
		if(!$this->HasPermission("LootTable"))
			return $this->setAjaxResult(false, "You do not have permission to edit loot tables.");
		if($this->loottable->guild != $this->guild->id)
			return $this->setAjaxResult(false, "You can not edit someone elses loot table");

		$id = util::getData("id");
		$entry = new dkpLootTableEntry();
		$entry->loadFromDatabase($id);
		if($entry->loottable != $this->loottable->id)
			return $this->setAjaxResult(false, "An invalid item id was passed. Item NOT deleted.");
		$entry->delete();

		$this->setAjaxResult(true, "Item Deleted!");
	}
	/*=================================================
	AJAX - EDIT AN ITEM WITHIN A LOOT TABLE
	=================================================*/
	function ajaxEditItem(){
		if(!$this->HasPermission("LootTable"))
			return $this->setAjaxResult(false, "You do not have permission to edit loot tables.");
		if($this->loottable->guild != $this->guild->id)
			return $this->setAjaxResult(false, "You can not edit someone elses loot table");

		$id = util::getData("id");
		$name = strip_tags(util::getData("name"));
		$cost = util::getData("cost");

		$entry = new dkpLootTableEntry();
		$entry->loadFromDatabase($id);
		if($entry->loottable != $this->loottable->id)
			return $this->setAjaxResult(false, "An invalid item id was passed. Item NOT edited.");

		$entry->name = $name;
		$entry->cost = $cost;
		$entry->save();
		$entry->loadFromDatabase($entry->id);

		$this->setAjaxResult(true, "Item Updated!", $entry);
	}
}
?>