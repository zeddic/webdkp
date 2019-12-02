<form  action="<?=$PHP_SELFDIR?>users/user/<?=$user->id?>" method="post" name="userform" style="display:inline">
<input type="hidden" name="event<?=$iid?>" value="editUser">
<table class="controltable" style="width:420px">
<caption>Edit User <?=$user->username?></caption>
<tbody>
	<tr>
		<td class="leftmost" style="width:125px"><b>user name</b></td>
		<td><input type="text" name="username" value="<?=$user->username?>" style="width:200px"  onkeypress="Util.SubmitIfEnter('userform',event)"></td>
	</tr>
	<tr>
		<td class="leftmost"><b>email</b></td>
		<td ><input type="text" name="email" value="<?=$user->email?>" style="width:200px"  onkeypress="Util.SubmitIfEnter('userform',event)"></td>
	</tr>
	<tr>
		<td class="leftmost"><b>usergroup</b></td>
		<td>
			<select name="usergroup" style="width:208px">
				<?php foreach($userGroups as $userGroup){ ?>
				<option value="<?=$userGroup->id?>" <?=($user->usergroup->id==$userGroup->id?"selected":"")?>><?=$userGroup->name?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="leftmost"><b>first name</b></td>
		<td><input type="text" name="firstname" value="<?=$user->firstname?>" style="width:200px"  onkeypress="Util.SubmitIfEnter('userform',event)"></td>
	</tr>
	<tr>
		<td class="leftmost"><b>last name</b></td>
		<td><input type="text" name="lastname" value="<?=$user->lastname?>" style="width:200px"  onkeypress="Util.SubmitIfEnter('userform',event)"></td>
	</tr>
	<tr>
		<td class="leftmost"><b>registered date</b></td>
		<td><?=$user->registerdateDate?> - <?=$user->registerdateTime?></td>
	</tr>
	<tr>
		<td class="leftmost"><b>password reset</b></td>
		<td>
			<a href="javascript:Util.Toggle('password_reset','password')">reset</a>
		</td>
	</tr>
	<tr>
		<td colspan=2">
			<input type="submit" value="Save Changes" class="mediumButton">
			<input type="button" value="Back" onclick="document.location='<?=$SiteRoot?>admin/users'" class="mediumButton">
		</td>
	</tr>
</tbody>
</table>
</form>


<form  action="<?=$PHP_SELFDIR?>users/user/<?=$user->id?>" method="post" name="resetpassword" style="display:inline">
<input type="hidden" name="event" value="resetUserPassword">
<table class="controltable" style="width:420px;display:none" id="password_reset">
<caption>Password Reset</caption>
<tbody>
	<tr>
		<td class="leftmost" style="width:125px"><b>new password</b></td>
		<td><input id="password" type="password" name="password" style="width:200px" onkeypress="Util.SubmitIfEnter('resetpassword',event)"></td>
	</tr>
	<tr >
		<td class="leftmost"><b>retype password</b></td>
		<td><input type="password" name="password2" style="width:200px" onkeypress="Util.SubmitIfEnter('resetpassword',event)"></td>
	</tr>
	<tr>
		<td colspan=2"><input type="submit" value="Submit" class="mediumButton"></td>
	</tr>
</tbody>
</table>
</form>

<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>" style="width:389px"><b><?=$eventMessage?></b></div>
<?php } ?>


