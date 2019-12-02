<form  action="<?=$PHP_SELFDIR?>permissions" method="post" name="permissions<?=$iid?>" style="display:inline">
<input type="hidden" name="event" value="updatePermissions">
<table class="controltable" cellspacing=0=0>
<caption>Permissions</caption>
<thead>
<tr>
	<th>&nbsp;</th>
	<?php foreach($userGroups as $userGroup){ ?>
	<th style="width:100px;text-align:center" ><?=$userGroup->name?></th>
	<?php } ?>
</tr>
</thead>
<tbody>
<?php foreach($permissions as $category => $permissionGroup) { ?>
<tr class="highlight">
	<td class="leftmost" colspan="<?=(1+count($userGroups))?>"><b><?=$category?></b></td>
</tr>
<?php foreach($permissionGroup as $permission){ $i++; ?>
<tr class="<?=($i%2==0?'odd':'')?>">
	<td class="leftmost"><?=$permission->name?></td>
	<?php foreach($userGroups as $userGroup){ ?>
	<td style="text-align:center"><input type="checkbox" name="permissions_<?=$userGroup->id?>[]" value="<?=$permission->id?>" style="border:0px" <?=($userGroup->hasPermissionId($permission->id)?"checked":"")?>></td>
	<?php } ?>
</tr>
<?php } ?>
<?php } ?>
</tbody>
</table>

<input type="submit" value="Save Changes" class="mediumButton" style="width:170px">
<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>" style="width:139px"><b><?=$eventMessage?></b></div>
<?php } ?>
</form>
