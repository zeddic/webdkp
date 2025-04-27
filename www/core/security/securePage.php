<?php

/*===========================================================

CLASS DESCRIPTION

=============================================================

Class Description should be placed here.

*/



class securePage {

	/*===========================================================

	MEMBER VARIABLES

	============================================================*/

	var $id;

	var $pageid;

	var $allowedGroups = array();
	
	var $tablename;
	const tablename = "security_securepages";

	/*===========================================================

	DEFAULT CONSTRUCTOR

	============================================================*/

	function __construct()

	{

		$this->tablename = securePage::tablename;

	}

	/*===========================================================

	loadFromDatabase($id)

	Loads the information for this class from the backend database

	using the passed string.

	============================================================*/

	function loadFromDatabase($id)

	{

		global $sql;

		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'");

		$this->loadFromRow($row);

	}

	/*===========================================================

	loadFromDatabase($id)

	Loads the permission information for a specific page.

	============================================================*/

	function loadFromDatabaseByPageid($pageid){

		global $sql;

		$pageid = sql::Escape($pageid);

		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE pageid='$pageid'");

		$this->loadFromRow($row);

	}



	/*===========================================================

	loadFromRow($row)

	Loads the information for this class from the passed database row.

	============================================================*/

	function loadFromRow($row)

	{

		$this->id=$row["id"] ?? null;

		$this->pageid = $row["pageid"] ?? null;

		$this->allowedGroups = explode(",",$row["allowedGroups"] ?? "");

	}

	/*===========================================================

	save()

	Saves data into the backend database using the supplied id

	============================================================*/

	function save()

	{

		global $sql;

		$toImplode = array();

		foreach($this->allowedGroups as $group) {

			if(is_a($group,"userGroup"))

				$toImplode[] = $group->id;

			else

				$toImplode[] = $group;

		}

		$allowedGroups = sql::escape(implode(",",$toImplode));

		$sql->Query("UPDATE $this->tablename SET

					pageid = '$this->pageid',

					allowedGroups = '$allowedGroups'

					WHERE id='$this->id'");

	}

	/*===========================================================

	saveNew()

	Saves data into the backend database as a new row entry. After

	calling this method $id will be filled with a new value

	matching the new row for the data

	============================================================*/

	function saveNew()

	{

		global $sql;

		$toImplode = array();

		foreach($this->allowedGroups as $group) {

			if(is_a($group,"userGroup"))

				$toImplode[] = $group->id;

			else

				$toImplode[] = $group;

		}

		$allowedGroups = sql::escape(implode(",",$toImplode));

		$sql->Query("INSERT INTO $this->tablename SET

					pageid = '$this->pageid',

					allowedGroups = '$allowedGroups'

					");

		$this->id=$sql->getLastId();

	}

	/*===========================================================

	delete()

	Deletes the row with the current id of this instance from the

	database

	============================================================*/

	function delete()

	{

		global $sql;

		$sql->Query("DELETE FROM $this->tablename WHERE id = '$this->id'");

	}

	/*===========================================================

	exists()

	STATIC METHOD

	Returns true if the a secure page information exists for the given

	page. Accepts either a page id or a page class instance.

	============================================================*/

	function exists($page)

	{

		if (is_a($page,"page")) {

			$page = $page->id;

		}

		$page = Sql::Escape($page);

		global $sql;

		$tablename = securePage::tablename;

		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE pageid='$page'");

		return ($exists != "");

	}

	/*===========================================================

	deleteSecurePage()

	STATIC METHOD

	Deletes a secure page entry for the given page.

	============================================================*/

	function deleteSecurePage($page){

		if (is_a($page,"page")) {

			$page = $page->id;

		}

		global $sql;

		$page = Sql::Escape($page);

		$tablename = securePage::tablename;

		$exists = $sql->QueryItem("DELETE FROM $tablename WHERE pageid='$page'");



		return ($exists != "");

	}



	/*===========================================================

	groupHasAccess()

	Returns true if a given group has access to the given secure page.

	Accepts either a groupid or a usergroup instance.

	============================================================*/

	function groupHasAccess(& $usergroup){

		if($this->id == "") {

			return true;

		}



		$groupid = 0;

		if(is_a($userGroup,"userGroup")){

			$groupid = $usergroup->id;

		}

		else {

			$groupid = $usergroup;

		}



		return (in_array($groupid, $this->allowedGroups));

	}

	/*===========================================================

	getSecurePagesList()

	Returns an array of all secure pages in the system.

	============================================================*/

	function getSecurePagesList(){

		global $sql;

		$toReturn = array();

		$tablename = securePage::tablename;

		$result = $sql->Query("SELECT * FROM $tablename");

		while($row = mysqli_fetch_array($result)){

			$securePage = new securePage();

			$securePage->loadFromRow($row);

			$toReturn[]=$securePage;

		}

		return $toReturn;

	}

	/*===========================================================

	valid()

	Returns true if this secure page information is valid

	============================================================*/

	function valid(){

		return ($this->id!="");

	}



	/*===========================================================

	pageMatches()

	Returns true if the passed page & parameters matches the

	the secure page represented by this instance. Parameters are:

	$page - the name of the page. For example: manageNews.php

	$parameters - an associative array of parameters that are part of

				  the query string associated with this page. Example:

				  ["view"] = main

				  ["id"] = 5

	============================================================*/

	/*function pageMatches($page, $parameters){

		//convert this pages name into a regular expression (since * is allowed in

		//names, such as manage*)

		$pattern = str_replace("*", ".*", $this->name);	//set up the ereg pattern

		if(ereg($pattern, $page)){

			//page name matches.

			//now to check to see if it matches any

			//view requirements for this secure page.

			if(size($this->views)==0){

				//no views to check, so they match be default

				return true;

			}

			//iterate through each of the views for this page. If

			//one of them matches the view parameter in the parameters array,

			//we have a match. If it doesn't match any of the secure page views,

			//then it does not match this page, even though it is part of the same

			//page

			foreach($this->views as $view){

				$pattern = str_replace("*", ".*", $view);

				if(ereg($pattern, $parameters["view"])){

					return true;

				}

			}

		}

		//no match

		return false;

	} */



	/*===========================================================

	setupTable()

	Checks to see if the classes database table exists. If it does not

	the table is created.

	============================================================*/

	static function setupTable()

	{

		if(!sql::tableExists(securePage::tablename)) {

			$tablename = securePage::tablename;

			global $sql;

			$sql->Query("CREATE TABLE `$tablename` (

						`id` INT NOT NULL AUTO_INCREMENT ,

						`pageid` INT NOT NULL,

						`allowedGroups` VARCHAR (256),

					PRIMARY KEY ( `id` )

					) TYPE = innodb;");

		}

	}

}

securePage::setupTable();

?>