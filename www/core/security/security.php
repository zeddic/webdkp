<?php
/*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
CLASS DESCRIPTION    -    class security
=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-+
*
*/
include_once("securePage.php");
class security
{
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $user;
	var $securePages = array();		//contains a list of all secure pages in
									//site. Populated in constuctor
	var $adminGroup = "Admin";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct(&$user){
		$this->user = $user;
	}
	/*===========================================================
	hasAccess()
	Returns true if the current user loaded into the security instance
	has access to the given property.
	The property can either be:
	1 - The name of an access right. (ex: "Manage News")
	2 - A page with its attached query stries. (ex: "ManageNews.php?view=masterList")
	============================================================*/
	function userHasAccess($property){

		//echo("checking user access for $property <br />");
		//print_r($this->user);
		//die();
		//admins always get access
		if($this->user->usergroup->name == $this->adminGroup) {
			return true;
		}

		//check to see if a file name was passed
		$pageTest = explode(".", $property);
		if($pageTest[sizeof($pageTest)-1] == "php")
		{
			return $this->hasPageAccess($property);
		}

		//other wise it is a regular permission check
		return security::hasPermissionAccess($property);
	}

	/*===========================================================
	hasPermissionAccess()
	Returns true if the current user loaded into the security instance
	has access to the given permission. ex: "Can Manage News"
	============================================================*/
	function userHasPermissionAccess($permissionName){
		//return true;
		//admins always get access
		if($this->user->usergroup->name == $this->adminGroup) {
			return true;
		}
		return $this->user->usergroup->hasPermission($permissionName);
	}

	/*===========================================================
	hasPermissionIdAccess()
	Returns true if the current user loaded into the security instance
	has access to the given permission with the given id
	============================================================*/
	function userHasPermissionIdAccess($permissionId){
		//return true;
		//admins always get access
		if($this->user->usergroup->name == $this->adminGroup) {
			return true;
		}
		return $this->user->usergroup->hasPermissionId($permissionId);
	}

	/*===========================================================
	hasPageAccess()
	Returns true if the current user loaded into the security instance
	has access to the given page /w query strings. Example:
	"manageNews.php?view=management"
	============================================================*/
	function userHasPageAccess(&$page){
		// admins always get access
		if($this->user->usergroup->name == $this->adminGroup) {
			return true;
		}

		// TODO(scott): Unused code was removed from here. Add
		// support for a required permission to be defined on the
		// page.
		return true;
	}
	/*===========================================================
	getParametersFromQueryString()
	Given a query string it will break it up into an associative array
	that is returned, with the variable names as indices.
	For example: view=page&id=5
	Is returned as:
	["view"] = "page"
	["id"] = "5"
	============================================================*/
	function getParametersFromQueryString($queryString){
		$parameters = array();
		//split the query string into individual variable entries
		//example: view=page & id=5
		$queryStringEntries = explode("&",$queryString);
		if($queryStringEntries!=""){
			foreach($queryStringEntries as $queryStringEntry) {
				//split the querystring entry. Example:
				//view=page ==> view & page
				$temp = explode("=",$queryStringValue);
				if(is_array($temp) && sizeof($temp)==2){
					$parameterName = $temp[0];
					$parameterValue = $temp[1];
					$parameters[$parameterName]=$parameterValue;
				}
			}
		}
		return $parameters;
	}

	/*===========================================================
	Calls hasAccess() for the current user. This method may be called
	statically.
	============================================================*/
	static function hasAccess($property){
		if(framework::getConfigValue("DisableSecurity"))
			return true;

		$security = security::getUserSecurity();
		return $security->userHasAccess($property);
	}

	/*===========================================================
	Calls hasPermissionAccess() for the current user. This method may be called
	statically.
	============================================================*/
	static function hasPermission($permissionName){
		if(framework::getConfigValue("DisableSecurity"))
			return true;

		$security = security::getUserSecurity();
		return $security->userHasPermissionAccess($permissionName);
	}

	/*===========================================================
	Calls hasPermissionAccess() for the current user. This method may be called
	statically.
	============================================================*/
	static function hasPermissionAccess($permissionName){
		if(framework::getConfigValue("DisableSecurity"))
			return true;

		$security = security::getUserSecurity();
		return $security->userHasPermissionAccess($permissionName);
	}

	/*===========================================================
	Calls hasPermissionIdAccess() for the current user. This method may be called
	statically.
	============================================================*/
	static function hasPermissionIdAccess($permissionId){
		if(framework::getConfigValue("DisableSecurity"))
			return true;

		$security = security::getUserSecurity();
		return $security->userHasPermissionIdAccess($permissionId);
	}

	/*===========================================================
	Calls hasPageAccess() for the current user. This method may be called
	statically. Accepts either the filename of the page, or a page
	class instance.
	============================================================*/
	static function hasPageAccess(&$page){
		if(framework::getConfigValue("DisableSecurity"))
			return true;

		$security = security::getUserSecurity();
		return $security->userHasPageAccess($page);
	}

	/*===========================================================
	Creates a sequrity instance for the current site user. This instance
	is then cached in the global property "security" so that it
	can be easily retrieved on future calls to static security
	methods.
	============================================================*/
	static function getUserSecurity(){
		global $siteUser;
		global $security;
		//echo("loading security instance <br />");
		if(empty($security)) {
			//echo("havn't seen before <br />");
			//information not in global cache, reload it and store it in cache
			//(this will save subsequent calls to this method from having to
			// making a lot of sql queries)
			$security = new security($siteUser);
			$GLOBALS["security"] = $security;
			//echo("created instance $security <br />");
		}
		return $security;
	}

	/*===========================================================
	Grants a permission to a given user group
	============================================================*/
	static function addPermissionToGroup($permissionName, $usergroupName){
		//create the permission
		$permission = new permission();
		$permission->loadFromDatabaseByName($permissionName);
		if(empty($permission->id)){
			return;
		}
		//load the user group
		$usergroup = new userGroup();
		$usergroup->loadFromDatabaseByName($usergroupName);
		//if the usergroup already has the permission, don't bother adding it
		if($usergroup->hasPermission($permissionName))
			return;
		//grant the permission
		$usergroup->permissions[]=$permission;
		$usergroup->save();
	}

	/*===========================================================
	Ensures that a given permission exists. If it doesn't, it is created
	============================================================*/
	static function ensurePermission($permissionName, $category){
		global $sql;
		$permission = new permission();
		$permission->loadFromDatabaseByName($permissionName);
		if($permission->id != ""){
			return;
		}

		$permission->name = $permissionName;
		$permission->category = $category;
		$permission->saveNew();

		security::addPermissionToGroup($permissionName,"Admin");
	}

	/*===========================================================
	Returns an array of all user groups in the system.
	============================================================*/
	static function getUserGroups(){
		global $sql;
		$userGroups = array();
		$table = userGroup::tablename;
		$result = $sql->Query("SELECT * FROM $table ORDER BY name DESC");
		$userGroups = array();
		while($row = mysqli_fetch_array($result)){
			$userGroup = new userGroup();
			$userGroup->loadFromRow($row);
			$userGroups[]=$userGroup;
		}
		return $userGroups;
	}

	/*===========================================================
	Returns an array of all available permissions
	============================================================*/
	static function getPermissions(){
		global $sql;
		$table = permission::tablename;
		$result = $sql->Query("SELECT * FROM $table ORDER BY name ASC");
		$permissions = array();
		while($row = mysqli_fetch_array($result)){
			$permission = new permission();
			$permission->loadFromRow($row);
			$permissions[]=$permission;
		}
		return $permissions;
	}
}
?>