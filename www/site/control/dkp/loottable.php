<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("dkpmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageLootTable extends pageDkpMain {
	var $layout = "Columns1";
	var $pageurl = "LootTable";
	var $loottable;
	var $loottables;
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		$this->LoadLootTable();
		global $sql;

		$this->pagetitle .= " - Loot Table";

		$this->title = $this->guild->name." Loot Table";
		$this->border = 1;

		$this->set("tabs",$this->GetTabs("loottable"));
		$this->set("loottable", $this->loottable);
		$this->set("lootselect", $this->GetLootTableSelect());
		return $this->fetch("loottable.tmpl.php");
	}
	/*=================================================
	Loads the currently selected loot table and populates
	it into the $this->loottable variable
	It also adds a list of all available loot tables
	to $this->loottables
	=================================================*/
	function LoadLootTable(){
		//get loot tables
		$this->loottables = dkpUtil::GetLootTables($this->guild->id);

		//find the selected loot table
		//If none is selected, default select the first one in the list
		$id = util::getData("l");
		if(empty($id)) {
			if(sizeof($this->loottables)>0)
				$id = $this->loottables[0]->id;
		}

		//load the details for only the selected loot table
		$this->loottable = new dkpLootTable();
		$this->loottable->loadFromDatabase($id);
		$this->loottable->loadTableData();
	}

	/*=================================================
	Generates a drop down box to select different loot
	tables to view
	=================================================*/
	function GetLootTableSelect(){
		$data = "<select name=\"tableid\" onchange=\"document.location='".$this->baseurl.$this->pageurl."?l='+options[selectedIndex].value\">\r\n";
		foreach($this->loottables as $table){
			$data .= "<option value=\"".$table->id."\" ";
			if($this->loottable->id == $table->id)
				$data.=" selected ";
			$data.= ">".$table->name."</option>\r\n";
		}
		$data.="</select>";
		return $data;
	}
}
?>