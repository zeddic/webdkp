<?=$tabs?>
<br />
<?php if(empty($fromAward->id) || empty($toAward->id)){ ?>
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

<form action="<?=$baseurl?>Admin/EditAward/<?=$fromAward->id?>" method="post" name="editaward">
<input type="hidden" name="event" value="updateAward">
<input type="hidden" name="backurl" value="<?=$backurl?>">
<input type="hidden" name="edittype" value="<?=$edittype?>">

<table class="dkpForm">
<tr>
	<td class="label"  style="width:170px"><b>Reason</b></td>
	<td><?=$fromAward->reason?></td>
</tr>
<tr>
	<td class="label"><b>Points Transfered</b></td>
	<td><input type="text" name="points" value="<?=($toAward->points)?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label"><b>Transfered From</b></td>
	<td>
	<select name="fromplayer" style="width:260px">
	<?php foreach($players as $temp){ ?>
		<option value="<?=$temp["id"]?>" <?=($temp["id"]==$fromAward->player->id?"selected":"")?>><?=$temp["name"]?></option>
	<?php } ?>
	</select>
	</td>
</tr>
<tr>
	<td class="label"><b>Transfered To</b></td>
	<td>
	<select name="toplayer" style="width:260px">
	<?php foreach($players as $temp){ ?>
		<option value="<?=$temp["id"]?>" <?=($temp["id"]==$toAward->player->id?"selected":"")?>><?=$temp["name"]?></option>
	<?php } ?>
	</select>
	</td>
</tr>

<tr>
	<td class="label"><b>Table</b></td>
	<td>
	<select name="awardtable" style="width:260px">
	<?php foreach($awardtables as $table) { ?>
		<option value="<?=$table->tableid?>" <?=($toAward->tableid == $table->tableid?"selected":"")?>><?=$table->name?></option>
	<?php } ?>
	</select>
	</td>
</tr>
<tr>
	<td class="label"><b>Awarded By</b></td>
	<td><input type="text" name="awardedby" value="<?=$toAward->awardedby?>" style="width:250px"></td>
</tr>
<tr>
	<td class="label"><b>Date</b></td>
	<td><?=$toAward->dateDate?> - <?=$toAward->dateTime?></td>
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

<?php } ?>

<br />
<br />