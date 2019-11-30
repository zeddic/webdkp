<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/
include_once("core/part/part.php");
include_once("core/part/partDefinition.php");
class partLibrary
{
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $partDefinitions = array();
	const directory = "site/parts";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function partLibrary()
	{
		//$this->partDefinitions = $this->getPartDefinitions();
	}

	/*===========================================================
	loadpartDefinitions
	Loads up an array of all part definitions currenlty known
	by the system.
	============================================================*/
	function getPartDefinitions(){
		global $sql;
		$toReturn = array();
		$tablename = partDefinition::tablename;
		$result = $sql->Query("SELECT * FROM $tablename ORDER BY name ASC");
		while($row = mysql_fetch_array($result)){
			$definition = & new partDefinition();
			$definition->loadFromRow($row);
			$toReturn[] = $definition;
		}
		return $toReturn;
	}

	/*===========================================================
	STATIC METHOD
	Given a part instance id, this method will return a class
	instance of this part. The returned class will be of the type
	defined by that part in the part library.
	For example, an instance id of 5 might be passed. Checking up
	this entry in the part instances table shows us that it is
	an instance of a news part. A new news part instance will
	be created and returned.
	============================================================*/
	function getPartInstance($partid){
		global $sql;
		if($partid == "")
			return null;

		//load up the part as a generic part first (since we don't know what it really is)
		$genericpart = & new part();
		$genericpart->loadFromDatabase($partid);


		//use the generic parts part definition to instantiate the actual class
		$realpart = $genericpart->definition->getInstance();

		if($realpart == null)
			return null;

		$realpart->loadFromDatabase($partid);
		return $realpart;
	}

	/*===========================================================
	Scans for any parts that need to be installed. This will
	scan all of the parts directory and the part / system
	directory.
	STATIC
	============================================================*/
	function scanForNewParts(){
		$_GLOBALS["scanErrors"] = array();

		$discoveredparts = partLibrary::scanDirectory("site/parts/");

		partLibrary::removeDeletedParts($discoveredparts);
		/*$discoveredparts = array();
		$temp = $this->scanDirectory("parts/");
		$discoveredparts = array_merge($discoveredparts, $temp );
		$temp = $this->scanDirectory("parts/system/");
		$discoveredparts = array_merge($discoveredparts, $temp );
		$this->removeDeletedParts($discoveredparts);*/
	}

	/*===========================================================
	Scans the given directory for any new parts. If any are found
	they are imported / installed
	STATIC
	============================================================*/
	function scanDirectory($directoryName){

		$knownParts = partLibrary::getPartDefinitions();

		global $scanErrors;

		if(!($directoryName))
			return;

		$directory = opendir($directoryName);
		$discoveredparts = array();
		while($filename = readdir($directory)) {
			//if its not a part directory, skip to the next one
			if(!is_dir($directoryName.$filename) || $filename == "." || $filename==".." || $filename=="system" || $filename==".svn")
				continue;

			$discoveredparts[] = $filename;

			//we found a part directory, before doing anything, check to see
			//if we already know about it. If we do, we can skip it.
			if(partLibrary::partKnown($filename,$knownParts))
				continue;

			//make sure the info file exists
			$infoFileName = $directoryName.$filename."/info.php";
			if(!file_exists($infoFileName)) {
				$scanErrors[]=new partScanError($filename,partScanError::ERR_PART_FILE_NOT_FOUND,$filename);
				continue;
			}

			//unset values that previous info file may have set set
			unset($permissions, $permissionAssignments, $options,
				  $name, $description, $createdBy, $system);

			//load info file
			$ok = include($infoFileName);
			if(!$ok) {
				$scanErrors[]=new partScanError($filename,partScanError::ERR_PART_PARSE_ERROR,$filename);
				continue;
			}

			//install the new part by creating a new part definition.
			//Many of the values, such as $filename and $description
			//were defined for us in the info.php file

			//create a new part definition
			$definition = & new partDefinition();
			$definition->systemName = $filename;
			$definition->createdBy = $createdBy;
			$definition->name = $name;
			$definition->description = $description;
			$definition->system = $system;
			$definition->directory = $directoryName.$filename."/";
			$definition->saveNew();

			//setup the requested permissions. Will return a list of names
			//of permissions that were setup by the part
			$addedPermissions = partLibrary::setupPartPermissions($permissions);
			//setup usergroup assignments to each of the permissions
			partLibrary::setupPartPermissionAssignments($permissionAssignments, $addedPermissions);
			//setup the default options that should be applied to each part
			partLibrary::setupPartOptions($definition, $options);
		}
		closedir($directory);

		return $discoveredparts;
	}


	/*===========================================================
	Sets up the permissions for a part on its first install.
	Passed an array of arrays, where each array entry is composed of:
	entry[0] = the name of the permission
	entry[1] = the category that the permission falls under

	Returns an array of names of the permissions that were successfully
	added.
	============================================================*/
	function setupPartPermissions($permissions)
	{
		//create the new permissions
		$addedPermissionNames = array();
		if (is_array($permissions)) {
			foreach($permissions as $permissionEntry){
				if(sizeof($permissionEntry) >= 2) {
					$permissionName = $permissionEntry[0];
					$permissionCategory = $permissionEntry[1];
					$added = partLibrary::addPermission($permissionName,$permissionCategory);
					if($added)
						$addedPermissionNames[] = $permissionName;
				}
			}
		}
	}

	/*===========================================================
	Sets up the permissions assignments for a parts first install.
	Parameters:
	$permissionAssignments - An array of arrays where each array enter
	is composed of:
	entry[0] = the name of the permission
	entry[1] = the name of the group to grant the permission to

	$addedPermissions - An array of names of permissions that the part
	added itself. This is checked against before allowing the part to
	assign permissions: it cannot assign permissions that it did
	not create.
	============================================================*/
	function setupPartPermissionAssignments($permissionAssignments, $addedPermissions)
	{
		//add the new permission assignements
		if(is_array($permissionAssignments)){
			foreach($permissionAssignments as $assignmentEntry){
				if(sizeof($assignmentEntry) >= 2) {
					$permissionName = $assignmentEntry[0];
					$groupName = $assignmentEntry[1];
					 partLibrary::addPermissionToGroup($permissionName,$groupName, $addedPermissions);
				}
			}
		}
		return $addedPermissionNames;
	}

	/*===========================================================
	Sets up the available / default options for a part.
	Accepts:
	$definition - the part definition that option information is being
				  setup for
	$options - an array of partOption instances of options that
			   should be available to set for this part
	============================================================*/
	function setupPartOptions($definition, $options)
	{
		//setup the options
		$partOptions =array();
		if(is_array($options)){
			foreach($options as $optionEntry){
				if (is_a($optionEntry,"partOption")) {
					$partOptions[] = $optionEntry;
				}
				else if(sizeof($optionEntry) >= 4) {
					$name = $optionEntry[0];
					$type = $optionEntry[1];
					$defaultValue = $optionEntry[2];
					$choices = $optionEntry[3];
					$option = & new partOption($name, $type, $defaultValue, $choices);
					$partOptions[] = $option;
				}
			}
		}
		$definition->addOptions($partOptions);
	}

	/*===========================================================
	addPermission()
	Adds a new permission that is required to exist for this
	part to function. This will check to see if this permission
	exists. If it does not, it will be added
	Secton parameter is an optional 'parent' permission, a permission
	that this permission will belong under
	============================================================*/
	function addPermission($permissionName, $category){
		//if it exists, we don't need to do anything
		if(permission::exists($permissionName))
			return false;
		//create the permission
		$permission = & new permission();
		$permission->name = $permissionName;
		$permission->category = $category;
		$permission->saveNew();
		return true;
	}
	/*===========================================================
	addPermissionToGroup()
	Grants permission to a given user group.
	============================================================*/
	function addPermissionToGroup($permissionName, $usergroup, $addedPermissions = null){
		//security check - a part can't grant a permission that
		//it didn't add itself
		if( $addedPermissions != null && !in_array($permissionName,$addedPermissions)){
			return;
		}
		security::addPermissionToGroup($permissionName, $usergroup);
	}

	/*===========================================================
	reloadpart
	Reloads a given part (ie, runs its setup again).
	This will first check to see if a part definition with a given
	id exists.
	- create a new part class instance of its file
	- run its setup() method again
	- delete all existing options
	- add options again
	- find all current instances
	- iterate through all current instances, set new defaults
	  for options if now present. preserve any options that are
	  already set

	============================================================*/
	function reloadPart($partDefinitionId){
		$definition = & new partDefinition();
		$definition->loadFromDatabase($partDefinitionId);
		if($definition->id == "") {
			return false;
		}
		//get an instance of the part from the definition
		$part = $definition->getInstance();

		//delete any old options that are present
		partOption::deleteDefinitionOptions($definition->id);

		//reinstall permissions, permission assignments, and
		//options (so we can pickup new ones)
		$infoFileName = $definition->directory."info.php";
		if(!file_exists($infoFileName))
			return false;
		$ok = include($infoFileName);
		if(!$ok)
			return false;

		//values are not set from info.php

		//setup the requested permissions. Will return a list of names
		//of permissions that were setup by the part
		$addedPermissions = partLibrary::setupPartPermissions($permissions);
		//setup usergroup assignments to each of the permissions
		partLibrary::setupPartPermissionAssignments($permissionAssignments, $addedPermissions);
		//setup the default options that should be applied to each part
		partLibrary::setupPartOptions($definition, $options);

		//if there were options present in the new definition, iterate through
		//all the current part instances and make sure they have any default
		//options present
		if(is_array($definition->options)){
			//get the current instances
			global $sql;
			$tablename = part::tablename;
			$result = $sql->Query("SELECT * FROM $tablename WHERE definition = '$definition->id'");
			while($row = mysql_fetch_array($result)) {
				$instance = & new part();
				$instance->loadFromRow($row);
				//for each instance, iterate through all the options and make sure
				//that they all have them present. In a reload, some new options may be present
				//- these new options will need to be added
				foreach($definition->options as $availableOption){
					if (!$instance->optionSet($availableOption->name)) {
						//a new option that wasn't present - add a default option
					    $instance->setOption($availableOption->name, $availableOption->default);
					}
				}
			}
		}
		return true;
	}


	/*===========================================================
	partKnown
	Helper method. Returns true if we already know about the given
	part with the given system name.
	============================================================*/
	function partKnown($systemName, & $knownParts){
		if($knownParts != ""){
			foreach($knownParts as $definition){
				if($definition->systemName == $systemName){
					return true;
				}
			}
		}
		return false;
	}

	/*===========================================================
	Removes any parts from the database that no longer exist.
	Must be passed an array of part system (directory) names
	of those parts that still do exist.
	============================================================*/
	function removeDeletedParts($discoveredPartNames){
		global $sql;
		if(count($discoveredPartNames) == 0 ) {
			return;
		}
		//iterate through all the discovered parts names and prepare
		//a where clause
		for($i = 0 ; $i < count($discoveredPartNames) ; $i++ ){
			$temp = $discoveredPartNames[$i];
			$temp = "systemName != '$temp'";
			$discoveredPartNames[$i] = $temp;
		}
		$clause = implode(" AND ",$discoveredPartNames);
		$table = partDefinition::tablename;

		//delete all parts that are not in the list of discovered themes
		$sql->Query("DELETE FROM $table WHERE $clause");
	}

	/*===========================================================
	setupTable
	============================================================*/
	function setupTable(){
		//noting to really do here - the table should have already been setup
		//by partDefinition
	}

	/*===========================================================
	Returns an array of partScanError instances that holds information
	on any parts that had trouble being loaded. Must be called after
	a partLibrary::scanForNewparts() call.
	============================================================*/
	function getScanErrors(){
		global $scanErrors;
		return $scanErrors;
	}
}

/*===========================================================
Helper data structor. Contains the data for a single error
that was encountered while scanning for new parts
============================================================*/
class partScanError {
	var $partname;
	var $error;
	var $partfile;

	const ERR_UNKNOWN = 0;
	const ERR_PART_FILE_NOT_FOUND = 1;
	const ERR_PART_CLASS_NOT_FOUND = 2;
	const ERR_PART_PARSE_ERROR = 3;
	function partScanError($partname, $error = 0, $partfile = "uknown"){
		$this->partname = $partname;
		$this->error = $error;
		$this->partfile = $partfile;
	}
}

partLibrary::setupTable();
?>