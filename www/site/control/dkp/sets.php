<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpUpdater.php");
include_once("lib/wow/armory.php");
include_once("dkpmain.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageSets extends pageDkpMain {


	var $layout = "Columns1";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{

		global $sql;

		$this->title = "Loot";
		$this->border = 1;


		$this->addCSSHeader($this->binDirectory."css/lightbox.css");

		$this->addCSSHeader($this->binDirectory."css/dkp.css");
		$this->addJavascriptHeader($this->binDirectory."js/dkp.js");
		$this->addJavascriptHeader($this->binDirectory."js/lightbox.js");


		$this->set("tabs",$this->GetTabs("sets"));
		$this->set("table", $table);
		return $this->fetch("sets.tmpl.php");
	}
}
?>