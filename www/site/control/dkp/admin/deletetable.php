<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpUpdater.php");
/*=================================================
Page allows users to edit the name and id of
individual dkp tables.
=================================================*/
class pageDeleteTable extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		$this->title = "Delete Table";
		$this->border = 1;

		$tableid = util::getData("id");
		$table = $this->updater->GetTable($tableid);


		//call the template
		$this->set("table",$table);
		return $this->fetch("settings/confirmdelete.tmpl.php");
	}
}
?>