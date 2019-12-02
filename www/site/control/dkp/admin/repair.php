<?php
include_once("adminmain.php");
/*=================================================
Gives user instruction on setting up remote dkp
on their own website
=================================================*/
class pageRepair extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;
		$this->title = "Repair Tasks";
		$this->border = 1;

		return $this->fetch("repair.tmpl.php");
	}

	/*=================================================
	Event - recalculates DKP totals for all players
	in the table. This will look at players history
	and recalculate both their lifetime DKP and
	their current dkp so it matches the sum of all
	their history entries.
	=================================================*/
	function eventRecalc(){
		global $siteUser;
		if (!$this->HasPermission("Repair")) {
			return $this->setEventResult("You do not have permission to perform this action");
		}

		//both sql queries are the same:
		//Create a temporary table that contains a join between all players in all
		//tables within the guild that maps them between their awards and history.
		//Each row in the table contains the userid, tableid, and the sum of all the
		//awards they recieved
		//The outer query then uses the inner query to perform updates to the
		//dkp_points table.

		//recalculate dkp
		global $sql;
		$guildid = sql::Escape($this->guild->id);
		$sql->Query(   "UPDATE dkp_points,
							( SELECT dkp_pointhistory.user, dkp_awards.tableid, sum( dkp_awards.points ) AS points
					  		FROM dkp_pointhistory, dkp_awards
							WHERE dkp_awards.guild = '$guildid'
							AND dkp_awards.id = dkp_pointhistory.award
							GROUP BY dkp_pointhistory.user, dkp_awards.tableid
							) AS temp
						SET dkp_points.points = temp.points
						WHERE dkp_points.guild = '$guildid'
						AND dkp_points.tableid = temp.tableid
						AND dkp_points.user = temp.user");
		//recalculate lifetime
		$sql->Query(   "UPDATE dkp_points,
							( SELECT dkp_pointhistory.user, dkp_awards.tableid, sum( dkp_awards.points ) AS points
					  		FROM dkp_pointhistory, dkp_awards
							WHERE dkp_awards.guild = '$guildid'
							AND dkp_awards.points > 0
							AND dkp_awards.id = dkp_pointhistory.award
							GROUP BY dkp_pointhistory.user, dkp_awards.tableid
							) AS temp
						SET dkp_points.lifetime = temp.points
						WHERE dkp_points.guild = '$guildid'
						AND dkp_points.tableid = temp.tableid
						AND dkp_points.user = temp.user");

		//and done
		return $this->setEventResult(true, "DKP Recalculated");

	}
}
?>