<form  action="<?=$PHP_SELF?>" method="post" name="login<?=$iid?>" style="display:inline">
<input type="hidden" name="siteUserEvent" value="login" />
<b>username</b> <br />
<input class="formInput" name="username" id="username<?=$iid?>" type="text" style="margin-bottom:2px;font-size:100%;width:95%"
	 	 value="<?=(isset($loginResult) && $loginResult!=user::LOGIN_BADUSER)?util::getData("username"):""?>">
<br />
<b>password</b>	<br />
<input class="formInput" name="password" id="password<?=$iid?>" style="margin-bottom:2px;font-size:100%;width:95%" type="password">
<br />
<input type="submit" class="mediumButton" value="Sign In">

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

