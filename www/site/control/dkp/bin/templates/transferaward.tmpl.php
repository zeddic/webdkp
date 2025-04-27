<?=$tabs?>
<?php if(empty($toAward->id)){ ?>
Invalid Award ID. Could not load award information from the database.
<?php } else { ?>
<br />
<table class="dkpForm">
<tr>
	<td><b>Reason</b></td>
	<td><?=$toAward->reason?></td>
</tr>
<tr>
	<td style="width:120px"><b>Points</b></td>
	<td><?=$toAward->points?></td>
</tr>
<tr>
	<td style="width:120px"><b>To</b></td>
	<td><?=$toAward->player->name?></td>
</tr>
<tr>
	<td style="width:120px"><b>From</b></td>
	<td><?=$fromAward->player->name?></td>
</tr>
<tr>
	<td><b>Location</b></td>
	<td><?=$toAward->location?></td>
</tr>
<tr>
	<td><b>Date</b></td>
	<td><?=$toAward->dateDate?> - <?=$toAward->dateTime?></td>
</tr>
<tr>
	<td><b>Awarded By</b></td>
	<td><?=$toAward->awardedby?></td>
</tr>
<?php if($canedit) { ?>
<tr>
	<td colspan=2>
		<input type="button" class="largeButton" value="Edit Award" onclick="document.location='<?=$baseurl?>/Admin/EditAward/<?=$toAward->id?>?b=e&aid=<?=$toAward->id?>'" style="width:100px">
	</td>
</tr>
<?php } ?>
</table>
<br />
<?php } ?>

<br />