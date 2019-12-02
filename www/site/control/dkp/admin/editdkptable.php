<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpUpdater.php");
/*=================================================
Page allows users to edit the name and id of
individual dkp tables.
=================================================*/
class pageEditDkpTable extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		$this->title = "Edit DKP Table";
		$this->border = 1;

		$tableid = util::getData("id");
		$table = $this->updater->GetTable($tableid);

		//call the template
		$this->set("table",$table);
		return $this->fetch("settings/editdkptable.tmpl.php");
	}

	/*=================================================
	EVENT - Updates a tables name
	=================================================*/
	function eventUpdateTable(){

		//get data
		$tableid = util::getData("id");
		$name = util::getData("name");
		$newtableid = util::getData("newtableid");
		if(!is_numeric($newtableid))
			return $this->setEventResult(false, "The TableId must be a number.");

		//attempt to perform the update
		$result = $this->updater->UpdateTableName($tableid, $name);
		if($result != dkpUpdater::UPDATE_OK)
			return $this->setEventResult(false, dkpUpdater::GetErrorString($result));

		//attempt to perform newtableid update (if needed )
		if($newtableid != $tableid)
			$result = $this->updater->UpdateTableId($tableid, $newtableid);
		if($result != dkpUpdater::UPDATE_OK)
			return $this->setEventResult(false, dkpUpdater::GetErrorString($result));

		//make sure there is always a tableid of 1
		if($this->updater->EnsureMainTableExists()) { //returns true when a main table needed to be created
			//see if we were the one that got pulled back to be 1
			if(!$this->updater->TableExists($newtableid)) {
				$_GET["id"] = 1;
				return $this->setEventResult(true,"Table Updated - But the Table ID had to be changed back to 1. There must always be a table
						with an ID of 1");
			}
		}

		$_GET["id"] = $newtableid;

		$this->setEventResult(true,"Table Updated");
	}

}
?>