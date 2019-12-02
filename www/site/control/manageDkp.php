<?php
/*=================================================
This page is intended to catch users using old
webdkp.com links and direct them to the new, correct
url which is based off of guild name / server
=================================================*/
include_once("lib/dkp/dkpUtil.php");
class pageManageDkp extends page {

	function area2()
	{
		global $siteRoot;
		util::forward($siteRoot);
	}
	function eventUploadLogFromClient(){
		//include("client/upload.php");
	}
}
?>