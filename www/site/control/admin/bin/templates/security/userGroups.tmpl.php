<form  action="<?=$PHP_SELFDIR?>usergroups" method="post" name="newgroup" style="display:inline">
<input type="hidden" id="eventGroups" name="event<?=$iid?>" value="addUserGroup">
<table class="controltable" style="width:550px" cellspacing=0>
<caption>User Groups</caption>
<thead>
<tr>
	<th class="leftmost" style="width:150px">User Group</th>
	<th style="text-align:center" title="The group that unregistered visitors will be placed in">Visitor</th>
	<th style="text-align:center" title="The group that new registered users will be placed in">Default</th>
	<th class="rightmost">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($userGroups as $userGroup) { $i++; ?>
<tr class="<?=($i%2==0?'odd':'')?>">
	<td class="leftmost">
		<?php if($userGroup->system){ ?>
			<?=$userGroup->name?>
		<?php } else { ?>
			<a href="<?=$PHP_SELFDIR?>usergroups/group/<?=$userGroup->id?>"><?=$userGroup->name?></a>
		<?php } ?>
	</td>
	<td style="text-align:center"><input type="radio" style="border:0px" name="visitor" value="<?=$userGroup->id?>" <?=($userGroup->visitor?"checked":"")?> onclick="$('eventGroups').value='updateGroups';Util.Submit('newgroup')"></td>
	<td style="text-align:center"><input type="radio" style="border:0px" name="default" value="<?=$userGroup->id?>" <?=($userGroup->default?"checked":"")?> onclick="$('eventGroups').value='updateGroups';Util.Submit('newgroup')"></td>
	<td class="rightmost" >
		<?php if($userGroup->system) { ?>
		<img class="iconButton" src="<?=$directory?>images/lock.png" title="Protected System User Group - Cannot Delete">
		<?php } else { ?>
		<a href="<?=$PHP_SELFDIR?>usergroups/group/<?=$userGroup->id?>" >
			<img class="iconButton" src="<?=$directory?>images/edit.png" title="Edit Group"></a>
		<a href="<?=$PHP_SELFDIR?>usergroups?event<?=$iid?>=deleteUserGroup&groupid=<?=$userGroup->id?>" onclick="return confirm('Are you sure that you want \nto delete this user group?')">
			<img class="iconButton" src="<?=$directory?>images/delete.png" title="Delete Group"></a>

		<?php } ?>
	</td>
</tr>
<?php } ?>
<tr>
	<td class="leftmost"><input type="text" style="width:150px" name="newGroup"></td>
	<td align=center></td>
	<td align=center></td>
	<td class="rightmost"><a href="javascript:Util.Submit('newgroup')">Add New Group</a></td>
</tr>
</tbody>
</table>
</form>

<?php if(isset($eventResult)) { ?>
<div id="UserGroupsMessage" class="<?=($eventResult?"message":"errorMessage")?>" style="width:519px"><b><?=$eventMessage?></b></div>
<script type="text/javascript">
</script>
<?php } ?>