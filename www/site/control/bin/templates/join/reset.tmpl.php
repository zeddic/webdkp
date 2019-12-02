<?php if($resetok) { ?>

<div class="message">
Your password has now been reset! You can now <a href="<?=$siteRoot?>Login">Login</a>
with your new password.</div>
<br />
<br />
<input type="button" class="largeButton" onclick="document.location='<?=$siteRoot?>Login'" value="Login!">
<?php } else { ?>
To reset the password for <b><?=$user->username?></b>, please enter a new password
below.
<br />

<div class="roundedcornr_box">
<div class="roundedcornr_top"><div></div></div>
<div class="roundedcornr_content">


<form action="<?=$PHP_SELF?>" method="post" name="requestserver">
<input type="hidden" name="event" value="reset">
<input type="hidden" name="uid" value="<?=$user->id?>">
<input type="hidden" name="key" value="<?=$key?>">
<table class="signup">
<tr>
	<td class="label" style="width:175px">Password:</td>
	<td><input name="password" type="password"></td>
</tr>
<tr>
	<td class="label" style="width:175px">Retype Password:</td>
	<td><input name="password2" type="password"></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Reset Password!"></td>
</tr>
</table>

</form>

</div>
<div class="roundedcornr_bottom"><div></div></div>
</div>
<br />
<br />
<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>
<?php } ?>