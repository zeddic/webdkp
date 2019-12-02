<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<br />

Here you can edit information for an individual loot table.
Individual loot tables are made up of different <i>sections</i>.
Each of these different sections ussually represents different raid bosses and
the items that they drop. However, you create organize your loot table
however you wish. If you do not wish to group your items by sections, you can
create a single section and place all items within it.
<br />
<br />
<input type="button" class="largeButton" value="Back" onclick="document.location='<?=$baseurl?>Admin/LootTable'">
<br />
<br />
<a href="javascript:Util.Toggle('renameTable')"><img src="<?=$siteRoot?>images/buttons/edit.png" style="vertical-align:text-bottom"> Rename Table</a>

<div id="renameTable" style="display:none">
<form action="<?=$baseurl?>Admin/EditLootTable/<?=$table->id?>" method="post" name="editLootTable">
<input type="hidden" name="event" value="updateTable">
<table class="dkpForm" >
<tr>
	<td><input name="name" type="text" value="<?=$table->name?>" style="width:200px" ></td>
</tr>
<tr>
	<td colspan=2><input type="submit" value="Rename" style="width:211px" onclick="this.value='Saving...';this.disable()"></td>
</tr>
</table>
</form>
</div>
<br />
<a href="javascript:Util.Toggle('createSection')"><img src="<?=$siteRoot?>images/buttons/edit.png" style="vertical-align:text-bottom"> Create New Section</a>
<div id="createSection" style="display:none">
<form action="<?=$baseurl?>Admin/EditLootTable/<?=$table->id?>" method="post" name="editLootTable">
<input type="hidden" name="event" value="createSection">
<table class="dkpForm" >
<tr>
	<td><input name="name" type="text" value="" style="width:200px" ></td>
</tr>
<tr>
	<td colspan=2><input type="submit" value="Create" style="width:211px" onclick="this.value='Creating...';this.disable()"></td>
</tr>
</table>
</form>
</div>

<br />

<?php if(isset($eventResult)){ ?>
<br />
<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>


<br />

<?php if(sizeof($table->sections)==0 ) { ?>
<i>This table does not have any sections yet. Please create a section above.</i>
<?php } ?>
<?php foreach($table->sections as $section) { ?>
<b><?=$section->name?></b> ( <a href="javascript:Util.Toggle('RenameSection<?=$section->id?>')">Rename</a> |
<a href="<?=$baseurl?>Admin/EditLootTable/<?=$table->id?>?event=deleteSection&id=<?=$section->id?>" onclick="return confirm('Delete section? All loot inside it wil be lost!')">Delete</a> )

<div id="RenameSection<?=$section->id?>" style="display:none">
<form action="<?=$baseurl?>Admin/EditLootTable/<?=$table->id?>" method="post" name="editLootTable">
<input type="hidden" name="event" value="renameSection">
<input type="hidden" name="id" value="<?=$section->id?>">
<table class="dkpForm" >
<tr>
	<td><input name="name" type="text" value="<?=$section->name?>" style="width:200px" ></td>
</tr>
<tr>
	<td colspan=2><input type="submit" value="Rename" style="width:211px" onclick="this.value='Saving...';this.disable()"></td>
</tr>
</table>
</form>
</div>

<table class="dkp" id="section<?=$section->id?>" cellpadding=0 cellspacing=0 >
	<thead>
	<tr class="header">
		<th class="link"><a>Name</a></th>
		<th class="link center" style="width:150px"><a>Cost</a></th>
		<th class="link center nosort"><a>Action</a></th>
	</tr>
	</thead>
	<tbody>
	</tbody>
</table>


<script type="text/javascript">
table<?=$section->id?> = new EditLootTable("section<?=$section->id?>");
table<?=$section->id?>.SetDetails(<?=$table->id?>, <?=$section->id?>);
<?php foreach($section->loot as $item) { ?>
table<?=$section->id?>.Add(<?=(util::json($item))?>);
<?php } ?>
table<?=$section->id?>.Draw();
</script>
<br />
<?php } ?>


<br />
<br />
<br />
<br />


</div>
