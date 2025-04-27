<?=$tabs?>
<br />
<?php if(empty($award->id)){ ?>
<br />
Invalid Award ID. Could not load award information from the database.
<?php } else if(!$canedit) { ?>
You do not have permission to edit awards.
<br />
<br />
<input type="button" class="largeButton" value="Back" onclick="document.location='<?=$backurl?>'">
<?php } else { ?>

<form action="<?=$baseurl?>Admin/EditAward/<?=$award->id?>" method="post" name="editaward">
<input type="hidden" name="event" value="updateAward">
<input type="hidden" name="backurl" value="<?=$backurl?>">
<input type="hidden" name="edittype" value="<?=$edittype?>">

<table class="dkpForm">
<tr>
	<td class="label"  style="width:170px"><b><?=($award->foritem?"Item Name":"Reason")?></b></td>
	<td><input type="text" name="reason" value="<?=$award->reason?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label"><b><?=($award->foritem == 1?"Cost":"Points")?></b></td>
	<td><input type="text" name="points" value="<?=($award->foritem == 1?$award->points*-1:$award->points)?>" style="width:250px">
		<?=($award->foritem == 1 ? "( Positive Number )" : "")?>
	</td>
</tr>
<?php if($award->foritem == 1) { ?>
<tr>
	<td class="label"><b>Awarded To</b></td>
	<td>
	<select name="player" style="width:260px">
	<?php foreach($players as $temp){ ?>
		<option value="<?=$temp["id"]?>" <?=($temp["id"]==$player->id?"selected":"")?>><?=$temp["name"]?></option>
	<?php } ?>
	</select>
	</td>
</tr>
<?php } ?>
<tr>
	<td class="label"><b>Table</b></td>
	<td>
	<select name="awardtable" style="width:260px">
	<?php foreach($awardtables as $table) { ?>
		<option value="<?=$table->tableid?>" <?=($award->tableid == $table->tableid?"selected":"")?>><?=$table->name?></option>
	<?php } ?>
	</select>
	</td>
</tr>
<tr>
	<td class="label"><b>Award Type</b></td>
	<td>
	<select name="foritem" style="width:260px">
		<option value="0">Regular Award</option>
		<option value="1" <?=($award->foritem==1?"selected":"")?>>Item Award</option>
	</select>
	</td>
</tr>
<tr>
	<td  class="label"><b>Location</b></td>
	<td><input type="text" name="location" value="<?=$award->location?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label"><b>Awarded By</b></td>
	<td><input type="text" name="awardedby" value="<?=$award->awardedby?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label"><b>Date</b></td>
	<td><?=$award->dateDate?> - <?=$award->dateTime?></td>
</tr>
<tr>
	<td class="label"><b># of Players</b></td>
	<td><?=$award->playercount?></td>
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

<?php if($award->foritem == 0) { ?>
<table class="dkp" id="selecttable" cellpadding=0 cellspacing=0 >
	<thead>
	<tr class="header">
		<th class="link nosort" colspan=5><a>Receiving Players</a></th>
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

<?php } ?>

<br />
<br />