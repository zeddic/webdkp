<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("site/control/dkp/admin/adminmain.php");
include_once("adminmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageIndex extends pageAdminMain {


	var $layout = "Columns1";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;

		$this->title = $this->guild->name." Control Center";
		$this->border = 1;

		$this->set("tabs",$this->GetTabs("admin"));
		return $this->fetch("main.tmpl.php");
	}
}
?>