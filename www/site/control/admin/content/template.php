<?php
/*include_once("core/general/file.php");
include_once("core/general/image.php");
include_once("site/control/news/post.php");
include_once("site/control/news/tag.php");
include_once("util/pager.php");*/
/*===========================================================
Controller
Allows a user to administer a mailing list. They can:
- view the mailing list
- edit entries
- delete entries
- upload files
============================================================*/
class pageContentTemplate extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = true;
	var $template = "admin/template";
	var $system = 1;
	var $layout = "Columns2";

	/*===========================================================
	TOP LEFT - Displays links to the different security sections.
	============================================================*/
	function area1_1(){
		$this->title = "Content Navigation";
		$this->border = 5;
		return $this->fetch("nav.tmpl.php");
	}
}
?>