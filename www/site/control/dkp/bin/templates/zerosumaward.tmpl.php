<?=$tabs?>
<?php if(empty($auto->id)){ ?>
Invalid Award ID. Could not load award information from the database.
<?php } else { ?>
<table class="dkpForm">
<tr>
	<td class="" style="width:170px"><b>Item Name</b></td>
	<td><?=$root->reason?></td>
</tr>
<tr>
	<td class="" ><b>Cost</b></td>
	<td><?=$root->points*-1?></td>
</tr>
<tr>
	<td class=""><b>Awarded To</b></td>
	<td><?=$root->player->name?></td>
</tr>
<tr>
	<td  class=""><b>Location</b></td>
	<td><?=$root->location?></td>
</tr>
<tr>
	<td class=""><b>Awarded By</b></td>
	<td><?=$root->awardedby?></td>
</tr>
<tr>
	<td class=""><b>Date</b></td>
	<td><?=$root->dateDate?> - <?=$root->dateTime?></td>
</tr>
<tr>
	<td class=""><b>Zerosum Players</b></td>
	<td><?=$auto->playercount?></td>
</tr>
<tr>
	<td class="" style="width:120px"><b>Zerosum Award</b></td>
	<td>+<?=$auto->points?></td>
</tr>
<?php if($canedit) { ?>
<tr>
	<td colspan=2>
		<input type="button" class="largeButton" value="Edit Award" onclick="document.location='<?=$baseurl?>Admin/EditAward/<?=$root->id?>?b=e&aid=<?=$root->id?>'" style="width:100px">
	</td>
</tr>
<?php } ?>
</table>
<br />
<?php if(sizeof($auto->players) > 0 ) { ?>
<table class="dkp simpletable" cellpadding=0 cellspacing=0 id="table">
<thead>
<tr class="header">
	<th class="link nosort" colspan=5><a>Zerosum Players</a></th>
</tr>
</thead>
<tbody>
	<tr>
	<?php
	$i = 0;
	foreach($auto->players as $player) {
		if( $i%5 == 0 && $i != 0) {
			echo("</tr>\r\n</tr>");
		}
	 ?>
	<td style="width:200px">
		<a href="<?=$baseurl?>/Player/<?=$player->name?>"><?=$player->name?></a>
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