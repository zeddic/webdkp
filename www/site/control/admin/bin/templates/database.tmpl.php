<br /><b>
Database Management Functions:<br />
Here you can run functions that will help manage the database.
</b><br />
<br />

<div align="left">
	<table border="0" width="800" cellspacing="0" cellpadding="0" id="table1">
		<tr>
			<td width="80" align = "center"><br>
			<a href="<?=$PHP_SELFDIR?>databasefunctions?event=PurgeBlankGuilds" onclick="return confirm('Are you sure that you want \nto purge all guilds with no data?')">
			<img class="iconButton" src="<?=$directory?>images/trash_canpurge.png" title="Check all guilds and delete any that have no data in use."></a></td>
			<td valign="top"><br>
			This function will go through and check every guild to see if 
			they have any pointhistory data in dkp_pointhistory. <br>
			If there is no data then the entire account is deleted.<br>
			Click the image to the left to run the function.<br>
&nbsp;</td>
		</tr>
	</table>
</div>

<div align="left">
	<table border="0" width="800" cellspacing="0" cellpadding="0" id="table2">
		<tr>
			<td width="80" align = "center"><br>
				<a href="<?=$PHP_SELFDIR?>databasefunctions?event=UpdateServerTotals" onclick="return confirm('Are you sure that you want \nto re-tally how many guilds each server has?')">
					<img class="iconButton" src="<?=$directory?>images/calculator.png" title="Re-Tally the number of guilds per server.">
				</a>
			</td>
			<td valign="top">
				<br>
				This function will go through and tally up all of the guilds 
				each server has which can be viewed on the Browse page.
				<br>
				<?php if($updateServerTotalsLog != "") { ?>
					<div class="message"><?=$updateServerTotalsLog?></div>
				<?php } ?>
      </td>
		</tr>
	</table>
</div>

<div align="left">
	<table border="0" width="800" cellspacing="0" cellpadding="0" id="table5">
		<tr>
			<td width="80" align = "center"><br>
			<a href="<?=$PHP_SELFDIR?>databasefunctions?event=UserCleanCode" onclick="return confirm('Are you sure that you want \nto clean dkp_users?')">
			<img class="iconButton" src="<?=$directory?>images/user_databasefunctions.jpg" title="Used to clean dkp_users."></a></td>
			<td valign="top"><br>
			Used to clean dkp_users. Checks dkp_users against dkp_guilds to 
			see if the user is leftover from a deleted guild.<br>

&nbsp;</td>
		</tr>
	</table>
</div>

<div align="left">
	<table border="0" width="800" cellspacing="0" cellpadding="0" id="table5">
		<tr>
			<td width="80" align = "center"><br>
				<img class="iconButton" src="<?=$directory?>images/trash_canpurge.png	" title="Used to deleted old guilds and data">
			</td>
			<td valign="top">
				<br>
				Finds guilds where nobody has signed in for 5+ years and the deletes the guilds, users, and all associated data.<br>
				<a href="<?=$PHP_SELFDIR?>databasefunctions?event=deleteOldData&dryrun=1">DryRun</a> |
				<a href="<?=$PHP_SELFDIR?>databasefunctions?event=deleteOldData" onclick="return confirm('Are you sure that you want to delete old guilds?')"> Real Delete</a>
				<?php if($deleteLog != "") { ?>
					<div class="message"><?=$deleteLog?></div>
				<?php } ?>
				<br>
      </td>
		</tr>
	</table>
</div>

<div align="left">
	<table border="0" width="800" cellspacing="0" cellpadding="0" id="table4">
		<tr>
			<td width="80" align = "center"><br>
			<img class="iconButton" src="<?=$directory?>images/broom.png" title="Compares dkp_settings to dkp_guilds and removes junk data from dkp_settings."></td>
			<td valign="top"><br>
			   Deletes any servers without guilds.<br>
			   <a href="<?=$PHP_SELFDIR?>databasefunctions?event=DeleteEmptyServers&dryrun=1">DryRun</a> |
			   <a href="<?=$PHP_SELFDIR?>databasefunctions?event=DeleteEmptyServers" onclick="return confirm('Are you sure that you want to clean dkp_settings?')">Real Delete</a>
				<?php if($deleteServersLog != "") { ?>
					<div class="message"><?=$deleteServersLog?></div>
				<?php } ?>
      </td>
		</tr>
	</table>
</div>

<div align="left">
	<table border="0" width="800" cellspacing="0" cellpadding="0" id="table6">
		<tr>
			<td width="80" align="center"><br>
				<img class="iconButton" src="<?=$directory?>images/trash_canpurge.png" title="Deletes guilds and users matching known bot/spam patterns">
			</td>
			<td valign="top">
				<br>
				Finds and deletes guilds with spam names (pharma SEO, BTC, etc.) and users with known bot email domains or injection patterns in their username.<br>
				<a href="<?=$PHP_SELFDIR?>databasefunctions?event=deleteBadContent&dryrun=1">DryRun</a> |
				<a href="<?=$PHP_SELFDIR?>databasefunctions?event=deleteBadContent" onclick="return confirm('Are you sure you want to delete bad content?')">Real Delete</a>
				<?php if($deleteBadContentLog != "") { ?>
					<div class="message"><?=$deleteBadContentLog?></div>
				<?php } ?>
				<br>
			</td>
		</tr>
	</table>
</div>
