<?=$tabs?>
<?=$sidebar?>
<div class="adminContents">

Here you can set 'alts', short for alternative characters. These represent other
players that are used by the same person. Adding this information is optional. If
you are using the Alt & Main DKP Sharing option, however, this information will be
used to transfer all DKP to the main account.
<br />
<?php if( $player->isMain() && sizeof($player->alts) == 0 ) { ?>
<div class="noticeMessage">This player is a main and has no alts. To add alts, use the table below.</div>
<?php }  ?>
<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>
<br />
<input type="button" class="mediumButton" onclick="document.location='<?=$baseurl?>Admin/Manage'" value="Back">
<br />
<br />
<?php if($player->isMain()) { ?>
<form action="<?=$baseurl?>Admin/PlayerAlts?player=<?=$player->id?>" method="post" name="playeralts">
<input type="hidden" name="event" value="addAlt">
<table class="dkp" id="alttable">
	<tr class="header">
		<th>Player</th>
		<th class="center" style="width:200px">Action</th>
	</tr>
	<tr>
		<td>
		<select name="alt" style="width:400px">
		<?php foreach($players as $temp) { ?>
			<option value="<?=$temp->id?>"><?=$temp->name?></option>
		<?php } ?>
		</select>
		</td>
		<td class="center">
			<a href="javascript:Util.Submit('playeralts')">Add Alt
			<img title="Delete Account" src="<?=$siteRoot?>images/buttons/new.png"></a>
		</td>
	</tr>
	<?php foreach($player->alts as $alt) { ?>
	<tr>
		<td>
			<a href="<?=$baseurl?>Admin/PlayerAlts?player=<?=$alt->id?>"><?=$alt->name?></a>
		</td>
		<td class="center middle">
			<a class="dkpbutton" href="<?=$baseurl?>Admin/PlayerAlts?player=<?=$player->id?>&event=deleteAlt&alt=<?=$alt->id?>">
			<img title="Delete Account" src="<?=$siteRoot?>images/buttons/delete.png"></a>
		</td>
	</tr>
	<?php } ?>
</table>
</form>
<script type="text/javascript">
table = new DKPTable("alttable");
table.DrawSimple();
</script>

<?php } else {  ?>
<div class="noticeMessage">
This player is an alt to <a href="<?=$baseurl?>Admin/PlayerAlts?player=<?=$player->mainUser->id?>"><?=$player->mainUser->name?></a>
</div>
<br />
<input type="button" class="largeButton" value="Unlink From <?=$player->mainUser->name?>" onclick="document.location='<?=$baseurl?>Admin/PlayerAlts?player=<?=$player->id?>&event=unlink'">
<input type="button" class="largeButton" value="Make <?=$player->name?> the Main" onclick="document.location='<?=$baseurl?>Admin/PlayerAlts?player=<?=$player->id?>&event=makeMain'">
<?php } ?>







<br />
<br />
</div>
