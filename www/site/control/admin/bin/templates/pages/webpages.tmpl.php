
<table class="controltable" style="width:100%" cellspacing="0">
<caption><?=$title?></caption>
<thead>
<?php if(security::hasAccess("Create Page")){ ?>
<tr>
	<th colspan=3>
		<img src="<?=$directory?>images/newpage.png">
		<a href="javascript:Util.Hide('newFolder');Util.Toggle('newFile');Util.Focus('newFileName')">new page</a>
		&nbsp;&nbsp;
		<img src="<?=$directory?>images/newfolder.png">
		<a href="javascript:Util.Hide('newFile');Util.Toggle('newFolder');Util.Focus('newFolderName')">new folder</a>
		<div id="newFile" style="display:none;color:black">
			<form action="<?=$SiteRoot?>edit/newpage" method="post" style="display:inline">
			<input type="hidden" name="from" value="webpages">
			<input type="hidden" name="path" value="<?=$path?>">
			<input id="newFileName" name="newFileName" type="text">
			<input type="submit" value="create">
			</form>
		</div>
		<div id="newFolder" style="display:none;color:black;margin-left:100px">
			<form  action="<?=$PHP_SELF?>?path=<?=$path?>" method="post" style="display:inline">
			<input type="hidden" name="event" value="createFolder">
			<input id="newFolderName" name="newFolderName" type="text">
			<input type="submit" value="create">
			</form>
		</div>
	</th>
</tr>
<?php } ?>
</thead>
<tbody>
<?php if($path != "") {?>
<tr class="odd" style="height:45px">
	<td class="leftmost" style="width:30px;"><img src="<?=$directory?>images/upfolder.gif" ></td>
	<td width="300"><a href="<?=$PHP_SELF?>?path=<?=$uppath?>">Up Directory</a></td>
	<td class="rightmost"></td>
</tr>
<?php } ?>
<?php foreach($folders as $folder){ $i++; ?>
<tr class="<?=($i%2==0?'odd':'')?>"  style="height:45px">
	<td class="leftmost" style="width:30px;"><img src="<?=$directory?>images/folder.gif" ></td>
	<td ><a href="<?=$PHP_SELF?>?path=<?=$path?><?=$folder->relativeUrl?>"><?=$folder->relativeUrl?></a></td>
	<td class="rightmost">
	<?php if(security::hasAccess("Create Page") && $folder->isRealFolder && !$folder->hasChildren) { ?>
		<a href="<?=$PHP_SELF?>?path=<?=$path?>&event=deleteFolder&folderid=<?=$folder->id?>" >
		<img class="iconButton" src="<?=$directory?>images/delete.png" title="Delete"></a>
	<?php } else if(security::hasAccess("Create Page")) { ?>
		<img class="iconButton" src="<?=$directory?>images/lock.png" title="You must delete folder contents before you can delete the folder.">
	<?php } ?>
	</td>
</tr>
<?php } ?>
<?php foreach($webpages as $webpage) { $i++; ?>
<tr class="<?=($i%2==0?'odd':'')?>" style="height:45px">
	<td class="leftmost" style="width:30px;"><img src="<?=$directory?>images/<?=($webpage->isControlFile?"codefile":"file")?>.gif"></td>
	<td><a href="<?=$SiteRoot?><?=$webpage->url?>"><?=$webpage->relativeUrl?></a></td>
	<td class="rightmost">
		<?php if(security::hasAccess("Edit Page")) { ?>
			<a href="<?=$SiteRoot?>edit/<?=$webpage->id?>?returnto=webpages&path=<?=$path?>" >
			<img class="iconButton" src="<?=$directory?>images/edit.png" title="Edit Settings"></a>
		<?php } ?>
		<?php if(security::hasAccess("Create Page") && !$webpage->isControlFile) {?>
			<a href="<?=$PHP_SELF?>?path=<?=$path?>&event<?=$iid?>=deleteWebpage&pageid<?=$iid?>=<?=$webpage->id?>" onclick="return confirm('Are you sure that you want \nto delete this webpage?')">
			<img class="iconButton" src="<?=$directory?>images/delete.png" title="Delete"></a>
		<?php } else if(security::hasAccess("Create Page")){ ?>
			<img class="iconButton" src="<?=$directory?>images/lock.png" title="You can not delete code behind file.">
		<?php } ?>
	</td>
</tr>
<?php } ?>
</tbody>
</table>


<?php if(isset($eventResult)){?>
<script type="text/javascript">
window.onload = function(){ Util.Flash("FormSaved",10000) };
</script>
<?php } ?>


