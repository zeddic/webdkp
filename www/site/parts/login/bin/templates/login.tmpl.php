<form  action="<?=$PHP_SELF?>" method="post" name="login<?=$iid?>" style="display:inline">
<input type="hidden" name="siteUserEvent" value="login" />
<table>
<tr>
	<td class="formLabel"  style="vertical-align:middle;width: 120px;">username</td>
	<td><input class="formInput" name="username" id="username<?=$iid?>" type="text" style="margin-bottom:2px" tabindex=1
	 	 value="<?=(isset($loginResult) && $loginResult!=user::LOGIN_BADUSER)?util::getData("username"):""?>">
	</td>
</tr>
<tr>
	<td class="formLabel" style="vertical-align:middle">password</td>
	<td><input class="formInput" name="password" id="password<?=$iid?>" type="password" tabindex=2></td>
</tr>
<tr>
	<td colspan=2>
	<br />
	<input type="submit" class="mediumButton" value="Sign In">
	</td>
</tr>
</table>
</form>

<?php if(isset($loginError)){ ?>
<div class="errorMessage"><?=$loginError?></div>
<script type="text/javascript">
	<?php if($loginResult==user::LOGIN_BADUSER){ ?>
	Util.Focus("username<?=$iid?>");
	<?php } else if($loginResult==user::LOGIN_BADPASSWORD) { ?>
	Util.Focus("password<?=$iid?>");
	<?php } ?>
</script>
<?php } else if($grabFocus) {?>
<script type="text/javascript">
	Util.Focus("username<?=$iid?>");
</script>
<?php } ?>