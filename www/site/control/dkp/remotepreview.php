<?php
include_once("lib/dkp/dkpPointsTable.php");
include_once("lib/dkp/dkpRemoteStyle.php");
include_once("lib/dkp/dkpCustomRemoteStyle.php");
include_once("dkpmain.php");
/*=================================================
This page generates the neccessary javascript
and css files that allow the Remote DKP / DKP Syndication
to function. Clients will request different types of
javascript files at run time - these javascript files
are actually dynamically created via php to hold payloads
of table data.

In addition, this page can also return the css style
selected by the current guild.
=================================================*/
class pageRemotePreview extends pageDkpMain {

	/*=================================================
	Default - return the starter javascript file. This file is loaded
	on the client page, generates a bare bones table,
	then starts requesting more data from webdkp
	=================================================*/
	function area2()
	{
		$styleid = util::getData("styleid");
		framework::useTemplateIndents(false);
		$this->set("styleid", $styleid);
		echo($this->fetch("/remote/preview.tmpl.php"));
		die();
	}
}

?>