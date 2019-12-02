<?=$tabs?>
<?=$tableselect?>
<br />
<?php if(sizeof($awards) == 0 ) { ?>
There are no awards in this table.
<?php } else { ?>
<table class="dkp" cellpadding=0 cellspacing=0 id="dkptable">
<thead>
<tr class="header">
	<th class="link" sorttype="award" ><a>award</a></th>
	<th class="link center" sorttype="dkp"  style="width:100px"><a>dkp</th>
	<th class="link center" sorttype="players" style="width:100px" sort="number"><a>players</a></th>
	<th class="link center" sorttype="date" style="width:220px" sort="number"><a>date</a></th>
	<?php if($canedit){ ?>
	<th class="link center nosort" style="width:80px"><a>Actions</a></th>
	<?php } ?>
</tr>
</thead>
<tbody>

</tbody>
</table>

<script type="text/javascript">
table = new AwardTable("dkptable");
table.SetCanEdit(<?=($canedit?"1":"0")?>);
table.SetPageData(<?=$page?>, <?=$maxpage?>);
table.SetSortData("<?=$sort?>", "<?=$order?>");
<?php foreach($awards as $award) { ?>
table.Add(<?=(util::json($award,true))?>);
<?php } ?>
table.Draw();
</script>

<br />
<?php } ?>
