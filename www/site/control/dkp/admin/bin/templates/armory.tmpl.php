<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<br />
Here you can synchronize your DKP table with your guild roster from the
<a href="http://www.wowarmory.com/">World of Warcraft Armory</a>. This will
make sure everyone in you're guild appears in your table. If a player
isn't in the table yet, they are added automatically with 0 DKP.
<br />
<br />
Below you must enter your guild's name and server, exactly as it is used on The Armory.
This can be different then the guild and server you used when you registered
with WebDKP.
<br />
<br />
<form action="<?=$baseurl?>Admin/Armory" method="post" name="armory">
<input type="hidden" name="event" value="sync">
<table class="dkpForm" >
<tr>
	<td class="label" style="width:180px">Guild:</td>
	<td><input name="guild" type="text" value="<?=$guild->name?>" ></td>
</tr>
<tr>
	<td class="label" style="width:180px">Server:</td>
	<td><input name="server" type="text" value="<?=$guild->server?>" ></td>
</tr>
<tr>
	<td class="label" style="width:180px">Min Player Level:</td>
	<td><input name="level" type="text" value="70"></td>
</tr>
<tr>
	<td class="label">Sync With:</td>
	<td>
		<select name="table">
			<option value="0">All DKP Tables</option>
			<?php foreach($dkptables as $table) { ?>
			<option value="<?=$table->tableid?>"><?=$table->name?></option>
			<?php } ?>
		</select>
	</td>
</tr>
<tr>
	<td class="label" style="width:180px">Armory Server:</td>
	<td>
		<select name="wowserver">
			<option value="<?=armory::AMERICAN?>">American</option>
			<option value="<?=armory::EURO?>">European</option>
		</select>
	</td>
</tr>
<tr>
	<td colspan=2><input type="submit" value="Load Roster!"></td>
</tr>
</table>
<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>

</form>

<br />
<br />
<br />

</div>
