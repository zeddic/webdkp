<?php
/*=================================================
The news page displays news to the user.
=================================================*/
class page404 extends page {
	var $layout = "Columns1";

	/*=================================================
	Main content for the news module goes here.
	=================================================*/
	function area2()
	{
		$this->border = 1;
		$this->title = "Error 404 - Not Found";

		$this->set("path", dispatcher::getUrl());

		return $this->fetch("404.tmpl.php");
	}
}

?>