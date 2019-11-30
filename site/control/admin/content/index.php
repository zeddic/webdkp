<?php
/*===========================================================
Controller
Allows a user to administer a mailing list. They can:
- view the mailing list
- edit entries
- delete entries
- upload files
============================================================*/
class pageIndex extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = true;
	var $template = "admin/content/template";
	var $system = 1;
	var $layout = "Columns2";

	/*===========================================================
	Center page. Default to managing news
	============================================================*/
	function area2(){
		$this->title = "Manage Site Content";
		$this->border = 1;
		return $this->fetch("index.tmpl.php");
	}

	/*===========================================================
	VIEW
	Breadcrumbs for the top of the page
	============================================================*/
	function area3(){
		global $SiteRoot;
		if($this->breadcrumbs == "") {
			$breadcrumbs = array();
			$breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
			$breadcrumbs[] = array("Content",$SiteRoot."admin/content");
			$breadcrumbs[] = array("News");
		}
		else
			$breadcrumbs = $this->breadcrumbs;
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}
}
?>