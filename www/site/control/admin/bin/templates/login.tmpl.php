<?php if($loggedin){ ?>
	<?php if(security::hasAccess("Control Panel")) { ?>
	<div class="message" style="width:270px">You are now logged in. </div>
	<br /> <input type="button" class="mediumButton" value="Goto Control Panel" onclick="document.location='<?=$SiteRoot?>admin'">
	<?php } else { ?>
	You're account does not have permission to view the control panel.
	<br />
	<br />
	<input type="button" class="mediumButton" value="Logout" onclick="document.location='<?=$SiteRoot?>admin/login?siteUserEvent=logout'">
	<input type="button" class="mediumButton" value="Return to Site" onclick="document.location='<?=$SiteRoot?>'">
	<?php } ?>
<?php } else { ?>

<form  action="<?=$PHP_SELF?>" method="post" name="login" style="display:inline">
<input type="hidden" name="siteUserEvent" value="login" />
<table>
<tr>
	<td class="formLabel"  style="vertical-align:middle;width: 120px;">username</td>
	<td><input class="formInput" name="username" id="username" type="text" style="margin-bottom:2px"
	 	 value="<?=(isset($loginResult) && $loginResult!=user::LOGIN_BADUSER)?util::getData("username"):""?>"></td>
</tr>
<tr>
	<td class="formLabel" style="vertical-align:middle">password</td>
	<td><input class="formInput" name="password" id="password" type="password"></td>
</tr>
<tr>
	<td colspan=2>
	<br />
	<input type="submit" class="mediumButton" value="Log In">
	</td>
</tr>
</table>
</form>

<?php if(isset($loginError)){ ?>
<div class="errorMessage" style="width:270px"><?=$loginError?></div>
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

<?php } ?>

