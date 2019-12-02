<?php if($siteUser->visitor){ ?>
<div class="roundedcornr_box">
<div class="roundedcornr_top"><div></div></div>
<div class="roundedcornr_content">


<form action="<?=$PHP_SELF?>" method="post" name="signup">
<input type="hidden" name="siteUserEvent" value="login" />

<table class="signup">
<tr>
	<td class="label">Username:</td>
	<td>
		<input id="username" name="username" type="text" value="<?=(isset($loginResult) && $loginResult!=user::LOGIN_BADUSER)?util::getData("username"):""?>" tabindex=1>
		<a href="<?=$siteRoot?>Join">Need an account?</a>
	</td>
</tr>
<tr>
	<td class="label">Password:</td>
	<td>
		<input id="password" name="password" type="password"  tabindex=2>
		<a href="<?=$siteRoot?>Forgot">Forgot your password?</a>
	</td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Login"></td>
</tr>
</table>

</form>

</div>
<div class="roundedcornr_bottom"><div></div></div>
</div>
<br />

<?php if(isset($loginError)){ ?>
<div class="errorMessage"><?=$loginError?></div>
<script type="text/javascript">
	<?php if($loginResult==user::LOGIN_BADUSER){ ?>
	Util.Focus("username");
	<?php } else if($loginResult==user::LOGIN_BADPASSWORD) { ?>
	Util.Focus("password");
	<?php } ?>
</script>
<?php } else {?>
<script type="text/javascript">
	Util.Focus("username");
</script>
<?php } ?>

<?php } else { ?>
Welcome <?=$siteUser->username?>. You are now logged in.
<br />
<br />
<a href="<?=dkpUtil::GetGuildUrl($siteUser->guild)?>">View your DKP Table</a>
<br />
<br />
<a href="<?=dkpUtil::GetGuildUrl($siteUser->guild)?>Admin">Goto your Control Panel</a>
<?php } ?>



