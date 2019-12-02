<?php
include_once("adminmain.php");
/*=================================================
Gives user instruction on setting up remote dkp
on their own website
=================================================*/
class pageRemote extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;
		$this->title = "Remote WebDKP";
		$this->border = 1;

		framework::useTemplateIndents(false);

		return $this->fetch("remote.tmpl.php");
	}
}
?>