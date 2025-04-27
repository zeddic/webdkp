<?=$tabs?>
<?php if(empty($award->id)){ ?>
Invalid Award ID. Could not load award information from the database.
<?php } else { ?>
<table class="dkpForm">
<tr>
	<td><b><?=($award->foritem?"Item Name":"Reason")?></b></td>
	<td><?=($award->foritem == 1?$award->points*-1:$award->points)?></td>
</tr>
<tr>
	<td style="width:120px"><b><?=($award->foritem?"Item Name":"Reason")?></b></td>
	<td><?=$award->reason?></td>
</tr>
<?php if($award->foritem == 1) { ?>
<tr>
	<td><b>Awarded To</b></td>
	<td><?=$award->player->name?></td>
</tr>
<?php } ?>
<tr>
	<td><b>Location</b></td>
	<td><?=$award->location?></td>
</tr>
<tr>
	<td><b>Date</b></td>
	<td><?=$award->dateDate?> - <?=$award->dateTime?></td>
</tr>
<tr>
	<td><b>Awarded By</b></td>
	<td><?=$award->awardedby?></td>
</tr>
<?php if($award->foritem == 0) { ?>
<tr>
	<td><b># of Players</b></td>
	<td><?=$award->playercount?></td>
</tr>
<?php } ?>
<?php if($canedit) { ?>
<tr>
	<td colspan=2>
		<input type="button" class="largeButton" value="Edit Award" onclick="document.location='<?=$baseurl?>/Admin/EditAward/<?=$award->id?>?b=e&aid=<?=$award->id?>'" style="width:100px">
	</td>
</tr>
<?php } ?>
</table>
<br />
<?php if(sizeof($award->players) > 0 && $award->foritem == 0 ) { ?>
<table class="dkp simpletable" cellpadding=0 cellspacing=0 id="table">
<thead>
<tr class="header">
	<th class="link nosort" colspan=5><a>Players</a></th>
</tr>
</thead>
<tbody>
	<tr>
	<?php
	$i = 0;
	foreach($award->players as $player) {
		if( $i%5 == 0 && $i != 0) {
			echo("</tr>\r\n</tr>");
		}
	 ?>
	<td style="width:200px">
		<a href="<?=$baseurl?>/Player/<?=str_replace(" ", "+", $player->name)?>"><?=$player->name?></a>
	</td>
	<?php $i++;	} ?>
	<?php while($i%5!=0) {echo("<td style='width:200px'></td>\r\n"); $i++; } ?>
	</tr>
</tbody>
</table>
<?php } ?>

<?php } ?>

<br />
<br />