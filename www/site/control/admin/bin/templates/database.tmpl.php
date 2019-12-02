<body>
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
			<a href="<?=$PHP_SELFDIR?>databasefunctions?event=TallyGuildPerServer" onclick="return confirm('Are you sure that you want \nto re-tally how many guilds each server has?')">
			<img class="iconButton" src="<?=$directory?>images/calculator.png" title="Re-Tally the number of guilds per server."></a></td>
			<td valign="top"><br>
			This function will go through and tally up all of the guilds 
			each server has which can be viewed on the Browse page. <br>

&nbsp;</td>
		</tr>
	</table>
</div>

<div align="left">
	<table border="0" width="800" cellspacing="0" cellpadding="0" id="table3">
		<tr>
			<td width="80" align = "center"><br>
			<a href="<?=$PHP_SELFDIR?>databasefunctions?event=BlackBoxCode" onclick="return confirm('Are you sure that you want \nto run the custom code?')">
			<img class="iconButton" src="<?=$directory?>images/blackbox.jpg" title="Used to run one time functions."></a></td>
			<td valign="top"><br>
			Used to run custom functions. Simply edit the "BlackBoxCode" function
			inside of the databasefunctions.php file. This is useful for running clean up stuff. <br>

&nbsp;</td>
		</tr>
	</table>
</div>

<div align="left">
	<table border="0" width="800" cellspacing="0" cellpadding="0" id="table4">
		<tr>
			<td width="80" align = "center"><br>
			<a href="<?=$PHP_SELFDIR?>databasefunctions?event=CleanDKPSettingsCode" onclick="return confirm('Are you sure that you want \nto clean dkp_settings?')">
			<img class="iconButton" src="<?=$directory?>images/broom.png" title="Compares dkp_settings to dkp_guilds and removes junk data from dkp_settings."></a></td>
			<td valign="top"><br>
			Used to clean dkp_settings. Checks dkp_settings against dkp_guilds to 
			see if there is guild data where the guild was removed from dkp_guilds.<br>

&nbsp;</td>
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

</body>