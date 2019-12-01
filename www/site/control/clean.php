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
	  while($row = mysql_fetch_array($result)) {
	    $name = $row["username"];
	    $userid = $row["userid"];
	    $lastlogin = $row["lastlogin"];
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

		if( $user->id == "" )
			return false;

		$user->delete();

		//see if there are any others users with this guild
		if( $guildid == "" ) {
			return false;
		}

		echo("Checking if there are any other admins... ");
		
    global $sql;
		$id = $sql->QueryItem("SELECT id FROM security_users WHERE guild='$guildid'");
		if( $id == "" ) {
		  echo("Nope! Deleting! <br />\r\n");
			dkpUtil::DeleteGuild($guildid);
			return true;
		} else {
		  echo("Yes! <br />\r\n");
		}
		return false;
	}
}

/*
 * 


SELECT dkp_pointhistory.id, dkp_pointhistory.guild, dkp_guilds.id
FROM dkp_pointhistory
LEFT JOIN dkp_guilds
  ON dkp_guilds.id = dkp_pointhistory.guild
WHERE dkp_guilds.id is null


SELECT dkp_awards.id, dkp_awards.date, dkp_guilds.id
FROM dkp_awards
LEFT JOIN dkp_guilds
  ON dkp_guilds.id = dkp_awards.guild
WHERE dkp_guilds.id is null

SELECT dkp_pointhistory.id, dkp_awards.id, dkp_awards.date
FROM dkp_pointhistory
  LEFT JOIN dkp_awards
    ON dkp_awards.id = dkp_pointhistory.award
WHERE dkp_awards.id is null
AND dkp_pointhistory.award != 0

SELECT dkp_pointhistory.id, dkp_awards.id, dkp_awards.date
FROM dkp_pointhistory
  LEFT JOIN dkp_awards
    ON dkp_awards.id = dkp_pointhistory.award
WHERE dkp_awards.id is null
AND dkp_pointhistory.award != 0
ORDER BY dkp_pointhistory.id DESC


DELETE dkp_pointhistory 
FROM dkp_pointhistory
  LEFT JOIN dkp_awards
    ON dkp_awards.id = dkp_pointhistory.award
WHERE dkp_awards.id is null


10026392

2110216	NULL	NULL
2110219	NULL	NULL
2110223	NULL	NULL
2110226	NULL	NULL
2110228	NULL	NULL
2110231	NULL	NULL
2110234	NULL	NULL
2110236	NULL	NULL
2110238	NULL	NULL
2110242	NULL	NULL
2110245	NULL	NULL
2110248	NULL	NULL
2110251	NULL	NULL
2110254	NULL	NULL
2110256	NULL	NULL
2450612	NULL	NULL
2450613	NULL	NULL
2110239	NULL	NULL
2110255	NULL	NULL
2110215	NULL	NULL
2110257	NULL	NULL
2110235	NULL	NULL
2110237	NULL	NULL
2110244	NU


101501725	NULL	NULL
101501724	NULL	NULL
101501723	NULL	NULL
101501722	NULL	NULL
101501721	NULL	NULL
101501720	NULL	NULL
101501719	NULL	NULL
101501718	NULL	NULL
101501717	NULL	NULL
101501716	NULL	NULL
101501715	NULL	NULL
101118437	NULL	NULL
101118436	NULL	NULL
101118435	NULL	NULL
101118434	NULL	NULL
101118433	NULL	NULL
101118432	NULL	NULL
101118431	NULL	NULL
101118430	NULL	NULL
101118429	NULL	NULL
101118428	NULL	NULL
101089635	NULL	NULL
101089634	NULL	NULL
101089633	NULL	NULL
101089632	NULL	NULL
101089631	NULL	NULL
101089630	NULL	NULL
101089629	NULL	NULL
101089628	NULL	NULL
101089627	NULL	NULL

 */

?>