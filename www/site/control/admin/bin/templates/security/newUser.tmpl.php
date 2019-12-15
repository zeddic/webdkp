<form  action="<?=$PHP_SELFDIR?>users?v<?=$iid?>=users" method="post" name="newuser<?=$iid?>" style="display:inline">
<input type="hidden" name="event<?=$iid?>" value="addUser">

<table>
	<tr>
		<td width=100><b>username</b></td>
		<td><input id="username" type="text" name="username" style="width:125px" autofocus value="<?=($fillInUserData?util::getData("username"):"")?>"></td>
	</tr>
	<tr>
		<td><b>password</b></td>
		<td><input type="password" name="password" style="width:125px" value="<?=($fillInUserData?util::getData("password"):"")?>"></td>
	</tr>
	<tr>
		<td><b>retype pass</b></td>
		<td><input type="password" name="password2" style="width:125px"  value="<?=($fillInUserData?util::getData("password2"):"")?>"></td>
	</tr>
	<tr>
		<td><b>usergroup</b></td>
		<td>
		<select name="usergroup" style="width:133px">
			<?php foreach($userGroups as $userGroup){ ?>
			<?php if($userGroup->name != "Visitor"){ ?>
			<option value="<?=$userGroup->id?>" <?=($fillInUserData?(util::getData("usergroup")==$userGroup->id?"selected":""):"")?>><?=$userGroup->name?></option>
			<?php } ?>
			<?php } ?>
		</select>
		</td>
	</tr>
	<tr>
		<td><b>email</b></td>
		<td><input type="text" name="email" style="width:125px"  value="<?=($fillInUserData?util::getData("email"):"")?>"></td>
	</tr>

	<tr>
		<td colspan=2>
		<input type="submit" value="Submit">
		<!-- <a href="javascript:document.forms['newuser<?=$iid?>'].submit()">Submit</a> -->
		<?php if($showNewUser){ ?>
		<br />
		<br />
			<span style="color:red">Error: <?=$newUserString?></span>
		<?php } ?>
		</td>
	</tr>
</table>
</form>

<?php if(isset($newUserResult)) { ?>
	<?php if($newUserOk){ ?>
		<div class="message">
			<b>New user created</b>
		</div>
	<?php } else { ?>
		<div class="errorMessage">
			<b><?=$newUserString?></b>
		</div>
	<?php } ?>
<?php } ?>