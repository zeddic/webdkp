<?=$tabs?>
<br />
<?php if(empty($root->id)){ ?>
Invalid Award ID. Could not load award information from the database.
<br />
<br />
<input type="button" class="largeButton" value="Back" onclick="document.location='<?=$backurl?>'">
<?php } else if(!$canedit) { ?>
You do not have permission to edit awards.
<br />
<br />
<input type="button" class="largeButton" value="Back" onclick="document.location='<?=$backurl?>'">
<?php } else { ?>

<form action="<?=$baseurl?>Admin/EditAward/<?=$root->id?>" method="post" name="editaward">
<input type="hidden" name="event" value="updateAward">
<input type="hidden" name="backurl" value="<?=$backurl?>">
<input type="hidden" name="edittype" value="<?=$edittype?>">

<table class="dkpForm">
<tr>
	<td class="label" style="width:170px"><b>Item Name</b></td>
	<td><input type="text" name="reason" value="<?=$root->reason?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label" ><b>Cost</b></td>
	<td><input type="text" name="points" value="<?=$root->points*-1?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label"><b>Awarded To</b></td>
	<td>
	<select name="player" style="width:260px">
	<?php foreach($players as $temp){ ?>
		<option value="<?=$temp["id"]?>" <?=($temp["id"]==$root->player->id?"selected":"")?>><?=$temp["name"]?></option>
	<?php } ?>
	</select>
	</td>
</tr>
<tr>
	<td class="label"><b>Table</b></td>
	<td>
	<select name="awardtable" style="width:260px">
	<?php foreach($awardtables as $table) { ?>
		<option value="<?=$table->tableid?>" <?=($root->tableid == $table->tableid?"selected":"")?>><?=$table->name?></option>
	<?php } ?>
	</select>
	</td>
</tr>
<tr>
	<td  class="label"><b>Location</b></td>
	<td><input type="text" name="location" value="<?=$root->location?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label"><b>Awarded By</b></td>
	<td><input type="text" name="awardedby" value="<?=$root->awardedby?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label"><b>Date</b></td>
	<td><?=$root->dateDate?> - <?=$root->dateTime?></td>
</tr>
<tr>
	<td class="label"><b>Zerosum Players</b></td>
	<td><?=$auto->playercount?></td>
</tr>
<tr>
	<td class="label" style="width:120px"><b>Zerosum Award</b></td>
	<td>+<?=$auto->points?></td>
</tr>
<tr>
	<td></td>
	<td>
		<input type="button" class="largeButton" value="Back" onclick="document.location='<?=$backurl?>'" style="width:100px">
		<input type="button" class="largeButton" value="Save Changes" onclick="Util.Submit('editaward')"  style="width:160px">
	</td>
</tr>
<tr>
	<td colspan=2>
	<?php if(isset($eventResult)){ ?>
	<div style="width:413px" class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
	<?php } ?>
	</td>
</tr>
</table>
<br />

<table class="dkp" id="selecttable" cellpadding=0 cellspacing=0 >
	<thead>
	<tr class="header">
		<th class="link" colspan=5><a>Zerosum Players</a></th>
	</tr>
	</thead>
	<tbody>
	</tbody>
</table>
<script type="text/javascript">
playertable = new CheckPlayerTable("selecttable");
<?php foreach($players as $player) { ?>
playertable.Add(<?=(util::json($player))?>);
<?php } ?>
playertable.Draw();
</script>
</form>


<?php } ?>

<br />
<br />