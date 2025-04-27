<?php
/*===========================================================
CLASS DESCRIPTION - dkpAccountUtil
=============================================================
The account util class is a static based class that provides
a set of update functions for both guilds and user accounts.

These update functions are primary related to ACCOUNT settings
and not DKP settings. Examples include:

- Updating Account Email
- Updating Account Username
- Updating Account Password
- Updating Guild Name
- Updating Guild Faction
- Updating Guild Server
- Creating Secondary Accounts
- Deleting Secondary Accounts
- Editing Permissions for Secondary Accounts

Note - Secondary Accounts are backup accounts that have shared
access to the same dkp table. This access may be complete admin access,
or may be restricted with specific permissions.
*/

include_once("dkpGuild.php");
include_once("dkpUtil.php");
include_once("dkpUserPermissions.php");

class dkpAccountUtil {
	/*===========================================================
	RETURN ERROR / UPDATE CODES
	============================================================*/
	//Update completed ok
	const UPDATE_OK = 1;
	//Could not process update - passed password empty
	const ERROR_NO_PASSWORD = 2;
	//Could not process update - passed password did not match db
	const ERROR_INVALID_PASSWORD = 3;
	//Could not process update - new password was empty
	const UPDATE_ERROR_NO_PASSWORD = 4;
	//Could not process udpate - new passwords did not match
	const UPDATE_ERROR_PASSWORD_MISMATCH = 5;
	//Could not process update - requested username empty
	const UPDATE_ERROR_NO_USERNAME = 6;
	//Could not process update - requested new username taken
	const UPDATE_ERROR_USERNAME_TAKEN = 7;
	//Could not update guild - passed guildid was invalid
	const UPDATE_ERROR_INVALID_GUILDID = 8;
	//Could not update guild - requested new guild name was already taken
	const UPDATE_ERROR_GUILDNAME_TAKEN = 9;
	//Could not perform update - the current signed in user doesn't have permissions
	const UPDATE_ERROR_NOACCESS = 10;
	//Another other, unknown error
	const UPDATE_ERROR_OTHER = 11;
	//Internal Error Code - the passed password was OK!
	const PASSWORD_OK = 99;

	/*===========================================================
	Updates the email address for the currently signed in user.
	Parameters:
	$password - the current password of the user. Used to verify
				that the current user has access to perform this
				update
	$email	  -	The desired new email
	============================================================*/
	static function UpdateEmail($password, $email) {
		global $siteUser;
		//make sure we have a valid password
		$result = dkpAccountUtil::IsPasswordOk($password);
		if($result != dkpAccountUtil::PASSWORD_OK)
			return $result;

		//check if there is anything to update. if not, just return
		if($siteUser->email == $email)
			return dkpAccountUtil::UPDATE_OK;

		$siteUser->email = $email;
		$siteUser->save();

		return dkpAccountUtil::UPDATE_OK;
	}

	/*===========================================================
	Updates the username for the currently signed in user.
	Parameters:
	$password - the current password of the user. Used to verify
				that the current user has access to perform this
				update
	$username -	The desired new username
	============================================================*/
	static function UpdateUsername($password, $newusername) {
		global $siteUser;

		//make sure we have a valid password
		$result = dkpAccountUtil::IsPasswordOk($password);
		if($result != dkpAccountUtil::PASSWORD_OK)
			return $result;

		//check if there is anything to update. if not, just return
		if($siteUser->username == $newusername)
			return dkpAccountUtil::UPDATE_OK;

		//make sure the new username is valid
		if(empty($newusername))
			return dkpAccountUtil::UPDATE_ERROR_NO_USERNAME;

		//make sure the username isn't already taken
		if(user::exists($newusername))
			return dkpAccountUtil::UPDATE_ERROR_USERNAME_TAKEN;

		//clear the users current cookie
		$siteUser->clearCookie();

		//updates the user
		$siteUser->username = $newusername;
		$siteUser->save();

		//sets a new cookie
		$siteUser->setCookie();

		return dkpAccountUtil::UPDATE_OK;
	}

	/*===========================================================
	Updates the password for the currently signed in user.
	Parameters:
	$password - the current password of the user. Used to verify
				that the current user has access to perform this
				update
	$password -	The desired new password
	$password2- The desired new password, retryped. It will be compared
			 	to password. If they do not match, an error will be
			 	returned.
	============================================================*/
	static function UpdatePassword($password, $newpassword, $newpassword2) {
		global $siteUser;

		//make sure we have a valid password
		$result = dkpAccountUtil::IsPasswordOk($password);
		if($result != dkpAccountUtil::PASSWORD_OK) {
			return $result;
		}

		//make sure they entered something for their new password
		if(empty($newpassword))
			return dkpAccountUtil::UPDATE_ERROR_NO_PASSWORD;

		//make sure the two passwords match
		if($newpassword != $newpassword2)
			return dkpAccountUtil::UPDATE_ERROR_PASSWORD_MISMATCH;


		//clear their current cookie before updating
		$siteUser->clearCookie();
		//set their new password
		$siteUser->setPassword($newpassword);
		$siteUser->save();
		//add their new cookie
		$siteUser->setCookie();

		//ok!
		return dkpAccountUtil::UPDATE_OK;
	}

	/*===========================================================
	Internal helper method. Checks if the passed password
	matches the password of the account in the database. (note
	that passwords are NOT stored in string form, but are instead
	hashed and compared that way)
	$password -	The password to check
	============================================================*/
	static function IsPasswordOk($password) {
		global $siteUser;

		//password empty
		if(empty($password)) {
			return dkpAccountUtil::ERROR_NO_PASSWORD;
		}

		//check if the password is valid
		$valid = $siteUser->passwordValid($password);

		if(!$valid) {
			return dkpAccountUtil::ERROR_INVALID_PASSWORD;
		}
		//password check was ok
		return dkpAccountUtil::PASSWORD_OK;
	}

	/*===========================================================
	Updates settings for a given guild. This will allow you to update
	a guilds: name, server, and faction.

	The updates here can get a little tricky, as well need to take into
	account the following cases:
	- The case where we rename a guild to the name of a guild that exists,
	  but is not yet claimed
	- The case were the guild changes servers. Here we need to not only
	  move the guild, but create a copy of all players on the new server
	  AND transisition their dkp history AND create a backup version of
	  of the guild on the original server with an unclaimed setting.

	Parameters:
	$guildid - The id of the guild to update
	$newname - The requested new name of the guild (can be same)
	$newserver - The requested new server of the guild (can be same)
	$newfaction - The requested new faction of the guild (can be same)
	============================================================*/
	static function UpdateGuild($guildid, $newname, $newserver, $newfaction) {
		global $sql;

		//make sure the user has permissions to do this
		if (!dkpUserPermissions::currentUserHasPermission("AccountEditGuild", $guildid)) {
			return dkpAccountUtil::UPDATE_ERROR_NOACCESS;
		}

		//attempt to load the guild
		$guild = new dkpGuild();
		$guild->loadFromDatabase($guildid);

		$guildid = sql::Escape($guild->id);
		$oldname = $guild->name;
		$oldserver = $guild->server;
		$oldfaction = $guild->faction;

		//make sure the guild we are trying to update exists
		if(empty($guild->id))
			return dkpAccountUtil::UPDATE_ERROR_INVALID_GUILDID;

		//make sure the requested new guild name / server combo is not yet taken
		$exists = dkpGuild::exists($newname, $newserver);
		$claimed = dkpGuild::isClaimed($newname, $newserver);
		$existingGuild = new dkpGuild();
		$existingGuild->loadFromDatabaseByName($newname, $newserver);
		$existingGuildIsUs = ($existingGuild->id == $guild->id);

		//if another guild already exists and is claimed , stop with an error
		if($exists && $claimed && !$existingGuildIsUs) {
			return dkpAccountUtil::UPDATE_ERROR_GUILDNAME_TAKEN;
		}

		//if another guild exists, but is not yet claimed, we can have this guild claim it
		if($exists && !$claimed && !$existingGuildIsUs) {
			//we have to be careful here ... there is a guild that is unclaimed.
			//we can't just replace it because other entries in the database may be
			//refering to it. So we'll have to find all those references and point
			//them to this guild (ie, we merge them)
			$toDelete = sql::Escape($existingGuild->id);
			$sql->Query("UPDATE dkp_users SET guild = '$guildid' WHERE guild='$toDelete'");
			$sql->Query("DELETE FROM dkp_guilds WHERE id='$toDelete'");
		}

		//we now update the guilds name / server / faction
		$guild->name = $newname;
		$guild->server = $newserver;
		$guild->faction = $newfaction;
		$guild->save();

		//If we moved servers, we need to take an extra step and update guild players
		//In our regular updates, we moved the guild over to a new server, but we
		//left all of the players behind. Now we need to move them
		if($newserver != $oldserver) {
			//we can't just move the current players over to the new server though
			//single players are shared between guilds, so the players we want to move
			//may already be used in other references. So what we'll do is leave
			//the originals behind, and make sure a new user exists on the new server
			//We then update all the dkp point entries to point to the new user

			//Get a list of all users in the guilds point tables
			$result = $sql->Query("SELECT *, dkp_users.id AS userid
								   FROM dkp_points, dkp_users
								   WHERE dkp_points.user = dkp_users.id
								   AND dkp_points.guild='$guildid'");
			//iterate through each, and make sure that a mirror player exists on the new server
			while($row = mysqli_fetch_array($result)){
				$oldUser = new dkpUser();
				$oldUser->loadFromRow($row);

				//get the mirror player (may be new, may be one that already exists)
				$newUser = dkpUtil::EnsurePlayerExists($oldUser->name, $oldUser->class, $guild->id, $guild->server, $guild->faction);
				$newUserId = sql::Escape($newUser->id);
				$oldUserId = sql::Escape($oldUser->id);

				//if their user id changed...
				if($oldUser->id != $newUser->id ) {
					//update the user data to point to their new id
					$sql->Query("UPDATE dkp_points SET user='$newUserId' WHERE user='$oldUserId' AND guild='$guildid'");
					$sql->Query("UPDATE dkp_pointhistory SET user='$newUserId' WHERE user='$oldUserId' AND guild='$guildid'");
				}
			}

			//if we moved the guild over to a new server, we also need to leave a dummy
			//guild in its place on the old server (for  the old player instances we left behind)
			$replacementGuild = new dkpGuild();
			$replacementGuild->name = $oldname;
			$replacementGuild->server = $oldserver;
			$replacementGuild->faction = $oldfaction;
			$replacementGuild->claimed = 0;
			$replacementGuild->saveNew();

			//any players that were left behind should have their guild id updated to
			//point to this new replacement guild. Otherwise they will be pointing
			//to the old guild which is now on a different server...
			$replacementId = sql::Escape($replacementGuild->id);
			$sql->Query("UPDATE dkp_users SET guild = '$replacementId' WHERE guild = '$guildid'");
		}

		return dkpAccountUtil::UPDATE_OK;
	}

	/*===========================================================
	Creates a secondary account for the current guild.
	Secondary accounts are other user accounts that can sign into
	webdkp and have access to managing tables and settings for the
	same guild. Secondary accounts can also have their
	permissions tailored to limit or restrict what settings
	they have access to

	Parameters:
	$guildid 	- The id of the guild to update
	$username 	- The name of the new account to create
	$password 	- The password for the account
	$password2 	- The new password for the account
	============================================================*/
	static function CreateSecondaryAccount($guildid, $username, $password, $password2, $email) {

		//make sure the current user has access to create secondary accounts
		if (!dkpUserPermissions::currentUserHasPermission("AccountSecondaryUsers", $guildid)) {
			return dkpAccoutUtil::UPDATE_ERROR_NOACCESS;
		}

		//make sure the parameters are ok
		if(empty($username))
			return dkpAccountUtil::UPDATE_ERROR_NO_USERNAME;
		if( user::exists($username) )
			return dkpAccountUtil::UPDATE_ERROR_USERNAME_TAKEN;
		if( $password != $password2 )
			return dkpAccountUtil::UPDATE_ERROR_PASSWORD_MISMATCH;
		if(empty($password))
			return dkpAccountUtil::UPDATE_ERROR_NO_PASSWORD;

		//create the new user
		$user = new user();
		$user->register($username, $password, $password2);
		$user->guild = $guildid;
		$user->email = $email;
		$user->save();

		//create a set of default permissions for them
		$newPermissions = new dkpUserPermissions();
		$newPermissions->user = $user->id;
		$newPermissions->isAdmin = 0;
		$newPermissions->loadDefaultPermissions();
		$newPermissions->saveNew();

		return dkpAccountUtil::UPDATE_OK;
	}

	/*===========================================================
	Deletes a secondary account for a for a guild

	Parameters:
	$guildid 	- The id of the guild to update
	$userid 	- The secondary account to kill
	============================================================*/
	static function DeleteSecondaryAccount($guildid, $userid) {
		//make sure we have permissions to perform this update
		if (!dkpUserPermissions::currentUserHasPermission("AccountSecondaryUsers",$guildid))
			return dkpAccountUtil::UPDATE_ERROR_NOACCESS;

		//load the requested user to delete
		$user = new user();
		$user->loadFromDatabase($userid);

		//make sure this user belongs to this guild (ie, this isn't someone
		//trying to delete arbitrary accounts)
		if($user->guild != $guildid)
			return dkpAccountUtil::UPDATE_ERROR_NOACCESS;

		//delete the user
		$user->delete();

		//delete their permissions
		$permissions = new dkpUserPermissions();
		$permissions->loadUserPermissions($user->id);
		$permissions->delete();

		//all done!
		return dkpAccountUtil::UPDATE_OK;
	}

	/*===========================================================
	Sets the password for an officer account

	Parameters:
	$guildid 	- The id of the guild to update
	$userid 	- The secondary account to kill
	$password1  - The new desired password for the officer account
	$password2  - The new password passed a second time.
	============================================================*/
	static function SetOfficerAccountPassword($guildid, $userid, $password1, $password2){
		//make sure we have permissions to perform this update
		if (!dkpUserPermissions::currentUserHasPermission("AccountSecondaryUsers",$guildid))
			return dkpAccountUtil::UPDATE_ERROR_NOACCESS;

		//load the requested user to update
		$user = new user();
		$user->loadFromDatabase($userid);

		//make sure this user belongs to this guild (ie, this isn't someone
		//trying to update someone else)
		if($user->guild != $guildid)
			return dkpAccountUtil::UPDATE_ERROR_NOACCESS;

		//make sure they entered something for their new password
		if(empty($password1))
			return dkpAccountUtil::UPDATE_ERROR_NO_PASSWORD;

		//make sure the two passwords match
		if( $password1 != $password2)
			return dkpAccountUtil::UPDATE_ERROR_PASSWORD_MISMATCH;

		//after all those checks... now we can update the password
		$user->setPassword($password1);
		$user->save();

		return dkpAccountUtil::UPDATE_OK;
	}

	/*===========================================================
	Updates permissions for a selected secondary account. This can set:
	- The types of permissions they have
	- The tables they have access to
	- Whether or not they have unrestircted table access
	- Whether the secondary account is an admin account

	Parameters:
	$guildid 	- The id of the guild to update
	$userid 	- The secondary account to update
	$selectedPermissions - An array of permission names that this user should have
						   (see dkpPermission setupTable() for list)
	$tables 	- The ids of tables that this user should have access to
	$tableAccess- Whether this user should have limited or all table access.
				  Pass "AllTableAccess" to specifiy all table access. Specify
				  any other string to limit access to tables specified in $tables
	$accountType- Whether the account is an admin account or a custom account
				  "adminAccount" specifies account with unlimited permissions for
				  this guild. Any other string reverts to checking permissions
				  supplied by the above permissions.
	============================================================*/
	static function UpdateSecondaryAccount($guildid, $userid, $selectedPermissions, $tables, $tableAccess, $accountType) {

		//make sure the current user has access to perform the update
		if (!dkpUserPermissions::currentUserHasPermission("AccountSecondaryUsers",$guildid))
			return dkpAccountUtil::UPDATE_ERROR_NOACCESS;


		//load the user we are requested to updating
		$user = new user();
		$user->loadFromDatabase($userid);

		//make sure the user we are trying to update belongs to this guild
		//(ie, this isn't someone trying to update a different user)
		if($user->guild != $guildid)
			return dkpAccountUtil::UPDATE_ERROR_NOACCESS;


		//load up their current permissions
		$permissions = new dkpUserPermissions();
		$permissions->loadUserPermissions($user->id);

		//now to update the permissions

		//admin account:
		if($accountType=="adminAccount") {
			$permissions->isAdmin = 1;
		}
		//custom account: more work
		else {
			$permissions->isAdmin = 0;
			//clear their current permissions
			$permissions->permissions = array();
			//add the permissions that were checked
			if(is_array($selectedPermissions)){
				foreach($selectedPermissions as $permissionName){
					$permissions->addPermission($permissionName);
				}
			}
			//get what table access they were granted
			if($tableAccess=="allTables") {
				$permissions->addPermission("AllTableAccess");
			}
			else { //not granted all table access... see what tables they do have
				$permissions->tables = array();
				if($tables!=""){
					foreach($tables as $tableid){
						$permissions->addTable($tableid);
					}
				}
			}
		}

		//save our changes
		$permissions->save();

		//all done!
		return dkpAccountUtil::UPDATE_OK;
	}
	/*===========================================================
	Given a guild id, this will return all the accounts
	that are officer accounts to that guild

	Parameters:
	$guildid 	- the id of the guild to get officers for

	Returns:
	An array of user instances.
	============================================================*/
	static function GetOfficerAccounts($guildid){
		global $siteUser;
		global $sql;

		$guildid = sql::Escape($guildid);
		$result = $sql->Query("SELECT * FROM security_users WHERE guild='$guildid' ORDER BY username ASC");

		$accounts = array();
		while($row = mysqli_fetch_array($result)) {
			$user = new user();
			$user->loadFromRow($row);
			if($user->username != $siteUser->username)
				$accounts[] = $user;
		}
		return $accounts;
	}


	/*===========================================================
	Utility method - given an error code returned from one
	of the other functions in this class, this will generate
	a string representation.

	Parameters:
	$errorcode 	- The error code to convert to a string
	============================================================*/
	static function GetErrorString($errorcode){
		if($errorcode == dkpAccountUtil::UPDATE_OK)
			return "Update Completed!";
		else if($errorcode == dkpAccountUtil::ERROR_NO_PASSWORD)
			return "The current password that you entered was empty. You must enter your accounts current password to update these settings.";
		else if($errorcode == dkpAccountUtil::ERROR_INVALID_PASSWORD)
			return "The password you entered was not valid. You must enter your accounts current password to update these settings.";
		else if($errorcode == dkpAccountUtil::UPDATE_ERROR_NO_PASSWORD)
			return "Sorry, but your new password must not be blank - it's too easy for people to guess ;)";
		else if($errorcode == dkpAccountUtil::UPDATE_ERROR_PASSWORD_MISMATCH)
			return "The new passwords that you entered did not match.";
		else if($errorcode == dkpAccountUtil::UPDATE_ERROR_NO_USERNAME)
			return "Your new username cannot be empty :)";
		else if($errorcode == dkpAccountUtil::UPDATE_ERROR_USERNAME_TAKEN)
			return "The new username that you requested is already taken";
		else if($errorcode == dkpAccountUtil::UPDATE_ERROR_INVALID_GUILDID)
			return "The passed guild id was not valid. Guild not updated.";
		else if($errorcode == dkpAccountUtil::UPDATE_ERROR_GUILDNAME_TAKEN)
			return "The requested guild name is already taken on that server.";
		else if($errorcode == dkpAccountUtil::UPDATE_ERROR_NOACCESS)
			return "You do not have permissions to perform this action.";
		else if($errorcode == dkpAccountUtil::UPDATE_ERROR_OTHER)
			return "An uknown error occured. Update not performed.";
		else
			return "Unknown Error";
	}
}
?>