<table class="dkp" cellpadding=0 cellspacing=0 id="dkptable">
	<thead>
		<tr class="header">
			<th class="link"><a>server</a></th>
			<th class="link center" style="width:100px" sort="number"><a>guilds</a></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<script type="text/javascript">
table = new ServerTable("dkptable");
<?php foreach($servers as $server) { ?>
table.Add(<?=(util::json($server,true))?>);
<?php } ?>
table.Draw();
</script>

