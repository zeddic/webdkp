<?=$tabs?>
<?=$tableselect?>
<br />
<?php if(sizeof($awards)==0){ ?>
This player does not have any history in this table.
<?php } else { ?>
<span class="dkpSubtitle">History</span>
<table class="dkp" cellpadding=0 cellspacing=0 id="dkptable2">
<thead>
<tr class="header">
	<th class="link" sorttype="date" style="width:200px"><a>date</a></th>
	<th class="link" sorttype="award"><a>award</th>
	<th class="link center" sorttype="dkp" style="width:100px"><a>+</a></th>
	<th class="link center" sorttype="dkp" style="width:100px"><a>-</a></th>
	<th class="link center nosort" style="width:100px"><a>Total</a></th>
	<?php if($canedit){ ?>
	<th class="link center nosort" style="width:100px"><a>Actions</a></th>
	<?php } ?>
</tr>
</thead>
<tbody>
</tbody>
</table>

<script type="text/javascript">
table = new PlayerHistoryTable("dkptable2");
table.SetCanEdit(<?=($canedit?"1":"0")?>);
table.SetPlayerInfo("<?=$player->name?>", <?=$player->id?>, <?=$dkp?>);
table.SetPageData(<?=$page?>, <?=$maxpage?>);
table.SetSortData("<?=$sort?>", "<?=$order?>");
<?php foreach($awards as $entry) { ?>
table.Add(<?=(util::json($entry,true))?>);
<?php } ?>
table.Draw();
</script>
<?php } ?>

<br />
<br />
<?php if(sizeof($loot)>0){ ?>
<span class="dkpSubtitle">Loot</span>
<table class="dkp" cellpadding=0 cellspacing=0 id="dkptable">
<thead>
<tr class="header">
	<th class="link" style="width:200px"><a>date</a></th>
	<th class="link"><a>loot</th>
	<th class="link center" style="width:100px" sort="number"><a>cost</a></th>
</tr>
</thead>
<tbody>

</tbody>
</table>

<script type="text/javascript">
table = new PlayerLootTable("dkptable");
<?php foreach($loot as $entry) { ?>
table.Add(<?=(util::json($entry,true))?>);
<?php } ?>
table.Draw();
</script>
<br />
<br />
<?php } ?>