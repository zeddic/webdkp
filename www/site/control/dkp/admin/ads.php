<?php
include_once("adminmain.php");
/*=================================================
Gives user instruction on setting up remote dkp
on their own website
=================================================*/
class pageAds extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;
		$this->title = "Support WebDKP and Disable Advertisements!";
		$this->border = 1;

		return $this->fetch("ads.tmpl.php");
	}
}
?>