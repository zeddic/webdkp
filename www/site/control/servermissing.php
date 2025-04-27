<?php
include_once("lib/dkp/dkpServer.php");
include_once("lib/dkp/dkpUserPermissions.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageServerMissing extends page {

	var $layout = "Columns1";
	var $pagetitle = "New Server Request";
	
	/*=================================================
	Gives the user a form where they can list the server
	that is missing.
	=================================================*/
	function area2()
	{
		$this->title = "Request New Server";
		$this->border = 1;

		return $this->fetch("join/servermissing.tmpl.php");
	}

	/*=================================================
	event handler for the missing server form.
	Detects what server is missing and sends an email
	to the admin, prompting them to add it
	=================================================*/
	function eventSubmitServer(){

		$server = util::getData("server");
		$server = trim($server);

		if(stripos($server,"brasil"))
			return $this->setEventResult(false, "Wow brasil isn't a real server name! Stop requesting it!");

		//perform sanity checks
		if($server=="") {
			return $this->setEventResult(false,"You must enter a valid server name :)");
		}
		if(dkpServer::exists($server)) {
			return $this->setEventResult(false,"The server that you requested is already in the system. You should be able to find it in the server list on the join screen.");
		}

		//construct an email to send to the admin
		//global $siteRoot;
		$url = "http://www.webdkp.com/ServerMissing?event=addServer&server=$server";


		$message = "<html><body><b>WebDKP New Server Request</b> ";
		$message.= "<br /><br />";
		$message.= "A user has requested to add the server '$server' to WebDKP ";
		$message.= "<br /><br />";
		$message.= "To add the server, please click on the following link:";
		$message.= "<br /><br />";
		$message.= "<a href='$url'>Add Server Now!</a>";
		$message.="</body></html>";

		$subject = "WebDKP Server Request";
		$headers = "From: WebDKP@webdkp.com\n";
		$headers.= "MIME-Version: 1.0\n";
		$headers.= "Content-type: text/html; charset=UTF-8";

		//send the email
		$ok = mail("scott@zeddic.com", $subject ,$message, $headers);


		//show a message to the user
		if(!$ok)
			return $this->setEventResult(false, "An error was encountered while trying to contact the WebDKP Admin.");

		$this->setEventResult(true,"A request has been sent. In the mean time, you can create an account on one of the other servers and switch to the
									new server once it becomes available.");
	}

	/*=================================================
	Event handler for an admin responding to an email.
	This will add the requested server name to the list.
	=================================================*/
	function eventAddServer(){
		$server = util::getData("server");

		if(!security::hasAccess("AddNewServers"))
			return $this->setEventResult(false, "You do not have access to add new servers. Are you logged in?");

		if(dkpServer::exists($server))
			return $this->setEventResult(false, "The requested server already exists.");

		$new = new dkpServer();
		$new->name = $server;
		$new->saveNew();

		$this->setEventResult(true, "New Server Added");
	}
}
?>