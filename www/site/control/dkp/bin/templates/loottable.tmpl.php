<?=$tabs?>

<?=$lootselect?>

<br />
<br />

<?php foreach($loottable->sections as $section) { ?>
<b><?=$section->name?></b>
<table class="dkp" id="section<?=$section->id?>" cellpadding=0 cellspacing=0 >
	<thead>
		<tr class="header">
			<th class="link" style="width:350px"><a>Name</a></th>
			<th class="link center" style="width:100px" sort="number"><a>Cost</a></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<script type="text/javascript">
table<?=$section->id?> = new ViewLootTable("section<?=$section->id?>");
<?php foreach($section->loot as $item) { ?>
table<?=$section->id?>.Add(<?=(util::json($item,false))?>);
<?php } ?>
table<?=$section->id?>.Draw();
</script>
<br />
<?php } ?>