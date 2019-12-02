<?=$tabs?>

<table class="dkp sortable" cellpadding=0 cellspacing=0 id="dkptable">
<thead>
<tr class="header">
	<th class="link"><a>name</a></th>
	<th class="link center" style="width:100px"><a>class</th>
	<th class="link center" style="width:100px"><a>dkp</a></th>
	<th class="link center" style="width:100px"><a>lifetime</a></th>
</tr>
</thead>
<tbody>
<?php /*foreach($table->table as $entry) { ?>
<tr>
	<td><?=$entry->user->name?></td>
	<td class="center"><img src="<?=$SiteRoot?>images/classes/small/<?=$entry->user->class?>.gif"></td>
	<td class="center"><?=$entry->points?></td>
	<td class="center"><?=$entry->lifetime?></td>
	<td class="center">
		<img src="<?=$directory?>images/add.png">
		<img src="<?=$directory?>images/subtract.png">
	</td>
</tr>
<?php } */ ?>
</tbody>
</table>

<form  action="<?=$PHP_SELF?>" method="post" id="addUser" style="display:inline">
<input type="hidden" name="event" value="addUser">
<input type="text" name="playername">

<select name="class" id="newPlayerClass" style="width:100px" >
	<option>Druid</option>
	<option>Hunter</option>
	<option>Mage</option>
	<option>Rogue</option>
	<option>Shaman</option>
	<option>Paladin</option>
	<option>Priest</option>
	<option>Warrior</option>
	<option>Warlock</option>
</select>

<br />
<br />

<div id="output">

</div>

<script type="text/javascript">
<?php foreach($table->table as $entry) { ?>
DkpList.AddPoints(<?=(util::json($entry,true))?>);
<?php } ?>
DkpList.DisplayTable();
</script>


<input type="submit" value="test" class="formInput">


</form>


<a href="form.html" class="lbOn">Email This</a>
