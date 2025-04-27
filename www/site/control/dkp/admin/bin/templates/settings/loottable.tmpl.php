<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/loot.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Loot Table -
	<?php if($settings->GetLootTableEnabled()){ ?>
	<span style="color:green">Enabled</span>
	<?php } else { ?>
	<span style="color:red">Disabled</span> (Enable in Settings)
	<?php } ?>
	</div>
	Here you can manage your guild's loot table. Your loot table allows you to set
	costs for items dropped in raids. The costs that you create here will be sent
	and used by the in-game addon to fill in item costs when loot drops.
	<br />
	The loot table is divided into <i><b>subtables</b></i>,
	with each subtable having multiple <i><b>sections</b></i>. Ussually each subtable is
	dedicated to a particular raid instance while the sections are dedicted to the
	different raid bosses in that instance. If you don't want to bother with
	subtables and sections you can also just create a single subtable and place your
	entire loot table in one section.

	<br />
	<br />

	<?php if(isset($eventResult)){ ?>
	<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
	<br />
	<?php } ?>

	<form action="<?=$baseurl?>Admin/LootTable" method="post" name="createTable">
	<input type="hidden" name="event" value="createTable">
	<table class="dkp simpletable" id="tablelist" cellpadding=0 cellspacing=0 >
		<tr class="header">
			<th>Sub-Table</th>
			<th class="center">Action</th>
		</tr>
		<?php foreach($tables as $table) { ?>
		<tr>
			<td><a href="<?=$baseurl?>Admin/EditLootTable/<?=$table->id?>"><?=$table->name?></a></td>
			<td class="center middle" style="width:150px">
				<a class="dkpbutton" href="<?=$baseurl?>Admin/EditLootTable/<?=$table->id?>"><img title="Edit Table" src="<?=$siteRoot?>images/buttons/edit.png"></a>
				<a class="dkpbutton" href="<?=$baseurl?>Admin/LootTable?event=deleteTable&id=<?=$table->id?>"
				onclick="return confirm('Are you sure that you want to delete this loot table?\nALL DATA WILL BE LOST!')">
				<img title="Delete Account" src="<?=$siteRoot?>images/buttons/delete.png"></a>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td>
				<input name="name" type="text" style="width:300px">
			</td>
			<td class="center">
				<img src="<?=$siteRoot?>images/buttons/new.png">
				<a href="javascript:Util.Submit('createTable')">Create Table</a>
			</td>
		</tr>
	</table>
	</form>

</div>

<br />
<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/loot.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Download Loot Table File</div>
	Below you can download a copy of your loot table, in tab seperated format.
	You can use this file as backup of your loot table or easily edit it in your
	favorite spreadsheet program. You can later upload your loot table file from
	this same page.
	<br />
	<br />
	<input type="button" class="largeButton" value="Download"
	onclick="document.location='<?=$baseurl?>Admin/LootTable?event=download'">
</div>
<br />
<br />
<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/loot.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Upload Loot Table File</div>
	Here you can upload a new loot table file. These files will append new subtables
	and information to your table. You can use this to restore a backup or to
	add a new loot table that you downloaded from the forums. Uploading files will
	only append information to your current table or update the cost of items already
	in your table. It will <b>never</b> delete items.
	<br />
	<br />

	<form name="uploadLog" enctype="multipart/form-data"  action="<?=$baseurl?>Admin/LootTable?event=upload" method="post">
	<input type='hidden' name='event' value='uploadLog'>
	<input type="file" name="userfile" class="formInput" >
	<input type="submit" value="Upload" class="mediumButton" onclick="this.value='Uploading...'">
	</form>
	<br />

	<a href="javascript:Util.Toggle('fileFormat')"><img src="<?=$siteRoot?>images/buttons/help.png" style="vertical-align:text-bottom"> File Format >></a>
	<div id="fileFormat" style="display:none">
	Loot table files are in <b>Tab-Delimited</b> format. You can create this format with
	your favorite Spreadsheet program, such as Excel. There are at most 4 columns in the file:
	<b>Subtable</b>, <b>Section</b>, <b>Item Name</b>, and <b>Item Cost</b>.
	Each row in the file represents a single item
	in your loot table. It is possible to leave out either the
	Subtable column, Section column, or both, in which case all entries will be put
	into a single Subtable and section. To simpify writing loot table files, it is
	not neccessary to enter values for the Subtable and Section column for each
	loot table entry. If left empty, the same Subtable and Section will be assumed
	from the previous row.
	<br />
	<br />
	Below you can download sample files that more clearly explain the loot table
	file format:
	<br />
	<br />
	<b>Regular File Format</b>
	<br />
	<img src="<?=$SiteRoot?>images/icons/textfile.png" style="vertical-align:text-bottom">
	<a href="<?=$SiteRoot?>files/loot/samples/loot_example_full.txt">Tab File Format</a>
	<br />
	<img src="<?=$SiteRoot?>images/icons/excelfile.png" style="vertical-align:text-bottom">
	<a href="<?=$SiteRoot?>files/loot/samples/loot_example_full.xls" class="tooltip" tooltip="Excel files must be converted to Tab-Delimited before uploading">Excel Format</a>
	<br />
	<br />
	<b>Missing Section</b>
	<br />
	<img src="<?=$SiteRoot?>images/icons/textfile.png" style="vertical-align:text-bottom">
	<a href="<?=$SiteRoot?>files/loot/samples/loot_example_2.txt">Tab File Format</a>
	<br />
	<img src="<?=$SiteRoot?>images/icons/excelfile.png" style="vertical-align:text-bottom">
	<a href="<?=$SiteRoot?>files/loot/samples/loot_example_2.xls" class="tooltip" tooltip="Excel files must be converted to Tab-Delimited before uploading">Excel Format</a>
	<br />
	<br />

	<b>Missing Subtable and Section</b>
	<br />
	<img src="<?=$SiteRoot?>images/icons/textfile.png" style="vertical-align:text-bottom">
	<a href="<?=$SiteRoot?>files/loot/samples/loot_example_3.txt">Tab File Format</a>
	<br />
	<img src="<?=$SiteRoot?>images/icons/excelfile.png" style="vertical-align:text-bottom">
	<a href="<?=$SiteRoot?>files/loot/samples/loot_example_3.xls" class="tooltip" tooltip="Excel files must be converted to Tab-Delimited before uploading">Excel Format</a>
	<br />
	<br />

	</div>
	<br />
	<br />
</div>

<br />
<br />
<br />
<br />

</div>
