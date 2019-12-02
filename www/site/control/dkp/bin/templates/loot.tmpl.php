<?=$tabs?>
<?=$tableselect?>
<br />
<?php if(sizeof($loot) == 0 ) { ?>
There is no loot in this table.
<?php } else { ?>
<table class="dkp" cellpadding=0 cellspacing=0 id="dkptable">
<thead>
<tr class="header">
	<th class="link" sorttype="award"><a>loot</a></th>
	<th class="link center" style="width:100px" sorttype="dkp"><a>dkp</th>
	<th class="link center" style="width:150px" sorttype="player"><a>player</a></th>
	<th class="link center" style="width:220px" sorttype="date"><a>date</a></th>
	<?php if($canedit){ ?>
	<th class="link center nosort" style="width:100px"><a>Actions</a></th>
	<?php } ?>
</tr>
</thead>
<tbody>

</tbody>
</table>

<script type="text/javascript">
table = new LootTable("dkptable");
table.SetCanEdit(<?=($canedit?"1":"0")?>);
table.SetPageData(<?=$page?>, <?=$maxpage?>);
table.SetSortData("<?=$sort?>", "<?=$order?>");
<?php foreach($loot as $entry) { ?>
table.Add(<?=(util::json($entry,false))?>);
<?php } ?>
table.Draw();
</script>

<br />
<?php } ?>