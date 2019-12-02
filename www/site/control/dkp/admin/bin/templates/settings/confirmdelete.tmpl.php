<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/info.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Warning!</div>

	You are about to delete your DKP Table named '<b><?=$table->name?></b>'. If you delete this table, ALL
	DATA IN IT WILL BE DESTROYED.
	<br />
	<br />Do you wish continue?

	<br />
	<br />

	<form action="<?=$baseurl?>Admin/DkpTables" method="post">
	<input type="hidden" name="event" value="deleteTable">
	<input type="hidden" name="id" value="<?=$table->tableid?>">
	<input type="button" class="largeButton" value="Cancel" onclick="document.location='<?=$baseurl?>Admin/DkpTables'">
	<input type="submit" class="largeButton" value="Destroy It!" onclick="return confirm('Last Chance... are you sure?')">
	</form>

</div>

<br />
<br />



</div>
