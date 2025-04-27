<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpRemoteStyle.php");
include_once("lib/dkp/dkpCustomRemoteStyle.php");

/*=================================================
Gives user instruction on setting up remote dkp
on their own website
=================================================*/
class pageRemoteStyle extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;
		$this->title = "Remote Table Style";
		$this->border = 1;

		$styles = dkpRemoteStyle::getStyles();

		$selectedid = $this->settings->GetRemoteStyle();
		$style = new dkpRemoteStyle();
		$style->loadFromDatabase($selectedid);

		$this->set("styles",$styles);
		$this->set("selected", $style);
		$this->set("selectedid", $selectedid);

		return $this->fetch("remotestyle.tmpl.php");
	}

	function eventSelectStyle(){
		$styleid = util::getData("id");
		if(empty($styleid))
			return $this->setEventResult(false, "Invalid Style ID Passed");

		$style = new dkpRemoteStyle();
		$style->loadFromDatabase($styleid);
		if(empty($style->id))
			return $this->setEventResult(false, "Invalid Style ID Passed");

		if($style->file == "custom") {
			dkpCustomRemoteStyle::ensureExists($this->guild->id);
		}

		$this->settings->SetRemoteStyle($styleid);

		$this->setEventResult(true, "Style Changed");
	}

	function eventUpdateCustom(){
		$content = util::getData("content");

	}
}
?>