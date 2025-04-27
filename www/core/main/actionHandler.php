<?php
/*===========================================================
ActionHandler is a small utility class that is intended
to be extended by small ajax pages. When an instance is created
it will look at the given value in get/post. It will then attempt
to execute a class method corresponding to the given value.

Example use in an extending class:

$temp = new myHandler("action");
class myHandler extends actionHandler(){
	function actionGetDate(){
		echo("return date here");
	}
	function actionGetTime(){
		echo("Return time here");
	}
}

If the above page were called with $_GET["action"] == "GetDate",
actionGetDate() would automattically be called.
============================================================*/
class actionHandler {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $actionVar;		//the name of the action variable in get/post
	var $action;		//the requested action

	/*===========================================================
	DEFAULT CONSTRUCTOR
	Accepts the name of the get/post value to examine.
	It will then attempt to invoke a method of the name "action".Value
	if it exists. These methods are intended to be implemented in
	extending classes.
	============================================================*/
	function __construct($var = "action"){
		$this->actionVar = $var;
		$action = util::getData($var);
		$method = "action".$action;
		$this->action = $action;
		if(method_exists($this,$method)) {
			$this->$method();
		}
	}
}


?>