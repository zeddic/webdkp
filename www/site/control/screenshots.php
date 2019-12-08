<?php
/*=================================================
The news page displays news to the user.
=================================================*/
class pageScreenshots extends page {

	var $layout = "Columns2Right";
	var $pagetitle = "Screenshots";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		$this->border = 1;
		$this->title = "Screenshots";
		return $this->fetch("screenshots.tmpl.php");
	}

	function area1(){
		$this->border = 0;
		return $this->fetch("screenshotsside.tmpl.php");
	}
}
?>