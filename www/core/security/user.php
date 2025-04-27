<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

include_once("userGroup.php");
include_once("passwordReset.php");
class user {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $username;					//username used for login
	var $firstname;
	var $lastname;
	var $email;
	var $registerdateDate;		//date that they registered
	var $registerdateTime;		//time that they registered
	var $password;				//user password (hashed)
	var $usergroup;				//the user group that the user belongs to. UserGroup instance
	var $guild;					//the guild that this account is tied to
	var $visitor = true;		//Is this a visitor and not a registered user?
	var $tablename;
	const tablename =	"security_users";
	/*===========================================================
	STATIC VARIABLES
	============================================================*/
	const LOGIN_OK = 0;
	const LOGIN_BADUSER = 1;
	const LOGIN_BADPASSWORD = 2;

	const REGISTER_OK = 0;
	const REGISTER_EMPTY_USERNAME = 1;
	const REGISTER_USERNAME_TAKEN = 2;
	const REGISTER_PASSWORD_MISMATCH = 3;
	const REGISTER_EMPTY_PASSWORD = 4;
	const REGISTER_GUILD_TAKEN = 4;

	const RESET_OK = 0;
	const RESET_NO_EMAIL = 1;
	const RESET_EMAIL_ERROR = 2;
	const RESET_BAD_KEY = 3;

	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = user::tablename;
	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabaseByUser($username)
	{
		global $sql;
		$username = sql::Escape($username);
		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE username='$username'");
		$this->loadFromRow($row);
		return $this->visitor;
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
		return $this->visitor;
	}
	/*===========================================================
	loadFromCookie($id)
	Trys to load this user instance from data available in a cookie.
	Will return false if the user instance could not be loaded,
	true otherwise.
	============================================================*/
	function loadFromCookie(){
		$cookiedata = $_COOKIE["userdata"] ?? "";//unserialize(html_entity_decode($_COOKIE["userdata"]));

		//cookie not present
		$cookiedata = explode("$|$",$cookiedata);
		if($cookiedata == "" || sizeof($cookiedata) < 2){
			$this->clearData();
			return false;
		}

		$username = $cookiedata[0];
		$password = $cookiedata[1];

		$this->loadFromDatabaseByUser($username);

		//(the cookie password is already hashed)
		if ($this->password != $password ){
			//bad password, don't load
			$this->clearData();
			return false;
		}
		//everything is ok
		return true;
	}
	/*===========================================================
	loadFromRow($row)
	Loads the information for this class from the passed database row.
	============================================================*/
	function loadFromRow($row)
	{
		$this->id=$row["id"] ?? null;
		$this->username = $row["username"] ?? null;
		$this->password = $row["password"] ?? null;
		$this->firstname = $row["firstname"] ?? null;
		$this->lastname = $row["lastname"] ?? null;
		$this->email = $row["email"] ?? null;
		$this->guild = $row["guild"] ?? null;
		$this->usergroup = new userGroup();
		$this->usergroup->loadFromDatabase($row["usergroup"] ?? null);
		if($row["registerdate"]!="")
		{
		  $this->registerdateDate = date("F j, Y", strtotime($row["registerdate"]));
		  $this->registerdateTime = date("g:i A", strtotime($row["registerdate"]));
		}
		if($this->id!="") {
			$this->visitor = false;
		}

		if($this->usergroup->id == ""){
			$this->usergroup->loadFromDatabaseByName("Visitor");
		}
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$user = sql::Escape($this->username);
		$firstname = sql::Escape($this->firstname);
		$lastname = sql::Escape($this->lastname);
		$email = sql::Escape($this->email);
		$password = sql::Escape($this->password);
		$guild = sql::Escape($this->guild);
		if(is_a($this->usergroup,"userGroup"))
			$usergroup = $this->usergroup->id;
		else
			$usergroup = $this->usergroup;
		$sql->Query("UPDATE $this->tablename SET
		          username = '$user',
		          firstname = '$firstname',
		          lastname = '$lastname',
		          email = '$email',
		          password = '$password',
		          usergroup = '$usergroup',
		          guild = '$guild'
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
		$user = sql::Escape($this->username);
		$firstname = sql::Escape($this->firstname);
		$lastname = sql::Escape($this->lastname);
		$email = sql::Escape($this->email);
		$password = sql::Escape($this->password);
		$guild = sql::Escape($this->guild);
		$guild = empty($guild) ? "NULL" : "'$guild'";
		
		if(is_a($this->usergroup,"userGroup")) {
			$usergroup = $this->usergroup->id;
		}
		else {
			$usergroup = $this->usergroup;
		}
		$sql->Query(" INSERT INTO $this->tablename SET
		          username = '$user',
		          firstname = '$firstname',
		          lastname = '$lastname',
		          email = '$email',
		          password = '$password',
		          usergroup = '$usergroup',
		          guild = $guild,
		          registerdate = NOW(),
			      lastlogin = NOW()
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
	clearData()
	Clears all member data variables
	============================================================*/
	function clearData(){
		unset($this->id);
		unset($this->username);
		unset($this->firstname);
		unset($this->lastname);
		unset($this->email);
		unset($this->registerdateDate);
		unset($this->registerdateTime);
		unset($this->password);
		unset($this->usergroup);
		unset($this->guild);
		$this->usergroup = new userGroup();
		$this->usergroup->loadFromDatabaseByName("Visitor");
		$this->visitor = true;
	}
	/*===========================================================
	setPassword()
	Sets a new password of the given user. This will first hash it
	with a given salt.
	============================================================*/
	function setPassword($plainTextPassword){
		$hashed = $this->generateHash($plainTextPassword);
		$this->password = $hashed;
	}
	/*===========================================================
	passwordValid()
	Returns true if the given password matchines the password for
	this account. This will work by first - hashing the
	plain text password supplied with the original salt, and comparing
	it to the currently stored hashed password.
	============================================================*/
	function passwordValid($plainTextPassword){
		//generate the new hash for the passed password. We can get the original
		//salt from the first part of the current password
		$passedHash = $this->generateHash($plainTextPassword,$this->password);
		return ($passedHash == $this->password);
	}

	/*===========================================================
	generateHash()
	Generates a hashed version of the given plain text password.
	This hashed version can then be saved in the database.
	The optional salt parameter can be used if you want to supply a
	salt instead of creating one from scratch. You would ussually
	use this if you are checking a user supplied password during login
	against a created user - ie, you already know the salt that was
	originally used, so you are comparing what they give with this
	salt to make sure it matches.
	============================================================*/
	function generateHash($plainTextPassword, $salt = null){
		$salt_length = 9;

		//We are generating a hash from scrach
		if ($salt === null) {
			//generate the hash
			$salt = substr(sha1(uniqid(rand(), true)), 0, $salt_length);
		}
		//we are supplied a salt already (we must be checking a user supplied password against one already created)
		else {
			$salt = substr($salt, 0, $salt_length);
		}
		//store the salt to the front of the hash. This means that if we
		//need to determine the original salt used in the future we can
		//just do a substring
		return $salt.sha1($salt.$plainTextPassword);
	}
	/*===========================================================
	setCookie()
	Sets a cookie for the current user instance into the users
	computer.
	============================================================*/
	function setCookie(){


		$data = array($this->username,$this->password);
		$data = implode("$|$",$data);
		setcookie("userdata", $data, time() + (3600 * 24 * 365), "/");

		//if(util::inSession("loggedOut"))
		//	util::clearFromSession("loggedOut");
	}
	/*===========================================================
	clearCookie()
	Clears cookie data for the current user
	============================================================*/
	function clearCookie(){
		$data = array($this->username ?? "",$this->password ?? "");
		$data = implode("$|$",$data);
		setcookie("userdata", $data, (60000), "/");
	}

	/*===========================================================
	login()
	Attempts to log in with the given username and text password,
	populating the current user instance with the user data if
	successful.
	Returns an int for a result, which could be one of $LOGIN_OK,
	$LOGIN_BADUSER, or $LOGIN_BADPASSWORD. If LOGIN_OK is returned,
	a user instance is valid and will contain data, otherwise the
	user instance is NOT valid.
	============================================================*/
	function login($username=null, $password=null){
		if($username == null){
			$username = $_POST["username"];
		}
		if($password == null) {
			$password = $_POST["password"];
		}

		//if user doesn't exist, return empty user
		if(!user::exists($username)) {
			$this->clearData();
			return user::LOGIN_BADUSER;
		}


		//load the user that they are claiming to be
		$this->loadFromDatabaseByUser($username);

		if(!$this->passwordValid($password)){
			$this->clearData();
			return user::LOGIN_BADPASSWORD;
		}

		//everything is ok
		$this->setCookie();

		return user::LOGIN_OK;
	}
	/*===========================================================
	register()
	Attempts to register a new user account with the given username
	and password. (other member variables for the user to register
	can be set by accessing them before calling this). Returns
	an int of $REGISTER_OK, $REGISTER_NAMETAKE, etc.
	If the register goes ok, this instance is populated with the
	users data. If the register failes, this calls will not have
	valid data any more.
	============================================================*/
	function register($username, $password1, $password2){

		//valid username?
		$username = trim($username);
		if($username == "")
			return user::REGISTER_EMPTY_USERNAME;

		//name taken?
		if(user::exists($username))
			return user::REGISTER_USERNAME_TAKEN;

		//passwords match?
		$password1 = trim($password1);
		$password2 = trim($password2);
		if($password1 != $password2) {
		   	return user::REGISTER_PASSWORD_MISMATCH;
		}

		//valid password?
		if($password1 == "")
			return user::REGISTER_EMPTY_PASSWORD;

		//ok, we can register
		$this->setPassword($password1);
		$this->username = $username;
		$this->usergroup = userGroup::getUserGroupIdByName("User");
		$this->saveNew();
		$this->visitor = false;

		return user::REGISTER_OK;
	}
	/*===========================================================
	logout()
	Logs out the current user. Can be called either statically
	or on a user isntance. In either case it logs out the CURRENT
	user, not the user identified by the instance. If called on
	an instance, the user instance will be cleared of all data
	afterwards.
	============================================================*/
	function logout(){
		$this->clearCookie();
		$this->clearData();
	}

	/*===========================================================
	userExists()
	STATIC METHOD
	Returns true if the given username exists in the database.
	============================================================*/
	static function exists($username){
		$username = sql::Escape($username);
		global $sql;
		$tablename = user::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE username='$username'");
		return ($exists != "");
	}

	/*===========================================================
	requestReset()
	Requests a passowrd reset for the current user. This will
	generate a email address with a special key for the user
	When the user clicks on the email link, they are taken to a page
	where they can enter a new password.
	============================================================*/
	function requestReset(){

		if($this->id == "" || $this->email == "")
			return user::RESET_NO_EMAIL;

		//destroy any previous password resets
		if(passwordReset::exists($this->id)) {
			$reset = new passwordReset();
			$reset->loadFromDatabaseByUser($this->id);
			$reset->delete();
		}
		//create a new hash
		$key = sha1($this->username.rand(1,9999));

		//save the hash in the database
		$reset = new passwordReset();
		$reset->user = $this->id;
		$reset->key = $key;
		$reset->saveNew();

		//now send an email out to the user with the key and
		$url = "http://www.webdkp.com/Reset?uid=$this->id&key=$key";
		echo($url);

		$message = "<html><body><b>WebDKP Password Reset</b> ";
		$message.= "<br /><br />";
		$message.= "A request was received to reset your password on <a href='http://www.webdkp.com'>WebDKP.com</a>";
		$message.= "<br /><br />";
		$message.= "To reset your password, please click on the following link:";
		$message.= "<br /><br />";
		$message.= "<a href='$url'>Reset Password Now!</a>";
		$message.="</body></html>";

		$to = $this->email;
		$subject = "WebDKP Password Reset";
		$headers = "From: WebDKP@webdkp.com\n";
		$headers.= "MIME-Version: 1.0\n";
		$headers.= "Content-type: text/html; charset=UTF-8";

		//send the email
		$ok = mail($this->email, $subject ,$message, $headers);

		if(!$ok)
			return user::RESET_EMAIL_ERROR;

		return user::RESET_OK;
	}
	/*===========================================================
	resetPassword()
	Attempts to reset the given password. This is called after a user
	recieves a reset password, clicks on the link, and goes to a special
	page to enter a new password. The key that the link contained is used
	to verify the reset request.
	============================================================*/
	function resetPassword($key, $newpassword){
		if(!passwordReset::exists($this->id))
			return user::RESET_BAD_KEY;

		$reset = new passwordReset();
		$reset->loadFromDatabaseByUser($this->id);

		if($reset->key != $key )
			return user::RESET_BAD_KEY;
		//delete the reset key - its only good for one use
		$reset->delete();


		$this->setPassword($newpassword);
		$this->save();

		return user::RESET_OK;
	}

	/*===========================================================
	Translates an error code returned from a reset event
	into a user friendly error string
	============================================================*/
	function getResetErrorString($error){
		if($error == user::RESET_OK) {
			return "Password Reset";
		}
		else if($error == user::RESET_NO_EMAIL){
			return "The Requested user does not have a registered email address. Please request a password reset via the forums.";
		}
		else if($error == user::RESET_NO_EMAIL){
			return "An error occured while trying to mail you a reset password. Please request a password reset via the forums.";
		}
		else if($error == user::RESET_BAD_KEY){
			return "An invalid password reset key was used. Unable to update your password";
		}
		else {
			return "An unknown error occured";
		}
	}

	/*===========================================================
	Converts one of the errors from one of the user methods into a string.
	============================================================*/
	static function getRegisterErrorString($error){
		if($error == user::REGISTER_OK) {
			return "Register Completed";
		}
		else if($error == user::REGISTER_EMPTY_USERNAME){
			return "Empty Username";
		}
		else if($error == user::REGISTER_USERNAME_TAKEN){
			return "Username Already Taken";
		}
		else if($error == user::REGISTER_PASSWORD_MISMATCH){
			return "Mismatching Passwords";
		}
		else if($error == user::REGISTER_EMPTY_PASSWORD){
			return "Empty Password";
		}
	}
	/*===========================================================
	Checks to see if the user table exists. If it does not, a new
	instance is created for it in the database.
	============================================================*/
	static function setupTable(){
		if(!sql::tableExists(user::tablename)) {
			$tablename = user::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`username` VARCHAR( 256 ) NOT NULL ,
						`password` VARCHAR( 49 ) NOT NULL ,
						`firstname` VARCHAR( 128 ) NOT NULL ,
						`lastname` VARCHAR( 128 ) NOT NULL ,
						`email` VARCHAR( 128 ) NOT NULL ,
						`usergroup` INT NOT NULL ,
						`guild` INT NOT NULL ,
						`registerdate` DATETIME NOT NULL ,
						`lastlogin` DATETIME NOT NULL ,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}
}
user::setupTable();
?>