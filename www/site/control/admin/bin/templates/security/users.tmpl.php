<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>"><b><?=$eventMessage?></b></div>
<?php } ?>
<table class="controltable" style="width:100%" cellspacing=0>
<caption>Users</caption>
<thead>
<tr>
	<th class="padleft" style="width:100px">
		<a href="<?=$PHP_SELFDIR?>users/1/username/<?=($sort=="username" && $sortType=="asc"?"desc":"asc")?>">User Account</a>
		<?=($sort=="username"?"<img style='vertical-align:middle' src='".$directory."images/sort$sortType.gif'>":"")?>
	</th>
	<th style="width:100px">
		<a href="<?=$PHP_SELFDIR?>users/1/gname/<?=($sort=="gname"&&$sortType=="asc"?"desc":"asc")?>" >Guild</a>
		<?=($sort=="gname"?"<img style='vertical-align:middle' src='".$directory."images/sort$sortType.gif'>":"")?>
	</th>
	<th style="width:100px">
		<a href="<?=$PHP_SELFDIR?>users/1/gserver/<?=($sort=="gserver"&&$sortType=="asc"?"desc":"asc")?>" >Server</a>
		<?=($sort=="gserver"?"<img style='vertical-align:middle' src='".$directory."images/sort$sortType.gif'>":"")?>
	</th>
	<th style="width:100px">
		<a href="<?=$PHP_SELFDIR?>users/1/email/<?=($sort=="email"&&$sortType=="asc"?"desc":"asc")?>" >Email</a>
		<?=($sort=="email"?"<img style='vertical-align:middle' src='".$directory."images/sort$sortType.gif'>":"")?>
	</th>
	<th style="width:100px">
		<a href="<?=$PHP_SELFDIR?>users/1/usergroup/<?=($sort=="usergroup"&&$sortType=="asc"?"desc":"asc")?>" >Group</a>
		<?=($sort=="usergroup"?"<img style='vertical-align:middle' src='".$directory."images/sort$sortType.gif'>":"")?>
	</th>
	<th style="width:200px" class="right">&nbsp;</th>
</tr>
</thead>
<tbody>
<?php foreach($users as $user) { $i++; ?>
<tr class="<?=($i%2==0?'odd':'')?>">
	<td class="padleft"><a href="<?=$PHP_SELFDIR?>users/user/<?=$user->id?>"><?=$user->username?></a></td>
	<td><a href="<?=$user->guildurl?>" target="guild"><?=$user->guildname?></a></td>
	<td><a href="<?=$PHP_SELFDIR?>users/user/<?=$user->id?>"><?=$user->servername?></a></td>
	<td><?=$user->email?></td>
	<td><?=$user->usergroup->name?></td>
	<td class="right padright" style="width:115px">
		<a href="<?=$PHP_SELFDIR?>users/user/<?=$user->id?>" >
			<img class="iconButton" src="<?=$directory?>images/edit.png" title="Edit User"></a>
		<a href="<?=$PHP_SELFDIR?>users?event=deleteUser&userid=<?=$user->id?>" onclick="return confirm('Are you sure that you want \nto delete this user?')">
			<img class="iconButton" src="<?=$directory?>images/delete.png" title="Delete User"></a>
		<a href="<?=$PHP_SELFDIR?>users?event=deleteUserAll&userid=<?=$user->id?>" onclick="return confirm('Are you sure that you want \nto delete all data associated with this user?')">
			<img class="iconButton" src="<?=$directory?>images/trash_can.png" title="Delete User And All Associated Data."></a>
	</td>
</tr>
<?php } ?>
</tbody>
<tfoot>
	<tr>
		<td colspan=4></td>
	</tr>
</tfoot>
</table>



<div style="width:100%"><span style="float:right"><?=$pageLinks?></span><?=$pageText?></div>





<!-- <?php if($search){?>
<script type="text/javascript">
window.onload = function(){ $('user_search_input').value='<?=$search?>';$('user_search_input').focus() };
</script>
<?php } ?> -->

