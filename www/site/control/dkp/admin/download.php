<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpUploader.php");

/*=================================================
The news page displays news to the user.
=================================================*/
class pageDownload extends pageAdminMain {

	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;

		$this->title = "Download Log File";
		$this->border = 1;


		$this->set("tabs",$this->GetTabs("admin"));
		return $this->fetch("download.tmpl.php");
	}
}
?>