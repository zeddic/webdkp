<?php
include_once("lib/dkp/dkpLuaGenerator.php");
include_once("dkpmain.php");

/*=================================================
The news page displays news to the user.
=================================================*/
class pageLog extends pageDkpMain {

	/*=================================================
	Generate the log file then die
	=================================================*/
	function area2()
	{
		$generator = new dkpLuaGenerator($this->guild->id);
		$generator->generateLuaFile(true);
		die();
	}
}

?>