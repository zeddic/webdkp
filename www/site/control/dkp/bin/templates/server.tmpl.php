<div class="editTabs">
	<ul>
		<li class="<?=($active=="guilds"?"selected":"")?>"><a href="<?=$siteRoot?>dkp/"><span>Guilds</span></a></li>
	</ul>
	<div class="editTabsUnderline"></div>
</div>

<table class="dkp" cellpadding=0 cellspacing=0 id="dkptable">
	<thead>
		<tr class="header">
			<th class="link"><a>guild</a></th>
			<th class="link center" style="width:100px"><a>faction</a></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<script type="text/javascript">
table = new GuildsTable("dkptable");
<?php foreach($guilds as $guild) { $guild->url = dkpUtil::GetGuildUrl($guild->id); ?>
table.Add(<?=(util::json($guild,false))?>);
<?php } ?>
table.Draw();
</script>

