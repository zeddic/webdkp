<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpUploader.php");

/*=================================================
The news page displays news to the user.
=================================================*/
class pageUpload extends pageAdminMain {

	var $log;

	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;

		$this->title = "Upload Log File";
		$this->border = 1;


		$this->set("tabs",$this->GetTabs("admin"));
		$this->set("log", $this->log);
		return $this->fetch("upload.tmpl.php");
	}

	function eventUploadLog(){

		if(!$this->canUploadLog ) {
			return $this->setEventResult(false,"You do not have permission to upload a log file.");
		}
		$uploader = new dkpUploader();
		$this->log = $uploader->UploadLog($this->guild->id);
	}

}
?>