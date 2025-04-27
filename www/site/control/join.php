<?php
include_once("lib/dkp/dkpUtil.php");
include_once("lib/dkp/dkpUserPermissions.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageJoin extends page {

	var $layout = "Columns2Right";
	var $pagetitle = "Create Account";
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		$this->title = "Join WebDKP";
		$this->border = 1;

		$servers = dkpUtil::GetServerList();

		$this->set("servers",$servers);

		$this->set("username",util::getData("username"));
		$this->set("guild",util::getData("guild"));
		$this->set("servername",util::getData("server"));
		$this->set("faction",util::getData("faction"));
		$this->set("email",util::getData("email"));
		return $this->fetch("join/join.tmpl.php");
	}

	/*=================================================
	Warning for the top right
	=================================================*/
	function area1(){
		$this->border = 0;

		return $this->fetch("join/joinnotes.tmpl.php");
	}

	/*=================================================
	Main event - user submitting the register
	form. This must attempt to regeiser the user
	and create their guild. It will display any
	errors to the user. If successful, users
	are automattically forwarded to the welcome page.
	=================================================*/
	function eventRegister(){
		$username = strip_tags(util::getData("username"));
		$password = util::getData("password");
		$password2 = util::getData("password2");
		$guildname = strip_tags(util::getData("guild"));
		$server = util::getData("server");
		$faction = util::getData("faction");
		$email = strip_tags(util::getData("email"));

		// Verify a username isn't blank
		if ($username == ""){
			return $this->setEventResult(false, "You did not enter a username, please enter a username.");
		}
		
		// Verify the password isn't blank
		if ($password == "" || $password2 ==""){
			return $this->setEventResult(false, "A password is required to register, please enter a password.");
		}
	
		// Verify that an email address works that has a proper domain 
		if($this->checkEmail($email) != true) { 
			return $this->setEventResult(false, "An email address is required to register an account. This address will be used if you need to reset your password.");
		}

	
		if(strpos($guildname, "'")!== false || strpos($guildname,"\"") !== false ||
			strpos($guildname,"/" ) !== false || strpos($guildname,"&") !== false ) {
			return $this->setEventResult(false, "You can not have special characters such as ', \", or / in your guild name.");
		}

		

		//step 1 - check if the guild is already taken
		if(dkpGuild::Exists($guildname, $server) && dkpUtil::IsGuildClaimed($guildname, $server)) {
			$this->setEventResult(user::REGISTER_GUILD_TAKEN, "The requsted guild has already been registered. Maybe you already have an account?");
			return;
		}

		//step 2 - register the account
		$user = new user();
		$result = $user->register($username, $password, $password2);
		if($result != user::REGISTER_OK)
		{
			$this->setEventResult($result, user::getRegisterErrorString($result));
			return;
		}

		//step 3 - create the guild (or set a preknown guilds claim flag to true)
		if (dkpGuild::Exists($guildname, $server)) {
			$guild = new dkpGuild();
			$guild->loadFromDatabaseByName($guildname, $server);
			$guild->claimed = 1;
			$guild->save();
		}
		else {
			$guild = new dkpGuild();
			$guild->name = $guildname;
			$guild->server = $server;
			$guild->faction = $faction;
			$guild->claimed = 1;
			$guild->saveNew();
		}

		//load default settings for this guild
		$settings = new dkpSettings($guild->id);
		$settings->LoadDefaultSettings();

		//make the new user an admin for the guild
		$permissions = new dkpUserPermissions($user->id);
		$permissions->user = $user->id;
		$permissions->isAdmin = 1;
		if($permissions->id == "")
			$permissions->saveNew();
		else
			$permissions->save();

		//if they are currently logged in as a different user, log them out
		global $siteUser;
		$siteUser->logout();

		//update the user account with the new guild id
		$user->guild = $guild->id;
		$user->email = $email;
		$user->save();
		$user->setCookie();

		global $SiteRoot;
		util::forward($SiteRoot."Welcome");
		die();
	}
	
	// This function verfies there's a valid email address
	function checkEmail($email) {

		// checks proper syntax
		if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/" , $email)) {
			// gets domain name
			// list($username,$domain)=split('@',$email);
			// checks for if MX records in the DNS
			//if(!checkdnsrr($domain, 'MX')) {
				//return false;
			//}
			// attempts a socket connection to mail server
			//if(!fsockopen($domain,25,$errno,$errstr,30)) {
				//return false;
			//}
			return true;
		}
		return false;
	} 
}
?>