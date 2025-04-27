<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">


<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/edit.gif"></div>
<div class="adminSection" style="padding-left:2px">

	<form action="<?=$baseurl?>Admin/EditDkpTable?id=<?=$table->tableid?>" method="post" name="updateTable">
	<input type="hidden" name="event" value="updateTable">
	<table class="dkp simpletable" id="tablelist" cellpadding="0" cellspacing="0">
		<tr class="header">
			<th colspan=2>Edit <?=$table->name?></th>
		</tr>
		<tr>
			<td style="width:100px">Name</td>
			<td><input class="paddedInput" type="text" name="name" value="<?=$table->name?>" style="width:300px"></td>
		</tr>
		<tr>
			<td style="width:100px">Tableid</td>
			<td><input class="paddedInput" type="text" name="newtableid" value="<?=$table->tableid?>" style="width:300px"></td>
		</tr>
		<tr>
			<td></td>
			<td >
			<input type="submit" class="largeButton" value="Save Changes" onclick="this.value='Saving...';">
			<input type="button" class="largeButton" value="Back" onclick="document.location='<?=$baseurl?>Admin/DkpTables'">
			</td>
		</tr>
	</table>
	</form>
	<?php if(isset($eventResult)){ ?>
	<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
	<?php } ?>
</div>


</div>
