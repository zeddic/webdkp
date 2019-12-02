<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpRemoteStyle.php");
include_once("lib/dkp/dkpCustomRemoteStyle.php");

/*=================================================
Gives user instruction on setting up remote dkp
on their own website
=================================================*/
class pageEditRemoteStyle extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;
		$this->title = "Edit Custom Style";
		$this->border = 1;

		dkpCustomRemoteStyle::ensureExists($this->guild->id);

		$custom = new dkpCustomRemoteStyle();
		$custom->loadFromGuild($this->guild->id);
		//$styles = dkpRemoteStyle::getStyles();

		//$selectedid = $this->settings->GetRemoteStyle();
		//$style = new dkpRemoteStyle();
		//$style->loadFromDatabase($selectedid);

		$this->set("custom", $custom);

		framework::useTemplateIndents(false);
		return $this->fetch("editremotestyle.tmpl.php");
	}


	function eventSaveChanges(){
		$content = util::getData("content");
		$custom = new dkpCustomRemoteStyle();
		$custom->loadFromGuild($this->guild->id);
		$custom->content = $content;
		$custom->save();

		$this->setEventResult(true, "Change Saved!");

	}
}
?>