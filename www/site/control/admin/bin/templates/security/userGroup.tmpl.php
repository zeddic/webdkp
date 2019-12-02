

<form  action="<?=$PHP_SELFDIR?>usergroups/group/<?=$userGroup->id?>" method="post" name="usergroup" style="display:inline">
<input type="hidden" name="event<?=$iid?>" value="editUserGroup">
<table class="controltable" style="width:420px">
<caption>Edit Group <?=$userGroup->name?></caption>
<tbody>
	<tr>
		<td class="leftmost" style="width:125px"><b>group name</b></td>
		<td><input type="text" name="groupname" value="<?=$userGroup->name?>" style="width:200px" onkeypress="Util.SubmitIfEnter('usergroup',event)"></td>
	</tr>
	<tr>
		<td class="leftmost"><b>visitor group</b></td>
		<td ><input type="checkbox" name="visitor" <?=($userGroup->visitor?"checked":"")?> style="border:0px"></td>
	</tr>
	<tr>
		<td class="leftmost"><b>default group</b></td>
		<td><input type="checkbox" name="default" <?=($userGroup->default?"checked":"")?>  style="border:0px"></td>
	</tr>
	<tr>
		<td colspan=2">
			<input type="submit" value="Save Changes" class="mediumButton">
			<input type="button" value="Back" onclick="document.location='<?=$SiteRoot?>admin/usergroups'" class="mediumButton">

		</td>
	</tr>
</tbody>
</table>
<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>" style="width:389px"><b><?=$eventMessage?></b></div>
<?php } ?>
</form>
