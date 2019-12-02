<?=$tabs?>
<?=$sidebar?>
<div class="adminContents">

<br />

<form action="<?=$baseurl?>Admin/UpdateGuild" method="post" name="updateGuild">
<input type="hidden" name="event" value="updateGuild">
<div style="float:left;padding-top:10px;"><img src="<?=$siteRoot?>images/dkp/guild.gif"></div>
<div style="margin-left:70px">
<table class="dkpForm" >
<tr>
	<td colspan=2 class="title">Change Guild Details</td>
</tr>
<tr>
	<td class="label" style="width:180px">Name:</td>
	<td><input name="name" type="text" value="<?=$guild->name?>" ></td>
</tr>
<tr>
	<td class="label">Faction:</td>
	<td>
		<select name="faction">
			<option value="Alliance" <?=($guild->faction=="Alliance"?"selected":"")?>>Alliance</option>
			<option value="Horde" <?=($guild->faction=="Horde"?"selected":"")?>>Horde</option>
		</select>
	</td>
</tr>
<tr>
	<td class="label">Server:</td>
	<td>
		<select name="server">
			<option value="0"><?=$guild->server?></option>
			<?php foreach($servers as $server) { ?>
			<option value="<?=$server->id?>"><?=$server->name?></option>
			<?php } ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan=2><input type="submit" value="Save Changes"></td>
</tr>
</table>
<?php if(isset($eventResult)){ ?>
<div style="width:375px" class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>
</div>
</form>

</div>