<?php
include_once("lib/dkp/dkpUtil.php");

class pageClean extends page {

	var $layout = "Columns1";

	function area2(){ die(); }
	
	function eventClean() {

	  global $sql;

	  /*$remaining = $sql->QueryItem("
	SELECT count(*)
	FROM security_users
        WHERE
          lastlogin < NOW() - INTERVAL 18 MONTH
        ORDER BY lastlogin ASC");
	  
	  $stillvalid = $sql->QueryItem("
	  		SELECT count(*)
	  		FROM security_users
        WHERE
          lastlogin >= NOW() - INTERVAL 18 MONTH
        ORDER BY lastlogin ASC");*/
        
        
        
        $remaining = $sql->QueryItem("
        SELECT count(*)
        FROM security_users
LEFT JOIN dkp_points
  ON security_users.guild = dkp_points.guild
WHERE lastlogin < NOW() - INTERVAL 12 MONTH
AND registerdate < NOW() - INTERVAL 12 MONTH
AND dkp_points.id is null");

$stillvalid = $sql->QueryItem("SELECT count(*) FROM security_users") - $remaining;
        

        
	  
	  echo("There are <b>$remaining</b> stale users and <b>$stillvalid</b> still active users. <br />\r\n");

	  die();
	  
	  /*$result = $sql->Query("
	  		SELECT username, security_users.id as userid, lastlogin
	  		FROM security_users
        WHERE
          lastlogin < NOW() - INTERVAL 18 MONTH
        ORDER BY
          lastlogin ASC
        LIMIT 200"); */
        
$result = $sql->Query("SELECT security_users.id as userid, security_users.guild, username, registerdate, lastlogin
FROM security_users
LEFT JOIN dkp_points
  ON security_users.guild = dkp_points.guild
WHERE lastlogin < NOW() - INTERVAL 12 MONTH
AND registerdate < NOW() - INTERVAL 12 MONTH
AND dkp_points.id is null
ORDER BY registerdate ASC, lastlogin ASC");

	  $usercount = 0;
	  $guildcount = 0;
	  while($row = mysqli_fetch_array($result)) {
	    $name = $row["username"] ?? null;
	    $userid = $row["userid"] ?? null;
	    $lastlogin = $row["lastlogin"] ?? null;
	    echo("Deleting $name with $userid who last loged in on $lastlogin <br />\r\n");
	    
	    $deletedGuild = self::deleteUser($userid);
	    $usercount++;
	    if ($deletedGuild) {
	      $guildcount++;
	    }
	  }
	  
	  echo("Deleted $usercount users and $guildcount guilds.\r\n");
	  die();
	}
	
  function deleteUser($userid) {
		$user = new user();
		$user->loadFromDatabase($userid);
		$guildid = $user->guild;

		if(empty($user->id))
			return false;

		$user->delete();

		//see if there are any others users with this guild
		if(empty($guildid)) {
			return false;
		}

		echo("Checking if there are any other admins... ");
		
    global $sql;
		$id = $sql->QueryItem("SELECT id FROM security_users WHERE guild='$guildid'");
		if(empty($id)) {
		  echo("Nope! Deleting! <br />\r\n");
			dkpUtil::DeleteGuild($guildid);
			return true;
		} else {
		  echo("Yes! <br />\r\n");
		}
		return false;
	}
}


?>