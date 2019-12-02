<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<?php if(isset($eventResult)){ ?>
<div style="margin-left:70px;" class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>
<br />


<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/tables.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">DKP Tables</div>

	<form action="<?=$baseurl?>Admin/DkpTables" method="post" name="createTable">
	<input type="hidden" name="event" value="createTable">
	<table class="dkp simpletable" id="tablelist" cellpadding="0" cellspacing="0">
		<tr class="header">
			<th>Table Name</th>
			<th class="center">ID</th>
			<th class="center">Action</th>
		</tr>
		<?php foreach($tables as $table) { ?>
		<tr>
			<td><?=$table->name?></td>
			<td class="center" style="width:100px"><?=$table->tableid?></td>
			<td class="center middle" style="width:150px">

				<a class="dkpbutton" href="<?=$baseurl?>Admin/EditDkpTable?id=<?=$table->tableid?>"><img title="Edit Account" src="<?=$siteRoot?>images/buttons/edit.png"></a>
				<a class="dkpbutton" href="<?=$baseurl?>Admin/DeleteTable?id=<?=$table->tableid?>"
				onclick="return confirm('Are you sure that you want to delete this table?\nALL DATA WILL BE LOST!')">
				<img title="Delete Account" src="<?=$siteRoot?>images/buttons/delete.png"></a>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan=2>
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

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/info.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">About DKP Tables</div>
	<b>You should read this! Or you might lose data :(</b>
	<br />
	Here you can view a list of all your guild's DKP Tables. Your guild can have many tables,
	which can help make your DKP easier to manage. For example, some guilds keep seperate
	tables for 5 and 25 man runs while others keep seperate tables for each type
	of raid. When you create multiple tables, a drop down will appear both on the site
	and in the in-game addon that allows you to select what table you wish to work with.
	<br />
	<br />
	From this page you can edit, delete, and create new DKP Tables. Be <b>very careful</b>
	when you delete tables. If you delete a table, you delete all the information in it to!

</div>



</div>
